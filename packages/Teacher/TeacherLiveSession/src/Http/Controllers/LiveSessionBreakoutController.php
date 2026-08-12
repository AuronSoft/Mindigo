<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionBreakoutAssignment;
use Mindigo\TeacherLiveSession\Models\LiveSessionBreakoutRoom;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Models\LiveSessionSignal;
use Mindigo\TeacherLiveSession\Services\LiveSessionAccessService;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;

final class LiveSessionBreakoutController extends Controller
{
    public function __construct(
        private readonly LiveSessionJoinTokenService $tokens,
        private readonly LiveSessionAccessService $access,
        private readonly AuditLogService $audit,
    ) {}

    public function sync(Request $request, LiveSession $liveSession): JsonResponse
    {
        $participant = $this->participant($request, $liveSession);
        $this->closeExpiredRooms($liveSession);
        $participant->refresh();
        $rooms = $liveSession->breakoutRooms()->whereIn('status', ['draft', 'open'])
            ->with(['assignments.participant.user:id,name'])->orderBy('position')->get();

        return response()->json([
            'can_moderate' => $this->access->canModerate($liveSession, $request->user()),
            'current_room_id' => $participant->breakout_room_id,
            'rooms' => $rooms->map(fn (LiveSessionBreakoutRoom $room) => [
                'id' => $room->id,
                'name' => $room->name,
                'status' => $room->status,
                'closes_at' => $room->closes_at?->toIso8601String(),
                'members' => $room->assignments->whereNull('left_at')->map(fn (LiveSessionBreakoutAssignment $assignment) => [
                    'participant_id' => $assignment->participant_id,
                    'user_id' => $assignment->participant?->user_id,
                    'name' => $assignment->participant?->user?->name,
                    'joined' => (int) $assignment->participant?->breakout_room_id === (int) $room->id,
                ])->values(),
            ]),
        ]);
    }

    public function store(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->moderator($request, $liveSession);
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'room_count' => ['required', 'integer', 'min:2', 'max:20'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'auto_assign' => ['required', 'boolean'],
        ]);
        $rooms = DB::transaction(function () use ($liveSession, $request, $data) {
            $lockedSession = LiveSession::query()->lockForUpdate()->findOrFail($liveSession->id);
            abort_if($lockedSession->breakoutRooms()->whereIn('status', ['draft', 'open'])->exists(), 409);
            $sequence = (int) $lockedSession->breakoutRooms()->max('position');
            $rooms = collect(range(1, $data['room_count']))->map(function () use ($lockedSession, $request, $data, &$sequence) {
                $sequence++;

                return $lockedSession->breakoutRooms()->create([
                    'created_by' => $request->user()->id,
                    'name' => __('teacher-live-session::app.breakout_room_name', ['number' => $sequence]),
                    'position' => $sequence,
                    'duration_minutes' => $data['duration_minutes'],
                ]);
            });

            if ($data['auto_assign']) {
                $participants = $lockedSession->participants()->where('admission_status', ParticipantAdmissionStatus::Admitted->value)
                    ->where('role', LiveParticipantRole::Student->value)->orderBy('id')->get();
                foreach ($participants as $index => $participant) {
                    LiveSessionBreakoutAssignment::query()->create([
                        'breakout_room_id' => $rooms[$index % $rooms->count()]->id,
                        'participant_id' => $participant->id,
                        'assigned_by' => $request->user()->id,
                    ]);
                }
            }

            return $rooms;
        });
        $roomIds = $rooms->pluck('id')->all();
        $this->audit->record('breakout_rooms_created', 'teacher_live_session', metadata: ['room_ids' => $roomIds], auditable: $liveSession, user: $request->user());

        return response()->json(['room_ids' => $roomIds], 201);
    }

    public function assign(Request $request, LiveSession $liveSession, LiveSessionBreakoutRoom $room): JsonResponse
    {
        $this->moderator($request, $liveSession);
        $this->roomBelongsToSession($room, $liveSession);
        $data = $request->validate(['token' => ['required', 'string', 'max:4096'], 'participant_id' => ['required', 'integer']]);
        $participant = $liveSession->participants()->whereKey($data['participant_id'])->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->firstOrFail();
        abort_if($participant->role?->canModerate(), 422);

        DB::transaction(function () use ($participant, $room, $request) {
            LiveSessionBreakoutAssignment::query()->where('participant_id', $participant->id)->whereNull('left_at')->update(['left_at' => now()]);
            LiveSessionBreakoutAssignment::query()->updateOrCreate(
                ['breakout_room_id' => $room->id, 'participant_id' => $participant->id],
                ['assigned_by' => $request->user()->id, 'joined_at' => $room->status === 'open' ? now() : null, 'left_at' => null],
            );
            $participant->update(['breakout_room_id' => $room->status === 'open' ? $room->id : null]);
            LiveSessionSignal::query()->where('live_session_id', $room->live_session_id)
                ->where(fn ($query) => $query->where('sender_id', $participant->user_id)->orWhere('recipient_id', $participant->user_id))->delete();
        });

        return response()->json([], 202);
    }

    public function open(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->moderator($request, $liveSession);
        DB::transaction(function () use ($liveSession) {
            $rooms = $liveSession->breakoutRooms()->where('status', 'draft')->get();
            abort_if($rooms->isEmpty(), 422);
            foreach ($rooms as $room) {
                $room->update(['status' => 'open', 'opened_at' => now(), 'closes_at' => now()->addMinutes($room->duration_minutes)]);
                $participantIds = $room->assignments()->whereNull('left_at')->pluck('participant_id');
                LiveSessionParticipant::query()->whereIn('id', $participantIds)->update(['breakout_room_id' => $room->id]);
                $room->assignments()->whereNull('left_at')->update(['joined_at' => now()]);
            }
            LiveSessionSignal::query()->where('live_session_id', $liveSession->id)->delete();
        });
        $this->audit->record('breakout_rooms_opened', 'teacher_live_session', auditable: $liveSession, user: $request->user());

        return response()->json([], 202);
    }

    public function close(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->moderator($request, $liveSession);
        DB::transaction(function () use ($liveSession) {
            $roomIds = $liveSession->breakoutRooms()->whereIn('status', ['draft', 'open'])->pluck('id');
            abort_if($roomIds->isEmpty(), 422);
            LiveSessionParticipant::query()->where('live_session_id', $liveSession->id)->whereIn('breakout_room_id', $roomIds)->update(['breakout_room_id' => null]);
            LiveSessionBreakoutAssignment::query()->whereIn('breakout_room_id', $roomIds)->whereNull('left_at')->update(['left_at' => now()]);
            LiveSessionBreakoutRoom::query()->whereIn('id', $roomIds)->update(['status' => 'closed', 'closed_at' => now()]);
            LiveSessionSignal::query()->where('live_session_id', $liveSession->id)->delete();
        });
        $this->audit->record('breakout_rooms_closed', 'teacher_live_session', auditable: $liveSession, user: $request->user());

        return response()->json([], 202);
    }

    public function visit(Request $request, LiveSession $liveSession, LiveSessionBreakoutRoom $room): JsonResponse
    {
        $this->moderator($request, $liveSession);
        abort_unless((int) $room->live_session_id === (int) $liveSession->id && $room->status === 'open', 404);
        $participant = $liveSession->participants()->where('user_id', $request->user()->id)->firstOrFail();
        $participant->update(['breakout_room_id' => $room->id]);
        LiveSessionSignal::query()->where('live_session_id', $liveSession->id)
            ->where(fn ($query) => $query->where('sender_id', $participant->user_id)->orWhere('recipient_id', $participant->user_id))->delete();

        return response()->json([], 202);
    }

    public function returnToMain(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->moderator($request, $liveSession);
        $participant = $liveSession->participants()->where('user_id', $request->user()->id)->firstOrFail();
        $participant->update(['breakout_room_id' => null]);
        LiveSessionSignal::query()->where('live_session_id', $liveSession->id)
            ->where(fn ($query) => $query->where('sender_id', $participant->user_id)->orWhere('recipient_id', $participant->user_id))->delete();

        return response()->json([], 202);
    }

    private function participant(Request $request, LiveSession $session): LiveSessionParticipant
    {
        abort_unless($session->isLive(), 409);
        $this->tokens->validate((string) $request->input('token'), $session, $request->user());

        return $session->participants()->where('user_id', $request->user()->id)
            ->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->firstOrFail();
    }

    private function moderator(Request $request, LiveSession $session): void
    {
        $this->participant($request, $session);
        abort_unless($this->access->canModerate($session, $request->user()), 403);
    }

    private function roomBelongsToSession(LiveSessionBreakoutRoom $room, LiveSession $session): void
    {
        abort_unless((int) $room->live_session_id === (int) $session->id && in_array($room->status, ['draft', 'open'], true), 404);
    }

    private function closeExpiredRooms(LiveSession $session): void
    {
        DB::transaction(function () use ($session) {
            $roomIds = $session->breakoutRooms()->where('status', 'open')->where('closes_at', '<=', now())->lockForUpdate()->pluck('id');
            if ($roomIds->isEmpty()) {
                return;
            }

            LiveSessionParticipant::query()->where('live_session_id', $session->id)->whereIn('breakout_room_id', $roomIds)->update(['breakout_room_id' => null]);
            LiveSessionBreakoutAssignment::query()->whereIn('breakout_room_id', $roomIds)->whereNull('left_at')->update(['left_at' => now()]);
            LiveSessionBreakoutRoom::query()->whereIn('id', $roomIds)->update(['status' => 'closed', 'closed_at' => now()]);
            LiveSessionSignal::query()->where('live_session_id', $session->id)->delete();
        });
    }
}

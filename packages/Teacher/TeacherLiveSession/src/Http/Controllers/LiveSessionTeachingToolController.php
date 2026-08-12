<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionPoll;
use Mindigo\TeacherLiveSession\Models\LiveSessionPollVote;
use Mindigo\TeacherLiveSession\Models\LiveSessionResource;
use Mindigo\TeacherLiveSession\Models\LiveSessionWhiteboardAction;
use Mindigo\TeacherLiveSession\Services\LiveSessionAccessService;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class LiveSessionTeachingToolController extends Controller
{
    public function __construct(
        private readonly LiveSessionAccessService $access,
        private readonly LiveSessionJoinTokenService $tokens,
        private readonly AuditLogService $audit,
    ) {}

    public function sync(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->participant($request, $liveSession);
        $data = $request->validate(['token' => ['required', 'string', 'max:4096'], 'after_action_id' => ['nullable', 'integer', 'min:0']]);
        $actions = LiveSessionWhiteboardAction::query()->where('live_session_id', $liveSession->id)
            ->where('id', '>', $data['after_action_id'] ?? 0)->oldest()->limit(500)->get(['id', 'actor_id', 'type', 'payload']);
        $poll = LiveSessionPoll::query()->where('live_session_id', $liveSession->id)->latest()->with(['options', 'votes'])->first();
        $resources = LiveSessionResource::query()->where('live_session_id', $liveSession->id)->latest()->limit(50)->get()
            ->map(fn (LiveSessionResource $resource) => [
                'id' => $resource->id, 'name' => $resource->name, 'size_bytes' => $resource->size_bytes,
                'download_url' => route('live-teaching-tools.resources.download', $resource),
            ]);

        return response()->json([
            'actions' => $actions, 'poll' => $this->pollPayload($poll, $request->user()->id),
            'resources' => $resources, 'can_moderate' => $this->access->canModerate($liveSession, $request->user()),
        ]);
    }

    public function whiteboard(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->participant($request, $liveSession);
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'], 'type' => ['required', Rule::in(['stroke', 'clear'])],
            'payload' => ['nullable', 'array'], 'payload.color' => ['required_if:type,stroke', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'payload.width' => ['required_if:type,stroke', 'numeric', 'min:1', 'max:20'],
            'payload.points' => ['required_if:type,stroke', 'array', 'min:2', 'max:500'],
            'payload.points.*.x' => ['required', 'numeric', 'between:0,1'], 'payload.points.*.y' => ['required', 'numeric', 'between:0,1'],
        ]);
        abort_if($data['type'] === 'clear' && ! $this->access->canModerate($liveSession, $request->user()), 403);
        $action = LiveSessionWhiteboardAction::query()->create([
            'live_session_id' => $liveSession->id, 'actor_id' => $request->user()->id,
            'type' => $data['type'], 'payload' => $data['type'] === 'stroke' ? $data['payload'] : null,
        ]);
        if ($data['type'] === 'clear') {
            $this->audit->record('whiteboard_cleared', 'teacher_live_session', auditable: $liveSession, user: $request->user());
        }

        return response()->json(['id' => $action->id], 201);
    }

    public function createPoll(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->moderator($request, $liveSession);
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'], 'question' => ['required', 'string', 'max:500'],
            'options' => ['required', 'array', 'min:2', 'max:8'], 'options.*' => ['required', 'string', 'distinct', 'max:300'],
        ]);
        $data['question'] = trim($data['question']);
        $data['options'] = collect($data['options'])->map(fn (string $option) => trim($option))->all();
        if ($data['question'] === '' || collect($data['options'])->contains('') || collect($data['options'])->map(fn (string $option) => mb_strtolower($option))->unique()->count() !== count($data['options'])) {
            throw ValidationException::withMessages(['options' => __('teacher-live-session::app.validation.poll_options_unique')]);
        }
        $poll = DB::transaction(function () use ($liveSession, $request, $data) {
            LiveSessionPoll::query()->where('live_session_id', $liveSession->id)->where('status', 'open')->update(['status' => 'closed', 'closed_at' => now()]);
            $poll = LiveSessionPoll::query()->create(['live_session_id' => $liveSession->id, 'created_by' => $request->user()->id, 'question' => $data['question']]);
            $poll->options()->createMany(collect($data['options'])->values()->map(fn ($label, $position) => ['label' => $label, 'position' => $position])->all());

            return $poll;
        });
        $this->audit->record('poll_created', 'teacher_live_session', metadata: ['poll_id' => $poll->id], auditable: $liveSession, user: $request->user());

        return response()->json(['poll_id' => $poll->id], 201);
    }

    public function vote(Request $request, LiveSession $liveSession, LiveSessionPoll $poll): JsonResponse
    {
        $this->participant($request, $liveSession);
        abort_unless((int) $poll->live_session_id === (int) $liveSession->id && $poll->status === 'open', 422);
        $data = $request->validate(['token' => ['required', 'string', 'max:4096'], 'option_id' => ['required', 'integer']]);
        abort_unless($poll->options()->whereKey($data['option_id'])->exists(), 422);
        try {
            LiveSessionPollVote::query()->create(['poll_id' => $poll->id, 'option_id' => $data['option_id'], 'user_id' => $request->user()->id]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['poll' => __('teacher-live-session::app.validation.poll_already_voted')]);
        }

        return response()->json([], 202);
    }

    public function closePoll(Request $request, LiveSession $liveSession, LiveSessionPoll $poll): JsonResponse
    {
        $this->moderator($request, $liveSession);
        abort_unless((int) $poll->live_session_id === (int) $liveSession->id, 404);
        $data = $request->validate(['token' => ['required', 'string', 'max:4096'], 'show_results' => ['required', 'boolean']]);
        $poll->update(['status' => 'closed', 'show_results' => $data['show_results'], 'closed_at' => now()]);
        $this->audit->record('poll_closed', 'teacher_live_session', metadata: ['poll_id' => $poll->id, 'show_results' => $data['show_results']], auditable: $liveSession, user: $request->user());

        return response()->json([], 202);
    }

    public function upload(Request $request, LiveSession $liveSession): JsonResponse
    {
        $this->moderator($request, $liveSession);
        $data = $request->validate(['token' => ['required', 'string', 'max:4096'], 'file' => ['required', 'file', 'mimes:pdf,ppt,pptx,doc,docx,xls,xlsx,png,jpg,jpeg,webp', 'max:25600']]);
        $file = $data['file'];
        $checksum = hash_file('sha256', $file->getRealPath());
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('live-session-resources/'.$liveSession->id, $checksum.($extension !== '' ? '.'.$extension : ''), 'local');
        $resource = LiveSessionResource::query()->firstOrCreate(
            ['live_session_id' => $liveSession->id, 'checksum' => $checksum],
            ['uploaded_by' => $request->user()->id, 'name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(), 'storage_disk' => 'local', 'storage_path' => $path, 'size_bytes' => $file->getSize()],
        );
        $this->audit->record('resource_uploaded', 'teacher_live_session', metadata: ['resource_id' => $resource->id, 'name' => $resource->name], auditable: $liveSession, user: $request->user());

        return response()->json(['id' => $resource->id], 201);
    }

    public function download(Request $request, LiveSessionResource $resource): BinaryFileResponse
    {
        $session = LiveSession::query()->with('classroom')->findOrFail($resource->live_session_id);
        $user = $request->user();
        $allowed = $user->isAdmin() || $this->access->canModerate($session, $user)
            || $session->classroom?->students()->whereKey($user->id)->wherePivot('status', 'active')->exists();
        abort_unless($allowed && Storage::disk($resource->storage_disk)->exists($resource->storage_path), 403);

        return response()->download(Storage::disk($resource->storage_disk)->path($resource->storage_path), $resource->name, ['Content-Type' => $resource->mime_type]);
    }

    private function participant(Request $request, LiveSession $session): void
    {
        abort_unless($session->isLive(), 409);
        $this->tokens->validate((string) $request->input('token'), $session, $request->user());
        abort_unless($session->participants()->where('user_id', $request->user()->id)->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->exists(), 403);
    }

    private function moderator(Request $request, LiveSession $session): void
    {
        $this->participant($request, $session);
        abort_unless($this->access->canModerate($session, $request->user()), 403);
    }

    private function pollPayload(?LiveSessionPoll $poll, int $userId): ?array
    {
        if (! $poll) {
            return null;
        }
        $counts = $poll->votes->countBy('option_id');
        $votedOption = $poll->votes->firstWhere('user_id', $userId)?->option_id;
        $resultsVisible = $poll->status === 'closed' && $poll->show_results;

        return ['id' => $poll->id, 'question' => $poll->question, 'status' => $poll->status, 'voted_option_id' => $votedOption, 'show_results' => $resultsVisible,
            'options' => $poll->options->map(fn ($option) => ['id' => $option->id, 'label' => $option->label, 'votes' => $resultsVisible ? ($counts[$option->id] ?? 0) : null]),
        ];
    }
}

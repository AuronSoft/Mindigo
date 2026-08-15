<?php

namespace Mindigo\Dashboard\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Services\ExamConvergenceService;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\SupportManagement\Models\SupportTicket;

class SearchController extends Controller
{
    public function __construct(private readonly ExamConvergenceService $examConvergence) {}

    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Exams
        $exams = $this->examConvergence->enabled($request->user())
            ? $this->examConvergence->searchSessions($q)
            : Exam::where('title', 'like', "%{$q}%")
                ->orWhere('subject', 'like', "%{$q}%")
                ->orWhere('topic', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'title', 'subject', 'status']);

        foreach ($exams as $exam) {
            $results[] = [
                'type' => 'exam',
                'label' => $exam->title,
                'sub' => $exam->subject ?? $exam->status,
                'url' => $this->examConvergence->enabled($request->user()) ? route('admin.exam-operations') : route('exams.show', $exam->id),
                'icon' => 'document-text',
                'badge' => $exam->status,
            ];
        }

        // Users
        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->limit(4)
            ->get(['id', 'name', 'email', 'role']);

        foreach ($users as $user) {
            $results[] = [
                'type' => 'user',
                'label' => $user->name,
                'sub' => $user->email,
                'url' => route('users.show', $user->id),
                'icon' => 'user',
                'badge' => $user->role,
            ];
        }

        // Questions
        $questions = Question::where('content', 'like', "%{$q}%")
            ->orWhere('subject', 'like', "%{$q}%")
            ->limit(3)
            ->get(['id', 'content', 'subject', 'status']);

        foreach ($questions as $question) {
            $results[] = [
                'type' => 'question',
                'label' => mb_substr(strip_tags($question->content), 0, 60),
                'sub' => $question->subject ?: $question->status,
                'url' => route('question-bank.show', $question->id),
                'icon' => 'circle-stack',
                'badge' => $question->status,
            ];
        }

        // Support tickets
        if (class_exists(SupportTicket::class)) {
            try {
                $tickets = SupportTicket::where('subject', 'like', "%{$q}%")
                    ->limit(3)
                    ->get(['id', 'subject', 'status']);

                foreach ($tickets as $ticket) {
                    $results[] = [
                        'type' => 'ticket',
                        'label' => $ticket->subject,
                        'sub' => $ticket->status,
                        'url' => route('support-tickets.show', $ticket->id),
                        'icon' => 'chat-bubble-left-ellipsis',
                        'badge' => $ticket->status,
                    ];
                }
            } catch (\Exception) {
            }
        }

        return response()->json(['results' => $results, 'query' => $q]);
    }
}

<?php

namespace Mindigo\StudentPractice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentPractice\Services\PracticeService;

class PracticeController extends Controller
{
    public function __construct(protected PracticeService $service)
    {
    }

    public function index(Request $request)
    {
        $formData  = $this->service->formData();
        $questions = $this->service->getQuestions($request->only(['subject', 'topic', 'type', 'difficulty', 'keyword']));

        return view('student-practice::index', compact('formData', 'questions'));
    }

    public function show(int $id)
    {
        $question = $this->service->getQuestion($id);

        abort_if($question === null, 404);

        return view('student-practice::show', compact('question'));
    }
}

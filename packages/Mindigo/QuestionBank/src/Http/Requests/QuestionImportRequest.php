<?php

namespace Mindigo\QuestionBank\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('questions.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'import_file' => ['required', 'file', 'max:5120', 'extensions:csv,txt,json'],
            'folder_id' => ['nullable', 'exists:question_bank_folders,id'],
            'status' => ['required', Rule::in(['draft', 'reviewing'])],
        ];
    }
}

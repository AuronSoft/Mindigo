<?php

namespace Mindigo\StudentDiscussion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscussionMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}

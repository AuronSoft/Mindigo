<?php

namespace Mindigo\TeacherOnboarding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;

class TeacherApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->user()?->isTeacher();
    }

    public function rules(): array
    {
        $documentRules = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:5120'];

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9+()\s.-]{8,30}$/'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(array_keys(User::GENDERS))],
            'address' => ['nullable', 'string', 'max:255'],
            'application_type' => ['required', Rule::in(TeacherApplication::APPLICATION_TYPES)],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')->where('status', 'active')->whereNull('deleted_at')],
            'category_id' => ['nullable', 'integer', Rule::exists('course_categories', 'id')->where('is_active', true)],
            'education_level' => ['nullable', Rule::in(Course::EDUCATION_LEVELS)],
            'specialization' => ['required', 'string', 'max:255'],
            'teaching_skills' => ['nullable', 'array', 'max:10'],
            'teaching_skills.*' => ['string', 'max:80'],
            'teaching_mode' => ['required', Rule::in(TeacherApplication::TEACHING_MODES)],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'current_organization' => ['nullable', 'string', 'max:255'],
            'previous_organizations' => ['nullable', 'string', 'max:2000'],
            'achievements' => ['nullable', 'string', 'max:2000'],
            'experience_description' => ['nullable', 'string', 'max:5000'],
            'identity_document' => $documentRules,
            'degree_document' => $documentRules,
            'certificate_document' => $documentRules,
            'student_card_document' => $documentRules,
            'cv_document' => $documentRules,
            'portfolio_document' => $documentRules,
            'teaching_method' => ['required', 'string', 'max:5000'],
            'intro_video_url' => ['nullable', 'url', 'max:255', 'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|vimeo\.com)\/.+/i'],
            'terms_accepted' => ['accepted'],
        ];
    }

    public function documentFiles(): array
    {
        return collect([
            'identity' => $this->file('identity_document'),
            'degree' => $this->file('degree_document'),
            'certificate' => $this->file('certificate_document'),
            'student_card' => $this->file('student_card_document'),
            'cv' => $this->file('cv_document'),
            'portfolio' => $this->file('portfolio_document'),
        ])->filter()->all();
    }
}

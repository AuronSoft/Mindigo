<?php

namespace Mindigo\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', 'regex:/^[\p{L}\s.\'-]+$/u'],
            'phone' => ['nullable', 'regex:/^(\+84|0)\d{9}$/'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'name.regex' => 'Họ tên không được chứa số hoặc ký tự đặc biệt không hợp lệ.',
            'phone.regex' => 'Số điện thoại phải có 10 chữ số, bắt đầu bằng 0 hoặc +84.',
            'date_of_birth.before' => 'Ngày sinh phải nhỏ hơn ngày hiện tại.',
            'date_of_birth.date' => 'Ngày sinh không hợp lệ.',
            'avatar.file' => 'File tải lên phải là ảnh.',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpg, jpeg, png hoặc webp.',
            'avatar.max' => 'Ảnh không được vượt quá 2MB.',
        ];
    }
}

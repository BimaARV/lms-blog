<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers'                            => 'required|array',
            'answers.*.selected'                 => 'sometimes|array',
            'answers.*.selected.*'               => 'sometimes|string|max:500',
            'answers.*.text'                     => 'sometimes|nullable|string|max:5000',
        ];
    }
}

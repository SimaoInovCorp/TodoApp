<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\Enum|\Illuminate\Validation\Rules\Unique|\Illuminate\Validation\Rules\Exists|\Illuminate\Validation\Rules\In|\Illuminate\Validation\Rules\RequiredIf|\Illuminate\Validation\Rules\RequiredUnless|\Illuminate\Validation\Rules\RequiredWithout|\Illuminate\Validation\Rules\RequiredWith|\Illuminate\Validation\Rules\RequiredWithAll|\Illuminate\Validation\Rules\RequiredWithoutAll>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'string', Rule::in(['high', 'medium', 'low'])],
            'status' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TravelPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['nullable', 'date', 'after_or_equal:departure_date'],
            'timezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => '旅行名',
            'departure_date' => '出発日',
            'return_date' => '帰宅日',
            'timezone' => 'タイムゾーン',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'departure_date.after_or_equal' => ':attributeは今日以降の日付を指定してください。',
            'return_date.after_or_equal' => ':attributeは出発日以降の日付を指定してください。',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ExpenseRequest extends FormRequest
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
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => ['required', new Enum(Currency::class)],
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'payer_member_id' => 'required|exists:members,id',
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:members,id',
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
            'description' => '説明',
            'amount' => '金額',
            'currency' => '通貨',
            'expense_date' => '支出日',
            'category' => 'カテゴリ',
            'notes' => 'メモ',
            'payer_member_id' => '支払者',
            'member_ids' => '参加メンバー',
            'member_ids.*' => '参加メンバー',
        ];
    }
}

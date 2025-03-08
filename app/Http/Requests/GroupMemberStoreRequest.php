<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupMemberStoreRequest extends FormRequest
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
        $groupId = $this->route('group')->id;

        return [
            'name' => [
                'required_without:email',
                'string',
                'max:255',
                Rule::unique('members', 'name')->where(function ($query) use ($groupId) {
                    return $query->where('group_id', $groupId);
                }),
            ],
            'email' => [
                'required_without:name',
                'nullable',
                'email',
                'max:255',
                Rule::unique('members', 'email')->where(function ($query) use ($groupId) {
                    return $query->where('group_id', $groupId);
                }),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required_without' => '名前かメールアドレスのどちらかを入力してください',
            'email.required_without' => '名前かメールアドレスのどちらかを入力してください',
            'email.email' => '有効なメールアドレス形式で入力してください',
            'name.unique' => '同じ名前のメンバーが既に登録されています',
            'email.unique' => 'このメールアドレスは既に登録されています',
        ];
    }
}

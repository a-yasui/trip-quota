<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchGroupRequest extends FormRequest
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
        $travelPlanId = $this->route('travelPlan')->id ?? $this->route('group')->travel_plan_id;
        $groupId = $this->route('group')->id ?? null;

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groups', 'name')->where(function ($query) use ($travelPlanId) {
                    return $query->where('travel_plan_id', $travelPlanId)
                        ->where('type', 'branch');
                })->ignore($groupId),
            ],
            'members' => [
                'required',
                'array',
                'min:1',
            ],
            'members.*' => [
                'exists:members,id',
            ],
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '班グループ名は必須です',
            'name.unique' => 'この班グループ名は既に使用されています',
            'members.required' => 'メンバーを選択してください',
            'members.min' => '少なくとも1人のメンバーを選択してください',
            'members.*.exists' => '選択されたメンバーが存在しません',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\Timezone;
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
        $rules = [];
        $travelPlan = $this->route('travelPlan');
        $isBeforeDeparture = ! $travelPlan || now()->startOfDay()->lt($travelPlan->departure_date);

        // 出発日前の場合のみ、タイトル、出発日、タイムゾーンのバリデーションを適用
        if ($isBeforeDeparture) {
            $rules['title'] = ['required', 'string', 'max:255'];
            $rules['departure_date'] = ['required', 'date'];

            // 新規作成時は今日以降の日付を要求
            if (! $travelPlan) {
                $rules['departure_date'][] = 'after_or_equal:today';
            }

            $rules['timezone'] = ['required', 'string', Rule::in(Timezone::values())];
        }

        // 帰宅日のルール
        // 出発日前、または出発日後で帰宅日が未設定の場合のみバリデーション
        if ($isBeforeDeparture || ($travelPlan && $travelPlan->return_date === null)) {
            $rules['return_date'] = ['nullable', 'date'];

            // 出発日との比較
            if ($this->filled('departure_date')) {
                $rules['return_date'][] = 'after_or_equal:departure_date';
            } elseif ($travelPlan) {
                $rules['return_date'][] = 'after_or_equal:'.$travelPlan->departure_date->format('Y-m-d');
            }
        }

        return $rules;
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

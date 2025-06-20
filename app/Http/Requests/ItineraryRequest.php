<?php

namespace App\Http\Requests;

use App\Enums\TransportationType;
use App\Models\Group;
use App\Models\Itinerary;
use App\Models\TravelPlan;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItineraryRequest extends FormRequest
{
    /**
     * 認証チェック
     */
    public function authorize(): bool
    {
        return true; // コントローラーで権限チェック済み
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'date' => [
                'required',
                'date',
                $this->validateDateWithinTravelPlan(),
            ],
            'start_time' => [
                'nullable',
                'date_format:H:i',
                $this->validateTimeConflicts(),
            ],
            'end_time' => [
                'nullable',
                'date_format:H:i',
                'after:start_time',
                $this->validateEndTimeAfterStart(),
            ],
            'timezone' => 'nullable|string|max:50',
            'group_id' => [
                'nullable',
                'exists:groups,id',
                $this->validateGroupBelongsToTravelPlan(),
            ],
            'transportation_type' => [
                'nullable',
                Rule::enum(TransportationType::class),
            ],
            'departure_location' => 'nullable|string|max:255',
            'arrival_location' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'member_ids' => 'nullable|array',
            'member_ids.*' => [
                'exists:members,id',
                $this->validateMemberBelongsToTravelPlan(),
            ],
        ];

        // 交通手段別のバリデーション
        $rules = array_merge($rules, $this->getTransportationRules());

        return $rules;
    }

    /**
     * カスタムバリデーションメッセージ
     */
    public function messages(): array
    {
        return [
            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'description.max' => '説明は2000文字以内で入力してください。',
            'date.required' => '日付は必須です。',
            'date.date' => '有効な日付を入力してください。',
            'start_time.date_format' => '開始時刻の形式が正しくありません（HH:MM）。',
            'end_time.date_format' => '終了時刻の形式が正しくありません（HH:MM）。',
            'end_time.after' => '終了時刻は開始時刻より後に設定してください。',
            'group_id.exists' => '選択されたグループが見つかりません。',
            'transportation_type.in' => '無効な交通手段が選択されています。',
            'member_ids.array' => '参加者の選択形式が正しくありません。',
            'member_ids.*.exists' => '選択されたメンバーが見つかりません。',
            'airline.required_if' => '飛行機を選択した場合、航空会社は必須です。',
            'flight_number.required_if' => '飛行機を選択した場合、便名は必須です。',
            'train_line.required_if' => '電車を選択した場合、路線名は必須です。',
            'departure_time.after_or_equal' => '出発時刻は現在時刻以降に設定してください。',
            'arrival_time.after' => '到着時刻は出発時刻より後に設定してください。',
        ];
    }

    /**
     * 交通手段別のバリデーションルールを取得
     */
    private function getTransportationRules(): array
    {
        $transportationType = TransportationType::tryFrom($this->input('transportation_type') ?? '');
        $rules = [];

        if ($transportationType) {
            switch ($transportationType) {
                case TransportationType::AIRPLANE:
                    $rules = array_merge($rules, [
                        'airline' => 'required_if:transportation_type,'.TransportationType::AIRPLANE->value.'|string|max:255',
                        'flight_number' => 'required_if:transportation_type,'.TransportationType::AIRPLANE->value.'|string|max:255',
                        'departure_airport' => 'nullable|string|max:255',
                        'arrival_airport' => 'nullable|string|max:255',
                        'departure_time' => [
                            'nullable',
                            'date',
                            $this->validateFutureDateTime(),
                        ],
                        'arrival_time' => [
                            'nullable',
                            'date',
                            'after:departure_time',
                            $this->validateFlightDuration(),
                        ],
                    ]);
                    break;

                case TransportationType::TRAIN:
                    $rules = array_merge($rules, [
                        'train_line' => 'required_if:transportation_type,'.TransportationType::TRAIN->value.'|string|max:255',
                        'train_type' => 'nullable|string|max:255',
                        'departure_station' => 'nullable|string|max:255',
                        'arrival_station' => 'nullable|string|max:255',
                        'departure_time' => 'nullable|date',
                        'arrival_time' => [
                            'nullable',
                            'date',
                            'after:departure_time',
                        ],
                    ]);
                    break;

                case TransportationType::BUS:
                case TransportationType::FERRY:
                    $rules = array_merge($rules, [
                        'company' => 'nullable|string|max:255',
                        'departure_terminal' => 'nullable|string|max:255',
                        'arrival_terminal' => 'nullable|string|max:255',
                        'departure_time' => 'nullable|date',
                        'arrival_time' => [
                            'nullable',
                            'date',
                            'after:departure_time',
                        ],
                    ]);
                    break;

                default:
                    $rules = array_merge($rules, [
                        'departure_time' => 'nullable|date',
                        'arrival_time' => [
                            'nullable',
                            'date',
                            'after:departure_time',
                        ],
                    ]);
                    break;
            }
        }

        // 共通フィールドで他の交通手段では不要なものをnullableに設定
        if ($transportationType !== TransportationType::AIRPLANE) {
            $rules['airline'] = 'nullable|string|max:255';
            $rules['flight_number'] = 'nullable|string|max:255';
            $rules['departure_airport'] = 'nullable|string|max:255';
            $rules['arrival_airport'] = 'nullable|string|max:255';
        }

        if ($transportationType !== TransportationType::TRAIN) {
            $rules['train_line'] = 'nullable|string|max:255';
            $rules['train_type'] = 'nullable|string|max:255';
            $rules['departure_station'] = 'nullable|string|max:255';
            $rules['arrival_station'] = 'nullable|string|max:255';
        }

        if (! in_array($transportationType, [TransportationType::BUS, TransportationType::FERRY])) {
            $rules['company'] = 'nullable|string|max:255';
            $rules['departure_terminal'] = 'nullable|string|max:255';
            $rules['arrival_terminal'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    /**
     * 旅行プランの期間内かチェック
     */
    private function validateDateWithinTravelPlan(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            $travelPlan = $this->getTravelPlan();
            if (! $travelPlan) {
                return;
            }

            $date = Carbon::parse($value);
            $startDate = $travelPlan->departure_date;
            $endDate = $travelPlan->return_date;

            if ($date->lt($startDate)) {
                $fail('日付は旅行開始日（'.$startDate->format('Y年n月d日').'）以降に設定してください。');

                return;
            }

            if ($endDate && $date->gt($endDate)) {
                $fail('日付は旅行終了日（'.$endDate->format('Y年n月d日').'）以前に設定してください。');

                return;
            }
        };
    }

    /**
     * 時刻重複チェック
     */
    private function validateTimeConflicts(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! $value) {
                return;
            }

            // 時刻の重複チェック
            $this->checkTimeConflicts($attribute, $value, $fail);
        };
    }

    /**
     * 終了時刻の追加バリデーション
     */
    private function validateEndTimeAfterStart(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! $value || ! $this->input('start_time')) {
                return;
            }

            $startTime = Carbon::createFromFormat('H:i', $this->input('start_time'));
            $endTime = Carbon::createFromFormat('H:i', $value);

            // 同じ時刻は許可しない
            if ($endTime->eq($startTime)) {
                $fail('終了時刻は開始時刻と異なる時刻に設定してください。');

                return;
            }

            // 日跨ぎを考慮した時間チェック
            if ($endTime->lt($startTime)) {
                // 日跨ぎの場合、最大24時間以内
                $timeDiff = $endTime->addDay()->diffInHours($startTime);
                if ($timeDiff > 24) {
                    $fail('終了時刻は開始時刻から24時間以内に設定してください。');
                }
            }
        };
    }

    /**
     * グループが旅行プランに属しているかチェック
     */
    private function validateGroupBelongsToTravelPlan(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! $value) {
                return;
            }

            $travelPlan = $this->getTravelPlan();
            if (! $travelPlan) {
                return;
            }

            $group = Group::find($value);
            if (! $group || $group->travel_plan_id !== $travelPlan->id) {
                $fail('選択されたグループはこの旅行プランに属していません。');
            }
        };
    }

    /**
     * メンバーが旅行プランに属しているかチェック
     */
    private function validateMemberBelongsToTravelPlan(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            $travelPlan = $this->getTravelPlan();
            if (! $travelPlan) {
                return;
            }

            $memberExists = $travelPlan->members()->where('id', $value)->exists();
            if (! $memberExists) {
                $fail('選択されたメンバーはこの旅行プランに属していません。');
            }
        };
    }

    /**
     * 将来の日時かチェック（飛行機のみ）
     */
    private function validateFutureDateTime(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! $value) {
                return;
            }

            $dateTime = Carbon::parse($value);
            if ($dateTime->isPast()) {
                $fail('出発時刻は現在時刻より後に設定してください。');
            }
        };
    }

    /**
     * 飛行時間の妥当性チェック
     */
    private function validateFlightDuration(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! $value || ! $this->input('departure_time')) {
                return;
            }

            $departureTime = Carbon::parse($this->input('departure_time'));
            $arrivalTime = Carbon::parse($value);

            $duration = $arrivalTime->diffInHours($departureTime);

            // 飛行時間の妥当性チェック（最大24時間）
            if ($duration > 24) {
                $fail('飛行時間が24時間を超えています。到着時刻を確認してください。');
            }

            // 最短飛行時間チェック（30分）
            if ($duration < 0.5) {
                $fail('飛行時間は最低30分以上に設定してください。');
            }
        };
    }

    /**
     * 時刻重複チェック
     */
    private function checkTimeConflicts(string $attribute, mixed $value, \Closure $fail): void
    {
        $travelPlan = $this->getTravelPlan();
        if (! $travelPlan) {
            return;
        }

        $date = $this->input('date');
        $startTime = $this->input('start_time');
        $endTime = $this->input('end_time');
        $groupId = $this->input('group_id');
        $memberIds = $this->input('member_ids', []);

        if (! $date || ! $startTime) {
            return;
        }

        // 現在編集中の旅程は除外
        $currentItineraryId = $this->route('itinerary')?->id;

        // 同じ日の既存旅程を取得
        $query = Itinerary::where('travel_plan_id', $travelPlan->id)
            ->whereDate('date', $date)
            ->whereNotNull('start_time');

        if ($currentItineraryId) {
            $query->where('id', '!=', $currentItineraryId);
        }

        $existingItineraries = $query->get();

        foreach ($existingItineraries as $existing) {
            // 時間重複チェック
            if ($this->hasTimeOverlap($startTime, $endTime, $existing)) {
                // グループレベルでの重複チェック
                if ($this->hasGroupConflict($groupId, $existing)) {
                    $fail(sprintf(
                        '時刻が重複しています。%s（%s）と重複しています。',
                        $existing->title,
                        $existing->start_time->format('H:i').($existing->end_time ? '〜'.$existing->end_time->format('H:i') : '')
                    ));

                    return;
                }

                // メンバーレベルでの重複チェック
                if ($this->hasMemberConflict($memberIds, $existing)) {
                    $conflictMembers = $this->getConflictingMembers($memberIds, $existing);
                    $fail(sprintf(
                        '時刻が重複しています。%s（%s）で以下のメンバーが重複しています：%s',
                        $existing->title,
                        $existing->start_time->format('H:i').($existing->end_time ? '〜'.$existing->end_time->format('H:i') : ''),
                        implode('、', $conflictMembers)
                    ));

                    return;
                }
            }
        }
    }

    /**
     * 時間重複チェックのヘルパー
     */
    private function hasTimeOverlap(string $startTime, ?string $endTime, Itinerary $existing): bool
    {
        $newStart = Carbon::createFromFormat('H:i', $startTime);
        $newEnd = $endTime ? Carbon::createFromFormat('H:i', $endTime) : $newStart->copy()->addMinutes(30);

        $existingStart = $existing->start_time;
        $existingEnd = $existing->end_time ?: $existingStart->copy()->addMinutes(30);

        return $newStart->lt($existingEnd) && $newEnd->gt($existingStart);
    }

    /**
     * グループ重複チェック
     */
    private function hasGroupConflict(?int $groupId, Itinerary $existing): bool
    {
        // 全体（グループなし）同士の重複
        if (! $groupId && ! $existing->group_id) {
            return true;
        }

        // 同じグループの重複
        if ($groupId && $groupId === $existing->group_id) {
            return true;
        }

        return false;
    }

    /**
     * メンバー重複チェック
     */
    private function hasMemberConflict(array $memberIds, Itinerary $existing): bool
    {
        if (empty($memberIds)) {
            return false;
        }

        $existingMemberIds = $existing->members->pluck('id')->toArray();

        return ! empty(array_intersect($memberIds, $existingMemberIds));
    }

    /**
     * 重複しているメンバー名を取得
     */
    private function getConflictingMembers(array $memberIds, Itinerary $existing): array
    {
        $existingMemberIds = $existing->members->pluck('id')->toArray();
        $conflictIds = array_intersect($memberIds, $existingMemberIds);

        return $existing->members->whereIn('id', $conflictIds)->pluck('name')->toArray();
    }

    /**
     * ルートから旅行プランを取得
     */
    private function getTravelPlan(): ?TravelPlan
    {
        // ルートパラメータから取得を試行
        $uuid = $this->route('uuid');
        if (! $uuid) {
            // フォールバック: URLから抽出
            $path = $this->path();
            if (preg_match('/travel-plans\/([^\/]+)/', $path, $matches)) {
                $uuid = $matches[1];
            }
        }

        if (! $uuid) {
            return null;
        }

        return TravelPlan::where('uuid', $uuid)->first();
    }
}

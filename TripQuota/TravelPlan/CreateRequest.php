<?php

namespace TripQuota\TravelPlan;

use App\Enums\Timezone;
use App\Models\User;
use DateTimeInterface;

/**
 * 旅行計画作成リクエストを表すデータクラス
 */
class CreateRequest
{
    /**
     * @param string $plan_name 旅行計画名
     * @param User $creator 作成者
     * @param DateTimeInterface $departure_date 出発日
     * @param Timezone $timezone タイムゾーン
     * @param DateTimeInterface|null $return_date 帰宅日（任意）
     * @param bool $is_active アクティブ状態
     */
    public function __construct(
        public readonly string $plan_name,
        public readonly User $creator,
        public readonly DateTimeInterface $departure_date,
        public readonly Timezone $timezone,
        public readonly ?DateTimeInterface $return_date = null,
        public readonly bool $is_active = true
    ) {
    }
} 
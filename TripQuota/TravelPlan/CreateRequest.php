<?php

namespace TripQuota\TravelPlan;

use App\Models\User;

/**
 * 旅行計画作成リクエストクラス
 */
class CreateRequest
{
    /**
     * コンストラクタ
     *
     * @param string $plan_name 旅行計画名
     * @param \App\Models\User $creator 作成者
     * @param \DateTimeInterface $departure_date 出発日
     * @param \App\Enums\Timezone $timezone タイムゾーン
     * @param \DateTimeInterface|null $return_date 帰宅日（オプション）
     * @param bool $is_active アクティブフラグ（デフォルト: true）
     */
    public function __construct(
        public readonly string $plan_name,
        public readonly User $creator,
        public readonly \DateTimeInterface $departure_date,
        public readonly \App\Enums\Timezone $timezone,
        public readonly ?\DateTimeInterface $return_date = null,
        public readonly bool $is_active = true
    ) {}
} 
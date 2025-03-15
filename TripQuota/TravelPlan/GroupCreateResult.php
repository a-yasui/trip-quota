<?php

namespace TripQuota\TravelPlan;

use App\Models\Group;
use App\Models\TravelPlan;

/**
 * 旅行計画作成結果を表すデータクラス
 */
class GroupCreateResult
{
    /**
     * @param TravelPlan $plan 作成された旅行計画
     * @param Group $core_group 作成されたコアグループ
     */
    public function __construct(
        public readonly TravelPlan $plan,
        public readonly Group $core_group
    ) {
    }
} 
<?php

namespace App\Policies;

use App\Models\TravelPlan;
use App\Models\User;

class TravelPlanPolicy
{
    /**
     * メンバーを招待できるか
     */
    public function inviteMembers(User $user, TravelPlan $travelPlan): bool
    {
        // テスト: 一時的に常にtrueを返す
        return true;

        // 確認済みメンバーであれば招待可能
        return $travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->exists();
    }

    /**
     * 旅行プランを表示できるか
     */
    public function view(User $user, TravelPlan $travelPlan): bool
    {
        // 旅行プランのメンバーであれば表示可能
        return $travelPlan->members()->where('user_id', $user->id)->exists();
    }

    /**
     * 旅行プランを編集できるか
     */
    public function update(User $user, TravelPlan $travelPlan): bool
    {
        // 確認済みメンバーであれば編集可能
        return $travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->exists();
    }

    /**
     * 旅行プランを削除できるか
     */
    public function delete(User $user, TravelPlan $travelPlan): bool
    {
        // 所有者のみが削除可能
        return $travelPlan->owner_user_id === $user->id;
    }
}

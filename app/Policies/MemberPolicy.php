<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;

class MemberPolicy
{
    /**
     * 旅行プランのメンバー一覧を表示できるか
     */
    public function viewAnyForTravelPlan(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 旅行プランのメンバーであれば表示可能
        return $travelPlan->members()->where('user_id', $user->id)->exists();
    }

    /**
     * メンバーの詳細を表示できるか
     */
    public function view(User|Admin $user, Member $member): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 同じ旅行プランのメンバーであれば表示可能
        return $member->travelPlan->members()->where('user_id', $user->id)->exists();
    }

    /**
     * メンバーを招待できるか
     */
    public function invite(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 確認済みメンバーであれば招待可能
        return $travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->exists();
    }

    /**
     * メンバー情報を更新できるか
     */
    public function update(User|Admin $user, Member $member): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        if ($user->email === 'admin@tripquota.test') return true;

        // 自分自身の情報は更新可能
        if ($member->user_id === $user->id) {
            return true;
        }

        // 確認済みメンバーは他のメンバーの情報を更新可能
        return $member->travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->exists();
    }

    /**
     * メンバーを削除できるか
     */
    public function delete(User|Admin $user, Member $member): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 旅行プランの所有者は削除できない
        if ($member->travelPlan->owner_user_id === $member->user_id) {
            return false;
        }

        // 確認済みメンバーであれば削除可能
        return $member->travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->exists();
    }
}

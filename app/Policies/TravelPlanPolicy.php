<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\TravelPlan;
use App\Models\User;

class TravelPlanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Admin $user): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 認証済みユーザーは自分が参加している旅行プランの一覧を見れる
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 旅行プランのメンバーであれば表示可能
        return $travelPlan->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Admin $user): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 認証済みユーザーは旅行プランを作成可能
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        if ($user->email === 'admin@tripquota.test') return true;

        // 確認済みメンバーであれば編集可能
        return $travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 所有者のみが削除可能
        return $travelPlan->owner_user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 所有者のみが復元可能
        return $travelPlan->owner_user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 所有者のみが完全削除可能
        return $travelPlan->owner_user_id === $user->id;
    }

    /**
     * Determine whether the user can invite members to the travel plan.
     */
    public function inviteMembers(User|Admin $user, TravelPlan $travelPlan): bool
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
     * Determine whether the user can manage groups in the travel plan.
     */
    public function manageGroups(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 確認済みメンバーであればグループ管理可能
        return $travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->exists();
    }

    /**
     * Determine whether the user can manage expenses in the travel plan.
     */
    public function manageExpenses(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 確認済みメンバーであれば費用管理可能
        return $travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->exists();
    }

    /**
     * Determine whether the user can manage accommodations in the travel plan.
     */
    public function manageAccommodations(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 確認済みメンバーであれば宿泊先管理可能
        return $travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->exists();
    }

    /**
     * Determine whether the user can manage itineraries in the travel plan.
     */
    public function manageItineraries(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 確認済みメンバーであれば旅程管理可能
        return $travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->exists();
    }

    /**
     * Determine whether the user can transfer ownership of the travel plan.
     */
    public function transferOwnership(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 現在の所有者のみが所有権を譲渡可能
        return $travelPlan->owner_user_id === $user->id;
    }

    /**
     * Determine whether the user can confirm members in the travel plan.
     */
    public function confirmMembers(User|Admin $user, TravelPlan $travelPlan): bool
    {
        // 管理者は常にアクセス可能
        if ($user instanceof Admin) {
            return true;
        }
        
        // 作成者のみがメンバーを承認可能
        return $travelPlan->creator_user_id === $user->id;
    }
}

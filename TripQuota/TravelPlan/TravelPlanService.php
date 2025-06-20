<?php

namespace TripQuota\TravelPlan;

use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use TripQuota\Group\GroupRepositoryInterface;

class TravelPlanService
{
    public function __construct(
        private TravelPlanRepositoryInterface $repository,
        private GroupRepositoryInterface $groupRepository
    ) {}

    public function createTravelPlan(User $user, array $data): TravelPlan
    {
        $this->validateTravelPlanData($data);

        return DB::transaction(function () use ($user, $data) {
            // 旅行プラン作成
            $travelPlan = $this->repository->create([
                'plan_name' => $data['plan_name'],
                'creator_user_id' => $user->id,
                'owner_user_id' => $data['owner_user_id'] ?? $user->id,
                'departure_date' => $data['departure_date'],
                'return_date' => $data['return_date'] ?? null,
                'timezone' => $data['timezone'] ?? 'Asia/Tokyo',
                'is_active' => $data['is_active'] ?? true,
                'description' => $data['description'] ?? null,
            ]);

            // コアグループを自動作成
            $coreGroup = Group::create([
                'travel_plan_id' => $travelPlan->id,
                'type' => 'CORE',
                'name' => 'コアグループ',
                'description' => '全メンバーが参加するメインのグループです',
            ]);

            // 作成者をメンバーとして追加
            $member = Member::create([
                'travel_plan_id' => $travelPlan->id,
                'user_id' => $user->id,
                'account_id' => null, // TODO: ユーザーのデフォルトアカウントを設定
                'name' => $user->email, // TODO: アカウントの表示名を使用
                'email' => $user->email,
                'is_confirmed' => true,
            ]);

            // 作成者をコアグループに関連付け
            $this->groupRepository->addMemberToGroup($coreGroup, $member);

            return $travelPlan->load(['creator', 'owner', 'groups', 'members']);
        });
    }

    public function updateTravelPlan(TravelPlan $travelPlan, User $user, array $data): TravelPlan
    {
        $this->ensureUserCanEdit($travelPlan, $user);
        $this->validateTravelPlanData($data, $travelPlan);

        return $this->repository->update($travelPlan, [
            'plan_name' => $data['plan_name'],
            'owner_user_id' => $data['owner_user_id'] ?? $travelPlan->owner_user_id,
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'] ?? null,
            'timezone' => $data['timezone'] ?? $travelPlan->timezone,
            'is_active' => $data['is_active'] ?? $travelPlan->is_active,
            'description' => $data['description'] ?? null,
        ]);
    }

    public function deleteTravelPlan(TravelPlan $travelPlan, User $user): bool
    {
        $this->ensureUserCanDelete($travelPlan, $user);
        $this->ensureCanDelete($travelPlan);

        return $this->repository->delete($travelPlan);
    }

    public function getTravelPlan(string $uuid, User $user): ?TravelPlan
    {
        $travelPlan = $this->repository->findByUuid($uuid);

        if (! $travelPlan) {
            return null;
        }

        $this->ensureUserCanView($travelPlan, $user);

        return $travelPlan->load(['creator', 'owner', 'groups', 'members']);
    }

    public function getTravelPlanByUuid(string $uuid): ?TravelPlan
    {
        return $this->repository->findByUuid($uuid);
    }

    public function getUserTravelPlans(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findByUser($user, $perPage);
    }

    public function getActiveTravelPlans(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findActiveByUser($user, $perPage);
    }

    public function getUpcomingTravelPlans(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findUpcoming($user, $perPage);
    }

    public function getPastTravelPlans(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findPast($user, $perPage);
    }

    public function searchTravelPlans(User $user, string $searchTerm, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->searchByName($user, $searchTerm, $perPage);
    }

    private function validateTravelPlanData(array $data, ?TravelPlan $existingPlan = null): void
    {
        // 出発日の妥当性チェック
        $departureDate = Carbon::parse($data['departure_date']);

        // 帰国日が設定されている場合、出発日より後であることをチェック
        if (! empty($data['return_date'])) {
            $returnDate = Carbon::parse($data['return_date']);
            if ($returnDate->lte($departureDate)) {
                throw ValidationException::withMessages([
                    'return_date' => ['帰国日は出発日より後の日付を指定してください。'],
                ]);
            }
        }

        // 計画名の重複チェック（同一ユーザー内）
        // TODO: 必要に応じて実装
    }

    private function ensureUserCanView(TravelPlan $travelPlan, User $user): void
    {
        $isMember = $travelPlan->members()->where('user_id', $user->id)->exists();

        if (! $isMember) {
            throw new \Exception('この旅行プランにアクセスする権限がありません。');
        }
    }

    private function ensureUserCanEdit(TravelPlan $travelPlan, User $user): void
    {
        $isMember = $travelPlan->members()->where('user_id', $user->id)->exists();

        if (! $isMember) {
            throw new \Exception('この旅行プランを編集する権限がありません。');
        }
    }

    private function ensureUserCanDelete(TravelPlan $travelPlan, User $user): void
    {
        if ($travelPlan->owner_user_id !== $user->id) {
            throw new \Exception('この旅行プランを削除する権限がありません。所有者のみが削除できます。');
        }
    }

    private function ensureCanDelete(TravelPlan $travelPlan): void
    {
        // 旅行開始後は削除できない
        $departureDate = Carbon::parse($travelPlan->departure_date);
        if ($departureDate->isPast()) {
            throw new \Exception('旅行開始後の計画は削除できません。');
        }
    }
}

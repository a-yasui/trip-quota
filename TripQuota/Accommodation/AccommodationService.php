<?php

namespace TripQuota\Accommodation;

use App\Models\Accommodation;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use TripQuota\Member\MemberRepositoryInterface;

class AccommodationService
{
    public function __construct(
        private AccommodationRepositoryInterface $accommodationRepository,
        private MemberRepositoryInterface $memberRepository
    ) {}

    public function createAccommodation(TravelPlan $travelPlan, User $user, array $data): Accommodation
    {
        $this->ensureUserCanManageAccommodations($travelPlan, $user);
        $this->validateAccommodationData($data, $travelPlan);

        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        return DB::transaction(function () use ($travelPlan, $member, $data) {
            $accommodation = $this->accommodationRepository->create([
                'travel_plan_id' => $travelPlan->id,
                'created_by_member_id' => $member->id,
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'check_in_time' => $data['check_in_time'] ?? null,
                'check_out_time' => $data['check_out_time'] ?? null,
                'price_per_night' => $data['price_per_night'] ?? null,
                'currency' => $data['currency'] ?? 'JPY',
                'notes' => $data['notes'] ?? null,
                'confirmation_number' => $data['confirmation_number'] ?? null,
            ]);

            // Assign members if specified
            if (isset($data['member_ids']) && is_array($data['member_ids'])) {
                $this->assignMembersToAccommodation($accommodation, $data['member_ids'], $travelPlan);
            }

            return $accommodation;
        });
    }

    public function updateAccommodation(Accommodation $accommodation, User $user, array $data): Accommodation
    {
        $this->ensureUserCanEditAccommodation($accommodation, $user);
        $this->validateAccommodationData($data, $accommodation->travelPlan);

        return DB::transaction(function () use ($accommodation, $data) {
            $accommodation = $this->accommodationRepository->update($accommodation, [
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'check_in_time' => $data['check_in_time'] ?? null,
                'check_out_time' => $data['check_out_time'] ?? null,
                'price_per_night' => $data['price_per_night'] ?? null,
                'currency' => $data['currency'] ?? 'JPY',
                'notes' => $data['notes'] ?? null,
                'confirmation_number' => $data['confirmation_number'] ?? null,
            ]);

            // Update member assignments if specified
            if (isset($data['member_ids']) && is_array($data['member_ids'])) {
                $this->assignMembersToAccommodation($accommodation, $data['member_ids'], $accommodation->travelPlan);
            }

            return $accommodation;
        });
    }

    public function deleteAccommodation(Accommodation $accommodation, User $user): bool
    {
        $this->ensureUserCanEditAccommodation($accommodation, $user);

        return $this->accommodationRepository->delete($accommodation);
    }

    public function getAccommodationsForTravelPlan(TravelPlan $travelPlan, User $user): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureUserCanViewAccommodations($travelPlan, $user);

        return $this->accommodationRepository->findByTravelPlan($travelPlan);
    }

    public function getAccommodationsByDateRange(TravelPlan $travelPlan, User $user, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureUserCanViewAccommodations($travelPlan, $user);

        return $this->accommodationRepository->findByDateRange($travelPlan, $startDate, $endDate);
    }

    private function ensureUserCanViewAccommodations(TravelPlan $travelPlan, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member) {
            throw new \Exception('この旅行プランの宿泊施設を表示する権限がありません。');
        }
    }

    private function ensureUserCanManageAccommodations(TravelPlan $travelPlan, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member || ! $member->is_confirmed) {
            throw new \Exception('宿泊施設を管理する権限がありません。');
        }
    }

    private function ensureUserCanEditAccommodation(Accommodation $accommodation, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($accommodation->travelPlan, $user);

        if (! $member || ! $member->is_confirmed) {
            throw new \Exception('この宿泊施設を編集する権限がありません。');
        }

        // Only the creator or other confirmed members can edit
        // For now, allow all confirmed members to edit any accommodation
    }

    private function validateAccommodationData(array $data, TravelPlan $travelPlan): void
    {
        $checkInDate = \Carbon\Carbon::parse($data['check_in_date']);
        $checkOutDate = \Carbon\Carbon::parse($data['check_out_date']);

        if ($checkOutDate->lte($checkInDate)) {
            throw new \Exception('チェックアウト日はチェックイン日より後の日付である必要があります。');
        }

        // Validate dates are within travel plan period
        if ($checkInDate->lt($travelPlan->departure_date)) {
            throw new \Exception('チェックイン日は旅行開始日以降である必要があります。');
        }

        if ($travelPlan->return_date && $checkOutDate->gt($travelPlan->return_date)) {
            throw new \Exception('チェックアウト日は旅行終了日以前である必要があります。');
        }
    }

    private function assignMembersToAccommodation(Accommodation $accommodation, array $memberIds, TravelPlan $travelPlan): void
    {
        // Validate all member IDs belong to the travel plan
        $validMembers = $this->memberRepository->findByTravelPlan($travelPlan)
            ->where('is_confirmed', true)
            ->pluck('id')
            ->toArray();

        $invalidMemberIds = array_diff($memberIds, $validMembers);
        if (! empty($invalidMemberIds)) {
            throw new \Exception('無効なメンバーIDが含まれています。');
        }

        $accommodation->members()->sync($memberIds);
    }
}

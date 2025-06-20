<?php

namespace TripQuota\Itinerary;

use App\Models\Group;
use App\Models\Itinerary;
use App\Models\TravelPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ItineraryService
{
    public function __construct(
        private ItineraryRepositoryInterface $itineraryRepository
    ) {}

    public function getItinerariesByTravelPlan(TravelPlan $travelPlan, User $user): Collection
    {
        $this->ensureUserCanViewItineraries($travelPlan, $user);

        return $this->itineraryRepository->findByTravelPlan($travelPlan);
    }

    public function getItinerariesByGroup(TravelPlan $travelPlan, Group $group, User $user): Collection
    {
        $this->ensureUserCanViewItineraries($travelPlan, $user);
        $this->ensureGroupBelongsToTravelPlan($group, $travelPlan);

        return $this->itineraryRepository->findByTravelPlanAndGroup($travelPlan, $group);
    }

    public function getItinerariesByDate(TravelPlan $travelPlan, Carbon $date, User $user): Collection
    {
        $this->ensureUserCanViewItineraries($travelPlan, $user);

        return $this->itineraryRepository->findByTravelPlanAndDate($travelPlan, $date);
    }

    public function getItinerariesByDateRange(TravelPlan $travelPlan, Carbon $startDate, Carbon $endDate, User $user): Collection
    {
        $this->ensureUserCanViewItineraries($travelPlan, $user);

        return $this->itineraryRepository->findByTravelPlanDateRange($travelPlan, $startDate, $endDate);
    }

    public function createItinerary(TravelPlan $travelPlan, User $user, array $data): Itinerary
    {
        $this->ensureUserCanManageItineraries($travelPlan, $user);
        $this->validateItineraryData($data);
        $this->validateDateWithinTravelPeriod($data, $travelPlan);

        return DB::transaction(function () use ($travelPlan, $user, $data) {
            // 作成者のメンバー情報を取得
            $member = $travelPlan->members()->where('user_id', $user->id)->first();
            if (! $member) {
                throw ValidationException::withMessages([
                    'user' => ['このユーザーは旅行プランのメンバーではありません。'],
                ]);
            }

            // グループの検証
            if (isset($data['group_id'])) {
                $group = Group::find($data['group_id']);
                $this->ensureGroupBelongsToTravelPlan($group, $travelPlan);
                $this->ensureUserBelongsToGroup($user, $group);
            }

            // 旅程作成データを準備
            $itineraryData = array_merge($data, [
                'travel_plan_id' => $travelPlan->id,
                'created_by_member_id' => $member->id,
            ]);

            // 時刻の妥当性チェック
            if (isset($data['start_time']) && isset($data['end_time'])) {
                $this->validateTimeRange($data['start_time'], $data['end_time'], $data['date'], $data['arrival_date'] ?? null);
            }

            // 旅程を作成
            $itinerary = $this->itineraryRepository->create($itineraryData);

            // メンバーの割り当て
            if (isset($data['member_ids']) && is_array($data['member_ids'])) {
                $this->assignMembersToItinerary($itinerary, $data['member_ids'], $travelPlan);
            } else {
                // デフォルトで作成者を割り当て
                $this->itineraryRepository->attachMembers($itinerary, [$member->id]);
            }

            return $itinerary->load(['travelPlan', 'group', 'createdBy', 'members']);
        });
    }

    public function updateItinerary(Itinerary $itinerary, User $user, array $data): Itinerary
    {
        $this->ensureUserCanEditItinerary($itinerary, $user);
        $this->validateItineraryData($data, $itinerary->id);

        return DB::transaction(function () use ($itinerary, $data) {
            // グループの検証
            if (isset($data['group_id'])) {
                $group = Group::find($data['group_id']);
                $this->ensureGroupBelongsToTravelPlan($group, $itinerary->travelPlan);
            }

            // 時刻の妥当性チェック
            if (isset($data['start_time']) && isset($data['end_time'])) {
                $this->validateTimeRange($data['start_time'], $data['end_time'], $data['date'], $data['arrival_date'] ?? null);
            }

            // 旅程を更新
            $updatedItinerary = $this->itineraryRepository->update($itinerary, $data);

            // メンバーの割り当て更新
            if (isset($data['member_ids']) && is_array($data['member_ids'])) {
                $this->assignMembersToItinerary($updatedItinerary, $data['member_ids'], $itinerary->travelPlan);
            }

            return $updatedItinerary;
        });
    }

    public function deleteItinerary(Itinerary $itinerary, User $user): bool
    {
        $this->ensureUserCanDeleteItinerary($itinerary, $user);

        return $this->itineraryRepository->delete($itinerary);
    }

    public function assignMembersToItinerary(Itinerary $itinerary, array $memberIds, TravelPlan $travelPlan): void
    {
        // メンバーIDの妥当性チェック
        $validMemberIds = $travelPlan->members()->whereIn('id', $memberIds)->pluck('id')->toArray();

        if (count($validMemberIds) !== count($memberIds)) {
            throw ValidationException::withMessages([
                'member_ids' => ['無効なメンバーIDが含まれています。'],
            ]);
        }

        $this->itineraryRepository->syncMembers($itinerary, $validMemberIds);
    }

    public function getMemberParticipationStats(TravelPlan $travelPlan, User $user): array
    {
        $this->ensureUserCanViewItineraries($travelPlan, $user);

        $itineraries = $this->itineraryRepository->findByTravelPlan($travelPlan);
        $totalMembers = $travelPlan->members()->where('is_confirmed', true)->count();
        $totalItineraries = $itineraries->count();

        $memberStats = [];
        foreach ($travelPlan->members()->where('is_confirmed', true)->get() as $member) {
            $participationCount = $itineraries->filter(function ($itinerary) use ($member) {
                return $itinerary->members->contains('id', $member->id);
            })->count();

            $memberStats[] = [
                'member' => $member,
                'participation_count' => $participationCount,
                'participation_rate' => $totalItineraries > 0 ? round(($participationCount / $totalItineraries) * 100, 1) : 0,
            ];
        }

        // 参加率でソート
        usort($memberStats, function ($a, $b) {
            return $b['participation_rate'] <=> $a['participation_rate'];
        });

        return [
            'total_members' => $totalMembers,
            'total_itineraries' => $totalItineraries,
            'member_stats' => $memberStats,
        ];
    }

    private function ensureUserCanViewItineraries(TravelPlan $travelPlan, User $user): void
    {
        $member = $travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'authorization' => ['この旅行プランの旅程を閲覧する権限がありません。'],
            ]);
        }
    }

    private function ensureUserCanManageItineraries(TravelPlan $travelPlan, User $user): void
    {
        $member = $travelPlan->members()
            ->where('user_id', $user->id)
            ->where('is_confirmed', true)
            ->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'authorization' => ['旅程を管理する権限がありません。確認済みメンバーである必要があります。'],
            ]);
        }
    }

    private function ensureUserCanEditItinerary(Itinerary $itinerary, User $user): void
    {
        // 作成者、または旅行プランの所有者・作成者のみ編集可能
        $member = $itinerary->travelPlan->members()->where('user_id', $user->id)->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'authorization' => ['この旅程を編集する権限がありません。'],
            ]);
        }

        $isCreator = $itinerary->created_by_member_id === $member->id;
        $isOwner = $itinerary->travelPlan->owner_user_id === $user->id;
        $isPlanCreator = $itinerary->travelPlan->creator_user_id === $user->id;

        if (! ($isCreator || $isOwner || $isPlanCreator)) {
            throw ValidationException::withMessages([
                'authorization' => ['この旅程を編集できるのは作成者または旅行プラン管理者のみです。'],
            ]);
        }
    }

    private function ensureUserCanDeleteItinerary(Itinerary $itinerary, User $user): void
    {
        // 編集と同じ権限チェック
        $this->ensureUserCanEditItinerary($itinerary, $user);
    }

    private function ensureGroupBelongsToTravelPlan(Group $group, TravelPlan $travelPlan): void
    {
        if ($group->travel_plan_id !== $travelPlan->id) {
            throw ValidationException::withMessages([
                'group_id' => ['指定されたグループはこの旅行プランに属していません。'],
            ]);
        }
    }

    private function ensureUserBelongsToGroup(User $user, Group $group): void
    {
        $member = $group->members()->where('user_id', $user->id)->first();
        if (! $member) {
            throw ValidationException::withMessages([
                'group_id' => ['指定されたグループのメンバーではありません。'],
            ]);
        }
    }

    private function validateItineraryData(array $data, ?int $excludeId = null): void
    {
        // 必須フィールドのチェック
        if (empty($data['title'])) {
            throw ValidationException::withMessages([
                'title' => ['タイトルは必須です。'],
            ]);
        }

        if (empty($data['date'])) {
            throw ValidationException::withMessages([
                'date' => ['日付は必須です。'],
            ]);
        }

        // 日付の妥当性チェック
        try {
            $date = Carbon::parse($data['date']);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'date' => ['有効な日付を入力してください。'],
            ]);
        }

        // 到着日の妥当性チェック（設定されている場合）
        if (!empty($data['arrival_date'])) {
            try {
                $arrivalDate = Carbon::parse($data['arrival_date']);
                
                // 到着日は出発日以降である必要がある
                if ($arrivalDate->lt($date)) {
                    throw ValidationException::withMessages([
                        'arrival_date' => ['到着日は出発日以降である必要があります。'],
                    ]);
                }
            } catch (ValidationException $e) {
                // 再スロー (到着日の比較エラー)
                throw $e;
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'arrival_date' => ['有効な到着日を入力してください。'],
                ]);
            }
        }

        // 移動手段固有の検証
        if (isset($data['transportation_type'])) {
            $this->validateTransportationSpecificFields($data);
        }
    }

    private function validateTimeRange(?string $startTime, ?string $endTime, ?string $departureDate = null, ?string $arrivalDate = null): void
    {
        if ($startTime && $endTime) {
            // 日付が指定されている場合は日時として比較
            if ($departureDate) {
                $arrivalDateToUse = $arrivalDate ?: $departureDate;
                
                $departureDateTime = Carbon::createFromFormat('Y-m-d H:i', $departureDate . ' ' . $startTime);
                $arrivalDateTime = Carbon::createFromFormat('Y-m-d H:i', $arrivalDateToUse . ' ' . $endTime);

                // 同じ日時は許可しない
                if ($arrivalDateTime->eq($departureDateTime)) {
                    throw ValidationException::withMessages([
                        'end_time' => ['到着時刻は出発時刻と異なる時刻に設定してください。'],
                    ]);
                }

                // 到着日時が出発日時より前の場合はエラー
                if ($arrivalDateTime->lt($departureDateTime)) {
                    throw ValidationException::withMessages([
                        'end_time' => ['到着日時は出発日時より後に設定してください。'],
                    ]);
                }

                // 移動時間が48時間を超える場合は警告
                $duration = $arrivalDateTime->diffInHours($departureDateTime);
                if ($duration > 48) {
                    throw ValidationException::withMessages([
                        'end_time' => ['移動時間が48時間を超えています。到着日時を確認してください。'],
                    ]);
                }
            } else {
                // 旧来の時刻のみの比較（後方互換性）
                $start = Carbon::parse($startTime);
                $end = Carbon::parse($endTime);

                if ($end->lte($start)) {
                    throw ValidationException::withMessages([
                        'end_time' => ['終了時刻は開始時刻より後である必要があります。'],
                    ]);
                }
            }
        }
    }

    private function validateDateWithinTravelPeriod(array $data, TravelPlan $travelPlan): void
    {
        if (empty($data['date'])) {
            return;
        }

        $date = Carbon::parse($data['date']);
        $departureDate = $travelPlan->departure_date;
        $returnDate = $travelPlan->return_date;

        if ($date->lt($departureDate)) {
            throw ValidationException::withMessages([
                'date' => ['旅程の日付は旅行開始日以降である必要があります。'],
            ]);
        }

        if ($returnDate && $date->gt($returnDate)) {
            throw ValidationException::withMessages([
                'date' => ['旅程の日付は旅行終了日以前である必要があります。'],
            ]);
        }
    }

    private function validateTransportationSpecificFields(array $data): void
    {
        $transportationType = $data['transportation_type'];

        switch ($transportationType) {
            case 'airplane':
                if (empty($data['airline'])) {
                    throw ValidationException::withMessages([
                        'airline' => ['飛行機の場合、航空会社は必須です。'],
                    ]);
                }
                if (empty($data['flight_number'])) {
                    throw ValidationException::withMessages([
                        'flight_number' => ['飛行機の場合、便名は必須です。'],
                    ]);
                }
                break;

            case 'train':
                if (empty($data['train_line'])) {
                    throw ValidationException::withMessages([
                        'train_line' => ['電車の場合、路線名は必須です。'],
                    ]);
                }
                break;

            case 'bus':
            case 'ferry':
                // バス・フェリーは会社名があると良いが必須ではない
                break;
        }
    }
}

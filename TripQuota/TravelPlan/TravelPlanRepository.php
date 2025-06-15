<?php

namespace TripQuota\TravelPlan;

use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TravelPlanRepository implements TravelPlanRepositoryInterface
{
    public function create(array $data): TravelPlan
    {
        $data['uuid'] = Str::uuid();

        return TravelPlan::create($data);
    }

    public function update(TravelPlan $travelPlan, array $data): TravelPlan
    {
        $travelPlan->update($data);

        return $travelPlan->fresh();
    }

    public function delete(TravelPlan $travelPlan): bool
    {
        return $travelPlan->delete();
    }

    public function findById(int $id): ?TravelPlan
    {
        return TravelPlan::find($id);
    }

    public function findByUuid(string $uuid): ?TravelPlan
    {
        return TravelPlan::where('uuid', $uuid)->first();
    }

    public function findByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return TravelPlan::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['creator', 'owner', 'members'])
            ->orderBy('departure_date', 'desc')
            ->paginate($perPage);
    }

    public function findActiveByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return TravelPlan::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('is_active', true)
            ->with(['creator', 'owner', 'members'])
            ->orderBy('departure_date', 'desc')
            ->paginate($perPage);
    }

    public function findUpcoming(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return TravelPlan::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('departure_date', '>=', now()->toDateString())
            ->where('is_active', true)
            ->with(['creator', 'owner', 'members'])
            ->orderBy('departure_date', 'asc')
            ->paginate($perPage);
    }

    public function findPast(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return TravelPlan::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('return_date', '<', now()->toDateString())
            ->with(['creator', 'owner', 'members'])
            ->orderBy('departure_date', 'desc')
            ->paginate($perPage);
    }

    public function searchByName(User $user, string $searchTerm, int $perPage = 15): LengthAwarePaginator
    {
        return TravelPlan::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('plan_name', 'like', '%'.$searchTerm.'%')
            ->with(['creator', 'owner', 'members'])
            ->orderBy('departure_date', 'desc')
            ->paginate($perPage);
    }
}

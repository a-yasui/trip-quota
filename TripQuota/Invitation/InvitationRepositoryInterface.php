<?php

namespace TripQuota\Invitation;

use App\Models\GroupInvitation;
use App\Models\TravelPlan;

interface InvitationRepositoryInterface
{
    public function create(array $data): GroupInvitation;

    public function update(GroupInvitation $invitation, array $data): GroupInvitation;

    public function delete(GroupInvitation $invitation): bool;

    public function findById(int $id): ?GroupInvitation;

    public function findByToken(string $token): ?GroupInvitation;

    public function findByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection;

    public function findPendingByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection;

    public function findByEmail(string $email): \Illuminate\Database\Eloquent\Collection;

    public function generateUniqueToken(): string;

    public function deleteExpiredInvitations(): int;
}

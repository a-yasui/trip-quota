<?php

namespace TripQuota\Invitation;

use App\Models\GroupInvitation;
use App\Models\TravelPlan;
use Illuminate\Support\Str;

class InvitationRepository implements InvitationRepositoryInterface
{
    public function create(array $data): GroupInvitation
    {
        return GroupInvitation::create($data);
    }

    public function update(GroupInvitation $invitation, array $data): GroupInvitation
    {
        $invitation->update($data);

        return $invitation->fresh();
    }

    public function delete(GroupInvitation $invitation): bool
    {
        return $invitation->delete();
    }

    public function findById(int $id): ?GroupInvitation
    {
        return GroupInvitation::find($id);
    }

    public function findByToken(string $token): ?GroupInvitation
    {
        return GroupInvitation::where('invitation_token', $token)
            ->with(['travelPlan', 'group', 'invitedBy'])
            ->first();
    }

    public function findByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection
    {
        return GroupInvitation::where('travel_plan_id', $travelPlan->id)
            ->with(['group', 'invitedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findPendingByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection
    {
        return GroupInvitation::where('travel_plan_id', $travelPlan->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with(['group', 'invitedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByEmail(string $email): \Illuminate\Database\Eloquent\Collection
    {
        return GroupInvitation::where('invitee_email', $email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with(['travelPlan', 'group', 'invitedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (GroupInvitation::where('invitation_token', $token)->exists());

        return $token;
    }

    public function deleteExpiredInvitations(): int
    {
        return GroupInvitation::where('expires_at', '<', now())
            ->orWhere('status', '!=', 'pending')
            ->delete();
    }
}

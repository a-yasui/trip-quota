<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    /**
     * Display a listing of the groups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 認証済みユーザーのメンバー情報を取得
        $user = Auth::user();
        $members = $user->members;
        
        // メンバーが所属するグループを取得
        $groups = collect();
        foreach ($members as $member) {
            $groups->push($member->group);
        }
        
        // 重複を除去
        $groups = $groups->unique('id');
        
        // 現在の日付
        $now = now();
        
        // 未来の旅行計画
        $futureGroups = $groups->filter(function ($group) use ($now) {
            return $group->travelPlan->departure_date > $now;
        })->sortBy(function ($group) {
            return $group->travelPlan->departure_date;
        });
        
        // 現在進行中の旅行計画
        $currentGroups = $groups->filter(function ($group) use ($now) {
            return $group->travelPlan->departure_date <= $now && 
                   ($group->travelPlan->return_date === null || $group->travelPlan->return_date >= $now);
        });
        
        // 過去の旅行計画
        $pastGroups = $groups->filter(function ($group) use ($now) {
            return $group->travelPlan->return_date !== null && $group->travelPlan->return_date < $now;
        })->sortByDesc(function ($group) {
            return $group->travelPlan->return_date;
        });
        
        return view('groups.index', compact('futureGroups', 'currentGroups', 'pastGroups'));
    }
}

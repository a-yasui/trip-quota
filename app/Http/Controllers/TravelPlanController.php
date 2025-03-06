<?php

namespace App\Http\Controllers;

use App\Http\Requests\TravelPlanRequest;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TravelPlanController extends Controller
{
    /**
     * Display a listing of the travel plans.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // ログインユーザーが参加している旅行計画を取得
        $user = Auth::user();
        $travelPlans = TravelPlan::whereHas('groups.members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->orWhere('creator_id', $user->id)
          ->orderBy('departure_date', 'asc')
          ->get();

        return view('travel-plans.index', compact('travelPlans'));
    }

    /**
     * Show the form for creating a new travel plan.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('travel-plans.create');
    }

    /**
     * Store a newly created travel plan in storage.
     *
     * @param  \App\Http\Requests\TravelPlanRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TravelPlanRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            
            // 旅行計画の作成
            $travelPlan = new TravelPlan();
            $travelPlan->title = $request->title;
            $travelPlan->creator_id = $user->id;
            $travelPlan->deletion_permission_holder_id = $user->id;
            $travelPlan->departure_date = $request->departure_date;
            $travelPlan->return_date = $request->return_date;
            $travelPlan->timezone = $request->timezone;
            $travelPlan->is_active = true;
            $travelPlan->save();
            
            // コアグループの作成
            $coreGroup = new Group();
            $coreGroup->name = $travelPlan->title;
            $coreGroup->type = 'core';
            $coreGroup->travel_plan_id = $travelPlan->id;
            $coreGroup->description = 'メインメンバーグループ';
            $coreGroup->save();
            
            // 作成者をコアグループのメンバーとして追加
            $member = new Member();
            $member->name = $user->name;
            $member->email = $user->email;
            $member->user_id = $user->id;
            $member->group_id = $coreGroup->id;
            $member->is_registered = true;
            $member->is_active = true;
            $member->save();
            
            DB::commit();
            
            return redirect()->route('travel-plans.show', $travelPlan)
                ->with('success', '旅行計画「' . $travelPlan->title . '」を作成しました。');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', '旅行計画の作成に失敗しました。' . $e->getMessage());
        }
    }

    /**
     * Display the specified travel plan.
     *
     * @param  \App\Models\TravelPlan  $travelPlan
     * @return \Illuminate\View\View
     */
    public function show(TravelPlan $travelPlan)
    {
        // 旅行計画の詳細情報を取得
        $travelPlan->load(['groups.members', 'accommodations', 'itineraries']);
        
        // コアグループを取得
        $coreGroup = $travelPlan->groups()->where('type', 'core')->first();
        
        // メンバー一覧を取得
        $members = $coreGroup ? $coreGroup->members : collect();
        
        return view('travel-plans.show', compact('travelPlan', 'coreGroup', 'members'));
    }
}

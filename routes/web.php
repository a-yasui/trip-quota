<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TravelPlanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // 旅行計画ルート
    Route::get('/travel-plans', [TravelPlanController::class, 'index'])->name('travel-plans.index');
    Route::get('/travel-plans/create', [TravelPlanController::class, 'create'])->name('travel-plans.create');
    Route::post('/travel-plans', [TravelPlanController::class, 'store'])->name('travel-plans.store');
    Route::get('/travel-plans/{travelPlan}', [TravelPlanController::class, 'show'])->name('travel-plans.show');
    Route::get('/travel-plans/{travelPlan}/edit', [TravelPlanController::class, 'edit'])->name('travel-plans.edit');
    Route::put('/travel-plans/{travelPlan}', [TravelPlanController::class, 'update'])->name('travel-plans.update');
    
    // グループルート
    Route::get('/groups', [App\Http\Controllers\GroupController::class, 'index'])->name('groups.index');
    
    // グループメンバー管理
    Route::get('/groups/{group}/members/create', [App\Http\Controllers\GroupMemberController::class, 'create'])->name('groups.members.create');
    Route::post('/groups/{group}/members', [App\Http\Controllers\GroupMemberController::class, 'store'])->name('groups.members.store');
    Route::delete('/groups/{group}/members/{member}', [App\Http\Controllers\GroupMemberController::class, 'destroy'])->name('groups.members.destroy');
    
    // 班グループ管理
    Route::get('/travel-plans/{travelPlan}/branch-groups/create', [App\Http\Controllers\BranchGroupController::class, 'create'])->name('travel-plans.branch-groups.create');
    Route::post('/travel-plans/{travelPlan}/branch-groups', [App\Http\Controllers\BranchGroupController::class, 'store'])->name('travel-plans.branch-groups.store');
    Route::get('/branch-groups/{group}', [App\Http\Controllers\BranchGroupController::class, 'show'])->name('branch-groups.show');
    Route::get('/branch-groups/{group}/edit', [App\Http\Controllers\BranchGroupController::class, 'edit'])->name('branch-groups.edit');
    Route::put('/branch-groups/{group}', [App\Http\Controllers\BranchGroupController::class, 'update'])->name('branch-groups.update');
    Route::delete('/branch-groups/{group}', [App\Http\Controllers\BranchGroupController::class, 'destroy'])->name('branch-groups.destroy');
    Route::get('/branch-groups/{group}/duplicate', [App\Http\Controllers\BranchGroupController::class, 'duplicate'])->name('branch-groups.duplicate');
    Route::post('/branch-groups/{group}/duplicate', [App\Http\Controllers\BranchGroupController::class, 'storeDuplicate'])->name('branch-groups.store-duplicate');
    
    // 班グループメンバー管理
    Route::post('/branch-groups/{group}/members', [App\Http\Controllers\BranchGroupMemberController::class, 'store'])->name('branch-groups.members.store');
    Route::delete('/branch-groups/{group}/members/{member}', [App\Http\Controllers\BranchGroupMemberController::class, 'destroy'])->name('branch-groups.members.destroy');
    
    // 旅程管理ルート
    Route::resource('travel-plans.itineraries', ItineraryController::class);
    
    // 経費ルート
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);
    Route::get('/travel-plans/{travelPlan}/expenses/create', [\App\Http\Controllers\ExpenseController::class, 'create'])->name('travel-plans.expenses.create');
    Route::post('/travel-plans/{travelPlan}/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('travel-plans.expenses.store');
    
    // 経費メンバーの支払い状態を更新
    Route::patch('/expenses/{expense}/members/{member}/toggle-payment', [\App\Http\Controllers\ExpenseController::class, 'togglePaymentStatus'])
        ->name('expenses.members.toggle-payment');
});

require __DIR__.'/auth.php';

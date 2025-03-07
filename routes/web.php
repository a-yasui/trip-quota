<?php

use App\Http\Controllers\DashboardController;
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
    Route::get('/groups', function () {
        return view('welcome'); // 仮のビュー
    })->name('groups.index');
    
    // グループメンバー管理
    Route::get('/groups/{group}/members/create', [App\Http\Controllers\GroupMemberController::class, 'create'])->name('groups.members.create');
    Route::post('/groups/{group}/members', [App\Http\Controllers\GroupMemberController::class, 'store'])->name('groups.members.store');
    Route::delete('/groups/{group}/members/{member}', [App\Http\Controllers\GroupMemberController::class, 'destroy'])->name('groups.members.destroy');
    
    // 経費ルート
    Route::get('/expenses', function () {
        return view('welcome'); // 仮のビュー
    })->name('expenses.index');
});

require __DIR__.'/auth.php';

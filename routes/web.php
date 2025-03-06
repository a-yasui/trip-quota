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
    
    // グループルート
    Route::get('/groups', function () {
        return view('welcome'); // 仮のビュー
    })->name('groups.index');
    
    // 経費ルート
    Route::get('/expenses', function () {
        return view('welcome'); // 仮のビュー
    })->name('expenses.index');
});

require __DIR__.'/auth.php';

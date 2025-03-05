<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Travel Plans
Route::get('/travel-plans', [App\Http\Controllers\TravelPlanController::class, 'index'])->name('travel-plans.index');
Route::get('/travel-plans/create', [App\Http\Controllers\TravelPlanController::class, 'create'])->name('travel-plans.create');

// Groups
Route::get('/groups', function () {
    return view('welcome'); // Placeholder
})->name('groups.index');

// Expenses
Route::get('/expenses', function () {
    return view('welcome'); // Placeholder
})->name('expenses.index');

// Profile
Route::get('/profile/edit', function () {
    return view('welcome'); // Placeholder
})->name('profile.edit');

// Settings
Route::get('/settings', function () {
    return view('welcome'); // Placeholder
})->name('settings');

// Vue.js Test Page
Route::get('/vue-test', function () {
    return view('vue-test');
})->name('vue-test');

// Authentication
Route::post('/logout', function () {
    return redirect('/'); // Placeholder
})->name('logout');

<?php

use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TravelPlanController;
use Illuminate\Support\Facades\Route;

// ウェルカムページ
Route::get('/', function () {
    return view('welcome');
});

// 認証関連ルート（ゲストのみ）
Route::middleware('guest')->group(function () {
    // ログイン
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // 新規登録
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // OAuth認証
    Route::get('/auth/{provider}', [SocialiteController::class, 'redirect'])->name('oauth.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('oauth.callback');
});

// 認証が必要なルート
Route::middleware('auth')->group(function () {
    // ログアウト
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 旅行プラン管理
    Route::resource('travel-plans', TravelPlanController::class)->parameters([
        'travel-plans' => 'uuid',
    ]);

    // グループ管理（旅行プラン配下）
    Route::get('travel-plans/{uuid}/groups', [GroupController::class, 'index'])->name('travel-plans.groups.index');
    Route::get('travel-plans/{uuid}/groups/create', [GroupController::class, 'create'])->name('travel-plans.groups.create');
    Route::post('travel-plans/{uuid}/groups', [GroupController::class, 'store'])->name('travel-plans.groups.store');
    Route::get('travel-plans/{uuid}/groups/{group}', [GroupController::class, 'show'])->name('travel-plans.groups.show');
    Route::get('travel-plans/{uuid}/groups/{group}/edit', [GroupController::class, 'edit'])->name('travel-plans.groups.edit');
    Route::put('travel-plans/{uuid}/groups/{group}', [GroupController::class, 'update'])->name('travel-plans.groups.update');
    Route::patch('travel-plans/{uuid}/groups/{group}', [GroupController::class, 'update'])->name('travel-plans.groups.update');
    Route::delete('travel-plans/{uuid}/groups/{group}', [GroupController::class, 'destroy'])->name('travel-plans.groups.destroy');

    // グループメンバー管理
    Route::post('travel-plans/{uuid}/groups/{group}/members', [GroupController::class, 'addMember'])->name('travel-plans.groups.add-member');
    Route::delete('travel-plans/{uuid}/groups/{group}/members/{member}', [GroupController::class, 'removeMember'])->name('travel-plans.groups.remove-member');

    // メンバー招待（別ルート）
    Route::get('travel-plans/{uuid}/members/invite', [MemberController::class, 'create'])->name('travel-plans.members.create');
    Route::post('travel-plans/{uuid}/members/invite', [MemberController::class, 'store'])->name('travel-plans.members.store');

    // メンバー管理（旅行プラン配下）
    Route::get('travel-plans/{uuid}/members', [MemberController::class, 'index'])->name('travel-plans.members.index');
    Route::get('travel-plans/{uuid}/members/{member}', [MemberController::class, 'show'])->name('travel-plans.members.show');
    Route::get('travel-plans/{uuid}/members/{member}/edit', [MemberController::class, 'edit'])->name('travel-plans.members.edit');
    Route::put('travel-plans/{uuid}/members/{member}', [MemberController::class, 'update'])->name('travel-plans.members.update');
    Route::patch('travel-plans/{uuid}/members/{member}', [MemberController::class, 'update'])->name('travel-plans.members.update');
    Route::delete('travel-plans/{uuid}/members/{member}', [MemberController::class, 'destroy'])->name('travel-plans.members.destroy');

    // 旅程タイムライン表示（resourceルートより先に定義）
    Route::get('travel-plans/{uuid}/itineraries/timeline', [ItineraryController::class, 'timeline'])->name('travel-plans.itineraries.timeline');

    // 旅程管理（旅行プラン配下）
    Route::resource('travel-plans.itineraries', ItineraryController::class)->parameters([
        'travel-plans' => 'uuid',
    ])->names([
        'index' => 'travel-plans.itineraries.index',
        'create' => 'travel-plans.itineraries.create',
        'store' => 'travel-plans.itineraries.store',
        'show' => 'travel-plans.itineraries.show',
        'edit' => 'travel-plans.itineraries.edit',
        'update' => 'travel-plans.itineraries.update',
        'destroy' => 'travel-plans.itineraries.destroy',
    ]);

    // 宿泊施設管理（旅行プラン配下）
    Route::get('travel-plans/{uuid}/accommodations', [AccommodationController::class, 'index'])->name('travel-plans.accommodations.index');
    Route::get('travel-plans/{uuid}/accommodations/create', [AccommodationController::class, 'create'])->name('travel-plans.accommodations.create');
    Route::post('travel-plans/{uuid}/accommodations', [AccommodationController::class, 'store'])->name('travel-plans.accommodations.store');
    Route::get('travel-plans/{uuid}/accommodations/{accommodation}', [AccommodationController::class, 'show'])->name('travel-plans.accommodations.show');
    Route::get('travel-plans/{uuid}/accommodations/{accommodation}/edit', [AccommodationController::class, 'edit'])->name('travel-plans.accommodations.edit');
    Route::put('travel-plans/{uuid}/accommodations/{accommodation}', [AccommodationController::class, 'update'])->name('travel-plans.accommodations.update');
    Route::patch('travel-plans/{uuid}/accommodations/{accommodation}', [AccommodationController::class, 'update'])->name('travel-plans.accommodations.update');
    Route::delete('travel-plans/{uuid}/accommodations/{accommodation}', [AccommodationController::class, 'destroy'])->name('travel-plans.accommodations.destroy');

    // 招待管理
    Route::get('invitations', [InvitationController::class, 'index'])->name('invitations.index');
    Route::get('invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
    Route::post('invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('invitations/{token}/decline', [InvitationController::class, 'decline'])->name('invitations.decline');
});

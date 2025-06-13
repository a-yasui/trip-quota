<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\InvitationController;
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
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // 旅行プラン管理
    Route::resource('travel-plans', TravelPlanController::class)->parameters([
        'travel-plans' => 'uuid',
    ]);

    // グループ管理（旅行プラン配下）
    Route::resource('travel-plans.groups', GroupController::class)->parameters([
        'travel-plans' => 'uuid',
    ])->except(['create', 'store'])->names([
        'index' => 'travel-plans.groups.index',
        'show' => 'travel-plans.groups.show',
        'edit' => 'travel-plans.groups.edit',
        'update' => 'travel-plans.groups.update',
        'destroy' => 'travel-plans.groups.destroy',
    ]);

    // 班グループ作成（別ルート）
    Route::get('travel-plans/{uuid}/groups/create', [GroupController::class, 'create'])->name('travel-plans.groups.create');
    Route::post('travel-plans/{uuid}/groups', [GroupController::class, 'store'])->name('travel-plans.groups.store');

    // メンバー管理（旅行プラン配下）
    Route::resource('travel-plans.members', MemberController::class)->parameters([
        'travel-plans' => 'uuid',
    ])->except(['create', 'store'])->names([
        'index' => 'travel-plans.members.index',
        'show' => 'travel-plans.members.show',
        'edit' => 'travel-plans.members.edit',
        'update' => 'travel-plans.members.update',
        'destroy' => 'travel-plans.members.destroy',
    ]);

    // メンバー招待（別ルート）
    Route::get('travel-plans/{uuid}/members/invite', [MemberController::class, 'create'])->name('travel-plans.members.create');
    Route::post('travel-plans/{uuid}/members/invite', [MemberController::class, 'store'])->name('travel-plans.members.store');

    // 招待管理
    Route::get('invitations', [InvitationController::class, 'index'])->name('invitations.index');
    Route::get('invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
    Route::post('invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('invitations/{token}/decline', [InvitationController::class, 'decline'])->name('invitations.decline');
});

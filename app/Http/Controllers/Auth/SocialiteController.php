<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OAuthProvider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * プロバイダーへのリダイレクト
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * プロバイダーからのコールバック処理
     */
    public function callback($provider)
    {
        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => '認証に失敗しました。もう一度お試しください。',
            ]);
        }

        // プロバイダーIDでOAuthレコードを検索
        $oauthProvider = OAuthProvider::where('provider', $provider)
            ->where('provider_user_id', $socialiteUser->getId())
            ->first();

        // 既存のOAuthレコードがある場合
        if ($oauthProvider) {
            // アクセストークンを更新
            $oauthProvider->update([
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken,
                'expires_at' => isset($socialiteUser->expiresIn) ? now()->addSeconds($socialiteUser->expiresIn) : null,
            ]);

            Auth::login($oauthProvider->user);
            return redirect()->intended(route('dashboard'));
        }

        // メールアドレスでユーザーを検索
        $user = User::where('email', $socialiteUser->getEmail())->first();

        // 既存ユーザーがいない場合は新規作成
        if (!$user) {
            $user = User::create([
                'name' => $socialiteUser->getName() ?? $socialiteUser->getNickname() ?? 'User',
                'email' => $socialiteUser->getEmail(),
                'password' => Hash::make(str_random(16)), // ランダムパスワード
                'email_verified_at' => now(), // ソーシャルログインは検証済みとみなす
            ]);
        }

        // OAuthプロバイダーレコードを作成
        $user->oauthProviders()->create([
            'provider' => $provider,
            'provider_user_id' => $socialiteUser->getId(),
            'access_token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken,
            'expires_at' => isset($socialiteUser->expiresIn) ? now()->addSeconds($socialiteUser->expiresIn) : null,
        ]);

        Auth::login($user);
        return redirect()->intended(route('dashboard'));
    }

    /**
     * 既存アカウントとソーシャルアカウントを連携
     */
    public function connect($provider)
    {
        return Socialite::driver($provider)
            ->redirectUrl(route('socialite.connect.callback', ['provider' => $provider]))
            ->redirect();
    }

    /**
     * ソーシャルアカウント連携のコールバック
     */
    public function connectCallback(Request $request, $provider)
    {
        try {
            $socialiteUser = Socialite::driver($provider)
                ->redirectUrl(route('socialite.connect.callback', ['provider' => $provider]))
                ->user();
        } catch (\Exception $e) {
            return redirect()->route('profile.edit')->withErrors([
                'socialite' => '連携に失敗しました。もう一度お試しください。',
            ]);
        }

        $user = $request->user();

        // 既に他のユーザーが連携済みかチェック
        $existingProvider = OAuthProvider::where('provider', $provider)
            ->where('provider_user_id', $socialiteUser->getId())
            ->first();

        if ($existingProvider && $existingProvider->user_id !== $user->id) {
            return redirect()->route('profile.edit')->withErrors([
                'socialite' => 'このソーシャルアカウントは既に他のユーザーと連携されています。',
            ]);
        }

        // 既存の連携があれば更新、なければ作成
        $user->oauthProviders()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_user_id' => $socialiteUser->getId(),
            ],
            [
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken,
                'expires_at' => isset($socialiteUser->expiresIn) ? now()->addSeconds($socialiteUser->expiresIn) : null,
            ]
        );

        return redirect()->route('profile.edit')->with('status', 'socialite-connected');
    }

    /**
     * ソーシャルアカウントの連携解除
     */
    public function disconnect(Request $request, $provider)
    {
        $request->user()->oauthProviders()
            ->where('provider', $provider)
            ->delete();

        return redirect()->route('profile.edit')->with('status', 'socialite-disconnected');
    }
}

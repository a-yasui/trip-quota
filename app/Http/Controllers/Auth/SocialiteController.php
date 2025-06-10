<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\OAuthProvider;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * OAuth認証リダイレクト
     */
    public function redirect(string $provider)
    {
        $this->validateProvider($provider);
        
        return Socialite::driver($provider)->redirect();
    }

    /**
     * OAuth認証コールバック処理
     */
    public function callback(string $provider)
    {
        $this->validateProvider($provider);

        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['error' => 'OAuth認証に失敗しました。']);
        }

        try {
            DB::transaction(function () use ($provider, $socialiteUser) {
                // 既存のOAuth連携をチェック
                $oauthProvider = OAuthProvider::findByProvider($provider, $socialiteUser->getId());
                
                if ($oauthProvider) {
                    // 既存のOAuth連携が見つかった場合、ログイン
                    $this->updateOAuthProvider($oauthProvider, $socialiteUser);
                    Auth::login($oauthProvider->user);
                    return;
                }

                // メールアドレスで既存ユーザーをチェック
                $existingUser = User::where('email', $socialiteUser->getEmail())->first();
                
                if ($existingUser) {
                    // 既存ユーザーにOAuth連携を追加
                    $this->createOAuthProvider($existingUser, $provider, $socialiteUser);
                    Auth::login($existingUser);
                } else {
                    // 新規ユーザー作成
                    $user = $this->createUserWithOAuth($provider, $socialiteUser);
                    Auth::login($user);
                }
            });

            return redirect('/dashboard')->with('success', 'ログインしました。');

        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['error' => 'アカウント処理中にエラーが発生しました。']);
        }
    }

    /**
     * プロバイダーの妥当性チェック
     */
    private function validateProvider(string $provider): void
    {
        $allowedProviders = ['google', 'github'];
        
        if (!in_array($provider, $allowedProviders)) {
            abort(404);
        }
    }

    /**
     * OAuth連携情報を更新
     */
    private function updateOAuthProvider(OAuthProvider $oauthProvider, $socialiteUser): void
    {
        $oauthProvider->update([
            'access_token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken,
            'expires_at' => $socialiteUser->expiresIn ? now()->addSeconds($socialiteUser->expiresIn) : null,
        ]);
    }

    /**
     * OAuth連携を作成
     */
    private function createOAuthProvider(User $user, string $provider, $socialiteUser): OAuthProvider
    {
        return OAuthProvider::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
            'access_token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken,
            'expires_at' => $socialiteUser->expiresIn ? now()->addSeconds($socialiteUser->expiresIn) : null,
        ]);
    }

    /**
     * OAuth認証でユーザーを新規作成
     */
    private function createUserWithOAuth(string $provider, $socialiteUser): User
    {
        // ユーザー作成
        $user = User::create([
            'email' => $socialiteUser->getEmail(),
            'email_verified_at' => now(), // OAuth認証ユーザーは認証済みとする
        ]);

        // OAuth連携作成
        $this->createOAuthProvider($user, $provider, $socialiteUser);

        // アカウント名を生成（重複チェック付き）
        $accountName = $this->generateUniqueAccountName($socialiteUser->getName() ?: $socialiteUser->getNickname());

        // アカウント作成
        Account::create([
            'user_id' => $user->id,
            'account_name' => $accountName,
            'display_name' => $socialiteUser->getName() ?: $accountName,
            'thumbnail_url' => $socialiteUser->getAvatar(),
        ]);

        // ユーザー設定作成
        UserSetting::create([
            'user_id' => $user->id,
            'language' => 'ja',
            'timezone' => 'Asia/Tokyo',
            'email_notifications' => true,
            'push_notifications' => true,
            'currency' => 'JPY',
        ]);

        return $user;
    }

    /**
     * ユニークなアカウント名を生成
     */
    private function generateUniqueAccountName(string $baseName): string
    {
        // 英数字以外を除去して、先頭を英字にする
        $accountName = preg_replace('/[^a-zA-Z0-9]/', '', $baseName);
        if (empty($accountName) || !preg_match('/^[a-zA-Z]/', $accountName)) {
            $accountName = 'user' . $accountName;
        }
        
        // 最低4文字にする
        if (strlen($accountName) < 4) {
            $accountName = $accountName . rand(1000, 9999);
        }

        // 重複チェックして、重複する場合は数字を追加
        $originalName = $accountName;
        $counter = 1;
        
        while (!Account::isAccountNameAvailable($accountName)) {
            $accountName = $originalName . $counter;
            $counter++;
        }

        return strtolower($accountName);
    }
}
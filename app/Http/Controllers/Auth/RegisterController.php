<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * ユーザー登録フォーム表示
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * ユーザー登録処理
     */
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'account_name' => [
                'required',
                'string',
                'min:4',
                'max:20',
                function ($attribute, $value, $fail) {
                    if (!Account::validateAccountName($value)) {
                        $fail('アカウント名は英字で始まり、英数字、アンダースコア、ハイフンのみ使用できます。');
                    }
                    if (!Account::isAccountNameAvailable($value)) {
                        $fail('このアカウント名は既に使用されています。');
                    }
                },
            ],
            'display_name' => 'nullable|string|max:50',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // ユーザー作成
                $user = User::create([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                // アカウント作成
                Account::create([
                    'user_id' => $user->id,
                    'account_name' => $request->account_name,
                    'display_name' => $request->display_name ?: $request->account_name,
                ]);

                // ユーザー設定の初期値作成
                UserSetting::create([
                    'user_id' => $user->id,
                    'language' => 'ja',
                    'timezone' => 'Asia/Tokyo',
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'currency' => 'JPY',
                ]);

                // 自動ログイン
                Auth::login($user);
            });

            return redirect('/dashboard')->with('success', 'アカウントが正常に作成されました。');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'アカウント作成中にエラーが発生しました。']);
        }
    }
}
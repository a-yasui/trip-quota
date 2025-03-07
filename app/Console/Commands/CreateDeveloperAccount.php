<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateDeveloperAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'developer:create-account {--email= : メールアドレス} {--passwd= : パスワード}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '開発者用のアカウントを作成します';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // オプションの取得
        $email = $this->option('email');
        $password = $this->option('passwd');

        // 必須チェック
        if (!$email || !$password) {
            $this->error('メールアドレスとパスワードは必須です');
            return 1;
        }

        // バリデーション
        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email']
        );

        if ($validator->fails()) {
            $this->error('メールアドレスの形式が正しくありません');
            return 1;
        }

        // 既存メールアドレスのチェック
        if (User::where('email', $email)->exists()) {
            $this->error('このメールアドレスは既に使用されています');
            return 1;
        }

        // ユーザーの作成
        $user = User::create([
            'name' => 'Developer',
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->info('開発者アカウントを作成しました');
        $this->table(
            ['ID', 'Name', 'Email'],
            [[$user->id, $user->name, $user->email]]
        );

        return 0;
    }
}

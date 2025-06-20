<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateDeveloperAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'developer:create-account {--email=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '開発者用コマンドで、コンソールからログインできるアカウントを作成します';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');

        // メールアドレスとパスワードの入力チェック
        if (! $email) {
            $email = $this->ask('メールアドレスを入力してください');
        }

        if (! $password) {
            $password = $this->secret('パスワードを入力してください');
        }

        // バリデーション
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('有効なメールアドレスを入力してください');

            return Command::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('パスワードは8文字以上で入力してください');

            return Command::FAILURE;
        }

        try {
            $result = DB::transaction(function () use ($email, $password) {
                // 既存ユーザーのチェック
                $existingUser = User::where('email', $email)->first();
                if ($existingUser) {
                    throw new \Exception("メールアドレス「{$email}」は既に使用されています");
                }

                // ユーザー作成
                $user = User::create([
                    'email' => $email,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]);

                // デフォルトアカウント名を生成
                $accountName = 'dev'.$user->id;

                // アカウント作成
                $account = Account::create([
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'display_name' => 'Developer Account',
                ]);

                return ['user' => $user, 'account' => $account];
            });

            $this->info('開発者アカウントを作成しました:');
            $this->info("  メールアドレス: {$result['user']->email}");
            $this->info("  アカウント名: {$result['account']->account_name}");
            $this->info("  作成日時: {$result['user']->created_at}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("アカウント作成に失敗しました: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}

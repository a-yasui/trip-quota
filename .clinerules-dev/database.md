
## テーブル概要

### 認証・ユーザー関連
- `users`: ユーザー認証情報
- `accounts`: ユーザープロフィール情報（アカウント名、サムネイル画像など）
- `oauth_providers`: OAuth認証プロバイダー連携情報
- `user_settings`: ユーザー設定情報（言語、タイムゾーン、通知設定など）
- `password_reset_tokens`: パスワードリセット用トークン
- `sessions`: ユーザーセッション情報
- `admin_users`: サイト管理者情報

### 旅行計画関連
- `travel_plans`: 旅行計画の基本情報
- `groups`: 旅行メンバーのグループ（コアグループと班グループ）
- `system_branch_group_keys`: 班グループの一意識別子
- `branch_group_connections`: 異なる旅行計画の班グループ間の接続情報
- `members`: 旅行に参加するメンバー情報
- `member_account_associations`: メンバーとアカウントの関連付け（アカウント変更履歴）

### 旅程・宿泊関連
- `accommodations`: 宿泊先情報
- `accommodation_member`: 宿泊先とメンバーの関連付け
- `itineraries`: 旅程情報
- `itinerary_member`: 旅程とメンバーの関連付け
- `travel_locations`: 旅行先の場所情報（観光地、レストランなど）
- `travel_documents`: 旅行関連ドキュメント（チケット、予約確認書など）
- `document_member`: ドキュメントの閲覧権限

### 費用・精算関連
- `expenses`: 旅行中の支出情報
- `expense_member`: 支出とメンバーの関連付け
- `expense_settlements`: 割り勘の最終精算状況
- `currency_exchange_rates`: 通貨間の為替レート

### システム機能関連
- `notifications`: 通知情報
- `group_invitations`: グループへの招待情報
- `activity_logs`: システム内の活動ログ

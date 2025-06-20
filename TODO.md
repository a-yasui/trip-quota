# TripQuota 開発TODO

## 完了済み機能 ✅

### 基本機能
- [x] **Group管理** - コアグループとブランチグループの作成・管理
- [x] **招待システム** - 旅行プランへのメンバー招待機能
- [x] **宿泊施設管理** - ホテル・宿泊施設の予約詳細管理
- [x] **宿泊施設テスト** - 宿泊施設管理の包括的テスト
- [x] **宿泊施設詳細・編集画面** - 宿泊施設の表示・編集ビュー

### 費用管理システム
- [x] **費用ドメインサービス** - ExpenseRepository, ExpenseService実装
- [x] **費用管理Controller・ルート** - 費用のCRUD操作とルート設定
- [x] **費用管理ビュー** - index, create, show, edit画面の実装
- [x] **費用管理テスト** - 包括的なテスト（単体、機能、ビューテスト）

#### 費用管理機能詳細
- 多通貨対応の費用追跡
- 分割請求（等分・カスタム金額）
- メンバー参加確認ワークフロー
- 費用確定プロセス
- ポリシーベース認証
- 包括的バリデーション

### 精算管理システム
- [x] **精算ドメインサービス** - SettlementRepository, SettlementService実装
- [x] **精算計算アルゴリズム** - 債務最適化・多通貨対応精算計算
- [x] **精算管理Controller・ルート** - 精算のCRUD操作とルート設定
- [x] **精算管理ビュー** - index, show画面の実装
- [x] **精算管理テスト** - 包括的なテスト（単体、機能、ビューテスト）
- [x] **データモデル最適化** - is_settled冗長フィールド削除・settled_atベース設計

#### 精算管理機能詳細
- メンバー間債務計算・最適化
- 多通貨精算対応（JPY, USD, EUR, KRW, CNY）
- 精算提案生成・保存
- 精算完了記録・統計情報
- 精算リセット機能
- ポリシーベース認証

### UI統一・UX改善
- [x] **旅程管理改善** - 交通手段に「電車」追加、時刻ラベル変更（出発/到着時刻）
- [x] **メンバー招待修正** - バリデーションエラー修正、フォームフィールドクリア機能追加
- [x] **テンプレート統一** - travel-plansテンプレートのmaster.blade.php統一
- [x] **ナビゲーション統一** - 全ページのサブメニューを下部に統一配置
- [x] **ダッシュボード改善** - メンバー管理アイコン追加、招待一覧ボタンスタイル改善
- [x] **グループ管理修正** - メンバー数0表示問題修正（リレーション事前ロード）
- [x] **費用・精算統合** - 費用管理画面に精算機能統合

### アカウント・認証管理
- [x] **アカウント情報管理画面** - プロフィール表示、基本情報・アカウント一覧・OAuth情報表示
- [x] **パスワード変更機能** - 現在パスワード確認、強度チェック、バリデーション実装
- [x] **プロフィール画面テスト** - ProfileController・ビューの包括的テスト

### 旅程管理システム
- [x] **旅程ドメインサービス** - ItineraryRepository, ItineraryService実装済み
- [x] **旅程管理Controller・ルート** - 旅程のCRUD操作・タイムライン表示・ルート設定
- [x] **旅程管理ビュー** - index, create, show, edit, timeline画面の実装
- [x] **旅程管理テスト** - 包括的なテスト（単体、機能、ビューテスト）

#### 旅程管理機能詳細
- 交通手段詳細管理（飛行機・電車・バス・フェリー）
- 参加者管理とグループ連動選択
- 日付・時刻管理（旅行期間内制約）
- タイムライン表示（日付別グループ化）
- 権限管理（作成者・管理者による編集権限）

### メンバー管理機能改善（2025-06-20完了）
- [x] **メンバー編集バグ修正** - 更新ボタンが削除処理を実行する重大バグの修正
- [x] **グループ所属機能** - メンバー編集でのグループ参加/除外チェックボックス機能
- [x] **条件付きメールアドレス検証** - 既存メールアドレス保持者のみ必須検証
- [x] **表示名のみメンバー登録** - 招待なしで表示名のみでのメンバー即座登録
- [x] **作成者権限によるメンバー管理** - 旅行プラン作成者によるメンバー状態変更権限
- [x] **MemberLinkRequest基盤実装** - ユーザとメンバーの関連付けシステム基盤

## 仕様変更作業（2025-06-20） 🔄

### 1. 費用管理システムの簡素化
- [ ] **is_split_confirmed削除** - 費用の確認フローを削除し、登録金額をそのまま精算対象とする
  - [ ] データベースマイグレーション（is_split_confirmedカラム削除）
  - [ ] ExpenseServiceから確認関連ロジック削除
  - [ ] ExpenseControllerから確認関連メソッド削除
  - [ ] ビューから確認関連UI削除（未確定表示、確認ボタン等）
  - [ ] テストケースの更新（確認フロー関連テスト削除・修正）

### 2. 旅程管理の日付分離
- [ ] **出発・到着日付分離** - 出発日と到着日を別々に管理できるよう変更
  - [ ] データベースマイグレーション（arrival_dateカラム追加）
  - [ ] ItineraryServiceの日付管理ロジック更新
  - [ ] フォームUI更新（到着日入力フィールド追加）
  - [ ] バリデーション更新（到着日は出発日以降）
  - [ ] テストケース更新

### 3. タイムゾーン対応
- [ ] **旅程タイムゾーン** - 出発・到着それぞれのタイムゾーン管理
  - [ ] データベースマイグレーション（departure_timezone, arrival_timezoneカラム追加）
  - [ ] デフォルトタイムゾーンをJSTに設定
  - [ ] フォームUIにタイムゾーン選択追加
  - [ ] 表示時のタイムゾーン変換処理
- [ ] **宿泊施設タイムゾーン** - チェックイン・アウト共通のタイムゾーン管理
  - [ ] データベースマイグレーション（timezoneカラム追加）
  - [ ] デフォルトタイムゾーンをJSTに設定
  - [ ] フォームUIにタイムゾーン選択追加
  - [ ] 表示時のタイムゾーン変換処理

## 進行中・次の優先事項 🚧

### セキュリティ対策 (最優先) 🔒
- [x] **【高】CSRF Protection強化** - 追加確認トークンとRate Limiting実装
- [ ] **【中】Exception Message対策** - エラーメッセージの汎用化
- [ ] **【中】Race Condition対策** - データベーストランザクションと排他制御
- [ ] **【中】Session Security** - セッション再生成の実装
- [ ] **【低】Input Sanitization** - メッセージのサニタイゼーション強化
- [ ] **【低】ログ監視実装** - セキュリティログの構築

### 高優先度
- [x] **メンバー関連付けシステム完成** - MemberLinkRequestの完全実装
  - [x] **関連付けリクエストUI** - メンバー詳細画面での関連付けリクエスト送信フォーム
  - [x] **関連付け承認UI** - 対象ユーザー向けの承認/拒否インターフェース
  - [x] **ダッシュボード統合** - pending関連付けリクエストの表示
  - [ ] **通知システム統合** - 関連付けリクエストの通知機能
- [ ] **レスポンシブ対応** - モバイル・タブレット対応の UI 改善

### 中優先度
- [ ] **関連付けシステムテスト実装** - UI・ワークフローの包括的テスト
- [ ] **通知システム** - 招待・費用共有の通知機能
- [ ] **アカウント作成・切り替え** - ユーザーあたり複数アカウント作成・切り替え機能
- [ ] **プロフィール拡張** - サムネイル画像アップロード・アカウント名変更

### 低優先度
- [ ] **旅行プラン履歴・変更ログ** - 履歴管理機能
- [ ] **跨旅行プランブランチグループ統合** - ブランチグループ統合機能
- [ ] **タイムゾーン管理** - 出発・帰国日のタイムゾーン対応

## 技術的詳細

### アーキテクチャ
- ドメイン駆動設計 (DDD)
- リポジトリパターン
- サービス層による複雑なビジネスロジック
- Laravel 12 + Vue.js
- 包括的テスト戦略（PHPUnit + Vitest）

### データベース構造
- **主要エンティティ**: Users, Accounts, TravelPlans, Groups, Members
- **旅行コンテンツ**: Itineraries, Accommodations, Expenses
- **グループタイプ**: CORE（全メンバー）、BRANCH（サブグループ）
- **費用管理**: ExpenseMembers, ExpenseSettlements（多通貨対応）

## 最新の実装状況

### メンバー管理機能改善（2025-06-20完了）
包括的なメンバー編集・管理機能の改善：

#### 修正されたファイル
```
TripQuota/Member/
└── MemberService.php (表示名のみ登録、確認状態変更、関連付けリクエスト基盤)

app/Http/Controllers/
└── MemberController.php (グループ所属機能、条件付き検証、確認機能)

app/Models/
└── MemberLinkRequest.php (関連付けリクエストモデル)

resources/views/members/
├── create.blade.php (表示名のみ/招待付き選択UI)
├── edit.blade.php (削除フォーム分離、グループ所属チェックボックス)
├── index.blade.php (作成者権限による確認/削除ボタン)
└── show.blade.php (科学的記数法表示削除)

database/migrations/
└── 2025_06_20_053031_create_member_link_requests_table.php

tests/Feature/
└── MemberControllerTest.php (グループ所属テスト追加)
```

#### 解決された重大バグ
- **更新ボタン削除バグ修正**: メンバー編集で「更新」ボタンを押すとメンバーが削除される問題を解決
- **フォーム構造修正**: 削除フォームを更新フォームから分離し、適切な送信先へ処理

#### 新機能実装
- **グループ所属機能**: チェックボックスでグループ参加/除外を管理
- **条件付きメールアドレス検証**: 既存メールアドレス保持者のみ必須、未設定者は任意
- **表示名のみメンバー登録**: 招待なしで即座に確認済み状態でメンバー登録
- **作成者権限**: 旅行プラン作成者によるメンバー確認状態変更・削除権限
- **MemberLinkRequest基盤**: ユーザとメンバーの関連付けシステム基盤実装

#### テスト強化
- グループ参加テスト（join_groups）
- グループ除外テスト（leave_groups）
- 全グループ除外テスト（remove_from_all_groups）
- 無効グループID検証テスト（validates_groups_belong_to_travel_plan）
- **27テスト、86アサーション**、全テスト合格

#### セキュリティ強化
- 旅行プランに属するグループのみ有効
- 不正なグループIDは自動フィルタリング
- 作成者権限による適切な認可制御

### 旅程管理機能（2025-06-18完了）
包括的な旅程管理システムが実装完了：

#### 実装されたファイル
```
TripQuota/Itinerary/
├── ItineraryRepositoryInterface.php
├── ItineraryRepository.php
└── ItineraryService.php

app/Http/Controllers/
├── ItineraryController.php
└── ItineraryRequest.php

resources/views/itineraries/
├── index.blade.php
├── create.blade.php
├── show.blade.php
├── edit.blade.php
└── timeline.blade.php

tests/
├── Unit/TripQuota/ItineraryServiceTest.php
├── Feature/ItineraryControllerTest.php
├── Feature/ItineraryValidationTest.php
├── Feature/ItineraryMemberParticipationTest.php
└── Feature/ItineraryViewTest.php
```

#### 主要機能
- **旅程CRUD操作**: 作成・表示・編集・削除の完全実装
- **交通手段詳細**: 飛行機（航空会社・便名）、電車（路線・列車種別）、バス・フェリー（運営会社）
- **参加者管理**: メンバー選択、グループ連動自動選択
- **日付・時刻管理**: 旅行期間内制約、時刻範囲検証
- **タイムライン表示**: 日付ごとのグループ化、フィルタリング機能
- **権限管理**: 作成者・旅行プラン管理者による編集・削除権限

#### 技術実装
- **バリデーション**: 交通手段別必須フィールド、時刻範囲、日付制約
- **UI機能**: 動的フォーム表示、グループ連動選択、全選択/解除
- **アクセサ**: 交通手段アイコン、ルート情報、詳細サマリー
- **Eloquentリレーション**: TravelPlan, Group, Member間の適切な関連

#### テスト範囲
- 16のサービス単体テスト（バリデーション・権限・統計テスト）
- 19の機能テスト（CRUD操作・認証・フィルタリングテスト）
- 30のバリデーションテスト（入力検証・競合チェック・制約テスト）
- 12のビューテスト（レンダリング・表示状態・ナビゲーションテスト）
- **合計67テスト**、全テスト合格

### アカウント情報管理機能（2025-06-17完了）
アカウント情報管理・パスワード変更機能：

#### 実装されたファイル
```
app/Http/Controllers/
└── ProfileController.php (プロフィール表示・パスワード変更)

resources/views/profile/
└── show.blade.php (アカウント情報管理画面)

tests/Feature/
├── ProfileControllerTest.php (機能テスト)
└── ProfileViewTest.php (ビューテスト)

routes/web.php (プロフィール関連ルート追加)
resources/views/dashboard.blade.php (設定リンク追加)
```

#### 主要機能
- **基本情報表示**: メールアドレス、登録日
- **アカウント一覧**: サムネイル画像、表示名、@アカウント名
- **OAuth連携情報**: 連携済みプロバイダー表示
- **パスワード変更**: 現在パスワード確認・強度チェック・確認一致検証
- **ダッシュボード統合**: 設定リンクでアクセス可能

#### テスト範囲
- 9の機能テスト（認証・バリデーション・表示テスト）
- 10のビューテスト（レンダリング・状態表示テスト）
- Laravelエスケープ問題修正（@マークの正しい表示）

### UI統一・UX改善（2025-06-17完了）
最新のコミットで実装されたユーザーインターフェース統一・改善：

#### 修正されたファイル
```
resources/views/
├── dashboard.blade.php (メンバー管理アイコン追加)
├── accommodations/index.blade.php (ナビゲーション統一)
├── expenses/index.blade.php (ナビゲーション統一)
├── expenses/show.blade.php (ナビゲーション統一)
├── settlements/index.blade.php (ナビゲーション統一)
├── itineraries/create.blade.php (電車オプション、時刻ラベル)
├── itineraries/edit.blade.php (電車オプション、時刻ラベル)
├── members/create.blade.php (フィールドクリア機能)
├── travel-plans/index.blade.php (master統一)
├── travel-plans/create.blade.php (master統一)
├── travel-plans/edit.blade.php (master統一)
└── travel-plans/show.blade.php (master統一)

app/Http/Controllers/
└── MemberController.php (バリデーション修正)
```

#### 主要改善点
- **ナビゲーション統一**: 全ページのサブメニューを下部に統一配置
- **テンプレート統一**: travel-plansの全テンプレートでmaster.blade.php使用
- **交通手段追加**: 旅程管理に「電車」オプション追加
- **ラベル改善**: 「開始時刻/終了時刻」→「出発時刻/到着時刻」に変更
- **バグ修正**: メンバー招待のバリデーションエラー解決
- **ダッシュボード改善**: メンバー管理のクイックアクション追加

#### 統一されたデザインパターン
- サブメニュー: `mt-8 flex justify-center`スタイル
- リンク色: `text-blue-600 hover:text-blue-800`
- レイアウト: `@extends('layouts.master')`
- コンポーネント: `@component('components.page-header')`

### 精算管理システム（2025-06-16完了）
最新のコミットで実装された包括的な精算管理システム：

#### 実装されたファイル
```
TripQuota/Settlement/
├── SettlementRepositoryInterface.php
├── SettlementRepository.php
└── SettlementService.php

app/Http/Controllers/
└── SettlementController.php

resources/views/settlements/
├── index.blade.php
└── show.blade.php

tests/
├── Unit/TripQuota/SettlementServiceTest.php
└── Feature/SettlementControllerTest.php

database/migrations/
└── 2025_06_16_164151_remove_is_settled_from_expense_settlements_table.php
```

#### 主要機能
- メンバー間債務計算・最適化アルゴリズム
- 多通貨精算対応（JPY, USD, EUR, KRW, CNY）
- 精算提案生成・保存機能
- 精算完了記録・統計情報表示
- 精算リセット機能
- データモデル最適化（is_settled冗長フィールド削除）

#### テスト範囲
- 9のサービス単体テスト（債務計算・統計・認証テスト）
- 11の機能テスト（HTTP操作・認証・統計表示テスト）
- 全テスト合格（20テスト、90アサーション）

### 費用管理システム（2025-06-16完了）
包括的な費用管理システム：

#### 実装されたファイル
```
TripQuota/Expense/
├── ExpenseRepositoryInterface.php
├── ExpenseRepository.php
└── ExpenseService.php

app/Http/Controllers/
└── ExpenseController.php

resources/views/expenses/
├── index.blade.php
├── create.blade.php
├── show.blade.php
└── edit.blade.php

tests/
├── Unit/TripQuota/ExpenseServiceTest.php
├── Feature/ExpenseControllerTest.php
└── Feature/ExpenseViewTest.php
```

#### 主要機能
- 多通貨費用管理（JPY, USD, EUR, KRW, CNY）
- 分割請求（等分・個別金額指定）
- メンバー参加確認ワークフロー
- 費用確定プロセス（編集不可状態）
- 統計情報表示（通貨別集計）
- レスポンシブUI設計

#### テスト範囲
- 14のサービス単体テスト
- 15の機能テスト
- 11のビューテスト
- エラーハンドリング・バリデーションテスト

## 開発ガイドライン

### テスト要件
- 新機能には必ず対応するテストを作成
- 単体テスト、機能テスト、ビューテストの包括的カバレッジ
- `php artisan test`で全テスト実行

### コード品質
- `php artisan pint`でコード整形
- ドメイン分離の維持
- Laravel・Vue.jsのベストプラクティス遵守

## メンバー追加仕様変更詳細 (2025-06-20)

### 現在の仕様
- メンバー追加時に招待（メールアドレスまたはアカウント名）が必須
- 招待されたユーザーが受諾することでメンバーになる

### 新仕様
1. **表示名のみでメンバー登録**
   - 表示名だけを入力してメンバーを即座に登録可能
   - 招待は不要、後から関連付け可能

2. **招待のオプション化**
   - メンバー登録と招待を分離
   - 既に登録済みのメンバーに対して後から招待を送信可能

3. **既存ユーザとの関連付け**
   - メールアドレスまたはアカウント名で既存ユーザと関連付け
   - 関連付け実行者がメンバー登録者
   - 対象ユーザに許可確認

4. **関連付け許可システム**
   - 対象ユーザに「このメンバーとして参加しますか？」の許可ボタン表示
   - 許可により正式にユーザとメンバーが関連付け

### 実装方針
- Memberテーブルにuser_id=nullを許可（表示名のみメンバー）
- 新しい関連付けリクエストテーブル追加
- MemberController, InvitationControllerの仕様変更
- メンバー一覧・詳細画面の表示改善

---
*最終更新: 2025-06-20*
*最新完了: メンバー管理機能改善（編集バグ修正、グループ所属機能、条件付き検証、関連付けシステム基盤）*
*次の優先事項: メンバー関連付けシステム完全実装、レスポンシブ対応*
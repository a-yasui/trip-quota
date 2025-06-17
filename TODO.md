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
- [x] **ダッシュボード改善** - メンバー管理アイコン追加

## 進行中・次の優先事項 🚧

### 高優先度
- [ ] **itinerary管理機能** - 旅程作成・編集・表示機能の完全実装
- [ ] **レスポンシブ対応** - モバイル・タブレット対応の UI 改善

### 中優先度
- [ ] **通知システム** - 招待・費用共有の通知機能
- [ ] **アカウント管理** - ユーザーあたり複数アカウント・アカウント切り替え
- [ ] **プロフィール管理** - サムネイル・アカウント名管理

### 低優先度
- [ ] **旅行プラン履歴・変更ログ** - 履歴管理機能
- [ ] **跨旅行プランブランチグループ統合** - ブランチグループ統合機能
- [ ] **タイムゾーン管理** - 出発・帰国日のタイムゾーン対応

## 技術的詳細

### アーキテクチャ
- ドメイン駆動設計 (DDD)
- リポジトリパターン
- サービス層による複雑なビジネスロジック
- Laravel 11 + Vue.js
- 包括的テスト戦略（PHPUnit + Vitest）

### データベース構造
- **主要エンティティ**: Users, Accounts, TravelPlans, Groups, Members
- **旅行コンテンツ**: Itineraries, Accommodations, Expenses
- **グループタイプ**: CORE（全メンバー）、BRANCH（サブグループ）
- **費用管理**: ExpenseMembers, ExpenseSettlements（多通貨対応）

## 最新の実装状況

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

---
*最終更新: 2025-06-17*
*最新完了: UI統一・UX改善（ナビゲーション統一、テンプレート統一、バグ修正）*
*次の優先事項: itinerary管理機能の完全実装、レスポンシブ対応*
# Itinerary管理機能 詳細仕様

## 概要

Itinerary管理機能は、旅行プランの詳細な旅程（行程）を管理するシステムです。日単位・時間単位での活動計画、移動手段、グループ別行動などを管理できます。

## 現在の実装状況

### ✅ 実装済み機能

#### アーキテクチャ・基盤
- **ドメインサービス**: `TripQuota\Itinerary\ItineraryService`
- **リポジトリパターン**: `ItineraryRepositoryInterface` & `ItineraryRepository`
- **コントローラー**: `ItineraryController` (CRUD操作)
- **ルート設定**: travel-plans/{uuid}/itineraries/* の完全ルート定義

#### データモデル・リレーション
- **Itineraryモデル**: 旅程の基本情報格納
- **中間テーブル**: itinerary_member（旅程参加者管理）
- **リレーション**: TravelPlan, Group, Member との関連付け
- **タイムスタンプ**: created_at, updated_at

#### ビジネスロジック
- **権限管理**: 確認済みメンバーのみ閲覧・作成・編集可能
- **バリデーション**: 日付・時刻・移動手段の妥当性チェック
- **グループ検証**: 旅程とグループの所属関係確認
- **日付検証**: 旅行期間内の日付制限

#### ビュー実装
- **一覧画面** (`index.blade.php`): フィルター機能付き旅程一覧
- **作成画面** (`create.blade.php`): 新規旅程作成フォーム
- **詳細画面** (`show.blade.php`): 旅程詳細表示
- **編集画面** (`edit.blade.php`): 旅程編集フォーム
- **タイムライン** (`timeline.blade.php`): 時系列旅程表示

#### フィルター・検索機能
- **グループフィルター**: コアグループ・班グループ別表示
- **日付フィルター**: 特定日の旅程抽出
- **期間検索**: 開始・終了日での範囲検索

#### テスト実装
- **単体テスト**: ItineraryServiceTest (13テスト)
- **機能テスト**: ItineraryControllerTest
- **ビューテスト**: ItineraryViewTest

### 🚧 完全実装に向けた追加要件

## 詳細機能仕様

### 1. 旅程データ構造

#### 基本情報
```php
Itinerary {
    id: int
    travel_plan_id: int          // 所属旅行プラン
    group_id: int|null           // 担当グループ（null=全体）
    created_by_member_id: int    // 作成者メンバーID
    
    title: string                // 旅程タイトル
    description: text|null       // 詳細説明
    date: date                   // 実施日
    start_time: time|null        // 開始時刻
    end_time: time|null          // 終了時刻
    
    location: string|null        // 場所・目的地
    transportation_type: enum    // 移動手段
    
    created_at: timestamp
    updated_at: timestamp
}
```

#### 移動手段タイプ
```php
enum TransportationType {
    'walking'     => '徒歩',
    'bicycle'     => '自転車', 
    'car'         => '車',
    'bus'         => 'バス',
    'train'       => '電車',        // ✅最近追加
    'ferry'       => 'フェリー',
    'airplane'    => '飛行機'
}
```

#### 移動手段別詳細フィールド
```php
// 飛行機の場合 - 必須フィールド
airplane: {
    airline: string           // 航空会社
    flight_number: string     // 便名
    departure_airport: string // 出発空港
    arrival_airport: string   // 到着空港
}

// 電車の場合 - 推奨フィールド  
train: {
    line_name: string|null         // 路線名
    departure_station: string|null // 出発駅
    arrival_station: string|null   // 到着駅
    train_type: string|null        // 列車種別（新幹線、特急等）
}

// バス・フェリーの場合
bus/ferry: {
    departure_point: string|null   // 出発地点
    arrival_point: string|null     // 到着地点
    company: string|null           // 運営会社
}
```

### 2. 権限・セキュリティ

#### 閲覧権限
- **確認済みメンバー**: 旅行プランの全旅程閲覧可能
- **未確認メンバー**: 閲覧不可
- **非メンバー**: アクセス拒否

#### 作成・編集権限
- **旅程作成**: 確認済みメンバー全員可能
- **自己作成旅程編集**: 作成者のみ
- **他者作成旅程編集**: 旅行プラン管理者（owner/creator）のみ
- **削除権限**: 編集権限と同一

#### グループ制限
- **班グループ旅程**: そのグループメンバーのみ作成・編集可能
- **コアグループ旅程**: 全メンバー参照可能
- **個人旅程**: group_id=nullで個人予定として作成可能

### 3. ビジネスロジック詳細

#### バリデーション規則
```php
// 基本バリデーション
title: required|string|max:255
description: nullable|string|max:1000
date: required|date|within_travel_period
start_time: nullable|time|before:end_time
end_time: nullable|time|after:start_time
location: nullable|string|max:255
transportation_type: nullable|in:walking,bicycle,car,bus,train,ferry,airplane

// 移動手段別バリデーション
airplane: {
    airline: required_if:transportation_type,airplane|string|max:100
    flight_number: required_if:transportation_type,airplane|string|max:20
    departure_airport: required_if:transportation_type,airplane|string|max:100
    arrival_airport: required_if:transportation_type,airplane|string|max:100
}

// グループ関連バリデーション
group_id: nullable|exists:groups,id|belongs_to_travel_plan
member_ids: array|members_belong_to_travel_plan
```

#### 日付・時刻制御
- **期間制限**: 旅行開始日 ≤ 旅程日 ≤ 旅行終了日
- **時刻順序**: 開始時刻 < 終了時刻
- **タイムゾーン**: 旅行先のタイムゾーンに基づく時刻管理
- **重複チェック**: 同一メンバーの時間重複警告（エラーではない）

#### メンバー割り当てロジック
```php
// デフォルト割り当て
if (group_id) {
    // グループの全メンバーを自動割り当て
    members = group.members;
} else {
    // 作成者のみ割り当て
    members = [creator];
}

// カスタム割り当て
if (member_ids_specified) {
    // 指定されたメンバーのみ
    // ただし、グループメンバー内に限定
    members = specified_members.intersect(group.members);
}
```

### 4. UI/UX設計

#### 一覧画面機能
```php
// フィルター機能
- グループ選択: 「すべて」「全体グループ」「班グループ1」...
- 日付選択: カレンダーピッカーで特定日選択
- 期間選択: 開始日〜終了日での範囲指定
- メンバー選択: 特定メンバーの参加旅程のみ表示

// 表示モード
- リスト表示: 日付順一覧（デフォルト）
- タイムライン表示: 時系列ガントチャート風
- カレンダー表示: 月間カレンダー上に旅程表示
- グループ別表示: グループごとに分類表示
```

#### 作成・編集フォーム
```html
<!-- 基本情報セクション -->
<section class="basic-info">
    <input name="title" placeholder="旅程タイトル">
    <textarea name="description" placeholder="詳細説明（任意）">
    <input type="date" name="date">
    <input type="time" name="start_time">
    <input type="time" name="end_time">
    <input name="location" placeholder="場所・目的地">
</section>

<!-- グループ・メンバー選択 -->
<section class="participants">
    <select name="group_id">
        <option value="">個人予定</option>
        <option value="1">[全体] 全員</option>
        <option value="2">[班] 観光グループ</option>
    </select>
    
    <!-- グループ選択後、メンバー詳細選択 -->
    <div class="member-selection" v-if="group_id">
        <label v-for="member in group.members">
            <input type="checkbox" :value="member.id" v-model="member_ids">
            {{ member.name }}
        </label>
    </div>
</section>

<!-- 移動手段セクション -->
<section class="transportation">
    <select name="transportation_type">
        <option value="">移動手段を選択</option>
        <option value="walking">徒歩</option>
        <option value="train">電車</option>
        <option value="airplane">飛行機</option>
        <!-- ... -->
    </select>
    
    <!-- 飛行機選択時の詳細フィールド -->
    <div v-if="transportation_type === 'airplane'">
        <input name="airline" placeholder="航空会社">
        <input name="flight_number" placeholder="便名">
        <input name="departure_airport" placeholder="出発空港">
        <input name="arrival_airport" placeholder="到着空港">
    </div>
</section>
```

#### タイムライン表示
```php
Timeline Features:
- 時間軸: 1日を24時間で表示
- 多レーン: メンバー別・グループ別レーン
- ドラッグ&ドロップ: 時間調整（将来機能）
- 重複表示: 同時刻の複数旅程をスタック表示
- 詳細ポップアップ: 旅程クリックで詳細表示
```

### 5. API仕様

#### RESTful エンドポイント
```php
// 基本CRUD
GET    /travel-plans/{uuid}/itineraries           // 一覧取得
POST   /travel-plans/{uuid}/itineraries           // 新規作成
GET    /travel-plans/{uuid}/itineraries/{id}      // 詳細取得
PUT    /travel-plans/{uuid}/itineraries/{id}      // 更新
DELETE /travel-plans/{uuid}/itineraries/{id}      // 削除

// 特殊表示
GET    /travel-plans/{uuid}/itineraries/timeline  // タイムライン
GET    /travel-plans/{uuid}/itineraries/calendar  // カレンダー

// フィルター付き取得
GET    /itineraries?group_id={id}                 // グループ別
GET    /itineraries?date={date}                   // 日付別  
GET    /itineraries?member_id={id}                // メンバー別
GET    /itineraries?start_date={date}&end_date={date} // 期間別
```

#### レスポンス形式
```json
{
  "data": {
    "id": 1,
    "title": "東京駅から新大阪駅へ移動",
    "description": "新幹線で移動",
    "date": "2025-07-15",
    "start_time": "09:30:00",
    "end_time": "12:45:00",
    "location": "新大阪駅",
    "transportation_type": "train",
    "transportation_details": {
      "line_name": "東海道新幹線",
      "departure_station": "東京駅",
      "arrival_station": "新大阪駅",
      "train_type": "のぞみ"
    },
    "travel_plan": {
      "id": 1,
      "plan_name": "大阪旅行"
    },
    "group": {
      "id": 2,
      "name": "移動グループ",
      "type": "BRANCH"
    },
    "created_by": {
      "id": 1,
      "name": "田中太郎"
    },
    "members": [
      {"id": 1, "name": "田中太郎"},
      {"id": 2, "name": "佐藤花子"}
    ]
  }
}
```

### 6. 完全実装への追加作業

#### 高優先度（必須実装）
1. **移動手段詳細フィールド**: 各交通手段の詳細情報格納・表示
2. **メンバー参加管理**: 旅程への個別メンバー割り当て機能
3. **タイムライン表示改善**: 見やすい時系列表示
4. **バリデーション強化**: 時刻重複チェック、移動手段必須フィールド

#### 中優先度（UX向上）
1. **ドラッグ&ドロップ**: タイムライン上での時間調整
2. **カレンダー表示**: 月間ビューでの旅程管理
3. **テンプレート機能**: よく使う旅程パターンの保存・再利用
4. **通知機能**: 旅程変更・追加の関係者通知

#### 低優先度（将来拡張）
1. **地図連携**: Google Maps APIでのルート表示
2. **費用連携**: 旅程に関連する費用の自動計算
3. **天気情報**: 旅程日の天気予報表示
4. **写真・メモ**: 旅程実行後の記録機能

### 7. テスト要件

#### 単体テスト
- ItineraryService の全メソッド
- バリデーション規則の境界値テスト
- 権限チェックロジック

#### 機能テスト  
- CRUD操作の全パターン
- フィルター機能の動作確認
- エラーハンドリングの検証

#### ビューテスト
- 各画面の正常表示
- フォームの入力・送信
- 条件分岐表示の確認

### 8. パフォーマンス要件

#### データベース最適化
- 旅程一覧取得時のN+1クエリ対策
- 日付範囲検索のインデックス最適化
- メンバー関連のEager Loading

#### キャッシュ戦略  
- 旅程一覧の短期キャッシュ
- グループ情報のキャッシュ
- タイムライン表示の最適化

---

## まとめ

Itinerary管理機能は既に堅実な基盤が実装されており、主要なCRUD操作、権限管理、基本的なUI/UXが完成しています。完全実装に向けては、移動手段詳細、メンバー参加管理、タイムライン表示の改善が重要な次のステップとなります。

特に、旅行計画において移動手段の詳細情報は重要度が高く、これらの実装により実用的な旅程管理システムが完成します。
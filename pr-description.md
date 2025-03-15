# 旅行計画サービスのリファクタリング

## 概要
`TripQuota\TravelPlan\TravelPlanService` クラスを作成し、旅行計画の作成・削除機能を実装します。

## 変更内容
- `TripQuota\TravelPlan\TravelPlanService` クラスの新規作成
  - `create()`: 旅行計画の作成
  - `addBranchGroup()`: 旅行計画に班グループの追加
  - `removeBranchGroup()`: 班グループの削除
  - `removeTravelPlan()`: 旅行計画の削除

## 関連する仕様
- 仕様2.1: 一つの旅行計画に対して、一つのコアグループが存在する
- 仕様2.2: 一つの旅行計画に対して、0以上、複数の班グループが存在する
- 仕様2.3: 旅行計画が破棄されば、コアグループと班グループは破棄される
- 仕様2.3.1: 班グループが破棄されても、旅行計画およびコアグループに影響はない 
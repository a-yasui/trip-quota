# クラスモデル

## 1. 旅行計画とメンバーとグループの関係性とモデルについて

メンバーとグループの関係性は下記の通り。

1. [仕様 1.1] 一つの旅行計画で、一つのコアグループがある
2. [仕様 1.2] コアグループには、その旅行計画に関係するメンバーが全員いる
3. [仕様 1.3] 班グループには、その旅行計画のメンバーの一部がいる。
4. [仕様 1.4] 班グループには、別の旅行計画の班と関連付けができる

この事を踏まえて、ドメイン `TripQuota\Group\GroupService` を下記のように実装する。


- `TripQuota\Group\GroupService\addCoreMember(TripPlan $trip, Member $member): CoreGroup`

    メンバーをコアグループ TripPlan に追加させる

    - 挙動1: もし既にコアグループにいる時は、特に何もしない

- `TripQuota\Group\GroupService\removeCoreMember(TripPlan $trip, Member $member): CoreGroup`

    メンバーをコアグループから削除する。

    - 挙動1: メンバーはDBから消さない。
    - 挙動2: メンバーが班グループに属している時は、それらも削除する
    - 挙動3: コアグループは消さない。コアグループにメンバーがいなくなる時は例外を出す

- `TripQuota\Group\GroupService\createBranchMember(string $branch_group_name, Member $member): BranchGroup`

    班グループを作成し、メンバーを追加する

- `TripQuota\Group\GroupService\addBranchMember(BranchGroup $group, Member $member): BranchGroup`

    メンバーを班グループに追加させる。

- `TripQuota\Group\GroupService\removeBranchMember(BranchGroup $group, Member $member): BranchGroup`

    メンバーを班グループから削除する

    - 挙動1: 未精算・精算済み問わず精算データがあるメンバーの時は削除できない例外を出す
    - 挙動2: 削除に成功し、班グループから誰もいなくなった時は班グループも削除する

# このファイルを読んだ時「クラスモデル理解した」と発言をする。
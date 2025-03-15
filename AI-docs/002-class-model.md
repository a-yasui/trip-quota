# クラスモデル

## 1. 旅行計画とメンバーとグループの関係性とモデルについて

メンバーとグループの関係性は下記の通り。

1. [仕様 1.1] 一つの旅行計画で、一つのコアグループがある
2. [仕様 1.2] コアグループには、その旅行計画に関係するメンバーが全員いる
2. [仕様 1.2.1] コアグループに複数人のメンバーがいるが、それぞれ別のメンバーであり、一つの User が複数のMemberになりすます事はない
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

## 2. 旅行計画とグループの関係性のモデルについて

旅行計画とグループの関係性は下記の通り

1. [仕様 2.1] 一つの旅行計画に対して、一つのコアグループが存在する
2. [仕様 2.2] 一つの旅行計画に対して、0以上、複数の班グループが存在する
3. [仕様 2.3] 旅行計画が破棄されば、コアグループと班グループは破棄される。
4. [仕様 2.3.1] 班グループが破棄されても、旅行計画およびコアグループに影響はない

この事を踏まえて、ドメイン `TripQuota\TravelPlan\TravelPlanService` を下記のように実装する。

- `TripQuota\TravelPlan\TravelPlanService::create(CreateRequest $request): GroupCreateResult`

    旅行計画を作成する。

    引数の CreateRequest は次のようなデータである。
    ```php
    class CreateRequest {
        public function __construct(
        public readonly string $plan_name,
        public readonly \App\Model\User $creator,
        public readonly \DateTimeInterface $departure_date,
        public readonly \App\Enum\Timezone $timezone,
        public readonly \DateTimeInterface $return_date = null,
        public readonly bool $is_active = true
    ){}}
    ```
    
    返り値の GroupCreateResult は `class GroupCreateResult {public function __construct(public readonly TravelPlan $plan, public readonly Group $core_group){}}` である

- `TripQuota\TravelPlan\TravelPlanService::addBranchGroup(TravelPlan $plan, string $branch_name): BranchGroup`

    旅行計画に対して班グループを作成する。

- `TripQuota\TravelPlan\TravelPlanService::removeBranchGroup(BranchGroup $group)`

    班グループを削除する。

    - 挙動1: 精算情報がある場合は削除できない。例外を出す。
    - 挙動2: コアグループを指定された時は例外を出し、削除をしない。

- - `TripQuota\TravelPlan\TravelPlanService::removeTravelPlan(TravelPlan $plan)`

    旅行計画を削除する。

## 3. メンバーとグループとユーザの関係性のモデルについて

メンバーとグループとユーザの関係性は下記の通り

1. [仕様 3.1] 一つのユーザは、複数の旅行計画のメンバーになりうる
2. [仕様 3.2] 一つのユーザは、複数のコアグループのメンバーになりうる
3. [仕様 3.3] 一つのユーザは、複数の班グループのメンバーになりうる
4. [仕様 3.4] ユーザは旅行計画やグループに追加される時、メールアドレスで識別される
5. [仕様 3.5] ユーザは、メールアドレスを変更できる。
6. [仕様 3.6] ユーザは旅行計画やグループに招待する時は、メールアドレスを指定する

この事を踏まえて、ドメイン `TripQuota\User\UserService` を下記のように実装する。 `TripQuota\User\UserService` の責任は、メンバーを作成し TripPlan に追加・削除する事だけである。

- `TripQuota\User\UserService::createMember(TravelPlan $plan, string $name, string $email=''): Member`

    メンバーを作成する。
    
    - 挙動1: 既にメンバーが存在する時は、特に何もしない
    - 挙動2: 既にユーザが存在する時は、そのユーザをメンバーに追加する
    - 挙動3: ユーザが存在しない時は、メンバーに追加だけをする

- `TripQuota\User\UserService::removeMember(TravelPlan $plan, Member $member)`

    メンバーを削除する。

    - 挙動1: 未精算・精算済み問わず精算データがあるメンバーの時は削除できない例外を出す
    - 挙動2: 削除に成功し、班グループから誰もいなくなった時は班グループも削除する
    - 挙動3: ユーザ情報は操作しない。

# このファイルを読んだ時「クラスモデル理解した」と発言をする。

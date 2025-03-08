# UI 設計

- 基調色は #cbf542 とする
- PC とスマートフォンでサイトを見る。
- Vue Router は使わない

## Styling Guildline

- Use Tailwind CSS classes instead of inline style objects for new markup
- VSCode CSS variables must be added to webview-ui/src/index.css before using them in Tailwind classes
- Example: `<div className="text-md text-vscode-descriptionForeground mb-2" />` instead of style objects

## Laravel 画面設計

基本 `resources/views` にテンプレートはあり、URLに対応したディレクトリ内にbladeのファイルを設置している。

```
resources/views
├── auth   認証（ログイン・登録）で使うテンプレート
├── branch-groups		班グループの操作系のテンプレートディレクトリ
├── components			UIパーツのディレクトリ
├── dashboard.blade.php	ログイン直下の画面ディレクトリ
├── group-members		班グループ等の画面ディレクトリ
├── groups				参加しているグループの一覧画面ディレクトリ
├── itineraries			旅程の編集画面ディレクトリ
├── layouts				ベースレイアウトディレクトリ
├── profile				ユーザのプロフィール編集画面ディレクトリ
├── travel-plans		旅行計画の閲覧編集画面ディレクトリ
└── welcome.blade.php	トップ画面
```

1. テンプレートを設置したページは、 **必ず** 表示テストを書かなければならない。そして、かならずテストをパスする必要がある。これは絶対である。

## Vuejs

- 複雑なフォームを作る必要がある時に使用する
    - 例えば、一つのチェックボックスを押したら、テキストエリアに任意の文字列を入れるといった感じ
- テストは `npm run test` で vitest test が動くようにする。
- vuejs のコンポーネントを作ったら、 *必ずテストを作成し、実行して問題がない事を確認する事*

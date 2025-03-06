@extends('layouts.app')

@section('title', 'ダッシュボード')

@section('header', 'ダッシュボード')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- 旅行計画カード -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">旅行計画</h3>
                    <a href="{{ route('travel-plans.create') }}" class="inline-flex items-center text-sm font-medium text-lime-600 hover:text-lime-500">
                        <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        新規作成
                    </a>
                </div>
                <p class="text-gray-600 mb-4">現在の旅行計画や過去の旅行履歴を確認できます。</p>
                <a href="{{ route('travel-plans.index') }}" class="inline-flex items-center px-4 py-2 bg-lime-500 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-lime-400 focus:bg-lime-400 active:bg-lime-600 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    旅行計画を見る
                </a>
            </div>
        </div>

        <!-- グループカード -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">グループ</h3>
                </div>
                <p class="text-gray-600 mb-4">参加中のグループや招待を確認できます。</p>
                <a href="{{ route('groups.index') }}" class="inline-flex items-center px-4 py-2 bg-lime-500 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-lime-400 focus:bg-lime-400 active:bg-lime-600 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    グループを見る
                </a>
            </div>
        </div>

        <!-- 経費精算カード -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">経費精算</h3>
                </div>
                <p class="text-gray-600 mb-4">旅行中の支出や割り勘の状況を確認できます。</p>
                <a href="{{ route('expenses.index') }}" class="inline-flex items-center px-4 py-2 bg-lime-500 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-lime-400 focus:bg-lime-400 active:bg-lime-600 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    経費を見る
                </a>
            </div>
        </div>
    </div>

    <!-- 最近の活動 -->
    <div class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">最近の活動</h3>
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="space-y-4">
                    <p class="text-gray-600">まだ活動はありません。旅行計画を作成して始めましょう！</p>
                </div>
            </div>
        </div>
    </div>
@endsection

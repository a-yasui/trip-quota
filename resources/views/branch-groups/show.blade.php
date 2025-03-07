@extends('layouts.app')

@section('title', $group->name)

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $group->travelPlan->title }} - {{ $group->name }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('travel-plans.show', $group->travelPlan) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                旅行計画に戻る
            </a>
            
            <a href="{{ route('branch-groups.edit', $group) }}" class="inline-flex items-center px-4 py-2 bg-lime-500 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-lime-400 active:bg-lime-600 focus:outline-none focus:border-lime-600 focus:ring ring-lime-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                編集
            </a>
            
            <a href="{{ route('branch-groups.duplicate', $group) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-400 active:bg-blue-600 focus:outline-none focus:border-blue-600 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                </svg>
                複製
            </a>
            
            <form method="POST" action="{{ route('branch-groups.destroy', $group) }}" onsubmit="return confirm('この班グループを削除してもよろしいですか？メンバーも削除されます。');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-400 active:bg-red-600 focus:outline-none focus:border-red-600 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    削除
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 班グループ情報 -->
        <div class="md:col-span-2">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">班グループ情報</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">班グループ名</p>
                            <p class="font-medium">{{ $group->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">メンバー数</p>
                            <p class="font-medium">{{ $members->count() }}人</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">作成日</p>
                            <p class="font-medium">{{ $group->created_at->format('Y年m月d日') }}</p>
                        </div>
                        @if($group->description)
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-600">説明</p>
                                <p class="font-medium">{{ $group->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 旅程情報 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">旅程</h3>
                        <a href="#" class="inline-flex items-center text-sm font-medium text-lime-600 hover:text-lime-500">
                            <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            旅程を追加
                        </a>
                    </div>
                    
                    <p class="text-gray-500">旅程情報はまだ登録されていません。</p>
                </div>
            </div>

            <!-- 経費情報 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">経費</h3>
                        <a href="#" class="inline-flex items-center text-sm font-medium text-lime-600 hover:text-lime-500">
                            <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            経費を追加
                        </a>
                    </div>
                    
                    <p class="text-gray-500">経費情報はまだ登録されていません。</p>
                </div>
            </div>
        </div>

        <!-- サイドバー -->
        <div class="md:col-span-1">
            <!-- メンバー情報 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">メンバー</h3>
                        
                        @if(!$availableMembers->isEmpty())
                            <button type="button" onclick="document.getElementById('add-member-modal').classList.remove('hidden')" class="inline-flex items-center text-sm font-medium text-lime-600 hover:text-lime-500">
                                <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                メンバー追加
                            </button>
                        @endif
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($members as $member)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-lime-100 flex items-center justify-center text-lime-500">
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                        @if($member->email)
                                            <p class="text-xs text-gray-500">{{ $member->email }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- 削除ボタン（自分自身は削除できない） -->
                                @if(Auth::id() !== $member->user_id)
                                    <form method="POST" action="{{ route('branch-groups.members.destroy', [$group, $member]) }}" onsubmit="return confirm('このメンバーを班グループから削除してもよろしいですか？');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                        
                        @if($members->isEmpty())
                            <p class="text-gray-500">メンバーがいません。</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- メンバー追加モーダル -->
    <div id="add-member-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">メンバー追加</h3>
                    <button type="button" onclick="document.getElementById('add-member-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form method="POST" action="{{ route('branch-groups.members.store', $group) }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="member_id" class="block text-sm font-medium text-gray-700 mb-2">追加するメンバー</label>
                        <select name="member_id" id="member_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-200 focus:ring-opacity-50" required>
                            <option value="">メンバーを選択してください</option>
                            @foreach($availableMembers as $availableMember)
                                <option value="{{ $availableMember->id }}">
                                    {{ $availableMember->name }}
                                    @if($availableMember->email)
                                        ({{ $availableMember->email }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="document.getElementById('add-member-modal').classList.add('hidden')" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            キャンセル
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-lime-500 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-lime-400 active:bg-lime-600 focus:outline-none focus:border-lime-600 focus:ring ring-lime-300 disabled:opacity-25 transition ease-in-out duration-150">
                            追加
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

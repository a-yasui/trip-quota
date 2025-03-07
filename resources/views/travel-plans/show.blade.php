@extends('layouts.app')

@section('title', $travelPlan->title)

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $travelPlan->title }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('travel-plans.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                一覧に戻る
            </a>
            
            @if(now()->startOfDay()->lt($travelPlan->departure_date) || !$travelPlan->return_date)
                <a href="{{ route('travel-plans.edit', $travelPlan) }}" class="inline-flex items-center px-4 py-2 bg-lime-500 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-lime-400 active:bg-lime-600 focus:outline-none focus:border-lime-600 focus:ring ring-lime-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    編集
                </a>
            @endif
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

    @if(!$travelPlan->return_date)
        <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">帰宅日が未設定です。<a href="{{ route('travel-plans.edit', $travelPlan) }}" class="underline font-medium">こちら</a>から設定できます。</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 旅行情報 -->
        <div class="md:col-span-2">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">旅行情報</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">旅行名</p>
                            <p class="font-medium">{{ $travelPlan->title }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">作成者</p>
                            <p class="font-medium">{{ $travelPlan->creator->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">出発日</p>
                            <p class="font-medium">{{ $travelPlan->departure_date->format('Y年m月d日') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">帰宅日</p>
                            <p class="font-medium">{{ $travelPlan->return_date ? $travelPlan->return_date->format('Y年m月d日') : '未定' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">タイムゾーン</p>
                            <p class="font-medium">{{ $travelPlan->timezone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">ステータス</p>
                            <p class="font-medium">
                                @if($travelPlan->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        アクティブ
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        非アクティブ
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 宿泊先情報 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">宿泊先</h3>
                        <a href="{{ route('travel-plans.branch-groups.create', $travelPlan) }}" class="inline-flex items-center text-sm font-medium text-lime-600 hover:text-lime-500">
                            <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            宿泊先を追加
                        </a>
                    </div>
                    
                    @if($travelPlan->accommodations->isEmpty())
                        <p class="text-gray-500">宿泊先情報はまだ登録されていません。</p>
                    @else
                        <div class="space-y-4">
                            @foreach($travelPlan->accommodations as $accommodation)
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between">
                                        <h4 class="font-medium">{{ $accommodation->name }}</h4>
                                        <div class="text-sm text-gray-500">
                                            {{ $accommodation->check_in_date->format('Y/m/d') }} - {{ $accommodation->check_out_date->format('Y/m/d') }}
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $accommodation->address }}</p>
                                    @if($accommodation->phone_number)
                                        <p class="text-sm text-gray-600">TEL: {{ $accommodation->phone_number }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
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
                    
                    @if($travelPlan->itineraries->isEmpty())
                        <p class="text-gray-500">旅程情報はまだ登録されていません。</p>
                    @else
                        <div class="space-y-4">
                            @foreach($travelPlan->itineraries as $itinerary)
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between">
                                        <h4 class="font-medium">{{ $itinerary->transportation_type }}</h4>
                                        <div class="text-sm text-gray-500">
                                            {{ $itinerary->departure_time->format('Y/m/d H:i') }}
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $itinerary->departure_location }} → {{ $itinerary->arrival_location }}
                                    </p>
                                    @if($itinerary->company_name)
                                        <p class="text-sm text-gray-600">{{ $itinerary->company_name }}</p>
                                    @endif
                                    @if($itinerary->reference_number)
                                        <p class="text-sm text-gray-600">予約番号: {{ $itinerary->reference_number }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
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
                        <a href="{{ route('groups.members.create', $coreGroup) }}" class="inline-flex items-center text-sm font-medium text-lime-600 hover:text-lime-500">
                            <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            メンバー追加
                        </a>
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
                                    <form method="POST" action="{{ route('groups.members.destroy', [$coreGroup, $member]) }}" onsubmit="return confirm('このメンバーを削除してもよろしいですか？');">
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
                    </div>
                </div>
            </div>

            <!-- グループ情報 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">グループ</h3>
                        <a href="{{ route('travel-plans.branch-groups.create', $travelPlan) }}" class="inline-flex items-center text-sm font-medium text-lime-600 hover:text-lime-500">
                            <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            班を作成
                        </a>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($travelPlan->groups as $group)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-lime-100 flex items-center justify-center text-lime-500">
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $group->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            @if($group->type === 'core')
                                                コアグループ
                                            @else
                                                班グループ
                                            @endif
                                            ({{ $group->members->count() }}人)
                                        </p>
                                    </div>
                                </div>
                                @if($group->type === 'branch')
                                    <a href="{{ route('branch-groups.show', $group) }}" class="text-lime-600 hover:text-lime-500">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- 経費情報 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
                    
                    @if($travelPlan->expenses->isEmpty())
                        <p class="text-gray-500">経費情報はまだ登録されていません。</p>
                    @else
                        <div class="space-y-3">
                            @foreach($travelPlan->expenses as $expense)
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $expense->description }}</p>
                                        <p class="text-xs text-gray-500">{{ $expense->expense_date->format('Y/m/d') }}</p>
                                    </div>
                                    <div class="text-sm font-medium">
                                        {{ number_format($expense->amount) }} {{ $expense->currency }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

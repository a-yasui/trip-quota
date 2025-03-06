@extends('layouts.app')

@section('title', '旅行計画一覧')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            旅行計画一覧
        </h2>
        <div>
            <a href="{{ route('travel-plans.create') }}" class="inline-flex items-center px-4 py-2 bg-lime-500 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-lime-400 active:bg-lime-600 focus:outline-none focus:border-lime-600 focus:ring ring-lime-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                新規旅行計画
            </a>
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

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            旅行名
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            期間
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            メンバー数
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ステータス
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">編集</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($travelPlans as $travelPlan)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-lime-100 text-lime-500">
                                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                                        </svg>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $travelPlan->title }}
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            作成者: {{ $travelPlan->creator->name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $travelPlan->departure_date->format('Y/m/d') }}
                                                    @if($travelPlan->return_date)
                                                        - {{ $travelPlan->return_date->format('Y/m/d') }}
                                                    @else
                                                        - 未定
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    @if($travelPlan->return_date)
                                                        {{ $travelPlan->departure_date->diffInDays($travelPlan->return_date) }}泊{{ $travelPlan->departure_date->diffInDays($travelPlan->return_date) + 1 }}日
                                                    @else
                                                        期間未定
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    @php
                                                        $memberCount = 0;
                                                        foreach ($travelPlan->groups as $group) {
                                                            if ($group->type === 'core') {
                                                                $memberCount = $group->members->count();
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    {{ $memberCount }}人
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $now = now();
                                                    $status = '';
                                                    $statusClass = '';
                                                    
                                                    if (!$travelPlan->is_active) {
                                                        $status = '非アクティブ';
                                                        $statusClass = 'bg-gray-100 text-gray-800';
                                                    } elseif ($travelPlan->departure_date->isPast() && (!$travelPlan->return_date || $travelPlan->return_date->isFuture())) {
                                                        $status = '旅行中';
                                                        $statusClass = 'bg-blue-100 text-blue-800';
                                                    } elseif ($travelPlan->departure_date->isPast() && $travelPlan->return_date && $travelPlan->return_date->isPast()) {
                                                        $status = '完了';
                                                        $statusClass = 'bg-purple-100 text-purple-800';
                                                    } elseif ($travelPlan->departure_date->isFuture() && $travelPlan->departure_date->diffInDays($now) <= 7) {
                                                        $status = '間もなく出発';
                                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                                    } else {
                                                        $status = '準備中';
                                                        $statusClass = 'bg-green-100 text-green-800';
                                                    }
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                    {{ $status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('travel-plans.show', $travelPlan) }}" class="text-lime-600 hover:text-lime-900">詳細</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                                旅行計画がありません。新しい旅行計画を作成しましょう！
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

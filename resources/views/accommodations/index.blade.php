@extends('layouts.master')

@section('title', '宿泊施設管理 - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container', ['class' => 'max-w-6xl'])
        @component('components.page-header', ['title' => '宿泊施設管理', 'subtitle' => $travelPlan->plan_name . 'の宿泊施設一覧'])
        @endcomponent

        @include('components.alerts')

        <!-- ヘッダーアクション -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" 
                   class="text-blue-600 hover:text-blue-800">
                    ← 旅行プラン詳細に戻る
                </a>
            </div>
            <a href="{{ route('travel-plans.accommodations.create', $travelPlan->uuid) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                <svg class="-ml-1 mr-2 h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                宿泊施設を追加
            </a>
        </div>

        @if($accommodations->count() > 0)
            <!-- 宿泊施設一覧 -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="divide-y divide-gray-200">
                    @foreach($accommodations as $accommodation)
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 8v-1a1 1 0 011-1h1a1 1 0 011 1v1m-4 0h4"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-medium text-gray-900 truncate">
                                                <a href="{{ route('travel-plans.accommodations.show', [$travelPlan->uuid, $accommodation->id]) }}" 
                                                   class="hover:text-blue-600">
                                                    {{ $accommodation->name }}
                                                </a>
                                            </h3>
                                            @if($accommodation->address)
                                                <p class="text-sm text-gray-500 truncate">{{ $accommodation->address }}</p>
                                            @endif
                                            <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                                <div class="flex items-center">
                                                    <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span>{{ $accommodation->check_in_date->format('Y/m/d') }} 〜 {{ $accommodation->check_out_date->format('Y/m/d') }}</span>
                                                </div>
                                                @if($accommodation->price_per_night)
                                                    <div class="flex items-center">
                                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span>{{ number_format($accommodation->price_per_night) }} {{ $accommodation->currency }}/泊</span>
                                                    </div>
                                                @endif
                                                @if($accommodation->members->count() > 0)
                                                    <div class="flex items-center">
                                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                        </svg>
                                                        <span>{{ $accommodation->members->count() }}人</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('travel-plans.accommodations.show', [$travelPlan->uuid, $accommodation->id]) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        詳細
                                    </a>
                                    <a href="{{ route('travel-plans.accommodations.edit', [$travelPlan->uuid, $accommodation->id]) }}" 
                                       class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                                        編集
                                    </a>
                                </div>
                            </div>

                            @if($accommodation->confirmation_number)
                                <div class="mt-3 text-sm text-gray-600">
                                    <strong>予約番号:</strong> {{ $accommodation->confirmation_number }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- 空の状態 -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 8v-1a1 1 0 011-1h1a1 1 0 011 1v1m-4 0h4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">宿泊施設がありません</h3>
                <p class="mt-1 text-sm text-gray-500">最初の宿泊施設を追加してみましょう。</p>
                <div class="mt-6">
                    <a href="{{ route('travel-plans.accommodations.create', $travelPlan->uuid) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        宿泊施設を追加
                    </a>
                </div>
            </div>
        @endif
    @endcomponent
@endsection
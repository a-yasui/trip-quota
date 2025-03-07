@extends('layouts.app')

@section('title', 'グループ一覧')

@section('header', '参加グループ一覧')

@section('content')
    <div class="space-y-8">
        {{-- 未来の旅行計画 --}}
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">今後の旅行</h3>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    @if($futureGroups->isEmpty())
                        <p class="text-gray-600">今後の旅行予定はありません。</p>
                    @else
                        <div class="space-y-4">
                            @foreach($futureGroups as $group)
                                <a href="{{ $group->type === \App\Enums\GroupType::CORE 
                                    ? route('travel-plans.show', $group->travel_plan_id) 
                                    : route('branch-groups.show', $group->id) }}" 
                                   class="block p-4 border rounded-lg hover:bg-lime-50 transition">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium">
                                            {{ $group->travelPlan->title }} - {{ $group->name }} : {{ $group->members->count() }}人
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ $group->travelPlan->departure_date->format('Y/m/d') }}〜
                                            @if($group->travelPlan->return_date)
                                                {{ $group->travelPlan->return_date->format('Y/m/d') }}
                                            @else
                                                未定
                                            @endif
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 現在進行中の旅行計画 --}}
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">現在進行中の旅行</h3>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    @if($currentGroups->isEmpty())
                        <p class="text-gray-600">現在進行中の旅行はありません。</p>
                    @else
                        <div class="space-y-4">
                            @foreach($currentGroups as $group)
                                <a href="{{ $group->type === \App\Enums\GroupType::CORE 
                                    ? route('travel-plans.show', $group->travel_plan_id) 
                                    : route('branch-groups.show', $group->id) }}" 
                                   class="block p-4 border rounded-lg hover:bg-lime-50 transition">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium">
                                            {{ $group->travelPlan->title }} - {{ $group->name }} : {{ $group->members->count() }}人
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ $group->travelPlan->departure_date->format('Y/m/d') }}〜
                                            @if($group->travelPlan->return_date)
                                                {{ $group->travelPlan->return_date->format('Y/m/d') }}
                                            @else
                                                未定
                                            @endif
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 過去の旅行計画 --}}
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">過去の旅行</h3>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    @if($pastGroups->isEmpty())
                        <p class="text-gray-600">過去の旅行記録はありません。</p>
                    @else
                        <div class="space-y-4">
                            @foreach($pastGroups as $group)
                                <a href="{{ $group->type === \App\Enums\GroupType::CORE 
                                    ? route('travel-plans.show', $group->travel_plan_id) 
                                    : route('branch-groups.show', $group->id) }}" 
                                   class="block p-4 border rounded-lg hover:bg-lime-50 transition">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium">
                                            {{ $group->travelPlan->title }} - {{ $group->name }} : {{ $group->members->count() }}人
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ $group->travelPlan->departure_date->format('Y/m/d') }}〜
                                            {{ $group->travelPlan->return_date->format('Y/m/d') }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

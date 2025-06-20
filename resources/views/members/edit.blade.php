@extends('layouts.master')

@section('title', 'メンバー編集 - ' . $member->name)

@section('content')
    @component('components.container', ['class' => 'max-w-3xl'])
        @component('components.page-header', ['title' => 'メンバー編集', 'subtitle' => $travelPlan->plan_name . ' - ' . $member->name])
        @endcomponent

        @include('components.alerts')

        <!-- フォーム -->
        <div class="bg-white shadow-sm rounded-lg">
            <form method="POST" action="{{ route('travel-plans.members.update', [$travelPlan->uuid, $member->id]) }}" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- 表示名 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        表示名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $member->name) }}"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="メンバー一覧で表示される名前">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- メールアドレス -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        メールアドレス @if($member->email)<span class="text-red-500">*</span>@else<span class="text-gray-500">（任意）</span>@endif
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $member->email) }}"
                           @if($member->email) required @endif
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="example@example.com">
                    @if(!$member->email)
                        <p class="mt-1 text-sm text-gray-500">メールアドレスは任意です。後から追加することもできます。</p>
                    @endif
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 現在の情報 -->
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                    <h3 class="text-sm font-medium text-gray-800 mb-3">現在の情報</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-gray-500">ステータス</dt>
                            <dd class="text-gray-900">
                                @if($member->status === 'CONFIRMED')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        確認済み
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        未確認
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">所属グループ数</dt>
                            <dd class="text-gray-900">{{ $member->groups->count() }}個</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">参加日</dt>
                            <dd class="text-gray-900">{{ $member->created_at->format('Y年m月d日') }}</dd>
                        </div>
                        @if($member->confirmed_at)
                            <div>
                                <dt class="text-gray-500">確認日</dt>
                                <dd class="text-gray-900">{{ $member->confirmed_at->format('Y年m月d日') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- 所属グループ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        所属グループ
                    </label>
                    <div class="space-y-2">
                        @foreach($availableGroups as $group)
                            <div class="flex items-center">
                                <input id="group_{{ $group->id }}" 
                                       name="groups[]" 
                                       type="checkbox" 
                                       value="{{ $group->id }}"
                                       {{ $member->groups->contains('id', $group->id) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="group_{{ $group->id }}" class="ml-3 flex items-center">
                                    @if($group->type === 'CORE')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                            全体
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                            班
                                        </span>
                                    @endif
                                    <span class="text-sm text-gray-900">{{ $group->name }}</span>
                                    @if($group->group_type === 'BRANCH')
                                        <span class="text-xs text-gray-500 ml-2">({{ $group->branch_key }})</span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('groups')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 権限情報 -->
                @if($travelPlan->owner_user_id === $member->user_id || $travelPlan->creator_user_id === $member->user_id)
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">特別な権限</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>このメンバーは以下の特別な権限を持っています：</p>
                                    <ul class="list-disc list-inside mt-1 space-y-1">
                                        @if($travelPlan->owner_user_id === $member->user_id)
                                            <li>所有者権限：このプランの完全な管理権限</li>
                                        @endif
                                        @if($travelPlan->creator_user_id === $member->user_id)
                                            <li>作成者権限：このプランの作成者</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 警告 -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">編集に関する注意</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>メールアドレスを変更した場合、再度確認が必要になる場合があります</li>
                                    <li>グループから除外すると、そのグループの情報にアクセスできなくなります</li>
                                    <li>少なくとも1つのグループに所属している必要があります</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ボタン -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="flex space-x-3">
                        <a href="{{ route('travel-plans.members.show', [$travelPlan->uuid, $member->id]) }}" 
                           class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            キャンセル
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            更新
                        </button>
                    </div>
                    
                    <!-- 削除ボタンのプレースホルダー（更新フォーム外で表示） -->
                    <div></div>
                </div>
            </form>
            
            <!-- 削除ボタン（更新フォームの外に移動） -->
            @if($member->user_id !== $travelPlan->owner_user_id)
                <div class="mt-6 flex justify-end">
                    <form method="POST" action="{{ route('travel-plans.members.destroy', [$travelPlan->uuid, $member->id]) }}" 
                          onsubmit="return confirm('本当にこのメンバーを削除しますか？\n\n削除すると以下の影響があります：\n・このメンバーはすべてのグループから除外されます\n・メンバー関連の履歴は削除されます\n・この操作は取り消せません')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            メンバーを削除
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @endcomponent
@endsection
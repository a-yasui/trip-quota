@extends('layouts.app')

@section('title', $group->name . ' - 編集')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $group->name }} - 編集
        </h2>
        <a href="{{ route('branch-groups.show', $group) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
            <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            戻る
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">班グループ編集</h3>
                
                @if (session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                
                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('branch-groups.update', $group) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">班グループ名</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $group->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-200 focus:ring-opacity-50" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700">説明（任意）</label>
                        <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-200 focus:ring-opacity-50">{{ old('description', $group->description) }}</textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">現在のメンバー</label>
                        
                        @if($members->isEmpty())
                            <p class="text-red-500">メンバーがいません。メンバーを追加してください。</p>
                        @else
                            <div class="space-y-2 max-h-60 overflow-y-auto p-2 border rounded-md">
                                @foreach($members as $member)
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-lime-100 flex items-center justify-center text-lime-500">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <span class="ml-2 text-sm text-gray-900">
                                            {{ $member->name }}
                                            @if($member->email)
                                                <span class="text-xs text-gray-500">({{ $member->email }})</span>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-2 text-sm text-gray-500">メンバーの追加・削除は班グループ詳細画面から行えます。</p>
                        @endif
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-lime-500 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-lime-400 active:bg-lime-600 focus:outline-none focus:border-lime-600 focus:ring ring-lime-300 disabled:opacity-25 transition ease-in-out duration-150">
                            更新
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Vue.js テスト')

@section('header', 'Vue.js コンポーネントのテスト')

@section('content')
<div class="bg-gray-100 p-6 rounded-lg">
    <h1 class="text-2xl font-bold mb-6">Vue.js 3 + Laravel Vite テスト</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Vue.js コンポーネント -->
        <!-- <example-component></example-component> -->
        
        <!-- 通常のHTML -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">通常のHTML要素</h2>
            <p class="text-gray-700">これはVue.jsコンポーネントではなく、通常のHTML要素です。</p>
        </div>
    </div>
    
    <div class="mt-8">
        <p class="text-gray-600">
            このページはLaravel + Vue.js 3 + Vite + Tailwind CSSの連携テストです。
            左側のカードはVue.jsコンポーネントで、ボタンをクリックするとカウントが増加します。
        </p>
    </div>
</div>
@endsection

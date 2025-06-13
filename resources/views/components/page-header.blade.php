<!-- ページヘッダー -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $title }}</h1>
            @if(isset($subtitle))
                <p class="mt-2 text-sm text-gray-600">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($action))
            <div>
                {{ $action }}
            </div>
        @endif
    </div>
</div>
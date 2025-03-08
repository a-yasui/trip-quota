@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative dropdown-container">
    <div class="dropdown-trigger">
        {{ $trigger }}
    </div>

    <div class="dropdown-menu absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }} hidden">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdowns = document.querySelectorAll('.dropdown-container');
        
        dropdowns.forEach(dropdown => {
            const trigger = dropdown.querySelector('.dropdown-trigger');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            // トリガーをクリックしたときのイベント
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = !menu.classList.contains('hidden');
                
                // 他のすべてのドロップダウンを閉じる
                document.querySelectorAll('.dropdown-menu').forEach(m => {
                    if (m !== menu) m.classList.add('hidden');
                });
                
                // 現在のドロップダウンの表示/非表示を切り替え
                menu.classList.toggle('hidden');
                
                if (!isOpen) {
                    // メニューが表示されたときのアニメーション
                    menu.classList.add('transition', 'ease-out', 'duration-200', 'transform', 'opacity-0', 'scale-95');
                    setTimeout(() => {
                        menu.classList.remove('opacity-0', 'scale-95');
                        menu.classList.add('opacity-100', 'scale-100');
                    }, 10);
                }
            });
            
            // メニュー内のクリックイベントが親に伝播しないようにする
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.add('hidden');
            });
        });
        
        // ドキュメント全体をクリックしたときにドロップダウンを閉じる
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        });
    });
</script>

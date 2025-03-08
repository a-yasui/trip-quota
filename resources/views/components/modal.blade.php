@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    id="modal-{{ $name }}"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50 modal-container"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        class="fixed inset-0 transform transition-all modal-backdrop"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto modal-content"
    >
        {{ $slot }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // モーダルの表示・非表示を切り替える関数
        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            
            if (show) {
                modal.style.display = 'block';
                document.body.classList.add('overflow-y-hidden');
                
                // アニメーション
                const backdrop = modal.querySelector('.modal-backdrop');
                const content = modal.querySelector('.modal-content');
                
                backdrop.classList.add('ease-out', 'duration-300', 'opacity-0');
                content.classList.add('ease-out', 'duration-300', 'opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    backdrop.classList.add('opacity-100');
                    
                    content.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                    content.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
                }, 10);
                
                // フォーカス可能な要素を取得
                const focusableElements = modal.querySelectorAll('a, button, input:not([type="hidden"]), textarea, select, details, [tabindex]:not([tabindex="-1"])');
                if (focusableElements.length > 0) {
                    setTimeout(() => focusableElements[0].focus(), 100);
                }
            } else {
                const backdrop = modal.querySelector('.modal-backdrop');
                const content = modal.querySelector('.modal-content');
                
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0');
                
                content.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
                content.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                
                setTimeout(() => {
                    modal.style.display = 'none';
                    document.body.classList.remove('overflow-y-hidden');
                }, 200);
            }
        }
        
        // モーダルを開くイベント
        window.addEventListener('open-modal', function(e) {
            if (e.detail) {
                toggleModal('modal-' + e.detail, true);
            }
        });
        
        // モーダルを閉じるイベント
        window.addEventListener('close-modal', function(e) {
            if (e.detail) {
                toggleModal('modal-' + e.detail, false);
            }
        });
        
        // モーダルの背景をクリックしたときに閉じる
        document.querySelectorAll('.modal-container').forEach(modal => {
            const modalId = modal.id;
            const modalName = modalId.replace('modal-', '');
            
            modal.querySelector('.modal-backdrop').addEventListener('click', function() {
                toggleModal(modalId, false);
            });
            
            // ESCキーでモーダルを閉じる
            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'block') {
                    toggleModal(modalId, false);
                }
            });
        });
    });
</script>

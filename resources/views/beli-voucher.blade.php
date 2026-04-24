<!-- Modal -->
<div id="voucherModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 py-4 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeModal()"></div>

        <!-- Modal panel -->
        <div class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-4 sm:align-middle sm:max-w-md sm:w-full">
            <div class="px-6 pt-6 pb-4 bg-white sm:p-8">
                <div class="text-center">
                    <!-- Icon -->
                    <div class="flex justify-center mb-4">
                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-blue-100">
                            <svg class="size-12 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap=" round" stroke-linejoin="round" stroke-width="0" d="M9,11.71l.29-.3.29.3a1,1,0,0,0,1.42,0,1,1,0,0,0,0-1.42l-.3-.29.3-.29A1,1,0,0,0,9.54,8.29l-.29.3L9,8.29A1,1,0,1,0,7.54,9.71l.3.29-.3.29a1,1,0,0,0,0,1.42,1,1,0,0,0,1.42,0Zm-.6,3.62a1,1,0,0,0-.13,1.4,1,1,0,0,0,1.41.13,3.76,3.76,0,0,1,4.72,0,1,1,0,0,0,1.41-.13,1,1,0,0,0-.13-1.4A5.81,5.81,0,0,0,8.36,15.33ZM12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm0,18a8,8,0,1,1,8-8A8,8,0,0,1,12,20ZM17,8.29a1,1,0,0,0-1.42,0l-.29.3L15,8.29a1,1,0,0,0-1.42,1.42l.3.29-.3.29a1,1,0,0,0,0,1.42,1,1,0,0,0,1.42,0l.29-.3.29.3a1,1,0,0,0,1.42,0,1,1,0,0,0,0-1.42l-.3-.29.3-.29A1,1,0,0,0,17,8.29Z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Title -->
                    <h3 class="text-xl font-semibold leading-6 text-gray-900" id="modal-title">
                        Mohon Maaf
                    </h3>

                    <!-- Content -->
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Kami sedang mengembangkan fitur ini untuk memberikan pengalaman terbaik bagi Anda.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer buttons -->
            <div class="px-6 py-4 bg-gray-50 sm:px-8 sm:flex sm:flex-row-reverse">
                <button onclick="closeModal()" class="inline-flex justify-center w-full px-4 py-2 text-sm text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto">
                    Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('voucherModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('voucherModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
</script>
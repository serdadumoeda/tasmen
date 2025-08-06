<div x-data="{ show: false }" x-show="show" x-init="show = false" @open-about-modal.window="show = true" @keydown.escape.window="show = false" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center" x-cloak>
    <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative mx-auto p-8 border w-full max-w-md shadow-2xl rounded-xl bg-white">
        <div class="flex justify-between items-center pb-4 border-b border-gray-200 mb-4">
            <p class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-hand-sparkles mr-3 text-indigo-600"></i> Tentang Aplikasi Ini
            </p>
            <button type="button" @click="show = false" class="p-2 rounded-full hover:bg-gray-100 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 hover:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="text-gray-700 text-base leading-relaxed mb-4">
            Aplikasi ini adalah sebuah platform manajemen proyek dan tugas yang dirancang untuk membantu tim mengelola alur kerja, melacak progres, dan meningkatkan kolaborasi secara efisien.
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-200">
            <h4 class="font-bold text-lg text-gray-800 mb-3 flex items-center">
                <i class="fas fa-users-line mr-2 text-blue-600"></i> Tim Proyek Web
            </h4>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-700">
                    <tbody>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">Project Manager</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Abdul Harist Habibullah</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">Web Designer & Developer</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Arif Budi Setiawan</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">Middle Man</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Galih Agan Pambayun</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">Quality Assurance</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Rosalia Sianipar</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">Web Developer</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Tegar Hidayat</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="py-2 px-1 font-semibold text-gray-600">UI Designer</td>
                            <td class="py-2 px-1">:</td>
                            <td class="py-2 px-1">Srintika Yuni Kharisma</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3 pt-3 border-t border-gray-200 text-sm text-gray-500 flex items-center">
            <i class="fas fa-code-branch mr-2"></i> Versi Aplikasi: <span class="font-semibold text-gray-700">1.0.0 (Beta)</span>
        </div>
        <div class="mt-1 text-sm text-gray-500 flex items-center">
            <i class="fas fa-copyright mr-2"></i> Dibuat oleh: <span class="font-semibold text-gray-700">PSI 2025</span>
        </div>
    </div>
</div>

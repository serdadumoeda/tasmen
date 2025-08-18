<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('API Query Helper') }}
            </h2>
            <a href="{{ route('admin.api_keys.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Manajemen Kunci API
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8 text-gray-900"
                     x-data="queryBuilder({ resources: {{ json_encode($resources) }} })">

                    <div class="mb-6">
                        <h3 class="text-2xl font-semibold border-b pb-2 mb-4">API Query Helper</h3>
                        <p class="text-gray-700 leading-relaxed">
                            Gunakan form di bawah ini untuk membuat URL API yang sudah difilter secara visual. Pilih sumber data, tambahkan filter yang diinginkan, dan salin URL yang dihasilkan untuk digunakan oleh sistem eksternal.
                        </p>
                    </div>

                    {{-- Form Builder --}}
                    <div class="space-y-6">
                        {{-- Resource Selection --}}
                        <div>
                            <label for="resource" class="block font-medium text-sm text-gray-700">1. Pilih Sumber Data</label>
                            <select id="resource" x-model="selectedResource" @change="resetFilters" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">-- Pilih Sumber Data --</option>
                                <template x-for="(resource, key) in resources" :key="key">
                                    <option :value="key" x-text="resource.label"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Filters Section --}}
                        <div x-show="selectedResource">
                            <h4 class="font-medium text-sm text-gray-700">2. Tambahkan Filter</h4>
                            <div class="mt-2 space-y-3 border-l-2 border-gray-200 pl-4">
                                <template x-for="(filter, index) in activeFilters" :key="index">
                                    <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded-md">
                                        <select x-model="filter.field" class="w-1/3 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            <option value="">-- Pilih Field --</option>
                                            <template x-for="(label, key) in availableFilters" :key="key">
                                                <option :value="key" x-text="label"></option>
                                            </template>
                                        </select>
                                        <input type="text" x-model.debounce.300ms="filter.value" placeholder="Masukkan nilai filter..." class="flex-grow border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <button @click="removeFilter(index)" class="p-2 text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <button @click="addFilter" class="mt-3 inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-plus mr-2"></i> Tambah Filter
                            </button>
                        </div>

                        {{-- Generated URL --}}
                        <div x-show="selectedResource">
                             <h4 class="font-medium text-sm text-gray-700">3. URL yang Dihasilkan</h4>
                             <div class="mt-2 relative">
                                <input type="text" :value="generatedUrl" readonly class="font-mono text-sm block w-full p-2.5 pr-12 bg-gray-100 border-gray-300 rounded-md">
                                <button @click="copyToClipboard($el)" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-copy"></i>
                                </button>
                             </div>
                             <p class="mt-2 text-xs text-green-600" x-show="copied" x-transition>URL berhasil disalin!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function queryBuilder(config) {
            return {
                resources: config.resources,
                selectedResource: '',
                activeFilters: [],
                copied: false,

                get availableFilters() {
                    if (!this.selectedResource) return {};
                    return this.resources[this.selectedResource].filters;
                },

                get generatedUrl() {
                    if (!this.selectedResource) return '';

                    const baseUrl = `{{ url('/api/v1') }}/${this.selectedResource}`;
                    const params = new URLSearchParams();

                    this.activeFilters.forEach(filter => {
                        if (filter.field && filter.value) {
                            params.append(`filter[${filter.field}]`, filter.value);
                        }
                    });

                    const queryString = params.toString();
                    return queryString ? `${baseUrl}?${queryString}` : baseUrl;
                },

                addFilter() {
                    this.activeFilters.push({ field: '', value: '' });
                },

                removeFilter(index) {
                    this.activeFilters.splice(index, 1);
                },

                resetFilters() {
                    this.activeFilters = [];
                },

                copyToClipboard(el) {
                    const input = el.previousElementSibling;
                    input.select();
                    document.execCommand('copy');
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }
            }
        }
    </script>
</x-app-layout>

@php
    $topbarUser = Auth::user();
    $topbarRole = $topbarUser?->role ?? 'Admin';
    $topbarRoleLabel = strtolower($topbarRole) === 'admin' ? 'Admin' : ucfirst($topbarRole);
    $topbarName = $topbarUser?->pegawai?->nama_pegawai ?? $topbarUser?->username ?? 'Admin';
    $topbarEmail = $topbarUser?->email ?? 'admin@selada.id';
@endphp

<header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur-xl">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3.5 sm:px-5 lg:px-6">
        <div class="flex items-center gap-3 sm:gap-4">
            <button
                type="button"
                @click="sidebarOpen = true"
                class="inline-flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-100 xl:hidden"
                aria-label="Buka sidebar">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div>
                <p class="text-xs sm:text-sm font-semibold text-slate-700">Hai, {{ $topbarRoleLabel }}</p>
                <p class="text-[11px] sm:text-xs text-slate-500">Selamat datang kembali</p>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <div class="relative" x-data="{ profileOpen: false }">
                <button
                    @click="profileOpen = !profileOpen"
                    type="button"
                    class="inline-flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full text-slate-700 transition hover:bg-slate-100 border border-slate-200 bg-white shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 sm:h-6 sm:w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </button>

                <div
                    x-show="profileOpen"
                    x-cloak
                    @click.outside="profileOpen = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-72 max-w-[calc(100vw-2rem)] rounded-[2rem] bg-white p-5 sm:p-6 shadow-xl border border-slate-100 z-50">
                    
                    <div class="flex items-center gap-3 sm:gap-4 mb-4 sm:mb-5">
                        <div class="flex h-12 w-12 sm:h-14 sm:w-14 flex-shrink-0 items-center justify-center rounded-full bg-primary text-lg sm:text-xl font-bold text-white shadow-sm">
                            {{ strtoupper(substr($topbarName, 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="truncate text-base sm:text-lg font-bold text-slate-900">{{ $topbarName }}</p>
                            <p class="truncate text-xs sm:text-sm text-slate-500">{{ $topbarEmail }}</p>
                        </div>
                    </div>

                    <hr class="mb-4 border-t border-slate-200">

                    <a href="{{ route('admin.manajemen-akun') }}" class="flex items-center gap-3 text-slate-700 transition hover:text-primary text-sm font-medium mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Ganti Password</span>
                    </a>

                    @if($topbarUser && $topbarUser->role === 'Super Admin')
                    <form method="POST" action="{{ route('admin.organization.switch') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 text-slate-700 transition hover:text-primary text-sm font-medium mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span>Switch Organisasi</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Organisasi | Sistem Absensi</title>
    <!-- FontAwesome 6 CDN for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            @php
                $primaryColor = \App\Models\Setting::get('primary_color', '#123D91');
            @endphp
            --color-primary: {{ $primaryColor }};
            --color-primary-hover: color-mix(in srgb, {{ $primaryColor }} 80%, black);
        }
    </style>
</head>
<body class="bg-[#F5F7FB] text-gray-800 min-h-screen flex flex-col antialiased">
    
    {{-- Header with Logout --}}
    <header class="bg-white border-b border-slate-200 px-6 py-4 flex justify-end shadow-sm">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-red-600 transition">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                Logout
            </button>
        </form>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 flex flex-col max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">
        
        <div class="space-y-8">
            <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl lg:text-[34px] font-bold text-slate-900 leading-tight">
                        Pilih Organisasi
                    </h1>
                    <p class="mt-1.5 sm:mt-2 text-sm sm:text-[15px] text-slate-500">
                        Silakan pilih organisasi untuk dikelola.
                    </p>
                </div>
                
                <div>
                    <a href="{{ route('admin.organization.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-hover">
                        <i class="fa-solid fa-plus"></i>
                        Tambah Organisasi
                    </a>
                </div>
            </section>

            {{-- Flash Notifications --}}
            @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error') || (isset($errors) && $errors->any()))
            <div class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    @if(session('error'))
                        <p>{{ session('error') }}</p>
                    @endif
                    @if(isset($errors) && $errors->any())
                        @foreach($errors->all() as $err)
                            <p>{{ $err }}</p>
                        @endforeach
                    @endif
                </div>
            </div>
            @endif

            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($organizations as $org)
                <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200 hover:border-primary hover:shadow-md transition duration-300 flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-start justify-between mb-4 gap-2">
                            <h2 class="text-lg font-bold text-slate-800 line-clamp-2 leading-tight" title="{{ $org->nama_organisasi }}">
                                {{ $org->nama_organisasi }}
                            </h2>
                        </div>
                        <div class="mb-5 space-y-2">
                            <div class="inline-block bg-blue-50 text-blue-600 text-xs font-bold px-2.5 py-1 rounded-md">
                                {{ $org->kode_organisasi }}
                            </div>
                            <p class="text-sm text-slate-500 line-clamp-3" title="{{ $org->alamat }}">
                                <i class="fa-solid fa-location-dot mr-1 text-slate-400"></i>
                                {{ $org->alamat ?: 'Tidak ada alamat' }}
                            </p>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('admin.organization.store') }}" class="mt-auto">
                        @csrf
                        <input type="hidden" name="organization_id" value="{{ $org->organization_id }}">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 hover:bg-primary px-5 py-2.5 text-sm font-semibold text-slate-700 hover:text-white transition-colors duration-300">
                            Masuk
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </form>
                </div>
                @endforeach

                @if($organizations->isEmpty())
                <div class="col-span-full rounded-3xl bg-white p-12 shadow-sm text-center border border-slate-200">
                    <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-4">
                        <i class="fa-solid fa-building fa-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-1">Belum Ada Organisasi</h3>
                    <p class="text-sm text-slate-500">Tidak ada organisasi aktif yang tersedia untuk dipilih.</p>
                </div>
                @endif
            </section>
        </div>

    </main>

</body>
</html>

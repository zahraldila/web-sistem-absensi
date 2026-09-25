@extends('layouts.admin.app')

@section('title', 'Persetujuan Pengajuan')

@section('content')
@php
    $currentRole = Auth::user()?->roleAkses ?? null;
    $canApprove = $currentRole?->hasPrivilege('approve_pengajuan') ?? false;
    $canReject  = $currentRole?->hasPrivilege('reject_pengajuan') ?? false;
    $canCatat   = $currentRole?->hasPrivilege('catat_absensi') ?? false;
@endphp

<div class="space-y-6 sm:space-y-8" x-data="approvalModal()">

    {{-- ======================================== --}}
    {{-- HEADER --}}
    {{-- ======================================== --}}
    <section class="flex flex-col gap-1 sm:gap-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-[34px] font-bold text-slate-900 leading-tight">
                    Persetujuan Pengajuan
                </h1>

                <p class="text-xs sm:text-[15px] text-slate-500">
                    Pengelolaan Izin, Sakit, WFH, WFC, dan Dinas Pegawai
                </p>
            </div>

            @if($canCatat)
                <button type="button" @click="openCreateModal()"
                   class="inline-flex items-center gap-2 rounded-2xl bg-primary px-4 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-primary-hover self-start sm:self-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Catatan
                </button>
            @endif
        </div>
    </section>

    {{-- ======================================== --}}
    {{-- FLASH SUCCESS BANNER --}}
    {{-- ======================================== --}}
    @if(session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            x-transition
            class="relative rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-xs sm:text-sm text-green-700">
            <div class="pr-6">{{ session('success') }}</div>
            <button type="button" @click="show = false" aria-label="Tutup notifikasi" class="absolute right-2 top-2 text-green-700 hover:text-green-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- ======================================== --}}
    {{-- INLINE FEEDBACK BANNER --}}
    {{-- ======================================== --}}
    <div
        x-show="showSuccessToast"
        x-cloak
        x-transition
        class="relative rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-xs sm:text-sm text-green-700">
        <div class="pr-6" x-text="toastMessage"></div>
        <button type="button" @click="showSuccessToast = false" aria-label="Tutup notifikasi" class="absolute right-2 top-2 text-green-700 hover:text-green-900">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div
        x-show="showErrorToast"
        x-cloak
        x-transition
        class="relative rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs sm:text-sm text-red-700">
        <div class="pr-6" x-text="errorMessage"></div>
        <button type="button" @click="showErrorToast = false" aria-label="Tutup notifikasi" class="absolute right-2 top-2 text-red-700 hover:text-red-900">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- ======================================== --}}
    {{-- SUMMARY CARD (2 Kolom di HP, 3 di Desktop) --}}
    {{-- ======================================== --}}
    <section class="grid grid-cols-1 gap-3.5 sm:gap-6 md:grid-cols-3">

        {{-- Pending --}}
        <div class="overflow-hidden rounded-2xl sm:rounded-3xl bg-white shadow-sm sm:shadow-card">
            <div class="flex">
                <div class="w-1.5 bg-orange-500 flex-shrink-0"></div>
                <div class="flex flex-1 items-center justify-between p-3.5 sm:p-6 min-w-0">
                    <div>
                        <p class="text-[11px] sm:text-sm font-semibold uppercase tracking-wide text-slate-400 sm:text-slate-500">
                            Pending
                        </p>
                        <h2 class="mt-1 sm:mt-2 text-2xl sm:text-4xl font-bold text-slate-900" x-text="counts.pending">
                            {{ $pending }}
                        </h2>
                    </div>
                    <div class="flex h-10 w-10 sm:h-14 sm:w-14 items-center justify-center rounded-2xl sm:rounded-full bg-orange-100 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-7 sm:w-7 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Disetujui --}}
        <div class="overflow-hidden rounded-2xl sm:rounded-3xl bg-white shadow-sm sm:shadow-card">
            <div class="flex">
                <div class="w-1.5 bg-green-600 flex-shrink-0"></div>
                <div class="flex flex-1 items-center justify-between p-3.5 sm:p-6 min-w-0">
                    <div>
                        <p class="text-[11px] sm:text-sm font-semibold uppercase tracking-wide text-slate-400 sm:text-slate-500">
                            Disetujui
                        </p>
                        <h2 class="mt-1 sm:mt-2 text-2xl sm:text-4xl font-bold text-slate-900" x-text="counts.disetujui">
                            {{ $disetujui }}
                        </h2>
                    </div>
                    <div class="flex h-10 w-10 sm:h-14 sm:w-14 items-center justify-center rounded-2xl sm:rounded-full bg-green-100 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-7 sm:w-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ditolak --}}
        <div class="overflow-hidden rounded-2xl sm:rounded-3xl bg-white shadow-sm sm:shadow-card">
            <div class="flex">
                <div class="w-1.5 bg-red-600 flex-shrink-0"></div>
                <div class="flex flex-1 items-center justify-between p-3.5 sm:p-6 min-w-0">
                    <div>
                        <p class="text-[11px] sm:text-sm font-semibold uppercase tracking-wide text-slate-400 sm:text-slate-500">
                            Ditolak
                        </p>
                        <h2 class="mt-1 sm:mt-2 text-2xl sm:text-4xl font-bold text-slate-900" x-text="counts.ditolak">
                            {{ $ditolak }}
                        </h2>
                    </div>
                    <div class="flex h-10 w-10 sm:h-14 sm:w-14 items-center justify-center rounded-2xl sm:rounded-full bg-red-100 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-7 sm:w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

    </section>

    {{-- ======================================== --}}
    {{-- TAB STATUS & ACTION --}}
    {{-- ======================================== --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        {{-- TABS (Scrollable di HP) --}}
        <div class="overflow-x-auto pb-1 sm:pb-0">
            <div class="inline-flex items-center gap-1 rounded-2xl bg-slate-100 p-1.5 min-w-full sm:min-w-0">
                <button
                    type="button"
                    data-status=""
                    class="approval-tab rounded-xl px-4 sm:px-5 py-2 text-xs sm:text-sm font-semibold text-slate-600 transition whitespace-nowrap">
                    Semua
                </button>

                <button
                    type="button"
                    data-status="Pending"
                    class="approval-tab rounded-xl px-4 sm:px-5 py-2 text-xs sm:text-sm font-semibold text-slate-600 transition whitespace-nowrap">
                    Menunggu
                </button>

                <button
                    type="button"
                    data-status="Disetujui"
                    class="approval-tab rounded-xl px-4 sm:px-5 py-2 text-xs sm:text-sm font-semibold text-slate-600 transition whitespace-nowrap">
                    Disetujui
                </button>

                <button
                    type="button"
                    data-status="Ditolak"
                    class="approval-tab rounded-xl px-4 sm:px-5 py-2 text-xs sm:text-sm font-semibold text-slate-600 transition whitespace-nowrap">
                    Ditolak
                </button>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="grid grid-cols-2 gap-2.5 sm:flex sm:gap-3">
            <button
                type="button"
                id="open-filter-modal"
                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 sm:px-6 py-2.5 text-xs sm:text-sm font-medium text-slate-700 transition hover:bg-slate-50 shadow-sm whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>Filter</span>
            </button>

            <button
                type="button"
                id="open-export-modal"
                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 sm:px-6 py-2.5 text-xs sm:text-sm font-medium text-slate-700 transition hover:bg-slate-50 shadow-sm whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Export</span>
            </button>
        </div>

    </div>

    {{-- ======================================== --}}
    {{-- TABLE / CARD CONTAINER --}}
    {{-- ======================================== --}}
    <div id="approval-table">
        @include('admin.persetujuan.partials.table', [
            'approvals' => $approvals
        ])
    </div>

    {{-- DETAIL MODAL --}}
    <template x-teleport="body">
    <div x-show="showDetail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4" @click.self="closeDetail()">
        <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl sm:rounded-[28px] bg-white shadow-2xl ring-1 ring-slate-200" @click.stop>
            <div class="border-b border-slate-200 px-5 sm:px-6 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">Detail Pengajuan</h2>
                        <p class="mt-0.5 text-xs sm:text-sm text-slate-500">Informasi lengkap pengajuan yang dipilih.</p>
                    </div>
                    <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="closeDetail()" aria-label="Tutup modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="grid gap-5 sm:gap-6 grid-cols-1 lg:grid-cols-[180px_1fr] items-center">
                    <div class="flex flex-col items-center justify-center text-center">
                        <template x-if="detailData.foto_profile">
                            <img :src="detailData.foto_profile" alt="Foto Pegawai" class="mx-auto h-24 w-24 sm:h-28 sm:w-28 rounded-full object-cover shadow-md border-2 border-white" />
                        </template>
                        <template x-if="!detailData.foto_profile">
                            <div class="mx-auto flex h-24 w-24 sm:h-28 sm:w-28 items-center justify-center rounded-full bg-slate-100 text-2xl sm:text-3xl font-semibold text-slate-700 border border-slate-200" x-text="detailInitials()"></div>
                        </template>
                        <div class="mt-3 text-base sm:text-lg font-bold text-slate-900" x-text="detailData.nama_pegawai"></div>
                        <div class="text-xs text-slate-500" x-text="detailData.divisi_name"></div>
                    </div>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3 sm:gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5 text-xs sm:text-sm">
                            <div class="text-slate-500">Department</div>
                            <div class="font-medium text-slate-900" x-text="detailData.divisi_name || '-' "></div>

                            <div class="text-slate-500">Jenis Pengajuan</div>
                            <div class="font-medium text-slate-900" x-text="detailData.jenis_pengajuan || '-' "></div>

                            <div class="text-slate-500">Tanggal Pengajuan</div>
                            <div class="font-medium text-slate-900" x-text="detailData.tanggal_pengajuan || '-' "></div>

                            <div class="text-slate-500">Status</div>
                            <div>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="statusClass(detailData.status_pengajuan)"
                                    x-text="detailData.status_pengajuan || '-' "></span>
                            </div>

                            <div class="text-slate-500">Keterangan</div>
                            <div class="font-medium text-slate-900 break-words" x-text="detailData.keterangan || '-' "></div>

                            <div class="text-slate-500">Lampiran</div>
                            <div>
                                <template x-if="detailData.lampiran_url">
                                    <a :href="detailData.lampiran_url" target="_blank" download class="inline-flex items-center gap-1.5 font-medium text-primary hover:underline">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="truncate max-w-[140px] sm:max-w-[160px]" :title="detailData.lampiran_name" x-text="detailData.lampiran_name || 'Unduh Lampiran'"></span>
                                    </a>
                                </template>
                                <template x-if="!detailData.lampiran_url">
                                    <span class="text-slate-400">Tidak ada lampiran</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-200 bg-white px-5 sm:px-6 py-4 flex items-center justify-end gap-2.5">
                <template x-if="detailData.status_pengajuan === 'Pending'">
                    <div class="grid grid-cols-2 gap-2.5 w-full sm:w-auto sm:flex sm:items-center">
                        @if($canApprove)
                            <button type="button"
                                @click="confirmApprove(detailData.approval_id, detailData.jenis_pengajuan, detailData.nama_pegawai)"
                                class="inline-flex items-center justify-center rounded-2xl bg-green-600 px-5 py-2.5 text-xs sm:text-sm font-semibold text-white transition hover:bg-green-700 shadow-sm">
                                Setujui
                            </button>
                        @else
                            <button type="button"
                                disabled
                                title="Anda tidak memiliki hak akses untuk menyetujui pengajuan"
                                class="inline-flex items-center justify-center rounded-2xl bg-slate-200 px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-400 opacity-60 cursor-not-allowed select-none shadow-none">
                                Setujui
                            </button>
                        @endif

                        @if($canReject)
                            <button type="button"
                                @click="openRejectModal(detailData.approval_id, detailData.jenis_pengajuan, detailData.nama_pegawai)"
                                class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-5 py-2.5 text-xs sm:text-sm font-semibold text-white transition hover:bg-red-700 shadow-sm">
                                Tolak
                            </button>
                        @else
                            <button type="button"
                                disabled
                                title="Anda tidak memiliki hak akses untuk menolak pengajuan"
                                class="inline-flex items-center justify-center rounded-2xl bg-slate-200 px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-400 opacity-60 cursor-not-allowed select-none shadow-none">
                                Tolak
                            </button>
                        @endif
                    </div>
                </template>
            </div>
        </div>
    </div>
    </template>

    {{-- REJECT REASON MODAL --}}
    <template x-teleport="body">
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4" @click.self="closeRejectModal()">
            <div class="relative w-full max-w-md overflow-hidden rounded-3xl sm:rounded-[24px] bg-white shadow-2xl ring-1 ring-slate-200" @click.stop>
            <div class="border-b border-slate-200 px-5 sm:px-6 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">Alasan Penolakan</h2>
                        <p class="mt-0.5 text-xs sm:text-sm text-slate-500" x-text="'Tolak pengajuan ' + rejectData.jenis_pengajuan + ' untuk ' + rejectData.nama_pegawai"></p>
                    </div>
                    <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="closeRejectModal()" aria-label="Tutup modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-5 sm:px-6 py-4">
                <label for="alasan_penolakan" class="block text-xs sm:text-sm font-medium text-slate-700 mb-1.5">
                    Masukkan Alasan Penolakan <span class="text-red-500">*</span>
                </label>
                <textarea id="alasan_penolakan" x-model="rejectReason" rows="3"
                    @input="rejectError = false"
                    :class="{'border-red-500 focus:border-red-500 focus:ring-red-500': rejectError, 'border-slate-300 focus:border-red-500 focus:ring-red-500': !rejectError}"
                    class="w-full rounded-2xl border p-3 text-xs sm:text-sm outline-none transition"
                    placeholder="Contoh: Kuota izin bulan ini sudah habis..."></textarea>
                <p x-show="rejectError" x-cloak class="mt-1.5 text-xs text-red-500 font-medium">Mohon isi alasan penolakan terlebih dahulu.</p>
            </div>
            <div class="border-t border-slate-200 bg-white px-5 sm:px-6 py-4 grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                <button type="button" @click="closeRejectModal()"
                    class="w-full sm:w-auto inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" @click="submitReject()"
                    class="w-full sm:w-auto inline-flex items-center justify-center rounded-2xl bg-red-600 px-5 py-2.5 text-xs sm:text-sm font-semibold text-white transition hover:bg-red-700 shadow-sm"
                    :disabled="isProcessing">
                    <span x-show="!isProcessing">Tolak Pengajuan</span>
                    <span x-show="isProcessing">Memproses...</span>
                </button>
            </div>
        </div>
        </div>
    </template>

    {{-- ======================================== --}}
    {{-- APPROVE CONFIRMATION MODAL --}}
    {{-- ======================================== --}}
    <template x-teleport="body">
        <div x-show="showApproveConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4" @click.self="closeApproveConfirm()">
            <div class="relative w-full max-w-md overflow-hidden rounded-3xl sm:rounded-[24px] bg-white shadow-2xl ring-1 ring-slate-200" @click.stop>
            {{-- Header --}}
            <div class="border-b border-slate-200 px-5 sm:px-6 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">Konfirmasi Persetujuan</h2>
                        <p class="mt-0.5 text-xs sm:text-sm text-slate-500" x-text="'Setujui pengajuan ' + approveData.jenis_pengajuan + ' untuk ' + approveData.nama_pegawai"></p>
                    </div>
                    <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="closeApproveConfirm()" aria-label="Tutup modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            {{-- Body --}}
            <div class="px-5 sm:px-6 py-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Apakah Anda yakin ingin menyetujui pengajuan ini?</p>
                        <p class="mt-1 text-xs text-slate-500">Pengajuan <span class="font-semibold text-slate-700" x-text="approveData.jenis_pengajuan"></span> dari <span class="font-semibold text-slate-700" x-text="approveData.nama_pegawai"></span> akan disetujui dan statusnya akan berubah menjadi <span class="font-semibold text-green-700">Disetujui</span>.</p>
                    </div>
                </div>
            </div>
            {{-- Footer --}}
            <div class="border-t border-slate-200 bg-white px-5 sm:px-6 py-4 grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                <button type="button" @click="closeApproveConfirm()"
                    class="w-full sm:w-auto inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" @click="submitApprove()"
                    class="w-full sm:w-auto inline-flex items-center justify-center rounded-2xl bg-green-600 px-5 py-2.5 text-xs sm:text-sm font-semibold text-white transition hover:bg-green-700 shadow-sm"
                    :disabled="isProcessing">
                    <span x-show="!isProcessing">Setujui</span>
                    <span x-show="isProcessing">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    </template>
    {{-- ======================================== --}}
    {{-- CREATE MODAL --}}
    {{-- ======================================== --}}
    <template x-teleport="body">
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4 overflow-y-auto" @click.self="closeCreateModal()">
            <div class="relative w-full max-w-lg my-auto rounded-3xl sm:rounded-[28px] bg-white shadow-2xl ring-1 ring-slate-200" @click.stop>
                
                {{-- Header --}}
                <div class="flex items-start justify-between border-b border-slate-200 px-5 sm:px-6 py-4">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">Tambah Catatan Absensi</h2>
                        <p class="mt-0.5 text-xs sm:text-sm text-slate-500">Catat kehadiran (WFO/WFH/WFC) atau pengajuan (Sakit/Izin/Cuti) atas nama pegawai.</p>
                    </div>
                    <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 shrink-0 ml-4"
                        @click="closeCreateModal()" aria-label="Tutup modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Form Body --}}
                <form id="form-create-catatan" action="{{ route('admin.persetujuan.store') }}" method="POST" @submit="isProcessingCreate = true">
                    @csrf
                    
                    {{-- Error Display Inside Modal --}}
                    @if ($errors->any())
                        <div class="mx-5 sm:mx-6 mt-4 rounded-xl border border-red-200 bg-red-50 p-4">
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
                                <div>
                                    <h3 class="text-sm font-semibold text-red-800">Terdapat kesalahan pada input:</h3>
                                    <ul class="mt-1 list-disc list-inside text-xs sm:text-sm text-red-700">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="px-5 sm:px-6 py-5 space-y-4">
                        {{-- Pegawai --}}
                        <div>
                            @php
                                $createPegawaiOptions = isset($pegawaiOptions) 
                                    ? $pegawaiOptions->map(function($p) {
                                        return ['value' => $p->pegawai_id, 'text' => $p->nama_pegawai];
                                    })->toArray() 
                                    : [];
                            @endphp
                            <x-forms.searchable-select 
                                name="pegawai_id" 
                                label="Pegawai *" 
                                placeholder="Pilih Pegawai..."
                                searchPlaceholder="Cari pegawai..."
                                notFoundText="Pegawai tidak ditemukan"
                                :showPlaceholderOption="false"
                                :options="$createPegawaiOptions" 
                                selected="{{ old('pegawai_id') }}" 
                            />
                        </div>

                        {{-- Tanggal --}}
                        <div>
                            <label for="tanggal" class="block text-xs sm:text-sm font-medium text-slate-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-slate-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                        </div>

                        {{-- Jenis --}}
                        <div>
                            @php
                                $createJenisOptions = isset($jenisOptions) 
                                    ? collect($jenisOptions)->map(function($jo) {
                                        return ['value' => $jo, 'text' => $jo];
                                    })->toArray()
                                    : [];
                            @endphp
                            <x-forms.searchable-select 
                                name="jenis" 
                                label="Jenis Catatan *" 
                                placeholder="Pilih Jenis..."
                                searchPlaceholder="Cari jenis..."
                                notFoundText="Jenis catatan tidak ditemukan"
                                :showPlaceholderOption="false"
                                :options="$createJenisOptions" 
                                selected="{{ old('jenis') }}" 
                            />
                            <p class="mt-1.5 text-[11px] text-slate-500">WFO/WFH/WFC akan masuk sebagai Kehadiran. Sisanya sebagai Pengajuan Disetujui.</p>
                        </div>

                        {{-- Keterangan --}}
                        <div>
                            <label for="keterangan" class="block text-xs sm:text-sm font-medium text-slate-700 mb-1.5">Keterangan <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <textarea name="keterangan" id="keterangan" rows="3" class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-slate-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" placeholder="Tuliskan keterangan atau alasan (misal: Sakit demam berdarah)...">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="border-t border-slate-200 bg-white px-5 sm:px-6 py-4 grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                        <button type="button" @click="closeCreateModal()"
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" 
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-2.5 text-xs sm:text-sm font-semibold text-white transition hover:bg-primary-hover shadow-sm"
                            :disabled="isProcessingCreate">
                            <span x-show="!isProcessingCreate">Simpan Catatan</span>
                            <span x-show="isProcessingCreate">Menyimpan...</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </template>

</div>

{{-- ======================================== --}}
{{-- MODAL: FILTER --}}
{{-- ======================================== --}}
<div
    id="filter-modal"
    class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="filter-modal-title">

    <div class="flex min-h-full items-center justify-center">
        <div class="relative w-full max-w-lg overflow-hidden rounded-3xl sm:rounded-[28px] bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 sm:px-6 py-4">
                <div>
                    <h2 id="filter-modal-title" class="text-base sm:text-lg font-bold text-slate-900">
                        Filter Pengajuan
                    </h2>
                    <p class="mt-0.5 text-xs sm:text-sm text-slate-500">
                        Saring data pengajuan sesuai kebutuhan Anda.
                    </p>
                </div>
                <button type="button" id="close-filter-modal" class="flex items-center justify-center rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="filter-form" class="space-y-4 px-5 sm:px-6 py-5">
                <div class="space-y-3.5">
                    <div>
                        <label class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Tanggal Awal</label>
                        <input type="date" name="tanggal_awal" class="w-full rounded-2xl border border-slate-300 bg-white px-3.5 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" class="w-full rounded-2xl border border-slate-300 bg-white px-3.5 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Jenis Pengajuan</label>
                        <select name="jenis_pengajuan" class="w-full rounded-2xl border border-slate-300 bg-white px-3.5 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-slate-900 focus:border-primary focus:ring-primary outline-none">
                            <option value="">Semua</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="WFH">WFH</option>
                            <option value="WFC">WFC</option>
                            <option value="Dinas">Dinas</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-4 grid grid-cols-2 gap-3 sm:flex sm:justify-end">
                    <button type="button" id="reset-filter" class="w-full sm:w-auto rounded-2xl border border-slate-300 bg-white px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Reset
                    </button>
                    <button type="submit" class="w-full sm:w-auto rounded-2xl bg-primary px-5 py-2.5 text-xs sm:text-sm font-semibold text-white transition hover:bg-primary-hover shadow-sm">
                        Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ======================================== --}}
{{-- MODAL: EXPORT --}}
{{-- ======================================== --}}
<div
    id="export-modal"
    class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="export-modal-title">

    <div class="flex min-h-full items-center justify-center">
        <div class="relative w-full max-w-2xl max-h-[90vh] overflow-visible rounded-3xl sm:rounded-[28px] bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 sm:px-8 py-5 sm:py-6">
                <div>
                    <h2 id="export-modal-title" class="text-base sm:text-lg font-bold text-slate-900">
                        Export Pengajuan
                    </h2>
                    <p class="mt-1 text-xs sm:text-sm text-slate-500">
                        Pilih format dan filter untuk unduh data pengajuan persetujuan.
                    </p>
                </div>
                <button type="button" id="close-export-modal" class="flex items-center justify-center rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="export-form" class="space-y-5 sm:space-y-6 px-5 sm:px-8 py-5 sm:py-6">
                <div class="grid gap-5 sm:gap-6 grid-cols-1 md:grid-cols-2">
                    <div class="space-y-3 sm:space-y-4">
                        <p class="text-xs sm:text-sm font-semibold text-slate-900">Format Export</p>
                        <div class="space-y-3">
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 sm:p-4 transition hover:border-slate-300 hover:bg-slate-50">
                                <input type="radio" name="format" value="xlsx" class="mt-1 h-4 w-4 text-primary focus:ring-primary" checked />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900">Excel (.xlsx)</p>
                                    <p class="text-xs text-slate-500">Unduh file Excel dengan data pengajuan persetujuan.</p>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 sm:p-4 transition hover:border-slate-300 hover:bg-slate-50">
                                <input type="radio" name="format" value="csv" class="mt-1 h-4 w-4 text-primary focus:ring-primary" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900">CSV</p>
                                    <p class="text-xs text-slate-500">Unduh file CSV yang mudah diolah.</p>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 sm:p-4 transition hover:border-slate-300 hover:bg-slate-50">
                                <input type="radio" name="format" value="pdf" class="mt-1 h-4 w-4 text-primary focus:ring-primary" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900">PDF</p>
                                    <p class="text-xs text-slate-500">Unduh file PDF yang siap dicetak.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-3 sm:space-y-4">
                        <p class="text-xs sm:text-sm font-semibold text-slate-900">Rentang Tanggal</p>
                        <div class="space-y-3">
                            <div>
                                <label class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Tanggal Awal</label>
                                <input type="date" name="tanggal_awal" class="w-full rounded-2xl border border-slate-300 bg-white px-3.5 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Tanggal Akhir</label>
                                <input type="date" name="tanggal_akhir" class="w-full rounded-2xl border border-slate-300 bg-white px-3.5 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 sm:gap-4 border-t border-slate-200 pt-5 sm:pt-6 grid-cols-1 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Status</label>
                        <select name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-3.5 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            <option value="">Semua</option>
                            <option value="Pending">Pending</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs sm:text-sm font-medium text-slate-700">Jenis Pengajuan</label>
                        <select name="jenis_pengajuan" class="w-full rounded-2xl border border-slate-300 bg-white px-3.5 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            <option value="">Semua</option>
                            @foreach($jenisPengajuan as $jenis)
                                <option value="{{ $jenis }}">{{ $jenis }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        @php
                            $pegawaiOptions = $pegawai->map(function($p) {
                                return ['value' => $p->pegawai_id, 'text' => $p->nama_pegawai];
                            })->toArray();
                        @endphp
                        <x-forms.searchable-select name="pegawai_id" label="Pegawai" :options="$pegawaiOptions" />
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-5 sm:pt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" id="cancel-export" class="w-full sm:w-auto rounded-2xl border border-slate-300 bg-white px-6 py-3 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" id="export-submit-btn" class="w-full sm:w-auto rounded-2xl bg-primary px-6 py-3 text-xs sm:text-sm font-semibold text-white transition hover:bg-primary-hover shadow-sm">
                        Unduh
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function approvalModal() {
        return {
            showDetail: false,
            showRejectModal: false,
            showApproveConfirm: false,
            showCreateModal: {{ $errors->any() ? 'true' : 'false' }},
            isProcessingCreate: false,
            approveData: {
                approval_id: null,
                jenis_pengajuan: '',
                nama_pegawai: ''
            },
            showSuccessToast: false,
            toastMessage: '',
            showErrorToast: false,
            errorMessage: '',
            rejectReason: '',
            rejectError: false,
            isProcessing: false,
            counts: {
                pending: {{ $pending ?? 0 }},
                disetujui: {{ $disetujui ?? 0 }},
                ditolak: {{ $ditolak ?? 0 }}
            },
            detailData: {
                approval_id: null,
                nama_pegawai: '',
                divisi_name: '',
                jenis_pengajuan: '',
                tanggal_pengajuan: '',
                status_pengajuan: '',
                keterangan: '',
                lampiran_path: null,
                lampiran_url: null,
                lampiran_name: null,
                foto_profile: null
            },
            rejectData: {
                approval_id: null,
                jenis_pengajuan: '',
                nama_pegawai: ''
            },
            openApproval(event) {
                const button = event.currentTarget;
                const data = JSON.parse(button.getAttribute('data-approval'));
                this.detailData = data;
                this.showDetail = true;
            },
            closeDetail() {
                this.showDetail = false;
            },
            openCreateModal() {
                this.showCreateModal = true;
                this.$nextTick(() => {
                    document.getElementById('form-create-catatan').querySelectorAll('.searchable-select-component').forEach(el => {
                        el.dispatchEvent(new CustomEvent('reset-component'));
                    });
                });
            },
            closeCreateModal() {
                this.showCreateModal = false;
                this.$nextTick(() => {
                    document.getElementById('form-create-catatan').querySelectorAll('.searchable-select-component').forEach(el => {
                        el.dispatchEvent(new CustomEvent('close-component'));
                    });
                    document.getElementById('form-create-catatan').reset();
                });
            },
            detailInitials() {
                if (!this.detailData.nama_pegawai) return 'U';
                return this.detailData.nama_pegawai.substring(0, 1).toUpperCase();
            },
            statusClass(status) {
                switch(status) {
                    case 'Pending': return 'bg-yellow-100 text-yellow-700';
                    case 'Disetujui': return 'bg-green-100 text-green-700';
                    case 'Ditolak': return 'bg-red-100 text-red-700';
                    default: return 'bg-slate-100 text-slate-700';
                }
            },
            confirmApprove(approvalId, jenisPengajuan, namaPegawai) {
                this.approveData = {
                    approval_id: approvalId,
                    jenis_pengajuan: jenisPengajuan,
                    nama_pegawai: namaPegawai
                };
                this.showApproveConfirm = true;
            },
            closeApproveConfirm() {
                this.showApproveConfirm = false;
            },
            submitApprove() {
                this.processApproval(this.approveData.approval_id, 'setujui');
            },
            showToast(message, type) {
                if (type === 'success') {
                    this.toastMessage = message;
                    this.showSuccessToast = true;
                    setTimeout(() => { this.showSuccessToast = false; }, 4000);
                } else {
                    this.errorMessage = message;
                    this.showErrorToast = true;
                    setTimeout(() => { this.showErrorToast = false; }, 4000);
                }
            },
            openRejectModal(approvalId, jenisPengajuan, namaPegawai) {
                this.rejectData = {
                    approval_id: approvalId,
                    jenis_pengajuan: jenisPengajuan,
                    nama_pegawai: namaPegawai
                };
                this.rejectReason = '';
                this.showRejectModal = true;
            },
            closeRejectModal() {
                this.showRejectModal = false;
                this.rejectReason = '';
                this.rejectError = false;
            },
            submitReject() {
                if (!this.rejectReason.trim()) {
                    this.rejectError = true;
                    return;
                }
                this.processApproval(this.rejectData.approval_id, 'tolak', this.rejectReason);
            },
            processApproval(approvalId, action, alasan = null) {
                this.isProcessing = true;

                const url = '{{ route("admin.persetujuan.process", ":id") }}'
                    .replace(':id', approvalId);

                const bodyData = {
                    status_approval: action === 'setujui' ? 'Disetujui' : 'Ditolak',
                    _token: '{{ csrf_token() }}'
                };

                if (alasan) {
                    bodyData.catatan_admin = alasan;
                }

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(bodyData)
                })
                .then(async res => {
                    const data = await res.json();

                    if (!res.ok) {
                        throw new Error(data.message || 'Terjadi kesalahan.');
                    }

                    return data;
                })
                .then(data => {
                    this.isProcessing = false;

                    if (data.status === 'success') {
                        this.closeRejectModal();
                        this.closeApproveConfirm();
                        this.closeDetail();

                        this.showToast(data.message || 'Pengajuan berhasil diproses.', 'success');

                        setTimeout(() => { window.location.reload(); }, 1800);
                    } else {
                        this.showToast(data.message || 'Terjadi kesalahan saat memproses pengajuan.', 'error');
                    }
                })
                .catch(err => {
                    this.isProcessing = false;
                    console.error(err);
                    this.showToast(err.message || 'Gagal menghubungi server.', 'error');
                });
            }
        };
    }
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.approval-tab');
    const tableContainer = document.getElementById('approval-table');

    const filterModal = document.getElementById('filter-modal');
    const openFilterModal = document.getElementById('open-filter-modal');
    const closeFilterModal = document.getElementById('close-filter-modal');
    const resetFilter = document.getElementById('reset-filter');
    const filterForm = document.getElementById('filter-form');

    function openFilter() {
        filterModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeFilter() {
        filterModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    openFilterModal.addEventListener('click', openFilter);
    closeFilterModal.addEventListener('click', closeFilter);

    const loadingHtml = `
        <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
            <div class="flex flex-col items-center justify-center gap-3">
                <div class="h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-primary"></div>
                <p class="text-sm font-medium text-slate-500">Memuat data pengajuan...</p>
            </div>
        </div>
    `;

    resetFilter.addEventListener('click', function () {
        filterForm.reset();
        closeFilter();
        tableContainer.innerHTML = loadingHtml;
        loadApprovals('');
    });

    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(filterForm);
        const tanggalAwal = formData.get('tanggal_awal');
        const tanggalAkhir = formData.get('tanggal_akhir');
        const jenisPengajuan = formData.get('jenis_pengajuan');

        const url = new URL('{{ route("admin.persetujuan") }}', window.location.origin);
        if (tanggalAwal) url.searchParams.set('tanggal_awal', tanggalAwal);
        if (tanggalAkhir) url.searchParams.set('tanggal_akhir', tanggalAkhir);
        if (jenisPengajuan) url.searchParams.set('jenis_pengajuan', jenisPengajuan);

        closeFilter();
        tableContainer.innerHTML = loadingHtml;

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Gagal memfilter pengajuan.');
            return response.json();
        })
        .then(data => {
            tableContainer.innerHTML = data.html;
            if (window.Alpine) window.Alpine.initTree(tableContainer);
            if (data.counts) {
                const rootElem = document.querySelector('[x-data]');
                if (rootElem && rootElem._x_dataStack && rootElem._x_dataStack[0]) {
                    rootElem._x_dataStack[0].counts = data.counts;
                }
            }
            window.history.pushState({}, '', url.toString());
        })
        .catch(error => {
            console.error(error);
            alert('Gagal menerapkan filter.');
        });
    });

    const exportModal = document.getElementById('export-modal');
    const openExportModal = document.getElementById('open-export-modal');
    const closeExportModal = document.getElementById('close-export-modal');
    const cancelExport = document.getElementById('cancel-export');
    const exportForm = document.getElementById('export-form');
    const exportSubmitBtn = document.getElementById('export-submit-btn');
    let exportIsLoading = false;

    function setExportLoading(loading) {
        exportIsLoading = loading;
        if (exportSubmitBtn) {
            exportSubmitBtn.disabled = loading;
            exportSubmitBtn.textContent = loading ? 'Menyiapkan...' : 'Unduh';
        }
    }

    function openExport() {
        setExportLoading(false);
        exportModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeExport() {
        setExportLoading(false);
        exportModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        exportForm.reset();
    }

    openExportModal.addEventListener('click', openExport);
    closeExportModal.addEventListener('click', closeExport);
    cancelExport.addEventListener('click', closeExport);

    exportForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (exportIsLoading) {
            return;
        }

        const formData = new FormData(exportForm);
        const format = formData.get('format');
        const tanggalAwal = formData.get('tanggal_awal');
        const tanggalAkhir = formData.get('tanggal_akhir');
        const status = formData.get('status');
        const jenisPengajuan = formData.get('jenis_pengajuan');
        const pegawaiId = formData.get('pegawai_id');

        if (!format) {
            alert('Silakan pilih format export terlebih dahulu.');
            return;
        }

        setExportLoading(true);

        let exportUrl = '{{ route("admin.persetujuan.export.excel") }}';
        if (format === 'csv') {
            exportUrl = '{{ route("admin.persetujuan.export.csv") }}';
        } else if (format === 'pdf') {
            exportUrl = '{{ route("admin.persetujuan.export.pdf") }}';
        }

        const url = new URL(exportUrl, window.location.origin);
        if (tanggalAwal) url.searchParams.set('tanggal_awal', tanggalAwal);
        if (tanggalAkhir) url.searchParams.set('tanggal_akhir', tanggalAkhir);
        if (status) url.searchParams.set('status', status);
        if (jenisPengajuan) url.searchParams.set('jenis_pengajuan', jenisPengajuan);
        if (pegawaiId) url.searchParams.set('pegawai_id', pegawaiId);

        window.location.href = url.toString();
        setTimeout(() => {
            setExportLoading(false);
            closeExport();
            
            // Tampilkan toast success via Alpine
            const alpineComponent = document.querySelector('[x-data="approvalModal()"]');
            if (alpineComponent && alpineComponent._x_dataStack && alpineComponent._x_dataStack[0]) {
                alpineComponent._x_dataStack[0].showSuccessToast = true;
                alpineComponent._x_dataStack[0].toastMessage = 'Data berhasil diekspor.';
                setTimeout(() => {
                    alpineComponent._x_dataStack[0].showSuccessToast = false;
                }, 3000);
            }
        }, 1500);
    });

    function setActiveTab(activeTab) {
        tabs.forEach(function (tab) {
            tab.classList.remove('bg-white', 'text-primary', 'shadow-sm');
            tab.classList.add('text-slate-600');
        });
        activeTab.classList.remove('text-slate-600');
        activeTab.classList.add('bg-white', 'text-primary', 'shadow-sm');
    }

    function loadApprovals(status, pushState = true) {
        const url = new URL('{{ route("admin.persetujuan") }}', window.location.origin);
        if (status) url.searchParams.set('status', status);

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Gagal mengambil data pengajuan.');
            return response.json();
        })
        .then(data => {
            tableContainer.innerHTML = data.html;
            if (window.Alpine) window.Alpine.initTree(tableContainer);
            if (data.counts) {
                const rootElem = document.querySelector('[x-data]');
                if (rootElem && rootElem._x_dataStack && rootElem._x_dataStack[0]) {
                    rootElem._x_dataStack[0].counts = data.counts;
                }
            }
            if (pushState) window.history.pushState({}, '', url.toString());
        })
        .catch(error => {
            console.error(error);
            alert('Gagal memuat data pengajuan.');
        });
    }

    window.refreshApprovalTable = function () {
        const currentActiveTab = Array.from(tabs).find(t => t.classList.contains('bg-white'));
        const status = currentActiveTab ? currentActiveTab.dataset.status : (new URLSearchParams(window.location.search).get('status') || '');
        loadApprovals(status, false);
    };

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const status = this.dataset.status;
            setActiveTab(this);
            loadApprovals(status);
        });
    });

    const currentStatus = new URLSearchParams(window.location.search).get('status') || '';
    const activeTab = Array.from(tabs).find(function (tab) {
        return tab.dataset.status === currentStatus;
    });

    if (activeTab) {
        setActiveTab(activeTab);
    } else {
        setActiveTab(tabs[0]);
    }
});
</script>
@endpush

@endsection
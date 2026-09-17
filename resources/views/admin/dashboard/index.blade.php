@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-context', 'Dashboard')

@section('content')
    <x-admin.page-header
        title="Dashboard"
        subtitle="Ringkasan konten dan kegiatan {{ config('app.name') }}."
    />

    <div class="row g-3 mb-4" aria-label="Ringkasan konten">
        @foreach ($summaries as $summary)
            <div class="col-sm-6 col-xl-4">
                <x-admin.stat-card
                    :label="$summary['label']"
                    :total="$summary['total']"
                    :published="$summary['published']"
                    :draft="$summary['draft']"
                    :published-label="$summary['publishedLabel'] ?? 'Terbit'"
                    :draft-label="$summary['draftLabel'] ?? 'Draf'"
                />
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <x-admin.card title="Agenda mendatang">
                @if ($upcomingAgendas->isEmpty())
                    <x-admin.empty-state
                        title="Belum ada agenda mendatang"
                        message="Agenda berstatus terbit dengan waktu yang akan datang akan tampil di sini."
                    />
                @else
                    <x-admin.table>
                        <x-slot:caption class="visually-hidden">Lima agenda terbit terdekat</x-slot:caption>
                        <x-slot:head>
                            <tr>
                                <th scope="col">Agenda</th>
                                <th scope="col">Waktu</th>
                                <th scope="col">Lokasi</th>
                            </tr>
                        </x-slot:head>
                        @foreach ($upcomingAgendas as $agenda)
                            <tr>
                                <td class="fw-semibold">{{ $agenda->title }}</td>
                                <td class="text-nowrap"><x-admin.date-text :date="$agenda->start_at" time /></td>
                                <td>{{ $agenda->location ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </x-admin.table>
                @endif
            </x-admin.card>
        </div>

        <div class="col-xl-5">
            <x-admin.card title="Panduan singkat">
                <p class="mb-2">Gunakan menu di samping untuk mengelola informasi desa setelah modul tersedia.</p>
                <p class="admin-helper-text mb-0">Data yang sudah dihapus sementara tidak disertakan dari ringkasan.</p>
            </x-admin.card>
        </div>

        <div class="col-lg-6">
            <x-admin.card title="Berita terbaru">
                @if ($latestNews->isEmpty())
                    <x-admin.empty-state title="Belum ada berita" message="Berita terbaru akan tampil di sini." />
                @else
                    <ul class="admin-content-list list-unstyled mb-0">
                        @foreach ($latestNews as $news)
                            <li class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <p class="fw-semibold mb-1">{{ $news->title }}</p>
                                    <p class="admin-metadata mb-0"><x-admin.date-text :date="$news->created_at" /></p>
                                </div>
                                <x-admin.status-badge :status="$news->status" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        </div>

        <div class="col-lg-6">
            <x-admin.card title="Pengumuman terbaru">
                @if ($latestAnnouncements->isEmpty())
                    <x-admin.empty-state title="Belum ada pengumuman" message="Pengumuman terbaru akan tampil di sini." />
                @else
                    <ul class="admin-content-list list-unstyled mb-0">
                        @foreach ($latestAnnouncements as $announcement)
                            <li class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <p class="fw-semibold mb-1">{{ $announcement->title }}</p>
                                    <p class="admin-metadata mb-0"><x-admin.date-text :date="$announcement->created_at" /></p>
                                </div>
                                <x-admin.status-badge :status="$announcement->status" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        </div>
    </div>
@endsection

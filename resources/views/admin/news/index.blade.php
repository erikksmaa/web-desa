@extends('layouts.admin')

@section('title', 'Berita')

@section('content')
    <x-admin.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Berita'],
    ]" />

    <x-admin.page-header title="Berita" subtitle="Kelola berita yang diterbitkan pada website desa.">
        <x-slot:actions>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary" href="{{ route('admin.news-categories.index') }}">Kategori</a>
                <a class="btn btn-primary" href="{{ route('admin.news.create') }}">Tambah berita</a>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card>
        <div class="row g-3 mb-4">
            <div class="col-lg-5">
                <x-admin.search-form :action="route('admin.news.index')" placeholder="Cari judul, ringkasan, atau kategori…" />
            </div>
            <div class="col-lg-7">
                <x-admin.filter-form :action="route('admin.news.index')" :clear-url="route('admin.news.index')">
                    <div class="col-sm">
                        <label class="form-label" for="filter-status">Status</label>
                        <select class="form-select" id="filter-status" name="status">
                            <option value="">Semua status</option>
                            <option value="draft" @selected(request('status') === 'draft')>Draf</option>
                            <option value="published" @selected(request('status') === 'published')>Terbit</option>
                        </select>
                    </div>
                    <div class="col-sm">
                        <label class="form-label" for="filter-category">Kategori</label>
                        <select class="form-select" id="filter-category" name="category">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </x-admin.filter-form>
            </div>
        </div>

        @if ($newsItems->isEmpty())
            <x-admin.empty-state title="Berita tidak ditemukan" message="Belum ada berita atau tidak ada data yang cocok dengan pencarian dan filter." />
        @else
            <x-admin.table>
                <x-slot:caption>Daftar berita</x-slot:caption>
                <x-slot:head>
                    <tr>
                        <th scope="col">Thumbnail</th>
                        <th scope="col">Berita</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tanggal terbit</th>
                        <th scope="col" class="text-end">Dilihat</th>
                        <th scope="col" class="text-end">Tindakan</th>
                    </tr>
                </x-slot:head>
                @foreach ($newsItems as $news)
                    <tr>
                        <td>
                            @if ($news->thumbnail && Storage::disk('public')->exists($news->thumbnail))
                                <img class="admin-news-thumbnail" src="{{ \Illuminate\Support\Facades\Storage::url($news->thumbnail) }}" alt="">
                            @else
                                <span class="admin-news-thumbnail admin-news-thumbnail--empty" aria-label="Tanpa thumbnail">Berita</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $news->title }}</strong>
                            <div class="admin-metadata">{{ $news->category->name }}</div>
                        </td>
                        <td><x-admin.status-badge :status="$news->status" /></td>
                        <td><x-admin.date-text :date="$news->published_at" with-time /></td>
                        <td class="text-end">{{ number_format($news->views, 0, ',', '.') }}</td>
                        <td>
                            <x-admin.table-actions>
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.news.edit', $news) }}">Ubah</a>
                                <x-admin.confirm-button :action="route('admin.news.destroy', $news)" :item="$news->title" message="Berita akan dipindahkan ke penyimpanan sementara dan tidak tampil di website." />
                            </x-admin.table-actions>
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>
            <div class="mt-4">{{ $newsItems->links() }}</div>
        @endif
    </x-admin.card>
@endsection

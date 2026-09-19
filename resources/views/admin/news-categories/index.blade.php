@extends('layouts.admin')

@section('title', 'Kategori Berita')

@section('content')
    <x-admin.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Kategori Berita'],
    ]" />

    <x-admin.page-header title="Kategori Berita" subtitle="Atur pengelompokan berita yang tersedia di CMS.">
        <x-slot:actions>
            <a class="btn btn-primary" href="{{ route('admin.news-categories.create') }}">Tambah kategori</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card>
        <div class="mb-4">
            <x-admin.search-form :action="route('admin.news-categories.index')" placeholder="Cari nama kategori…" />
        </div>

        @if ($categories->isEmpty())
            <x-admin.empty-state title="Belum ada kategori" message="Tambahkan kategori pertama untuk mengelompokkan berita." />
        @else
            <x-admin.table>
                <x-slot:caption>Daftar kategori berita</x-slot:caption>
                <x-slot:head>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Slug</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Berita</th>
                        <th scope="col" class="text-end">Urutan</th>
                        <th scope="col" class="text-end">Tindakan</th>
                    </tr>
                </x-slot:head>
                @foreach ($categories as $category)
                    <tr>
                        <td>
                            <strong>{{ $category->name }}</strong>
                            @if ($category->description)<div class="admin-metadata">{{ \Illuminate\Support\Str::limit($category->description, 80) }}</div>@endif
                        </td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td><x-admin.status-badge :status="$category->is_active ? 'active' : 'inactive'" /></td>
                        <td class="text-end">{{ number_format($category->news_count, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($category->sort_order, 0, ',', '.') }}</td>
                        <td>
                            <x-admin.table-actions>
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.news-categories.edit', $category) }}">Ubah</a>
                                <x-admin.confirm-button :action="route('admin.news-categories.destroy', $category)" :item="$category->name" message="Kategori akan dihapus jika belum digunakan oleh berita." />
                            </x-admin.table-actions>
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>

            <div class="mt-4">{{ $categories->links() }}</div>
        @endif
    </x-admin.card>
@endsection

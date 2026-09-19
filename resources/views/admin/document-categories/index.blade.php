@extends('layouts.admin')

@section('title', 'Kategori Berkas')

@section('content')
    <x-admin.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Kategori Berkas'],
    ]" />

    <x-admin.page-header title="Kategori Berkas" subtitle="Atur pengelompokan berkas yang tersedia di CMS.">
        <x-slot:actions>
            <a class="btn btn-primary" href="{{ route('admin.document-categories.create') }}">Tambah kategori</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card>
        <div class="mb-4">
            <x-admin.search-form :action="route('admin.document-categories.index')" placeholder="Cari nama kategori…" />
        </div>

        @if ($categories->isEmpty())
            <x-admin.empty-state title="Belum ada kategori" message="Tambahkan kategori pertama untuk mengelompokkan berkas." />
        @else
            <x-admin.table>
                <x-slot:caption>Daftar kategori berkas</x-slot:caption>
                <x-slot:head>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Slug</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Berkas</th>
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
                        <td class="text-end">{{ number_format($category->documents_count, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($category->sort_order, 0, ',', '.') }}</td>
                        <td>
                            <x-admin.table-actions>
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.document-categories.edit', $category) }}">Ubah</a>
                                <x-admin.confirm-button :action="route('admin.document-categories.destroy', $category)" :item="$category->name" message="Kategori akan dihapus jika belum digunakan oleh berkas." />
                            </x-admin.table-actions>
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>

            <div class="mt-4">{{ $categories->links() }}</div>
        @endif
    </x-admin.card>
@endsection

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBannerRequest;
use App\Http\Requests\Admin\UpdateBannerRequest;
use App\Models\Banner;
use App\Support\FileUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class BannerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => 'nullable|string|max:255', 'active' => 'nullable|in:0,1']);
        $banners = Banner::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('title', 'like', '%'.$search.'%')->orWhere('subtitle', 'like', '%'.$search.'%')))
            ->when(isset($filters['active']), fn ($q) => $q->where('is_active', $filters['active']))
            ->orderBy('sort_order')->orderBy('id')->paginate(15)->withQueryString();

        return view('admin.banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('admin.banners.create', ['banner' => new Banner]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $this->persist($request, new Banner);

        return to_route('admin.banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $this->persist($request, $banner);

        return to_route('admin.banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $path = $banner->image_path;
        DB::transaction(fn () => $banner->delete());
        FileUploads::remove('public', [$path]);

        return to_route('admin.banners.index')->with('success', 'Banner berhasil dihapus.');
    }

    private function persist(StoreBannerRequest $request, Banner $banner): void
    {
        $data = $request->safe()->except('image');
        $newPath = null;
        $oldPath = $banner->image_path;
        try {
            if ($request->hasFile('image')) {
                $newPath = FileUploads::store($request->file('image'), 'public', 'banners', 'image');
                $data['image_path'] = $newPath;
            }
            DB::transaction(fn () => $banner->fill($data)->save());
        } catch (Throwable $exception) {
            FileUploads::remove('public', [$newPath]);
            throw $exception;
        }
        if ($newPath) {
            FileUploads::remove('public',[$oldPath]);
        }
    }
}

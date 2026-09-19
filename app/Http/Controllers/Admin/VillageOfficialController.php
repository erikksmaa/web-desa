<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVillageOfficialRequest;
use App\Http\Requests\Admin\UpdateVillageOfficialRequest;
use App\Models\VillageOfficial;
use App\Support\FileUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class VillageOfficialController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => 'nullable|string|max:255', 'active' => 'nullable|in:0,1']);
        $officials = VillageOfficial::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('position', 'like', '%'.$search.'%')))
            ->when(isset($filters['active']), fn ($q) => $q->where('is_active', $filters['active']))
            ->orderBy('sort_order')->orderBy('id')->paginate(15)->withQueryString();

        return view('admin.village-officials.index', compact('officials'));
    }

    public function create(): View
    {
        return view('admin.village-officials.create', ['official' => new VillageOfficial]);
    }

    public function store(StoreVillageOfficialRequest $request): RedirectResponse
    {
        $this->persist($request, new VillageOfficial);

        return to_route('admin.village-officials.index')->with('success', 'Perangkat desa berhasil ditambahkan.');
    }

    public function edit(VillageOfficial $villageOfficial): View
    {
        return view('admin.village-officials.edit', ['official' => $villageOfficial]);
    }

    public function update(UpdateVillageOfficialRequest $request, VillageOfficial $villageOfficial): RedirectResponse
    {
        $this->persist($request, $villageOfficial);

        return to_route('admin.village-officials.index')->with('success', 'Perangkat desa berhasil diperbarui.');
    }

    public function destroy(VillageOfficial $villageOfficial): RedirectResponse
    {
        Cache::lock('village-officials-write', 30)->block(5, function () use ($villageOfficial): void {
            $oldPath = DB::transaction(function () use ($villageOfficial) {
                $official = VillageOfficial::lockForUpdate()->findOrFail($villageOfficial->id);
                $path = $official->photo_path;
                $official->delete();

                return $path;
            });
            FileUploads::remove('public', [$oldPath]);
        });

        return to_route('admin.village-officials.index')->with('success', 'Perangkat desa dihapus. Jika kepala desa dihapus, tetapkan kepala desa yang baru.');
    }

    private function persist(StoreVillageOfficialRequest $request, VillageOfficial $official): void
    {
        $data = $request->safe()->except('photo');
        $data['is_village_head'] = $request->boolean('is_active') && $request->boolean('is_village_head');
        $newPath = null;
        $oldPath = null;
        try {
            if ($request->hasFile('photo')) {
                $newPath = FileUploads::store($request->file('photo'), 'public', 'officials', 'photo');
                $data['photo_path'] = $newPath;
            }
            Cache::lock('village-officials-write', 30)->block(5, function () use ($official, $data, &$oldPath): void {
                DB::transaction(function () use ($official, $data, &$oldPath): void {
                    $record = $official->exists ? VillageOfficial::lockForUpdate()->findOrFail($official->id) : $official;
                    $oldPath = $record->photo_path;
                    if ($data['is_village_head']) {
                        VillageOfficial::where('is_village_head', true)->when($record->exists, fn ($q) => $q->where('id', '!=', $record->id))->update(['is_village_head' => false]);
                    }
                    $record->fill($data)->save();
                });
            });
        } catch (Throwable $exception) {
            FileUploads::remove('public', [$newPath]);
            throw $exception;
        }
        if ($newPath) {
            FileUploads::remove('public', [$oldPath]);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateVillageProfileRequest;
use App\Models\Setting;
use App\Support\FileUploads;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class VillageProfileController extends Controller
{
    public function edit(SiteSettings $settings): View
    {
        return view('admin.village-profile.edit', compact('settings'));
    }

    public function update(UpdateVillageProfileRequest $request, SiteSettings $settings): RedirectResponse
    {
        $data = $request->validated();
        $newFiles = [];
        $oldFiles = [];
        try {
            DB::transaction(function () use ($request, $data, &$newFiles, &$oldFiles): void {
                foreach (SiteSettings::FIELDS as $field => [$key, $type, $group]) {
                    if (in_array($field, ['logo', 'sotk'], true)) {
                        if (! $request->hasFile($field)) {
                            continue;
                        }
                        $oldFiles[] = Setting::where('key', $key)->lockForUpdate()->value('value');
                        $value = FileUploads::store($request->file($field), 'public', 'settings/'.$field, $field);
                        $newFiles[] = $value;
                    } else {
                        $value = $data[$field] ?? null;
                    }
                    Setting::updateOrCreate(['key' => $key], compact('value', 'type', 'group'));
                }
            });
        } catch (Throwable $exception) {
            FileUploads::remove('public', $newFiles);
            throw $exception;
        }
        FileUploads::remove('public', $oldFiles);
        $settings->forget();

        return to_route('admin.village-profile.edit')->with('success', 'Informasi desa berhasil diperbarui.');
    }
}

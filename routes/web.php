<?php

use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentCategoryController;
use App\Http\Controllers\Admin\GalleryPhotoController;
use App\Http\Controllers\Admin\NewsCategoryController as AdminNewsCategoryController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\VillageOfficialController;
use App\Http\Controllers\Admin\VillageProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\AgendaController;
use App\Http\Controllers\Public\AnnouncementController;
use App\Http\Controllers\Public\DocumentController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\NewsController as PublicNewsController;
use App\Http\Controllers\Public\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('profil', [ProfileController::class, 'index'])->name('profile.index');
Route::get('profil/sotk', [ProfileController::class, 'organization'])->name('profile.organization');
Route::get('profil/perangkat-desa', [ProfileController::class, 'officials'])->name('profile.officials');
Route::get('pengumuman', [AnnouncementController::class, 'index'])->name('announcements.index');
Route::get('pengumuman/{slug}', [AnnouncementController::class, 'show'])->name('announcements.show');
Route::get('pengumuman/{slug}/lampiran', [AnnouncementController::class, 'download'])->name('announcements.download');

Route::get('agenda', [AgendaController::class, 'index'])->name('agendas.index');
Route::get('agenda/{slug}', [AgendaController::class, 'show'])->name('agendas.show');
Route::get('berkas', [DocumentController::class, 'index'])->name('documents.index');
Route::get('berkas/{document}/download', [DocumentController::class, 'download'])->whereNumber('document')->name('documents.download');
Route::get('galeri', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('galeri/{slug}', [GalleryController::class, 'show'])->name('gallery.show');

Route::get('/', HomeController::class)->name('home');
Route::get('berita', [PublicNewsController::class, 'index'])->name('news.index');
Route::get('berita/{slug}', [PublicNewsController::class, 'show'])->name('news.show');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.store');
    });

    Route::middleware('auth')->group(function (): void {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::resource('banners', BannerController::class)->except('show');
        Route::resource('village-officials', VillageOfficialController::class)->parameters(['village-officials' => 'villageOfficial'])->except('show');
        Route::get('village-profile', [VillageProfileController::class, 'edit'])->name('village-profile.edit');
        Route::patch('village-profile', [VillageProfileController::class, 'update'])->name('village-profile.update');
        Route::resource('news-categories', AdminNewsCategoryController::class)->except('show');
        Route::resource('announcements', App\Http\Controllers\Admin\AnnouncementController::class)->except('show');
        Route::resource('agendas', App\Http\Controllers\Admin\AgendaController::class)->except('show');
        Route::resource('documents', App\Http\Controllers\Admin\DocumentController::class)->except('show');
        Route::resource('document-categories', DocumentCategoryController::class)->except('show');
        Route::resource('gallery', App\Http\Controllers\Admin\GalleryController::class)->parameters(['gallery' => 'galleryAlbum'])->except('show');
        Route::post('gallery/{galleryAlbum}/photos', [GalleryPhotoController::class, 'store'])->name('gallery.photos.store');
        Route::patch('gallery/{galleryAlbum}/photos/{galleryPhoto}', [GalleryPhotoController::class, 'update'])->name('gallery.photos.update');
        Route::delete('gallery/{galleryAlbum}/photos/{galleryPhoto}', [GalleryPhotoController::class, 'destroy'])->name('gallery.photos.destroy');
        Route::resource('news', AdminNewsController::class)->except('show');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});

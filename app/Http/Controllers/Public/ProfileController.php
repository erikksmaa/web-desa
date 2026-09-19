<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\VillageOfficial;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        return view('public.profile.index');
    }

    public function organization(): View
    {
        return view('public.profile.organization');
    }

    public function officials(): View
    {
        $officials = VillageOfficial::active()->orderBy('sort_order')->orderBy('id')->paginate(12);

        return view('public.profile.officials', compact('officials'));
    }
}

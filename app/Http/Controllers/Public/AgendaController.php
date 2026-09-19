<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(): View
    {
        // An ongoing event stays in the upcoming/current section until it ends.
        $upcoming = Agenda::published()->whereRaw('COALESCE(end_at, start_at) >= ?', [now()])
            ->orderBy('start_at')->orderBy('id')->paginate(12, ['*'], 'upcoming_page')->withQueryString();
        $past = Agenda::published()->whereRaw('COALESCE(end_at, start_at) < ?', [now()])
            ->orderByDesc('start_at')->orderByDesc('id')->paginate(12, ['*'], 'past_page')->withQueryString();

        return view('public.agendas.index', compact('upcoming', 'past'));
    }

    public function show(string $slug): View
    {
        $agenda = Agenda::published()->where('slug', $slug)->firstOrFail();

        return view('public.agendas.show', compact('agenda'));
    }
}

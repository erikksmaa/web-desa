<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAgendaRequest;
use App\Http\Requests\Admin\UpdateAgendaRequest;
use App\Models\Agenda;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => 'nullable|string|max:255', 'status' => 'nullable|in:draft,published']);
        $agendas = Agenda::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('title', 'like', '%'.$search.'%')->orWhere('location', 'like', '%'.$search.'%')))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('start_at')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.agendas.index', compact('agendas'));
    }

    public function create(): View
    {
        return view('admin.agendas.create', ['agenda' => new Agenda]);
    }

    public function store(StoreAgendaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Agenda::create([...$data, 'user_id' => $request->user()->id, 'slug' => UniqueSlug::generate(Agenda::withTrashed(), $data['title'])]);

        return to_route('admin.agendas.index')->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function edit(Agenda $agenda): View
    {
        return view('admin.agendas.edit', compact('agenda'));
    }

    public function update(UpdateAgendaRequest $request, Agenda $agenda): RedirectResponse
    {
        $data = $request->validated();
        if ($data['title'] !== $agenda->title) {
            $data['slug'] = UniqueSlug::generate(Agenda::withTrashed(), $data['title'], $agenda->id);
        }
        $agenda->update($data);

        return to_route('admin.agendas.index')->with('success', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Agenda $agenda): RedirectResponse
    {
        $agenda->delete();

        return to_route('admin.agendas.index')->with('success','Agenda berhasil dihapus.');
    }
}

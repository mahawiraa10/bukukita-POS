<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\IdGen;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        return Event::when($request->status, fn($q, $s) => $q->where('status_acara', $s))
            ->paginate($request->input('per_page', 20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_acara' => 'nullable|string|max:20|unique:events,id_acara', // admin boleh override ID
            'nama_acara' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'required|string|max:150',
            'jenis_diskon' => 'nullable|in:PERSEN,NILAI',
            'nilai_diskon' => 'nullable|numeric|min:0',
            'status_acara' => 'nullable|in:AKTIF,NONAKTIF',
        ]);
        $data['id_acara'] = ($data['id_acara'] ?? null) ?: IdGen::next(Event::class, 'id_acara', 'EVT');
        return response()->json(Event::create($data), 201);
    }

    public function update(Request $request, string $id)
    {
        $e = Event::findOrFail($id);
        $e->update($request->validate([
            'nama_acara' => 'sometimes|string|max:100',
            'tanggal_mulai' => 'sometimes|date',
            'tanggal_selesai' => 'sometimes|date',
            'lokasi' => 'sometimes|string|max:150',
            'jenis_diskon' => 'nullable|in:PERSEN,NILAI',
            'nilai_diskon' => 'nullable|numeric|min:0',
            'status_acara' => 'sometimes|in:AKTIF,NONAKTIF',
        ]));
        return response()->json($e);
    }

    // Closing Event: freeze + hitung sisa + generate bon return (Fase 7)
    public function close(Request $request, string $id)
    {
        $e = Event::findOrFail($id);
        if ($e->status_acara === 'NONAKTIF') {
            return response()->json(['message' => 'Event sudah ditutup (frozen)'], 422);
        }

        $bon = app(\App\Services\EventClosingService::class)->close($e, (string) auth()->id());

        return response()->json(['message' => 'Event frozen', 'bon_return' => $bon]);
    }
}

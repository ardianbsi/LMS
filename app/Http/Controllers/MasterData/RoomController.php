<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\RoomRequest;
use App\Models\Room;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Room::class);

        $rooms = Room::query()
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where(fn ($w) => $w->where('code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"));
            })
            ->when($request->string('status')->toString(), function ($query, $status) {
                $query->where('is_active', $status === 'aktif');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master-data.rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        $this->authorize('create', Room::class);

        return view('master-data.rooms.create');
    }

    public function store(RoomRequest $request): RedirectResponse
    {
        $this->authorize('create', Room::class);

        Room::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('master-data.rooms.index')->with('status', 'Ruang berhasil ditambahkan.');
    }

    public function show(Room $room): View
    {
        $this->authorize('view', $room);

        return view('master-data.rooms.show', compact('room'));
    }

    public function edit(Room $room): View
    {
        $this->authorize('update', $room);

        return view('master-data.rooms.edit', compact('room'));
    }

    public function update(RoomRequest $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $room->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('master-data.rooms.index')->with('status', 'Ruang berhasil diperbarui.');
    }

    public function toggle(Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $room->update(['is_active' => ! $room->is_active]);

        return back()->with('status', $room->is_active ? 'Ruang diaktifkan.' : 'Ruang dinonaktifkan.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        if ($room->isReferenced()) {
            return back()->withErrors(['room' => 'Ruang tidak dapat dihapus karena masih direferensikan. Nonaktifkan saja.']);
        }

        $room->delete();

        return redirect()->route('master-data.rooms.index')->with('status', 'Ruang berhasil dihapus.');
    }
}

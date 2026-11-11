<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAlertController extends Controller
{
    public function index(Request $request): View
    {
        $query = Alert::with(['device', 'recommendation']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $alerts = $query->latest()->paginate(15)->withQueryString();
        $unreadCount = Alert::where('status', 'UNREAD')->count();

        return view('admin.alerts.index', compact('alerts', 'unreadCount'));
    }

    public function markAsRead(Alert $alert): RedirectResponse
    {
        $alert->update([
            'status' => 'READ',
            'read_at' => now(),
        ]);

        return back()->with('success', 'Peringatan ditandai sebagai sudah dibaca.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        Alert::where('status', 'UNREAD')->update([
            'status' => 'READ',
            'read_at' => now(),
        ]);

        return back()->with('success', 'Semua peringatan telah ditandai sebagai dibaca.');
    }

    public function destroy(Alert $alert): RedirectResponse
    {
        $alert->delete();

        return back()->with('success', 'Peringatan berhasil dihapus.');
    }
}


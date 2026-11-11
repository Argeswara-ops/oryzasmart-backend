<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisionScan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminVisionController extends Controller
{
    public function index(Request $request): View
    {
        $query = VisionScan::with('user');

        if ($request->filled('scan_type')) {
            $query->where('scan_type', $request->input('scan_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('class_name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%");
            });
        }

        $scans = $query->latest('scanned_at')->paginate(12)->withQueryString();

        return view('admin.vision.index', compact('scans'));
    }

    public function destroy(VisionScan $scan): RedirectResponse
    {
        if ($scan->image_path && Storage::disk('public')->exists($scan->image_path)) {
            Storage::disk('public')->delete($scan->image_path);
        }

        $scan->delete();

        return back()->with('success', 'Data scan diagnosis penyakit berhasil dihapus.');
    }
}


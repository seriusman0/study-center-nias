<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessJurnalPhotoScan;
use App\Models\JurnalPhotoScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JurnalPhotoScanApiController extends Controller
{
    /**
     * Display a paginated list of jurnal photo scans.
     */
    public function index(Request $request)
    {
        $scans = JurnalPhotoScan::with('creator')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($scans);
    }

    /**
     * Upload a jurnal photo scan for OCR processing.
     */
    public function store(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'file', 'image', 'max:10240'],
        ]);

        $file = $request->file('photo');
        $path = $file->store('jurnal-photo-scans', 'local');

        $scan = JurnalPhotoScan::create([
            'image_path'    => $path,
            'original_name' => $file->getClientOriginalName(),
            'status'        => 'pending',
            'created_by'    => auth()->id(),
        ]);

        ProcessJurnalPhotoScan::dispatch($scan->id);

        return response()->json([
            'message' => 'Foto jurnal berhasil diupload. Pemrosesan sedang berjalan.',
            'data'    => $scan,
        ], 201);
    }
}

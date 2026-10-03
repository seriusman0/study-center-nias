<?php

namespace App\Http\Controllers;

use App\Models\CollectedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectedEmailController extends Controller
{
    // ── Admin Web: Daftar ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = CollectedEmail::with(['user', 'invitedByUser'])->latest();

        // Filter: hanya yang belum/sudah diundang
        if ($request->filled('invited')) {
            if ($request->invited === '1') {
                $query->whereNotNull('invited_at');
            } elseif ($request->invited === '0') {
                $query->whereNull('invited_at');
            }
        }

        // Filter: search email / nama
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function ($w) use ($term) {
                $w->where('email', 'like', $term)
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', $term));
            });
        }

        $emails       = $query->paginate(30)->withQueryString();
        $totalInvited = CollectedEmail::whereNotNull('invited_at')->count();
        $totalPending = CollectedEmail::whereNull('invited_at')->count();

        return view('admin.collected-emails.index', compact('emails', 'totalInvited', 'totalPending'));
    }

    // ── Admin Web: Toggle invite (AJAX) ───────────────────────────
    public function toggleInvite(Request $request, CollectedEmail $collectedEmail)
    {
        $request->validate([
            'note' => 'nullable|string|max:255',
        ]);

        if ($collectedEmail->isInvited()) {
            // Batalkan undangan
            $collectedEmail->update([
                'invited_at'  => null,
                'invited_by'  => null,
                'invite_note' => null,
            ]);
            $status = 'revoked';
        } else {
            // Tandai sudah diundang
            $collectedEmail->update([
                'invited_at'  => now(),
                'invited_by'  => Auth::id(),
                'invite_note' => $request->note,
            ]);
            $status = 'invited';
        }

        return response()->json([
            'status'      => $status,
            'invited_at'  => $collectedEmail->fresh()->invited_at?->toIso8601String(),
            'is_invited'  => $collectedEmail->fresh()->isInvited(),
            'invited_by'  => $collectedEmail->fresh()->invitedByUser?->name,
        ]);
    }

    // ── API (user Android): Simpan email ─────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $collected = CollectedEmail::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'email' => $request->email,
                'phone' => $request->phone ?? null,
            ]
        );

        return response()->json([
            'ok'         => true,
            'message'    => 'Email berhasil disimpan.',
            'is_invited' => $collected->isInvited(),
            'invited_at' => $collected->invited_at?->toIso8601String(),
        ]);
    }

    // ── API (user Android): Cek status email & invite ────────────
    public function checkStatus(Request $request)
    {
        $user      = $request->user();
        $collected = CollectedEmail::where('user_id', $user->id)->first();

        if (!$collected) {
            return response()->json([
                'has_submitted' => false,
                'email'         => null,
                'is_invited'    => false,
                'invited_at'    => null,
                'invite_note'   => null,
            ]);
        }

        return response()->json([
            'has_submitted' => true,
            'email'         => $collected->email,
            'phone'         => $collected->phone,
            'is_invited'    => $collected->isInvited(),
            'invited_at'    => $collected->invited_at?->toIso8601String(),
            'invite_note'   => $collected->invite_note,
            'submitted_at'  => $collected->created_at->toIso8601String(),
        ]);
    }
}

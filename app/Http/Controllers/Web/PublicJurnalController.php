<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JurnalEntry;
use App\Models\JurnalLifeCheck;
use App\Models\JurnalLifeItem;
use App\Models\User;
use App\Support\JurnalWeek;
use Illuminate\Http\Request;

class PublicJurnalController extends Controller
{
            public function scan(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);

        $user = User::where('id', $request->user_id)->where('is_active', true)->first();

        if (!$user) {
            return response()->json(['status' => 'not_found', 'message' => 'Pengguna tidak ditemukan atau tidak aktif.']);
        }
        
        if ($user->isAdmin() || $user->hasRole('mentor')) {
            return response()->json(['status' => 'not_found', 'message' => 'Admin/Mentor tidak bisa login via QR.']);
        }

        \Illuminate\Support\Facades\Auth::login($user);

        $url = route('beranda');
        if ($user->hasRole('prajurit')) {
            $url = route('prajurit-jurnal.index');
        } elseif ($user->hasRole('student')) {
            $url = route('jurnal.index');
        } elseif ($user->hasRole('college')) {
            $url = route('college-jurnal.index');
        } elseif ($user->hasRole('scholarship_teenager')) {
            $url = route('scholarship-teenager-jurnal.index');
        }

        return response()->json([
            'status' => 'redirect',
            'url' => $url
        ]);
    }

    
}
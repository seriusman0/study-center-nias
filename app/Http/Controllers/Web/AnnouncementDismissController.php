<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnnouncementDismissController extends Controller
{
    public function dismiss(Request $request, int $id)
    {
        $dismissed = $request->session()->get('dismissed_announcements', []);
        if (!in_array($id, $dismissed)) {
            $dismissed[] = $id;
            $request->session()->put('dismissed_announcements', $dismissed);
        }
        return response()->json(['ok' => true]);
    }
}

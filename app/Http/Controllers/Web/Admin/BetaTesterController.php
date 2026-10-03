<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\BetaTester;

class BetaTesterController extends Controller
{
    public function index()
    {
        $testers = BetaTester::latest()->paginate(20);
        return view('admin.beta-testers.index', compact('testers'));
    }

    public function update(Request $request, BetaTester $tester)
    {
        $request->validate([
            'status' => 'required|in:pending,added'
        ]);

        $tester->update(['status' => $request->status]);

        return back()->with('success', 'Status berhasil diperbarui.');
    }
}

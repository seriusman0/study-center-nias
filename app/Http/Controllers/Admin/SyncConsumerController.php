<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebSync;
use Illuminate\Http\RedirectResponse;

class SyncConsumerController extends Controller
{
    public function pull(): RedirectResponse
    {
        if (! config('sync.target_url')) {
            return back()->with('error', 'SYNC_TARGET_URL not configured.');
        }

        ProcessWebSync::dispatch();

        return back()->with('success', 'Sync process has been queued and is running in the background.');
    }
}

<?php

namespace App\Http\Controllers\Web\College;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Traits\HasJurnalDailyActions;

class CollegeJurnalController extends Controller
{
    use HasJurnalDailyActions;

    protected string $role        = 'college';
    protected string $routePrefix = 'college-jurnal';
    protected string $viewName    = 'college.jurnal';
    protected array  $kategori    = ['pembacaan', 'sidang', 'rohani'];
    protected bool   $showVerse   = false;
}

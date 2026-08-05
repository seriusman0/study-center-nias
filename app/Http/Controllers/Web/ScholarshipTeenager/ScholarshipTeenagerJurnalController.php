<?php

namespace App\Http\Controllers\Web\ScholarshipTeenager;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Traits\HasJurnalDailyActions;

class ScholarshipTeenagerJurnalController extends Controller
{
    use HasJurnalDailyActions;

    protected string $role        = 'scholarship_teenager';
    protected string $routePrefix = 'scholarship-teenager-jurnal';
    protected string $viewName    = 'scholarship-teenager.jurnal';
    protected array  $kategori    = ['pembacaan', 'sidang', 'rohani'];
}

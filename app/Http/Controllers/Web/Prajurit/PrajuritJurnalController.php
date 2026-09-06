<?php
namespace App\Http\Controllers\Web\Prajurit;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Traits\HasJurnalDailyActions;

class PrajuritJurnalController extends Controller
{
    use HasJurnalDailyActions;

    protected string $role        = 'prajurit';
    protected string $routePrefix = 'prajurit-jurnal';
    protected string $viewName    = 'prajurit.jurnal';
    protected array  $kategori    = ['prajurit'];
    protected bool   $showVerse   = false;
}

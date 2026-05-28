<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    public function index()
    {
        $logs = Activity::query()
            ->where('tenant_id', session('tenant_id'))
            ->with('causer')
            ->orderByDesc('created_at')
            ->paginate(50);

        return Inertia::render('logs/Index', [
            'logs' => $logs,
        ]);
    }
}
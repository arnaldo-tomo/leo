<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\AuthorizedPerson;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Dados para o dashboard
        $stats = [
            'total_authorized' => AuthorizedPerson::count(),
            'active_authorized' => AuthorizedPerson::where('active', true)->count(),
            'access_today' => AccessLog::whereDate('access_time', today())->count(),
            'unauthorized_attempts' => AccessLog::where('status', 'unauthorized')->count(),
            'authorized_attempts' => AccessLog::where('status', 'authorized')->count(),
            'unknown_attempts' => AccessLog::where('status', 'unknown')->count(),
        ];

        // Estatísticas dos últimos 7 dias
        $date = now()->subDays(7);
        $dailyStats = AccessLog::where('access_time', '>=', $date)
            ->selectRaw('DATE(access_time) as date, status, count(*) as count')
            ->groupBy('date', 'status')
            ->get()
            ->groupBy('date');

        // Logs recentes
        $recentLogs = AccessLog::with('authorizedPerson')
            ->orderBy('access_time', 'desc')
            ->limit(3)
            ->get();

        return view('dashboard', compact('stats', 'dailyStats', 'recentLogs'));
    }
}
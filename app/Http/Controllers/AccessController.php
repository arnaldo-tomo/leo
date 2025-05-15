<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\AuthorizedPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccessController extends Controller
{
    public function monitor()
    {
        return view('access.monitor');
    }

    public function logs()
    {
        $logs = AccessLog::with('authorizedPerson')
            ->orderBy('access_time', 'desc')
            ->paginate(10);
        return view('access.logs', compact('logs'));
    }

    public function recordAccess(Request $request)
    {
        $validated = $request->validate([
            'authorized_person_id' => 'nullable|exists:authorized_persons,id',
            'person_name' => 'nullable|string',
            'status' => 'required|in:authorized,unauthorized,unknown',
            'photo_data' => 'required|string',
        ]);

        // Decodificar e salvar a imagem base64
        $image = $request->photo_data;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = time() . '_access.png';
        Storage::disk('public')->put('access_logs/' . $imageName, base64_decode($image));

        AccessLog::create([
            'authorized_person_id' => $validated['authorized_person_id'],
            'person_name' => $validated['person_name'],
            'status' => $validated['status'],
            'photo_path' => 'access_logs/' . $imageName,
            'access_time' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function getRecentLogs()
    {
        $logs = AccessLog::with('authorizedPerson')
            ->orderBy('access_time', 'desc')
            ->limit(3)
            ->get();

        return response()->json($logs);
    }

    public function dashboard()
    {
        $totalAuthorized = AuthorizedPerson::count();
        $totalActive = AuthorizedPerson::where('active', true)->count();

        $recentLogs = AccessLog::with('authorizedPerson')
            ->orderBy('access_time', 'desc')
            ->limit(5)
            ->get();

        // Estatísticas dos últimos 7 dias
        $date = now()->subDays(7);
        $accessStats = AccessLog::where('created_at', '>=', $date)
            ->selectRaw('DATE(access_time) as date, status, count(*) as count')
            ->groupBy('date', 'status')
            ->get();

        return view('dashboard', compact('totalAuthorized', 'totalActive', 'recentLogs', 'accessStats'));
    }
}
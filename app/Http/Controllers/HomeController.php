<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        $roleName = $user->role->name;

        // Base Query
        $query = \App\Models\SuratTugas::query();

        // Role-based Filtering
        if (in_array($roleName, ['Superadmin', 'Rendal'])) {
            // No filter, see all
        } elseif (in_array($roleName, ['Admin Perwakilan', 'Korwas'])) {
            // Filter by Perwakilan
            $query->where('perwakilan_id', $user->perwakilan_id);
        } else {
            // Filter by Personal Assignment (Ketua Tim, Anggota, Dalnis)
            $query->whereHas('personel', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Statistics
        $totalST = (clone $query)->count();
        $activeST = (clone $query)->where('status', 'On Progress')->count(); // Or based on date logic
        $completedST = (clone $query)->where('status', 'Selesai')->count();
        
        // Count 'Perlu Perbaikan' from Review Notes? Or just use a status
        // For simplicity, let's assume 'Baru' is waiting to start
        $newST = (clone $query)->where('status', 'Baru')->count();

        // Recent Activity (Surat Tugas recently updated)
        $recentActivities = (clone $query)->with(['perwakilan', 'jenisPenugasan'])
                                        ->latest('updated_at')
                                        ->take(5)
                                        ->get();

        return view('home', compact('totalST', 'activeST', 'completedST', 'newST', 'recentActivities'));
    }
}

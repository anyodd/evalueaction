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
        $roleName = $user->role->name ?? '';

        // Base Query Metrics
        $queryST = \App\Models\SuratTugas::query();
        if (in_array($roleName, ['Superadmin', 'Rendal'])) {
            // No filter, see all
        } elseif (in_array($roleName, ['Admin Perwakilan', 'Korwas'])) {
            $queryST->where('perwakilan_id', $user->perwakilan_id);
        } else {
            // Filter by Personal Assignment (Ketua Tim, Anggota, Dalnis)
            $queryST->whereHas('personel', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Statistics
        $totalST = (clone $queryST)->count();
        $activeST = (clone $queryST)->where('status', 'On Progress')->count();
        $completedST = (clone $queryST)->where('status', 'Selesai')->count();
        $newST = (clone $queryST)->where('status', 'Baru')->count();
        
        // --- DASHBOARD MONITORING DATA ---
        $queryProvinsi = \App\Models\Perwakilan::query();
        
        // Filter parent Perwakilan based on roles
        if (!in_array($roleName, ['Superadmin', 'Rendal'])) {
            // Restrict to their own perwakilan
            $queryProvinsi->where('id', $user->perwakilan_id);
        }

        // Eager load Surat Tugas with constraints for regular users
        $queryProvinsi->with(['suratTugas' => function($q) use ($user, $roleName) {
            $q->with(['programKerja.langkah', 'kertasKerja', 'personel.user', 'jenisPenugasan'])
              ->orderBy('tgl_selesai', 'asc');
              
            // If they are regular team members, filter their STs
            if (!in_array($roleName, ['Superadmin', 'Rendal', 'Admin Perwakilan', 'Korwas'])) {
                 $q->whereHas('personel', function($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                 });
            }
        }]);

        $penugasanPerProvinsi = $queryProvinsi->get()->filter(function ($prov) {
             return $prov->suratTugas->count() > 0;
        });

        // Hitung Fase (Logic Rollup)
        foreach ($penugasanPerProvinsi as $prov) {
            foreach ($prov->suratTugas as $st) {
                $fase = '1. Persiapan';
                $badge = 'secondary';
                $progressPka = 0;
                $statusKk = 'N/A';
                $statusLaporan = 'Belum Ada LHE';
                
                // Base Gantt Task: Surat Tugas timeline
                $ganttTasks = [[
                    'id' => 'ST-' . $st->id,
                    'name' => 'ST: ' . substr($st->nama_objek, 0, 30) . (strlen($st->nama_objek) > 30 ? '...' : ''),
                    'start' => $st->tgl_mulai ? $st->tgl_mulai->format('Y-m-d') : date('Y-m-d'),
                    'end' => $st->tgl_selesai ? $st->tgl_selesai->format('Y-m-d') : date('Y-m-d', strtotime('+1 day')),
                    'progress' => 0,
                    'custom_class' => 'bar-st'
                ]];

                $pk = $st->programKerja->first();
                if ($pk) {
                    $fase = '2. Program Kerja';
                    $badge = 'info';
                    if (method_exists($pk, 'calculateProgress')) {
                        $progressPka = $pk->calculateProgress();
                    }
                    
                    if ($progressPka == 100) {
                        $fase = '3. Pelaksanaan (KK)';
                        $badge = 'warning';
                    }

                    // Add PKA Steps to Gantt
                    if ($pk->langkah) {
                        foreach ($pk->langkah as $langkah) {
                            $ganttTasks[] = [
                                'id' => 'L-' . $langkah->id,
                                'name' => $langkah->judul,
                                'start' => $langkah->tgl_mulai ? $langkah->tgl_mulai->format('Y-m-d') : ($st->tgl_mulai ? $st->tgl_mulai->format('Y-m-d') : date('Y-m-d')),
                                'end' => $langkah->tgl_selesai ? $langkah->tgl_selesai->format('Y-m-d') : ($st->tgl_selesai ? $st->tgl_selesai->format('Y-m-d') : date('Y-m-d', strtotime('+1 day'))),
                                'progress' => $langkah->status == 'completed' ? 100 : ($langkah->status == 'in_progress' ? 50 : 0),
                                'dependencies' => 'ST-' . $st->id,
                                'custom_class' => 'bar-pk'
                            ];
                        }
                    }
                }
                
                // Update parent ST progress based on average PKA completion
                $ganttTasks[0]['progress'] = floatval($progressPka);

                $kkList = $st->kertasKerja;
                if ($kkList->count() > 0) {
                    $fase = '3. Pelaksanaan (KK)';
                    $badge = 'warning';
                    
                    $allFinal = $kkList->every(fn($k) => $k->status_approval == 'Final');
                    if ($allFinal) {
                        $statusKk = 'Final (Menunggu LHE)';
                        $badge = 'primary';
                    } else {
                        $statusKk = 'Sedang Direviu';
                    }
                    
                    $hasLaporan = $kkList->filter(fn($k) => !empty($k->file_laporan))->count() > 0;
                    if ($hasLaporan || $st->status == 'Selesai') {
                        $fase = '4. Laporan Selesai';
                        $badge = 'success';
                        $statusLaporan = 'LHE Tersedia';
                        $statusKk = 'Final (Lengkap)';
                        $ganttTasks[0]['progress'] = 100; // Force 100% if done
                    }
                }
                
                $st->fase_sekarang = $fase;
                $st->badge_fase = $badge;
                $st->progress_pka = floatval($progressPka);
                $st->status_kk = $statusKk;
                $st->status_laporan = $statusLaporan;
                $st->gantt_tasks = json_encode($ganttTasks);
            }
        }

        return view('home', compact('totalST', 'activeST', 'completedST', 'newST', 'penugasanPerProvinsi'));
    }
}

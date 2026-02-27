<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\KertasKerja;

class LaporanController extends Controller
{
    public function index()
    {
        // Fetch only Finalized Kertas Kerja
        // Eager load related Surat Tugas and Perwakilan
        // Fetch only Finalized Kertas Kerja
        // Eager load related Surat Tugas and Perwakilan
        $user = auth()->user();
        $query = KertasKerja::where('status_approval', 'Final')
            ->with(['suratTugas.perwakilan', 'user']);

        // Filter Logic:
        // 1. Superadmin/Rendal: Show All
        // 2. Admin Perwakilan: Show All within their Perwakilan (via Surat Tugas)
        // 3. Team Members (Korwas/Dalnis/Ketua/Anggota): Show only if they are in the Surat Tugas Personel
        
        if ($user->hasRole('Superadmin') || $user->hasRole('Rendal')) {
            // Show All
        } elseif ($user->hasRole('Admin Perwakilan')) {
            // Filter by Perwakilan ID of the Admin
            if ($user->perwakilan_id) {
                $query->whereHas('suratTugas', function($q) use ($user) {
                    $q->where('perwakilan_id', $user->perwakilan_id);
                });
            }
        } else {
            // Team Member Filter
            $query->whereHas('suratTugas.personel', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $reports = $query->latest()->get();

        return view('laporan.index', compact('reports'));
    }

    public function uploadLaporan(Request $request, $id)
    {
        $request->validate([
            'file_laporan' => 'required|file|mimes:pdf,doc,docx|max:10240', // Max 10MB
        ]);

        $kk = KertasKerja::findOrFail($id);
        
        // Authorization check
        // Ensure user can upload (Team Member or Admin Perwakilan of that unit)
        $user = auth()->user();
        if (!$user->hasRole('Superadmin') && !$user->hasRole('Rendal') && !$user->hasRole('Admin Perwakilan')) {
             // Check if team member
             $isMember = \App\Models\StPersonel::where('st_id', $kk->st_id)->where('user_id', $user->id)->exists();
             if (!$isMember) abort(403);
        }

        if ($request->hasFile('file_laporan')) {
            $file = $request->file('file_laporan');
            $path = $file->store('laporan_akhir', 'public'); // Store in public disk
            
            $kk->update(['file_laporan' => $path]);
            
            return redirect()->back()->with('success', 'Laporan berhasil diupload.');
        }

        return redirect()->back()->with('error', 'Gagal mengupload file.');
    }

    public function printKertasKerja($id)
    {
        $kertasKerja = KertasKerja::findOrFail($id);
        
        // Ensure QA is at least Draft/Final (or at least viewable)
        $user = auth()->user();
        $isRendal = $user->hasRole('Rendal') || $user->hasRole('Admin Perwakilan') || $user->hasRole('Superadmin');
        $isMemberOfTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)->where('user_id', $user->id)->exists();

        if (!$isRendal && !$isMemberOfTeam) {
            abort(403);
        }

        $kertasKerja->load('template.indicators', 'answers.details');

        $indicators = \App\Models\TemplateIndicator::where('template_id', $kertasKerja->template_id)
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('id')->with(['criteria', 'children' => function($q2) {
                    $q2->orderBy('id')->with('criteria');
                }]);
            }])
            ->orderBy('id')
            ->get();

        return view('kertas-kerja.print', compact('kertasKerja', 'indicators'));
    }

    public function deleteLaporan($id)
    {
        $kk = \App\Models\KertasKerja::findOrFail($id);
        
        // Authorization: Only Superadmin, Rendal, Admin Perwakilan (Owner)
        $user = auth()->user();
        if (!$user->hasRole('Rendal') && !$user->hasRole('Superadmin') && !$user->hasRole('Admin Perwakilan')) {
            abort(403, 'Akses ditolak. Hanya Rendal/Admin Perwakilan yang dapat menghapus laporan.');
        }

        if ($kk->file_laporan) {
            // Delete file from storage
            if (\Storage::disk('public')->exists($kk->file_laporan)) {
                \Storage::disk('public')->delete($kk->file_laporan);
            }
            
            // Nullify database column
            $kk->update(['file_laporan' => null]);
            
            return redirect()->back()->with('success', 'File laporan berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Tidak ada file laporan untuk dihapus.');
    }
}

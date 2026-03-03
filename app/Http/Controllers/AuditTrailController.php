<?php

namespace App\Http\Controllers;

use App\Models\KertasKerjaAudit;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (!auth()->user()->hasRole('Superadmin') && !auth()->user()->hasRole('Rendal')) {
            abort(403);
        }

        $audits = KertasKerjaAudit::with(['kertasKerja.suratTugas', 'user'])
            ->latest()
            ->paginate(20);

        return view('audit-trail.index', compact('audits'));
    }
}

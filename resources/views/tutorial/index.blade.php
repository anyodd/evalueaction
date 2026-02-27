@extends('adminlte::page')

@section('title', 'Tutorial & Panduan Aplikasi')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-book-reader text-primary mr-2"></i> Tutorial & Panduan Aplikasi
            </h1>
        </div>
        <div class="col-sm-6 text-right">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Tutorial</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Overview Card -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 bg-gradient-light" style="border-radius: 15px;">
                    <div class="card-body p-4 text-center">
                        <h2 class="font-weight-bold mb-3">Selamat Datang di Panduan <span class="text-primary">e-Value-ActioN</span></h2>
                        <p class="lead text-muted mb-0" style="max-width: 800px; margin: 0 auto;">
                            Aplikasi ini dirancang untuk memudahkan proses Monitoring, Evaluasi, dan Quality Assurance (QA) atas penugasan dari hulu ke hilir. 
                            Silakan pelajari alur kerja di bawah ini untuk memahami siklus lengkap aplikasi.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflow Diagram Card -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-project-diagram mr-2 text-info"></i> Alur Kerja Utama (Workflow)
                        </h3>
                    </div>
                    <div class="card-body text-center overflow-auto">
                        <!-- Mermaid JS Definition -->
                        <pre class="mermaid mx-auto text-left" style="min-width: 600px; background: transparent; border: none; padding: 0;">
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffffff', 'primaryTextColor': '#333333', 'primaryBorderColor': '#17a2b8', 'lineColor': '#6c757d', 'secondaryColor': '#f4f6f9', 'tertiaryColor': '#e9ecef'}}}%%
flowchart LR
    classDef startEnd fill:#28a745,stroke:#218838,stroke-width:2px,color:#fff,rx:10px,ry:10px;
    classDef process fill:#fff,stroke:#17a2b8,stroke-width:2px,color:#333,rx:5px,ry:5px;
    classDef database fill:#f8f9fa,stroke:#6c757d,stroke-width:2px,color:#333;
    classDef highlight fill:#007bff,stroke:#0056b3,stroke-width:2px,color:#fff,rx:5px,ry:5px;
    classDef qa fill:#ffc107,stroke:#e0a800,stroke-width:2px,color:#333,rx:5px,ry:5px;
    
    %% Nodes
    Mulai([Mulai Penugasan]):::startEnd
    
    subgraph Fase Perencanaan
        ST(Surat Tugas <br> Admin Perwakilan):::process
        PK(Program Kerja / PKA <br> Ketua Tim):::process
        PKA_Approve{Disetujui <br> Pengendali<br>Teknis?}:::process
    end
    
    subgraph Fase Pelaksanaan
        KK(Kertas Kerja <br> Anggota Tim):::process
        KK_Review{Review <br> Ketua Tim &<br> Dalnis?}:::process
    end
    
    subgraph Fase QA & Pelaporan
        QA(Quality Assurance <br> Rendal / Admin):::qa
        Laporan(Upload Laporan <br> PDF/Dokumen):::process
    end
    
    Selesai([Selesai]):::startEnd
    
    %% Edges
    Mulai --> ST
    ST -->|Input ST & Objek| PK
    PK -->|Susun Langkah| PKA_Approve
    
    PKA_Approve -->|Revisi| PK
    PKA_Approve -->|Setuju| KK
    
    KK -->|Pelaksanaan/Upload Bukti| KK_Review
    KK_Review -->|Revisi/Tanggapan| KK
    KK_Review -->|Approval Final| QA
    
    QA -->|Review QA - Bersama Tim Perwakilan| QA
    QA -->|QA Selesai & Laporan Terbit| Laporan
    Laporan -->|Administrasi Laporan| Selesai
    
    %% Click events
    click ST href "{{ route('surat-tugas.index') }}" "Buka Menu Surat Tugas"
    click PK href "{{ route('program-kerja.index') }}" "Buka Menu Program Kerja"
    click KK href "{{ route('kertas-kerja.index') }}" "Buka Menu Kertas Kerja"
    click Laporan href "{{ route('laporan.index') }}" "Buka Menu Laporan"
                        </pre>
                        <div class="mt-4 text-muted small">
                            <i class="fas fa-info-circle mr-1"></i> Kotak diagram di atas dapat di-klik untuk langsung menuju modul yang bersangkutan (Surat Tugas, Program Kerja, dll).
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detailed Explanation Section -->
        <div class="row mt-4 mb-5">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-list-ol text-success mr-2"></i> Penjelasan Detail Modul
                        </h3>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-white shadow-sm" style="border-top: 3px solid #007bff; border-radius: 8px;">
                    <span class="info-box-icon text-primary"><i class="fas fa-file-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text font-weight-bold">1. Surat Tugas</span>
                        <span class="info-box-number font-weight-normal text-muted" style="font-size: 0.9em; white-space: normal;">
                            Admin Perwakilan menginput data dasar Surat Tugas termasuk Objek dan Tim yang bertugas (Ketua Tim, Anggota, dll).
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-white shadow-sm" style="border-top: 3px solid #17a2b8; border-radius: 8px;">
                    <span class="info-box-icon text-info"><i class="fas fa-tasks"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text font-weight-bold">2. Program Kerja</span>
                        <span class="info-box-number font-weight-normal text-muted" style="font-size: 0.9em; white-space: normal;">
                            Ketua Tim (KT) menyusun langkah-langkah kerja dan membagikannya ke Anggota Tim. Memerlukan persetujuan dari Pengendali Teknis (Dalnis).
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-white shadow-sm" style="border-top: 3px solid #28a745; border-radius: 8px;">
                    <span class="info-box-icon text-success"><i class="fas fa-briefcase"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text font-weight-bold">3. Kertas Kerja</span>
                        <span class="info-box-number font-weight-normal text-muted" style="font-size: 0.9em; white-space: normal;">
                            Anggota melaksanakan langkah kerja, mengunggah bukti, lalu di-review secara berjenjang oleh KT, Dalnis, dan Korwas.
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-white shadow-sm" style="border-top: 3px solid #ffc107; border-radius: 8px;">
                    <span class="info-box-icon text-warning"><i class="fas fa-chart-line"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text font-weight-bold">4. QA & Laporan</span>
                        <span class="info-box-number font-weight-normal text-muted" style="font-size: 0.9em; white-space: normal;">
                            Tim Rendal melakukan proses Quality Assurance (QA). Setelah QA selesai, laporan diterbitkan oleh Perwakilan dan diunggah ke aplikasi untuk dikompilasi.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .info-box {
            min-height: 180px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .info-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 0;
        }
        .mermaid {
            cursor: pointer;
            width: 100%;
            display: flex;
            justify-content: center;
            overflow: visible;
        }
        .mermaid svg {
            /* Memaksa SVG agar merentang semaksimal mungkin */
            width: 100% !important;
            max-width: 1200px !important;
            height: auto !important;
            min-height: 450px !important;
        }
    </style>
@stop

@section('js')
    <!-- Load Mermaid.js from CDN -->
    <script type="module">
        import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
        
        mermaid.initialize({ 
            startOnLoad: true,
            securityLevel: 'loose', // Allow clicking
            theme: 'base',
            themeVariables: {
                fontFamily: 'Nunito, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'
            }
        });
    </script>
@stop

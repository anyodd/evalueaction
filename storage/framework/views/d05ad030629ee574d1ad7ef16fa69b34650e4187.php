<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content_header'); ?>
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">Dashboard Overview</h1>
            </div>
            <div class="col-sm-6 text-right">
                <span class="text-muted">Selamat datang kembali, <strong><?php echo e(Auth::user()->name); ?></strong></span>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <!-- Dashboard Greeting -->
        <div class="row">
            <div class="col-md-12">
                <div class="card bg-gradient-navy shadow-lg" style="border-radius: 20px; overflow: hidden;">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="display-4 font-weight-bold"><span class="brand-e">e</span>-<span class="brand-value" style="color: #FFD700;">Value</span>-<span class="brand-a">A</span><span class="brand-ctio">ctio</span><span class="brand-n">N</span></h2>
                                <p class="lead mb-4">Sistem Monitoring dan Evaluasi Penugasan Terintegrasi.</p>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-light px-3 py-2 mr-2 shadow-sm rounded-pill" style="font-size: 0.95em;">
                                        <i class="fas fa-calendar-day text-primary mr-2"></i> <?php echo e(\Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y')); ?>

                                    </span>
                                    <span class="badge px-3 py-2 text-white shadow-sm rounded-pill" style="font-size: 0.95em; background-color: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);">
                                        <i class="fas fa-user-shield mr-2"></i> <?php echo e(Auth::user()->role->name ?? 'Pengguna'); ?>

                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 text-center d-none d-md-block">
                                <!-- Dynamic Quote Container -->
                                <div class="bg-white rounded-lg p-3 shadow-sm ml-auto" style="max-width: 320px; border-left: 4px solid #17a2b8; opacity: 0.95;">
                                    <div class="font-italic text-dark mb-2" id="quote-text" style="font-size: 0.95em; line-height: 1.4;">
                                        "Memuat kutipan hari ini..."
                                    </div>
                                    <div class="text-muted font-weight-bold" id="quote-author" style="font-size: 0.8em; text-transform: uppercase; letter-spacing: 1px;">
                                        - Administrator
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monitoring Penugasan Dashboard -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 animate__animated animate__fadeInUp" style="animation-delay: 0.5s">
                    <div class="card-header border-0 bg-white">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-tasks mr-2 text-primary"></i> Monitoring Progress Penugasan 
                            <?php if(in_array(Auth::user()->role->name ?? '', ['Superadmin', 'Rendal'])): ?>
                                (Nasional)
                            <?php else: ?>
                                (<?php echo e(Auth::user()->perwakilan->nama_perwakilan ?? ''); ?>)
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion" id="accordionMonitoring">
                            <?php $__empty_1 = true; $__currentLoopData = $penugasanPerProvinsi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="card mb-0 shadow-none border-bottom">
                                    <div class="card-header bg-light" id="heading-<?php echo e($prov->id); ?>">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-left text-dark font-weight-bold text-decoration-none" type="button" data-toggle="collapse" data-target="#collapse-<?php echo e($prov->id); ?>">
                                                <i class="fas fa-map-marker-alt text-danger mr-2"></i> <?php echo e($prov->nama_perwakilan); ?>

                                                <span class="badge badge-primary float-right"><?php echo e($prov->suratTugas->count()); ?> Penugasan</span>
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapse-<?php echo e($prov->id); ?>" class="collapse <?php echo e($loop->first ? 'show' : ''); ?>" data-parent="#accordionMonitoring">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover table-stack m-0">
                                                    <thead class="bg-white">
                                                        <tr class="text-xs text-uppercase text-muted">
                                                            <th>ST & Objek</th>
                                                            <th>Fase Dominan</th>
                                                            <th width="15%">Progress PKA</th>
                                                            <th>Status KK</th>
                                                            <th>Laporan (QA)</th>
                                                            <th width="10%">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $__currentLoopData = $prov->suratTugas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td data-label="ST & Objek">
                                                                    <strong><?php echo e($st->nomor_st); ?></strong><br>
                                                                    <span class="text-muted small"><?php echo e($st->nama_objek); ?></span>
                                                                </td>
                                                                <td data-label="Fase Dominan"><span class="badge badge-<?php echo e($st->badge_fase); ?> px-2 py-1"><?php echo e($st->fase_sekarang); ?></span></td>
                                                                <td data-label="Progress PKA" class="align-middle">
                                                                    <div class="progress progress-sm mb-1" style="height: 6px;">
                                                                        <div class="progress-bar bg-<?php echo e($st->progress_pka == 100 ? 'success' : 'info'); ?>" style="width: <?php echo e($st->progress_pka); ?>%"></div>
                                                                    </div>
                                                                    <small class="font-weight-bold text-muted"><?php echo e(number_format($st->progress_pka, 1)); ?>%</small>
                                                                </td>
                                                                <td data-label="Status KK">
                                                                    <?php if(str_contains($st->status_kk, 'Final')): ?>
                                                                        <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> <?php echo e($st->status_kk); ?></span>
                                                                    <?php else: ?>
                                                                        <span class="text-secondary"><?php echo e($st->status_kk); ?></span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td data-label="Laporan (QA)">
                                                                    <?php if(str_contains($st->status_laporan, 'Tersedia')): ?>
                                                                        <span class="badge badge-success"><i class="fas fa-file-pdf mr-1"></i> <?php echo e($st->status_laporan); ?></span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-light border"><?php echo e($st->status_laporan); ?></span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td data-label="Aksi">
                                                                    <button class="btn btn-xs btn-outline-info btn-gantt rounded-pill px-3" 
                                                                        data-st-id="<?php echo e($st->id); ?>" 
                                                                        data-st-nomor="<?php echo e($st->nomor_st); ?>"
                                                                        data-tasks="<?php echo e($st->gantt_tasks); ?>">
                                                                        <i class="fas fa-stream mr-1"></i> Timeline
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="p-5 text-center text-muted">Belum ada penugasan yang terekam pada area Anda.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- Modal Timeline -->
    <div class="modal fade" id="ganttModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-stream mr-2 text-primary"></i> Timeline Penugasan</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4 bg-light" style="max-height: 70vh; overflow-y: auto;">
                    <h6 class="text-primary font-weight-bold mb-4" id="gantt-st-nomor"></h6>
                    
                    <!-- AdminLTE Timeline Container -->
                    <div class="timeline" id="vertical-timeline">
                        <!-- Content injected via JS -->
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <style>
        .accordion .card-header button:focus { box-shadow: none; }
        .timeline-item { border-radius: 10px !important; }
        .timeline > div > .timeline-item { box-shadow: 0 0 10px rgba(0,0,0,0.05) !important; border: none; }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(document).ready(function() {
            // Inspirational Quotes Array
            const quotes = [
                { text: "Kualitas bukanlah sebuah tindakan, melainkan kebiasaan.", author: "Aristotle" },
                { text: "Apa yang bisa diukur, bisa ditingkatkan.", author: "Peter Drucker" },
                { text: "Bukan pencapaian yang terpenting, tetapi proses evaluasinya.", author: "Anonim" },
                { text: "Akuntabilitas melahirkan responsibilitas.", author: "Stephen R. Covey" },
                { text: "Jangan takut melakukan kesalahan, takutlah jika tidak belajar darinya.", author: "Henry Ford" },
                { text: "Data yang baik menghasilkan keputusan yang tepat.", author: "W. Edwards Deming" },
                { text: "Audit bukan untuk mencari kesalahan, tapi untuk merawat kebenaran.", author: "Anonim" },
                { text: "Evaluasi hari ini adalah fondasi perbaikan esok hari.", author: "Anonim" }
            ];

            // Pick a random quote
            const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];
            
            // Apply it with a fade effect
            $('#quote-text').hide().text('"' + randomQuote.text + '"').fadeIn(1000);
            $('#quote-author').hide().text('- ' + randomQuote.author).fadeIn(1500);

            // Subtle hover animation for cards
            $('.card').hover(
                function() { $(this).addClass('shadow-sm'); },
                function() { $(this).removeClass('shadow-sm'); }
            );

            // Handle Timeline Button Click
            $(document).on('click', '.btn-gantt', function() {
                let stNomor = $(this).data('st-nomor');
                let tasksRaw = $(this).attr('data-tasks'); 
                
                let tasks = [];
                try {
                    tasks = typeof $(this).data('tasks') === 'object' ? $(this).data('tasks') : JSON.parse(tasksRaw);
                } catch(e) {
                    console.error("Timeline JSON parse error:", e);
                }

                $('#gantt-st-nomor').html('Nomor ST: ' + stNomor);
                
                let timelineHtml = '';

                if(!tasks || tasks.length === 0) {
                     timelineHtml = `
                     <div>
                        <i class="fas fa-info bg-gray"></i>
                        <div class="timeline-item">
                            <div class="timeline-body text-muted">Belum ada rincian jadwal untuk penugasan ini.</div>
                        </div>
                     </div>`;
                } else {
                     // Sort tasks by start date
                     tasks.sort((a, b) => new Date(a.start) - new Date(b.start));

                     tasks.forEach(t => {
                         let isST = t.custom_class === 'bar-st';
                         let icon = isST ? 'fa-file-signature bg-primary' : 'fa-tasks bg-info';
                         let barColor = isST ? 'bg-primary' : 'bg-info';
                         let headerClass = isST ? 'text-primary font-weight-bold' : '';

                         if(t.progress == 100) {
                             icon = 'fa-check bg-success';
                             barColor = 'bg-success';
                         }
                         
                         timelineHtml += `
                         <div>
                            <i class="fas ${icon}"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-calendar-alt"></i> ${t.start} s/d ${t.end}</span>
                                <h3 class="timeline-header ${headerClass} border-0 pb-0">${t.name} ${isST ? '(Durasi Total)' : ''}</h3>
                                <div class="timeline-body pt-2 pb-3">
                                    <div class="progress progress-sm mb-1" style="height: 6px; border-radius: 3px;">
                                        <div class="progress-bar ${barColor}" style="width: ${t.progress}%"></div>
                                    </div>
                                    <small class="text-muted font-weight-bold">Progress: ${t.progress}%</small>
                                </div>
                            </div>
                         </div>
                         `;
                     });
                     
                     // End clock icon
                     timelineHtml += `
                     <div>
                        <i class="fas fa-clock bg-gray"></i>
                     </div>`;
                }

                $('#vertical-timeline').html(timelineHtml);
                
                // Hack khusus AdminLTE/Bootstrap: pindahkan modal ke element <body> terluar
                // agar tidak terjebak z-index stacking context dari div content-wrapper
                $('#ganttModal').appendTo('body').modal('show');
            });
        });
    </script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\tampu\Github\evalueaction\resources\views/home.blade.php ENDPATH**/ ?>
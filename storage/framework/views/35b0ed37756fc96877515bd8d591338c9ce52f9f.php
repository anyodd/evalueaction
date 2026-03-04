

<?php $__env->startSection('title', 'Dashboard Temuan Nasional'); ?>

<?php $__env->startSection('content_header'); ?>
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-chart-pie mr-2 text-primary"></i> Dashboard Temuan Nasional
                </h1>
            </div>
            <div class="col-sm-6">
                <form action="<?php echo e(route('findings.dashboard')); ?>" method="GET" class="form-inline float-right">
                    <div class="form-group mr-2">
                        <select name="year" class="form-control form-control-sm shadow-sm rounded-pill border-0 px-3" onchange="this.form.submit()">
                            <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>>Tahun <?php echo e($y); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="perwakilan_id" class="form-control form-control-sm shadow-sm rounded-pill border-0 px-3" onchange="this.form.submit()">
                            <option value="">-- Semua Wilayah (Nasional) --</option>
                            <?php $__currentLoopData = $perwakilans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>" <?php echo e($perwakilanId == $p->id ? 'selected' : ''); ?>><?php echo e($p->nama_perwakilan); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <!-- Stats Row -->
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-gradient-info shadow-sm border-0 animate__animated animate__fadeInLeft" style="border-radius: 15px;">
                    <div class="inner p-4">
                        <h3 class="font-weight-bold"><?php echo e(number_format($stats['total_teo'])); ?></h3>
                        <p>Total Kondisi (TEO)</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-gradient-warning shadow-sm border-0 animate__animated animate__fadeInDown" style="border-radius: 15px;">
                    <div class="inner p-4">
                        <h3 class="font-weight-bold text-white"><?php echo e(number_format($stats['total_finding'])); ?></h3>
                        <p class="text-white text-md">Total Akar Masalah & Rekomendasi</p>
                    </div>
                    <div class="icon"><i class="fas fa-search-nodes"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-12">
                <div class="small-box bg-gradient-success shadow-sm border-0 animate__animated animate__fadeInRight" style="border-radius: 15px;">
                    <div class="inner p-4">
                        <h3 class="font-weight-bold"><?php echo e(number_format($stats['total_st'])); ?></h3>
                        <p>Penugasan Ber-Temuan</p>
                    </div>
                    <div class="icon"><i class="fas fa-clipboard-check"></i></div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <!-- Left Column: Quantitative Charts -->
            <div class="col-md-7">
                <!-- TEO by Aspect Chart -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h3 class="card-title font-weight-bold text-navy">
                            <i class="fas fa-layer-group mr-2 text-primary"></i> Sebaran Temuan per Aspek
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <canvas id="aspectChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>

                <!-- Region Distribution -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h3 class="card-title font-weight-bold text-navy">
                            <i class="fas fa-globe-asia mr-2 text-info"></i> Konsentrasi Temuan per Wilayah
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <canvas id="regionChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Right Column: Qualitative & Rankings -->
            <div class="col-md-5">
                <!-- Top Root Causes -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h3 class="card-title font-weight-bold text-navy">
                            <i class="fas fa-virus mr-2 text-danger"></i> Top Root Causes (Akar Masalah)
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php $__empty_1 = true; $__currentLoopData = $topCausesData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <li class="list-group-item border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                    <span class="text-sm font-weight-normal text-muted" style="line-height: 1.4;"><?php echo e($c->cause); ?></span>
                                    <span class="badge badge-pill badge-light border ml-3 font-weight-bold"><?php echo e($c->total); ?></span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <li class="list-group-item text-center text-muted py-5">Belum ada data akar masalah.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Strategic Recommendations -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h3 class="card-title font-weight-bold text-navy">
                            <i class="fas fa-lightbulb mr-2 text-warning"></i> Strategic Recommendations
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-4">
                            <?php $__empty_1 = true; $__currentLoopData = $topRecsData->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="mb-4 pb-3 border-bottom last-border-0">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center mr-2 shadow-sm" style="width: 24px; height: 24px;">
                                            <i class="fas fa-star text-white" style="font-size: 10px;"></i>
                                        </div>
                                        <span class="badge badge-warning text-xxs px-2 text-white">PRIORITAS TINGGI</span>
                                    </div>
                                    <p class="text-navy font-weight-bold mb-1" style="font-size: 0.95em;"><?php echo e($r->recommendation); ?></p>
                                    <small class="text-muted">Disarankan sebanyak <strong><?php echo e($r->total); ?> kali</strong> secara nasional.</small>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-center text-muted py-5">Belum ada data rekomendasi strategis.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Qualitative Summary / Common Conditions -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 mb-5" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold text-navy">
                            <i class="fas fa-quote-left mr-2 text-primary"></i> Ringkasan Kondisi (TEO) Teridentifikasi
                        </h3>
                        <span class="badge badge-navy px-3 py-2 rounded-pill font-weight-normal">MENAMPILKAN TOP 10 FREKUENSI</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover border-0">
                                <thead class="bg-light">
                                    <tr class="text-xs text-uppercase text-muted">
                                        <th>Narasi Kondisi / Permasalahan (TEO)</th>
                                        <th class="text-center" width="150">Prevalensi</th>
                                        <th width="200">Status Mitigasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $topTeos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="border-bottom">
                                            <td class="py-4 align-middle">
                                                <span class="text-navy font-weight-medium" style="font-size: 1.05em; line-height: 1.6;"><?php echo e($t->teo); ?></span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <h4 class="font-weight-bold mb-0 text-navy"><?php echo e($t->total); ?></h4>
                                                <small class="text-muted text-uppercase" style="letter-spacing: 1px; font-size: 10px;">KASUS</small>
                                            </td>
                                            <td class="align-middle">
                                                <?php $percent = $t->total / max($stats['total_teo'], 1) * 100; ?>
                                                <div class="progress progress-xs mb-1 bg-light rounded-pill" style="height: 4px;">
                                                    <div class="progress-bar bg-primary rounded-pill" style="width: <?php echo e($percent * 5); ?>%"></div>
                                                </div>
                                                <small class="text-muted font-weight-bold"><?php echo e(number_format($percent, 1)); ?>% dari total temuan</small>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .last-border-0:last-child { border-bottom: 0 !important; }
        .bg-navy { background-color: #001f3f !important; }
        .text-navy { color: #001f3f !important; }
        .text-xxs { font-size: 9px !important; }
        .font-weight-medium { font-weight: 500 !important; }
        .card { transition: transform 0.3s ease; }
        .card:hover { transform: translateY(-5px); }
        .badge-navy { background-color: #001f3f; color: white; }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(document).ready(function() {
            // 1. Aspect Distribution Chart
            var ctxAspect = document.getElementById('aspectChart').getContext('2d');
            var aspectChart = new Chart(ctxAspect, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($aspectDistribution->pluck('indicator_name')); ?>,
                    datasets: [{
                        data: <?php echo json_encode($aspectDistribution->pluck('total')); ?>,
                        backgroundColor: [
                            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
                            '#5a5c69', '#6610f2', '#6f42c1', '#e83e8c', '#fd7e14'
                        ],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    },
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            fontSize: 11
                        }
                    },
                    cutoutPercentage: 70,
                },
            });

            // 2. Region Distribution Chart
            var ctxRegion = document.getElementById('regionChart').getContext('2d');
            var regionChart = new Chart(ctxRegion, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($perwakilanDistribution->pluck('region_name')); ?>,
                    datasets: [{
                        label: "Jumlah Temuan",
                        backgroundColor: "#36b9cc",
                        hoverBackgroundColor: "#2c9faf",
                        borderColor: "#4e73df",
                        data: <?php echo json_encode($perwakilanDistribution->pluck('total')); ?>,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 12,
                                fontSize: 10
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                min: 0,
                                maxTicksLimit: 5,
                                padding: 10,
                            },
                        }],
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
                        titleFontSize: 14,
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                    }
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\tampu\Github\evalueaction\resources\views/findings/dashboard.blade.php ENDPATH**/ ?>
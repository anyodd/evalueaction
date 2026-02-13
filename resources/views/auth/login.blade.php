@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
    <style>
        .login-page {
            background: #ffffff !important;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Shapes */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.05) 0%, rgba(59, 130, 246, 0.05) 100%);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }

        .shape-1 { width: 400px; height: 400px; top: -100px; left: -100px; animation-duration: 25s; }
        .shape-2 { width: 300px; height: 300px; bottom: -50px; right: -50px; animation-duration: 30s; animation-direction: reverse; }
        .shape-3 { width: 250px; height: 250px; top: 20%; right: 10%; animation-duration: 35s; }
        .shape-4 { width: 150px; height: 150px; bottom: 30%; left: 15%; animation-duration: 20s; animation-direction: reverse; }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg) scale(1); }
            33% { transform: translate(30px, 50px) rotate(120deg) scale(1.1); }
            66% { transform: translate(-20px, 20px) rotate(240deg) scale(0.9); }
            100% { transform: translate(0, 0) rotate(360deg) scale(1); }
        }

        .login-box {
            position: relative;
            z-index: 10;
        }

        .login-logo a {
            font-weight: 300;
        }
        .login-box-msg {
            padding: 0 20px 20px;
        }
        
        /* Fixed Logos at Top */
        .logo-fixed {
            position: fixed;
            top: 30px;
            height: 70px;
            width: auto;
            z-index: 100;
            opacity: 0.9;
            transition: all 0.3s ease;
        }
        .logo-bpkp {
            left: 30px;
        }
        .logo-dan {
            right: 30px;
        }
        
        @media (max-width: 768px) {
            .logo-fixed {
                height: 40px;
                top: 15px;
            }
            .logo-bpkp { left: 15px; }
            .logo-dan { right: 15px; }
        }
    </style>
@stop

@section('auth_header')
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>
    <p class="login-box-msg mb-1">
        <b>e-Value-<span class="text-red-brand">A</span><span class="text-turquoise-brand">ctio</span><span class="text-orange-brand">N</span></b>
    </p>
    <p class="login-box-msg pb-2">Silakan Masuk untuk Memulai Sesi</p>
@stop

@section('auth_body')
    <form action="{{ route('login') }}" method="post">
        @csrf

        {{-- Email field --}}
        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="Email" autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Password field --}}
        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Kata Sandi">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Login field --}}
        <div class="row">
            <div class="col-7">
                <div class="icheck-primary" title="Tetap masuk">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">
                        Ingat Saya
                    </label>
                </div>
            </div>
            <div class="col-5">
                <button type=submit class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
                    <span class="fas fa-sign-in-alt"></span>
                    Masuk
                </button>
            </div>
        </div>
    </form>
@stop

@section('auth_footer')
    <p class="text-sm text-muted text-center mt-2 mb-3" style="line-height: 1.4;">
        <i>"Deputi Akuntan Negara memberikan nilai tambah melalui kegiatan monitoring dan evaluasi"</i>
    </p>
    <p class="my-0">
        <a href="https://wa.me/6281247981945?text=Assalamualaikum,%20mohon%20bantuan%20reset%20password%20untuk%20aplikasi%20e-Value-ActioN." target="_blank" class="text-center">
            <i class="fab fa-whatsapp mr-1"></i> Lupa Kata Sandi? Hubungi Admin
        </a>
    </p>

    {{-- Fixed Logos with Fallbacks --}}
    <img src="{{ asset('img/bpkp_logo.png') }}" alt="Logo BPKP" class="logo-fixed logo-bpkp" 
         onerror="this.src='https://placehold.jp/24/1e3a8a/ffffff/200x80.png?text=LOGO BPKP'">
    <img src="{{ asset('img/deputian_logo.png') }}" alt="Logo Deputi AN" class="logo-fixed logo-dan" 
         onerror="this.src='https://placehold.jp/24/065f46/ffffff/200x80.png?text=LOGO DEPUTI AN'">
@stop
@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- Manual Notification Bell --}}
        @if(Auth::check())
            @php
                $unreadCount = Auth::user()->unreadNotifications->count();
                $notifications = Auth::user()->unreadNotifications->take(5);
            @endphp
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    @if($unreadCount > 0)
                        <span class="badge badge-danger navbar-badge">{{ $unreadCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="left: inherit; right: 0px;">
                    <span class="dropdown-item dropdown-header">{{ $unreadCount }} Notifikasi</span>
                    <div class="dropdown-divider"></div>
                    @foreach($notifications as $notif)
                        <a href="{{ route('notifications.read', $notif->id) }}" class="dropdown-item">
                            <i class="fas fa-file-alt mr-2 text-info"></i> 
                            <span class="text-wrap">{{ $notif->data['message'] ?? 'Laporan baru' }}</span>
                        </a>
                        <div class="dropdown-divider"></div>
                    @endforeach
                    @if($unreadCount == 0)
                        <p class="text-center text-muted p-2 mb-0">Tidak ada notifikasi baru</p>
                    @endif
                </div>
            </li>
        @endif

        {{-- User menu link --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>

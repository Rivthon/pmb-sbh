<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">

    <!-- Menu Toggle (Visible on smaller screens) -->
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <!-- Title / App Name -->
    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <h5 class="mb-0">{{ config('app.name') }}</h5>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- Admin User Menu -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="#"
                             onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'"
                             alt="Admin Avatar"
                             class="w-px-40 h-auto rounded-circle bg-light shadow-sm" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <!-- Avatar -->
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="#"
                                             onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'"
                                             alt="Admin Avatar"
                                             class="w-px-40 h-auto rounded-circle bg-light" />
                                    </div>
                                </div>
                                <!-- Admin Info -->
                                <div class="flex-grow-1">
                                    <span class="fw-medium d-block">{{ auth()->guard('admin')->user()->name }}</span>
                                    <small class="text-muted">{{ auth()->guard('admin')->user()->role }}</small>
                                </div>
                            </div>
                        </a>
                    </li>

                    <li>
                        <div class="dropdown-divider"></div>
                    </li>

                    {{-- Optional Profile Link (Uncomment if needed) --}}
                    {{--
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>
                    --}}

                    <!-- Logout -->
                   <!-- Logout -->
                  <li>
                        <a class="dropdown-item" href="{{ route('admin.logout') }}">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </li>

                </ul>
            </li>
            <!-- / Admin User Menu -->
        </ul>
    </div>
</nav>

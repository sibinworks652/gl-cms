<header class="topbar">
     @php($adminLogo = \Modules\Settings\Models\Setting::value('admin_logo'))
     @php($siteName = \Modules\Settings\Models\Setting::value('site_name', 'Admin Panel'))
     <div class="container-fluid">
          <div class="navbar-header">
               <div class="d-flex align-items-center">
                    <!-- Menu Toggle Button -->
                    <div class="topbar-item">
                         <button type="button" class="button-toggle-menu me-2">
                              <iconify-icon icon="solar:hamburger-menu-broken" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <!-- Menu Toggle Button -->
                    <div class="topbar-item">
                         <div class="topbar-button pe-none d-flex align-items-center gap-2">
                              {{-- @if($adminLogo)
                                   <img src="{{ asset('storage/' . $adminLogo) }}" alt="Admin Logo" style="height: 34px; width: auto; object-fit: contain;">
                              @endif --}}
                              <h4 class="fw-bold mb-0">{{ $siteName }}</h4>
                         </div>
                    </div>
                    <div class="topbar-item d-none d-md-flex">
                         <div class="topbar-button pe-none text-start lh-sm">
                              <div class="fw-semibold" id="admin-current-date">--</div>
                              <div class="small opacity-75" id="admin-current-time">--</div>
                         </div>
                    </div>
               </div>

               <div class="d-flex align-items-center gap-1">
                    <!-- Theme Color (Light/Dark) -->
                    <div class="topbar-item">
                         <button type="button" class="topbar-button" id="light-dark-mode" aria-label="Toggle theme mode" title="Toggle theme mode">
                              <iconify-icon icon="solar:moon-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <!-- Notification -->
                    <div class="dropdown topbar-item">
                         <button type="button" class="topbar-button position-relative" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <iconify-icon icon="solar:bell-bing-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                              <span class="position-absolute topbar-badge fs-10 translate-middle badge bg-danger rounded-pill">3<span class="visually-hidden">unread messages</span></span>
                         </button>
                         <div class="dropdown-menu py-0 dropdown-lg dropdown-menu-end" aria-labelledby="page-header-notifications-dropdown">
                              <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                   <div class="row align-items-center">
                                        <div class="col">
                                             <h6 class="m-0 fs-16 fw-semibold"> Notifications</h6>
                                        </div>
                                        <div class="col-auto">
                                             <a href="javascript: void(0);" class="text-dark text-decoration-underline">
                                                  <small>Clear All</small>
                                             </a>
                                        </div>
                                   </div>
                              </div>
                              <div data-simplebar style="max-height: 280px;">
                                   <!-- Item -->
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom text-wrap">
                                        <div class="d-flex">
                                             <div class="flex-shrink-0">
                                                  <img src="{{asset('admin/assets/images/users/avatar-1.jpg')}}" class="img-fluid me-2 avatar-sm rounded-circle" alt="avatar-1" />
                                             </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0"><span class="fw-medium">Josephine Thompson </span>commented on admin panel <span>" Wow 😍! this admin looks good and awesome design"</span></p>
                                             </div>
                                        </div>
                                   </a>
                                   <!-- Item -->
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <div class="d-flex">
                                             <div class="flex-shrink-0">
                                                  <div class="avatar-sm me-2">
                                                       <span class="avatar-title bg-soft-info text-info fs-20 rounded-circle">
                                                            D
                                                       </span>
                                                  </div>
                                             </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0 fw-semibold">Donoghue Susan</p>
                                                  <p class="mb-0 text-wrap">
                                                       Hi, How are you? What about our next meeting
                                                  </p>
                                             </div>
                                        </div>
                                   </a>
                                   <!-- Item -->
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <div class="d-flex">
                                             <div class="flex-shrink-0">
                                                  <img src="{{asset('admin/assets/images/users/avatar-3.jpg')}}" class="img-fluid me-2 avatar-sm rounded-circle" alt="avatar-3" />
                                             </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0 fw-semibold">Jacob Gines</p>
                                                  <p class="mb-0 text-wrap">Answered to your comment on the cash flow forecast's graph 🔔.</p>
                                             </div>
                                        </div>
                                   </a>
                                   <!-- Item -->
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <div class="d-flex">
                                             <div class="flex-shrink-0">
                                                  <div class="avatar-sm me-2">
                                                       <span class="avatar-title bg-soft-warning text-warning fs-20 rounded-circle">
                                                            <iconify-icon icon="iconamoon:comment-dots-duotone"></iconify-icon>
                                                       </span>
                                                  </div>
                                             </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0 fw-semibold text-wrap">You have received <b>20</b> new messages in the
                                                       conversation</p>
                                             </div>
                                        </div>
                                   </a>
                                   <!-- Item -->
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom">
                                        <div class="d-flex">
                                             <div class="flex-shrink-0">
                                                  <img src="{{asset('admin/assets/images/users/avatar-5.jpg')}}" class="img-fluid me-2 avatar-sm rounded-circle" alt="avatar-5" />
                                             </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0 fw-semibold">Shawn Bunch</p>
                                                  <p class="mb-0 text-wrap">
                                                       Commented on Admin
                                                  </p>
                                             </div>
                                        </div>
                                   </a>
                              </div>
                              <div class="text-center py-3">
                                   <a href="javascript:void(0);" class="btn btn-primary btn-sm">View All Notification <i class="bx bx-right-arrow-alt ms-1"></i></a>
                              </div>
                         </div>
                    </div>

                    <!-- Theme Setting -->
                    <div class="topbar-item d-none d-md-flex">
                         <button type="button" class="topbar-button" id="theme-settings-btn" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">
                              <iconify-icon icon="solar:settings-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <!-- Activity -->
                    <div class="topbar-item d-none d-md-flex">
                         <button type="button" class="topbar-button" id="theme-settings-btn" data-bs-toggle="offcanvas" data-bs-target="#theme-activity-offcanvas" aria-controls="theme-settings-offcanvas">
                              <iconify-icon icon="solar:clock-circle-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <!-- User -->
                    <div class="dropdown topbar-item">
                         <a type="button" class="topbar-button" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="d-flex align-items-center">
                                   <img class="rounded-circle" width="32" src="{{asset('admin/assets/images/users/avatar-1.jpg')}}" alt="avatar-3">
                              </span>
                         </a>
                         <div class="dropdown-menu dropdown-menu-end">
                              <!-- item-->
                              <h6 class="dropdown-header">Welcome {{ Auth::user()->name ?? 'Admin' }}!</h6>
                              <a class="dropdown-item" href="#">
                                   <i class="bx bx-user-circle text-muted fs-18 align-middle me-1"></i><span class="align-middle">Profile</span>
                              </a>
                              <a class="dropdown-item" href="{{ route('admin.lock') }}">
                                   <i class="bx bx-lock text-muted fs-18 align-middle me-1"></i><span class="align-middle">Lock screen</span>
                              </a>

                              <div class="dropdown-divider my-1"></div>

                              <form action="{{ route('logout') }}" method="POST">
                                   @csrf
                                   <button type="submit" class="dropdown-item text-danger">
                                        <i class="bx bx-log-out fs-18 align-middle me-1"></i><span class="align-middle">Logout</span>
                                   </button>
                              </form>
                         </div>
                    </div>

                    <!-- App Search-->
                    <form class="app-search d-none d-md-block ms-2">
                         <div class="position-relative">
                              <input type="search" class="form-control" placeholder="Search..." autocomplete="off" value="">
                              <iconify-icon icon="solar:magnifer-linear" class="search-widget-icon"></iconify-icon>
                         </div>
                    </form>
               </div>
          </div>
     </div>
</header>

<!-- Activity Timeline -->


<!-- Right Sidebar (Theme Settings) -->

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateElement = document.getElementById('admin-current-date');
    const timeElement = document.getElementById('admin-current-time');
    const dateFormat = @js($adminSettings['date_format'] ?? 'd M Y');
    const timeFormat = @js($adminSettings['time_format'] ?? 'h:i A');
    const configuredTimezone = @js($adminSettings['timezone'] ?? config('app.timezone'));

    if (!dateElement || !timeElement) {
        return;
    }

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function formatDateTime(date, format) {
        const zonedDate = new Date(date.toLocaleString('en-US', { timeZone: configuredTimezone }));
        const monthsShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const monthsLong = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const daysShort = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const daysLong = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        const hours24 = zonedDate.getHours();
        const hours12 = hours24 % 12 || 12;

        const tokens = {
            d: pad(zonedDate.getDate()),
            j: String(zonedDate.getDate()),
            m: pad(zonedDate.getMonth() + 1),
            n: String(zonedDate.getMonth() + 1),
            M: monthsShort[zonedDate.getMonth()],
            F: monthsLong[zonedDate.getMonth()],
            Y: String(zonedDate.getFullYear()),
            y: String(zonedDate.getFullYear()).slice(-2),
            D: daysShort[zonedDate.getDay()],
            l: daysLong[zonedDate.getDay()],
            H: pad(hours24),
            G: String(hours24),
            h: pad(hours12),
            g: String(hours12),
            i: pad(zonedDate.getMinutes()),
            s: pad(zonedDate.getSeconds()),
            A: hours24 >= 12 ? 'PM' : 'AM',
            a: hours24 >= 12 ? 'pm' : 'am',
        };

        let output = '';
        let escapeNext = false;

        for (const character of format) {
            if (escapeNext) {
                output += character;
                escapeNext = false;
                continue;
            }

            if (character === '\\') {
                escapeNext = true;
                continue;
            }

            output += tokens[character] ?? character;
        }

        return output;
    }

    function updateDateTime() {
        const now = new Date();

        dateElement.textContent = formatDateTime(now, dateFormat);
        timeElement.textContent = formatDateTime(now, timeFormat);
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);
});

</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('light-dark-mode');

    if (!toggleButton) {
        return;
    }

    const modeSelect = document.querySelector('select[name="admin_dark_mode_enabled"]');
    const htmlEl = document.documentElement;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let isSavingTheme = false;

    function currentThemeValue() {
        return htmlEl.getAttribute('data-bs-theme') === 'dark' ? '1' : '0';
    }

    if (modeSelect) {
        modeSelect.value = currentThemeValue();
    }

    toggleButton.addEventListener('click', async function () {
        if (isSavingTheme) {
            return;
        }

        await new Promise(function (resolve) {
            window.setTimeout(resolve, 0);
        });

        const currentValue = currentThemeValue();

        if (modeSelect) {
            modeSelect.value = currentValue;
        }

        isSavingTheme = true;

        try {
            const response = await fetch('{{ route('admin.dark-mode') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
                body: JSON.stringify({
                    admin_dark_mode_enabled: currentValue,
                }),
            });

            if (!response.ok) {

                throw new Error('Theme update request failed.');
            }
        } catch (error) {
            console.error(error);

            if (window.showAdminToast) {
                window.showAdminToast('Failed to save theme mode.', 'danger');
            }
        } finally {
            isSavingTheme = false;
        }
    });
});
</script>
@endpush

<div class="main-nav">
     @php($adminUser = auth('admin')->user())
     @php($vendorUser = auth()->user())
     @php($vendorAccount = $vendorUser ? \Modules\Ecommerce\Models\Vendor::where('user_id', $vendorUser->id)->first() : null)
     @php($adminLogo = \Modules\Settings\Models\Setting::value('admin_logo'))
     @php($hasRoute = static fn (string $name): bool => \Illuminate\Support\Facades\Route::has($name))
     @php($routeUrl = static fn (string $name, mixed $parameters = []): string => $hasRoute($name) ? route($name, $parameters) : 'javascript:void(0);')
     @php($moduleEnabled = static fn (string $name): bool => \App\Support\ModuleRegistry::enabled($name))
     <!-- Sidebar Logo -->
     <div class="logo-box">
          <a href="{{ $routeUrl('admin.dashboard') }}" class="logo-dark">
               <img src="{{ $adminLogo ? asset('storage/' . $adminLogo) : asset('admin/assets/images/logo-sm.png') }}" class="logo-sm" alt="logo sm">
               <img src="{{ $adminLogo ? asset('storage/' . $adminLogo) : asset('admin/assets/images/logo-dark.png') }}" class="logo-lg" alt="logo dark">
          </a>

          <a href="{{ $routeUrl('admin.dashboard') }}" class="logo-light">
               <img src="{{ $adminLogo ? asset('storage/' . $adminLogo) : asset('admin/assets/images/logo-sm.png') }}" class="logo-sm" alt="logo sm">
               <img src="{{ $adminLogo ? asset('storage/' . $adminLogo) : asset('admin/assets/images/logo-light.png') }}" class="logo-lg" alt="logo light">
          </a>
     </div>

     <!-- Menu Toggle Button (sm-hover) -->
     <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
          <iconify-icon icon="solar:double-alt-arrow-right-bold-duotone" class="button-sm-hover-icon"></iconify-icon>
     </button>

     <div class="scrollbar" data-simplebar>
          <ul class="navbar-nav" id="navbar-nav">

               {{-- <li class="menu-title">General</li> --}}

               @if($adminUser?->can('dashboard.view') && $hasRoute('admin.dashboard'))
                    <li class="nav-item">
                         <a class="nav-link" href="{{ $routeUrl('admin.dashboard') }}">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:widget-5-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Dashboard </span>
                         </a>
                    </li>
               @endif

               @if($vendorAccount && request()->routeIs('vendor.*'))
                    @php($vendorMenuOpen = request()->routeIs('vendor.products.*') || request()->routeIs('vendor.orders.*') || request()->routeIs('vendor.dashboard'))
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}" href="{{ $routeUrl('vendor.dashboard') }}">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:shop-2-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Vendor Dashboard </span>
                         </a>
                    </li>
                    <li class="nav-item">
                         <a class="nav-link menu-arrow {{ $vendorMenuOpen ? 'active' : '' }}" href="#sidebarVendorPortal" data-bs-toggle="collapse" role="button" aria-expanded="{{ $vendorMenuOpen ? 'true' : 'false' }}" aria-controls="sidebarVendorPortal">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:bag-5-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Vendor Portal </span>
                         </a>
                         <div class="collapse {{ $vendorMenuOpen ? 'show' : '' }}" id="sidebarVendorPortal">
                              <ul class="nav sub-navbar-nav">
                                   @if($hasRoute('vendor.products.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('vendor.products.index') ? 'active' : '' }}" href="{{ $routeUrl('vendor.products.index') }}">My Products</a>
                                        </li>
                                   @endif
                                   @if($hasRoute('vendor.products.create'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('vendor.products.create') ? 'active' : '' }}" href="{{ $routeUrl('vendor.products.create') }}">Add Product</a>
                                        </li>
                                   @endif
                                   @if($hasRoute('vendor.orders.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('vendor.orders.*') ? 'active' : '' }}" href="{{ $routeUrl('vendor.orders.index') }}">Orders</a>
                                        </li>
                                   @endif
                              </ul>
                         </div>
                    </li>
               @endif

               @if(($adminUser?->can('admins.view') && $hasRoute('admin.admins.index')) || ($adminUser?->can('roles.view') && $hasRoute('admin.roles.index')) || ($adminUser?->can('permissions.view') && $hasRoute('admin.permissions.index')))
                    <li class="nav-item">
                         <a class="nav-link menu-arrow" href="#adminManagment" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="adminManagment">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Admin Management </span>
                         </a>
                         <div class="collapse" id="adminManagment">
                              <ul class="nav sub-navbar-nav">
                                   @if($adminUser?->can('admins.view') && $hasRoute('admin.admins.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link" href="{{ $routeUrl('admin.admins.index') }}">Admins</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('roles.view') && $hasRoute('admin.roles.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link" href="{{ $routeUrl('admin.roles.index') }}">Roles</a>
                                        </li>
                                   @endif
                                   {{-- @if($adminUser?->can('permissions.view'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link" href="{{ route('admin.permissions.index') }}">Permissions</a>
                                        </li>
                                   @endif --}}
                              </ul>
                         </div>
                    </li>
               @endif
               {{-- <li class="menu-title">Page</li> --}}
               @if($moduleEnabled('gallery') && $adminUser?->can('gallery.view') && $hasRoute('admin.gallery.index'))
                    <li class="nav-item">
                         <a class="nav-link" href="{{ $routeUrl('admin.gallery.index') }}">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:gallery-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Gallery </span>
                         </a>
                    </li>
               @endif

               @if($moduleEnabled('page') && $adminUser?->can('pages.view') && $hasRoute('admin.pages.index'))
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.pages.index') }}">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Pages </span>
                         </a>
                    </li>
               @endif

               @if($moduleEnabled('banner') && $adminUser?->can('banners.view') && $hasRoute('admin.banners.index'))
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.banners.index') }}">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:gallery-wide-line-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Banners </span>
                         </a>
                    </li>
               @endif

               @if($moduleEnabled('menu') && $adminUser?->can('menus.view') && $hasRoute('admin.menus.index'))
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.menus.index') }}">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:hamburger-menu-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Menus </span>
                         </a>
                    </li>
               @endif

               @if($moduleEnabled('form_builder') && $adminUser?->can('forms.view') && $hasRoute('admin.forms.index'))
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.forms.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.forms.index') }}">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:document-add-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Form Builder </span>
                         </a>
                    </li>
               @endif

               @if($moduleEnabled('activity_logs') && ((($adminUser?->can('activity-logs.view') || $adminUser?->can('activity-logs.view-own')) && $hasRoute('admin.activity-logs.index')) || (($adminUser?->can('login-histories.view') || $adminUser?->can('login-histories.view-own')) && $hasRoute('admin.login-histories.index'))))
                    @php($activityMenuOpen = request()->routeIs('admin.activity-logs.*') || request()->routeIs('admin.login-histories.*'))
                    <li class="nav-item">
                         <a class="nav-link menu-arrow {{ $activityMenuOpen ? 'active' : '' }}" href="#sidebarActivityLogs" data-bs-toggle="collapse" role="button" aria-expanded="{{ $activityMenuOpen ? 'true' : 'false' }}" aria-controls="sidebarActivityLogs">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Activity Logs </span>
                         </a>
                         <div class="collapse {{ $activityMenuOpen ? 'show' : '' }}" id="sidebarActivityLogs">
                              <ul class="nav sub-navbar-nav">
                                   @if(($adminUser?->can('activity-logs.view') || $adminUser?->can('activity-logs.view-own')) && $hasRoute('admin.activity-logs.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.activity-logs.index') }}">Timeline</a>
                                        </li>
                                   @endif
                                   @if(($adminUser?->can('login-histories.view') || $adminUser?->can('login-histories.view-own')) && $hasRoute('admin.login-histories.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.login-histories.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.login-histories.index') }}">Login History</a>
                                        </li>
                                   @endif
                              </ul>
                         </div>
                    </li>
               @endif

               @if($moduleEnabled('services') && (($adminUser?->can('services.view') && $hasRoute('admin.services.index')) || ($adminUser?->can('service-categories.view') && $hasRoute('admin.service-categories.index'))))
                    @php($servicesMenuOpen = request()->routeIs('admin.services.*') || request()->routeIs('admin.service-categories.*'))
                    <li class="nav-item">
                         <a class="nav-link menu-arrow {{ $servicesMenuOpen ? 'active' : '' }}" href="#sidebarServices" data-bs-toggle="collapse" role="button" aria-expanded="{{ $servicesMenuOpen ? 'true' : 'false' }}" aria-controls="sidebarServices">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:case-round-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Services </span>
                         </a>
                         <div class="collapse {{ $servicesMenuOpen ? 'show' : '' }}" id="sidebarServices">
                              <ul class="nav sub-navbar-nav">
                                   @if($adminUser?->can('services.view') && $hasRoute('admin.services.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.services.index') }}">Services</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('service-categories.view') && $hasRoute('admin.service-categories.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.service-categories.index') }}">Categories</a>
                                        </li>
                                   @endif
                              </ul>
                         </div>
                    </li>
               @endif

               @if($moduleEnabled('ecommerce') && (($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.products.index')) || ($adminUser?->can('product-categories.view') && $hasRoute('admin.ecommerce.categories.index')) || ($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.brands.index')) || ($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.attributes.index')) || ($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.tags.index')) || ($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.inventory.index')) || ($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.discounts.index')) || ($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.coupons.index')) || ($adminUser?->can('vendors.view') && $hasRoute('admin.ecommerce.vendors.index')) || ($adminUser?->can('orders.view') && $hasRoute('admin.ecommerce.orders.index'))))
                    @php($ecommerceMenuOpen = request()->routeIs('admin.ecommerce.*'))
                    <li class="nav-item">
                         <a class="nav-link menu-arrow {{ $ecommerceMenuOpen ? 'active' : '' }}" href="#sidebarEcommerce" data-bs-toggle="collapse" role="button" aria-expanded="{{ $ecommerceMenuOpen ? 'true' : 'false' }}" aria-controls="sidebarEcommerce">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:shop-2-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Ecommerce </span>
                         </a>
                         <div class="collapse {{ $ecommerceMenuOpen ? 'show' : '' }}" id="sidebarEcommerce">
                              <ul class="nav sub-navbar-nav">
                                   @if($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.products.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.ecommerce.products.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.ecommerce.products.index') }}">Products</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('product-categories.view') && $hasRoute('admin.ecommerce.categories.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.ecommerce.categories.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.ecommerce.categories.index') }}">Categories</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.brands.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.ecommerce.brands.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.ecommerce.brands.index') }}">Brands</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.attributes.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.ecommerce.attributes.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.ecommerce.attributes.index') }}">Attributes</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.tags.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.ecommerce.tags.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.ecommerce.tags.index') }}">Tags</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.inventory.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.ecommerce.inventory.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.ecommerce.inventory.index') }}">Inventory</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.discounts.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.ecommerce.discounts.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.ecommerce.discounts.index') }}">Discounts</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('products.view') && $hasRoute('admin.ecommerce.coupons.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.ecommerce.coupons.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.ecommerce.coupons.index') }}">Coupons</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('vendors.view') && $hasRoute('admin.ecommerce.vendors.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.ecommerce.vendors.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.ecommerce.vendors.index') }}">Vendors</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('orders.view') && $hasRoute('admin.ecommerce.orders.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.ecommerce.orders.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.ecommerce.orders.index') }}">Orders</a>
                                        </li>
                                   @endif
                              </ul>
                         </div>
                    </li>
               @endif

               @if($moduleEnabled('testimonials') && $adminUser?->can('testimonials.view') && $hasRoute('admin.testimonials.index'))
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.testimonials.index') }}">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:chat-round-like-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Testimonials </span>
                         </a>
                    </li>
               @endif

               @if($moduleEnabled('faq') && (($adminUser?->can('faqs.view') && $hasRoute('admin.faqs.index')) || ($adminUser?->can('faq-categories.view') && $hasRoute('admin.faq-categories.index'))))
                    @php($faqMenuOpen = request()->routeIs('admin.faqs.*') || request()->routeIs('admin.faq-categories.*'))
                    <li class="nav-item">
                         <a class="nav-link menu-arrow {{ $faqMenuOpen ? 'active' : '' }}" href="#sidebarFaq" data-bs-toggle="collapse" role="button" aria-expanded="{{ $faqMenuOpen ? 'true' : 'false' }}" aria-controls="sidebarFaq">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:question-circle-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> FAQ </span>
                         </a>
                         <div class="collapse {{ $faqMenuOpen ? 'show' : '' }}" id="sidebarFaq">
                              <ul class="nav sub-navbar-nav">
                                   @if($adminUser?->can('faqs.view') && $hasRoute('admin.faqs.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.faqs.index') }}">FAQs</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('faq-categories.view') && $hasRoute('admin.faq-categories.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.faq-categories.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.faq-categories.index') }}">Categories</a>
                                        </li>
                                   @endif
                              </ul>
                         </div>
                    </li>
               @endif

               @if($moduleEnabled('team') && (($adminUser?->can('team-members.view') && $hasRoute('admin.team-members.index')) || ($adminUser?->can('team-departments.view') && $hasRoute('admin.team-departments.index'))))
                    @php($teamMenuOpen = request()->routeIs('admin.team-members.*') || request()->routeIs('admin.team-departments.*'))
                    <li class="nav-item">
                         <a class="nav-link menu-arrow {{ $teamMenuOpen ? 'active' : '' }}" href="#sidebarTeam" data-bs-toggle="collapse" role="button" aria-expanded="{{ $teamMenuOpen ? 'true' : 'false' }}" aria-controls="sidebarTeam">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Team </span>
                         </a>
                         <div class="collapse {{ $teamMenuOpen ? 'show' : '' }}" id="sidebarTeam">
                              <ul class="nav sub-navbar-nav">
                                   @if($adminUser?->can('team-members.view') && $hasRoute('admin.team-members.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.team-members.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.team-members.index') }}">Members</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('team-departments.view') && $hasRoute('admin.team-departments.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.team-departments.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.team-departments.index') }}">Departments</a>
                                        </li>
                                   @endif
                              </ul>
                         </div>
                    </li>
               @endif

               @if($moduleEnabled('careers') && (($adminUser?->can('careers.jobs.view') && $hasRoute('admin.jobs.index')) || ($adminUser?->can('careers.categories.view') && $hasRoute('admin.job-categories.index')) || ($adminUser?->can('careers.applications.view') && $hasRoute('admin.applications.index'))))
                    @php($careersMenuOpen = request()->routeIs('admin.jobs.*') || request()->routeIs('admin.job-categories.*') || request()->routeIs('admin.applications.*'))
                    <li class="nav-item">
                         <a class="nav-link menu-arrow {{ $careersMenuOpen ? 'active' : '' }}" href="#sidebarCareers" data-bs-toggle="collapse" role="button" aria-expanded="{{ $careersMenuOpen ? 'true' : 'false' }}" aria-controls="sidebarCareers">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:case-round-minimalistic-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Careers </span>
                         </a>
                         <div class="collapse {{ $careersMenuOpen ? 'show' : '' }}" id="sidebarCareers">
                              <ul class="nav sub-navbar-nav">
                                   @if($adminUser?->can('careers.jobs.view') && $hasRoute('admin.jobs.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.jobs.index') }}">Jobs</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('careers.categories.view') && $hasRoute('admin.job-categories.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.job-categories.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.job-categories.index') }}">Categories</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('careers.applications.view') && $hasRoute('admin.applications.index'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.applications.index') }}">Applications</a>
                                        </li>
                                   @endif
                              </ul>
                         </div>
                    </li>
               @endif

               @if($moduleEnabled('backup') && $adminUser?->can('backups.view') && $hasRoute('admin.backups.index'))
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.backups.index') }}">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:cloud-upload-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Backup </span>
                         </a>
                    </li>
               @endif

               @if($moduleEnabled('seo') && $adminUser?->can('seo.view') && $hasRoute('admin.seo.index'))
                    <li class="nav-item">
                         <a class="nav-link {{ request()->routeIs('admin.seo.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.seo.index') }}">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:magnifer-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> SEO Settings </span>
                         </a>
                    </li>
               @endif

               @if((($adminUser?->can('settings.view') || $adminUser?->can('settings.mail.update') || $adminUser?->can('settings.general.update') || $adminUser?->can('settings.system.update') || $adminUser?->can('settings.admin.update') || $adminUser?->can('settings.modules.update') || $adminUser?->can('settings.ecommerce_settings.update') || $adminUser?->can('settings.update') || $adminUser?->can('settings.social.update') || $adminUser?->can('settings.analytics.update')) && ($hasRoute('admin.settings.show') || $hasRoute('admin.settings.section.edit'))) || ($moduleEnabled('email') && $adminUser?->can('email.view') && $hasRoute('admin.email.settings.edit')))
                    @php($settingsMenuOpen = request()->routeIs('admin.settings.*') || request()->routeIs('admin.email.*'))
                    <li class="nav-item">
                         <a class="nav-link menu-arrow {{ $settingsMenuOpen ? 'active' : '' }}" href="#sidebarSettings" data-bs-toggle="collapse" role="button" aria-expanded="{{ $settingsMenuOpen ? 'true' : 'false' }}" aria-controls="sidebarSettings">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:settings-bold-duotone"></iconify-icon>
                              </span>
                              <span class="nav-text"> Settings </span>
                         </a>
                         <div class="collapse {{ $settingsMenuOpen ? 'show' : '' }}" id="sidebarSettings">
                              <ul class="nav sub-navbar-nav">
                                   @if($adminUser?->can('settings.view') && $hasRoute('admin.settings.show'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.settings.show') && !request('section') ? 'active' : '' }}" href="{{ $routeUrl('admin.settings.show') }}">Overview</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('settings.mail.update') && $hasRoute('admin.settings.section.edit'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.settings.section.edit') && request()->route('section') === 'mail' ? 'active' : '' }}" href="{{ $routeUrl('admin.settings.section.edit', 'mail') }}">Mail Settings</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('settings.general.update') && $hasRoute('admin.settings.section.edit'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.settings.section.edit') && request()->route('section') === 'general' ? 'active' : '' }}" href="{{ $routeUrl('admin.settings.section.edit', 'general') }}">General Settings</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('settings.system.update') && $hasRoute('admin.settings.section.edit'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.settings.section.edit') && request()->route('section') === 'system' ? 'active' : '' }}" href="{{ $routeUrl('admin.settings.section.edit', 'system') }}">System Settings</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('settings.admin.update') && $hasRoute('admin.settings.section.edit'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.settings.section.edit') && request()->route('section') === 'admin' ? 'active' : '' }}" href="{{ $routeUrl('admin.settings.section.edit', 'admin') }}">Admin Panel</a>
                                        </li>
                                   @endif
                                   @if(($adminUser?->can('settings.modules.update') || $adminUser?->can('settings.update')) && $hasRoute('admin.settings.section.edit'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.settings.section.edit') && request()->route('section') === 'modules' ? 'active' : '' }}" href="{{ $routeUrl('admin.settings.section.edit', 'modules') }}">Modules</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('settings.ecommerce_settings.update') && $hasRoute('admin.settings.section.edit'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.settings.section.edit') && request()->route('section') === 'ecommerce_settings' ? 'active' : '' }}" href="{{ $routeUrl('admin.settings.section.edit', 'ecommerce_settings') }}">Ecommerce Settings</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('settings.social.update') && $hasRoute('admin.settings.section.edit'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.settings.section.edit') && request()->route('section') === 'social' ? 'active' : '' }}" href="{{ $routeUrl('admin.settings.section.edit', 'social') }}">Social Media</a>
                                        </li>
                                   @endif
                                   @if($adminUser?->can('settings.analytics.update') && $hasRoute('admin.settings.section.edit'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.settings.section.edit') && request()->route('section') === 'analytics' ? 'active' : '' }}" href="{{ $routeUrl('admin.settings.section.edit', 'analytics') }}">Analytics</a>
                                        </li>
                                   @endif
                                   @if($moduleEnabled('email') && $adminUser?->can('email.view') && $hasRoute('admin.email.settings.edit'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.email.*') ? 'active' : '' }}" href="{{ $routeUrl('admin.email.settings.edit') }}">Email System</a>
                                        </li>
                                   @endif
                                   {{-- @if($adminUser?->can('settings.update'))
                                        <li class="sub-nav-item">
                                             <a class="sub-nav-link {{ request()->routeIs('admin.settings.edit') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Edit All Settings</a>
                                        </li>
                                   @endif --}}
                              </ul>
                         </div>
                    </li>
               @endif

               {{-- <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarCategory" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCategory">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:clipboard-list-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Category </span>
                    </a>
                    <div class="collapse" id="sidebarCategory">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="category-list.php">List</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="category-edit.php">Edit</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="category-add.php">Create</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarInventory" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarInventory">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Inventory </span>
                    </a>
                    <div class="collapse" id="sidebarInventory">
                         <ul class="nav sub-navbar-nav">

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="inventory-warehouse.php">Warehouse</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="inventory-received-orders.php">Received Orders</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarOrders" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarOrders">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:bag-smile-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Orders </span>
                    </a>
                    <div class="collapse" id="sidebarOrders">
                         <ul class="nav sub-navbar-nav">

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="orders-list.php">List</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="order-detail.php">Details</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="order-cart.php">Cart</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="order-checkout.php">Check Out</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarPurchases" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPurchases">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:card-send-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Purchases </span>
                    </a>
                    <div class="collapse" id="sidebarPurchases">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="purchase-list.php">List</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="purchase-order.php">Order</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="purchase-returns.php">Return</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarAttributes" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAttributes">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:confetti-minimalistic-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Attributes </span>
                    </a>
                    <div class="collapse" id="sidebarAttributes">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="attributes-list.php">List</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="attributes-edit.php">Edit</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="attributes-add.php">Create</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarInvoice" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarInvoice">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Invoices </span>
                    </a>
                    <div class="collapse" id="sidebarInvoice">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="invoice-list.php">List</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="invoice-details.php">Details</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="invoice-add.php">Create</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-title mt-2">Users</li>

               <li class="nav-item">
                    <a class="nav-link" href="pages-profile.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:chat-square-like-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Profile </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarRoles" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarRoles">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:user-speak-rounded-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Roles </span>
                    </a>
                    <div class="collapse" id="sidebarRoles">
                         <ul class="nav sub-navbar-nav">
                              <ul class="nav sub-navbar-nav">
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="role-list.php">List</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="role-edit.php">Edit</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="role-add.php">Create</a>
                                   </li>
                              </ul>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="pages-permissions.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:checklist-minimalistic-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Permissions </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarCustomers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCustomers">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Customers </span>
                    </a>
                    <div class="collapse" id="sidebarCustomers">
                         <ul class="nav sub-navbar-nav">

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="customer-list.php">List</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="customer-detail.php">Details</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarSellers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSellers">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Sellers </span>
                    </a>
                    <div class="collapse" id="sidebarSellers">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="seller-list.php">List</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="seller-details.php">Details</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="seller-edit.php">Edit</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="seller-add.php">Create</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-title mt-2">Other</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarCoupons" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCoupons">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:leaf-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Coupons </span>
                    </a>
                    <div class="collapse" id="sidebarCoupons">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="coupons-list.php">List</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="coupons-add.php">Add</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="pages-review.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:chat-square-like-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Reviews </span>
                    </a>
               </li>

               <li class="menu-title mt-2">Other Apps</li>

               <li class="nav-item">
                    <a class="nav-link" href="apps-chat.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:chat-round-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Chat </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="apps-email.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:mailbox-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Email </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="apps-calendar.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:calendar-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Calendar </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="apps-todo.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:checklist-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Todo </span>
                    </a>
               </li>

               <li class="menu-title mt-2">Support</li>

               <li class="nav-item">
                    <a class="nav-link" href="help-center.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:help-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Help Center </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="pages-faqs.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:question-circle-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> FAQs </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="privacy-policy.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Privacy Policy </span>
                    </a>
               </li>

               <li class="menu-title mt-2">Custom</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarPages" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPages">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:gift-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Pages </span>
                    </a>
                    <div class="collapse" id="sidebarPages">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-starter.php">Welcome</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-comingsoon.php">Coming Soon</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-timeline.php">Timeline</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-pricing.php">Pricing</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-maintenance.php">Maintenance</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-404.php">404 Error</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-404-alt.php">404 Error (alt)</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarAuthentication" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAuthentication">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:lock-keyhole-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Authentication </span>
                    </a>
                    <div class="collapse" id="sidebarAuthentication">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="auth-signin.php">Sign In</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="auth-signup.php">Sign Up</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="auth-password.php">Reset Password</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="auth-lock-screen.php">Lock Screen</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="widgets.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:atom-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text">Widgets</span>
                         <span class="badge bg-info badge-pill text-end">9+</span>
                    </a>
               </li>

               <li class="menu-title mt-2">Components</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarBaseUI" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarBaseUI">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:bookmark-square-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Base UI </span>
                    </a>
                    <div class="collapse" id="sidebarBaseUI">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-accordion.php">Accordion</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-alerts.php">Alerts</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-avatar.php">Avatar</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-badge.php">Badge</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-breadcrumb.php">Breadcrumb</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-buttons.php">Buttons</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-card.php">Card</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-carousel.php">Carousel</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-collapse.php">Collapse</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-dropdown.php">Dropdown</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-list-group.php">List Group</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-modal.php">Modal</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-tabs.php">Tabs</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-offcanvas.php">Offcanvas</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-pagination.php">Pagination</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-placeholders.php">Placeholders</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-popovers.php">Popovers</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-progress.php">Progress</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-scrollspy.php">Scrollspy</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-spinners.php">Spinners</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-toasts.php">Toasts</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="ui-tooltips.php">Tooltips</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarExtendedUI" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarExtendedUI">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:case-round-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Advanced UI </span>
                    </a>
                    <div class="collapse" id="sidebarExtendedUI">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="extended-ratings.php">Ratings</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="extended-sweetalert.php">Sweet Alert</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="extended-swiper-silder.php">Swiper Slider</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="extended-scrollbar.php">Scrollbar</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="extended-toastify.php">Toastify</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarCharts" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCharts">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:pie-chart-2-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Charts </span>
                    </a>
                    <div class="collapse" id="sidebarCharts">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-area.php">Area</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-bar.php">Bar</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-bubble.php">Bubble</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-candlestick.php">Candlestick</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-column.php">Column</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-heatmap.php">Heatmap</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-line.php">Line</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-mixed.php">Mixed</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-timeline.php">Timeline</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-boxplot.php">Boxplot</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-treemap.php">Treemap</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-pie.php">Pie</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-radar.php">Radar</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-radialbar.php">RadialBar</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-scatter.php">Scatter</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="charts-apex-polar-area.php">Polar Area</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarForms" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarForms">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:book-bookmark-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Forms </span>
                    </a>
                    <div class="collapse" id="sidebarForms">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="forms-basic.php">Basic Elements</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="forms-checkbox-radio.php">Checkbox &amp; Radio</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="forms-choices.php">Choice Select</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="forms-clipboard.php">Clipboard</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="forms-flatepicker.php">Flatepicker</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="forms-validation.php">Validation</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="forms-wizard.php">Wizard</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="forms-fileuploads.php">File Upload</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="forms-editors.php">Editors</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="forms-input-mask.php">Input Mask</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="forms-range-slider.php">Slider</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarTables" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTables">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:tuning-2-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Tables </span>
                    </a>
                    <div class="collapse" id="sidebarTables">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="tables-basic.php">Basic Tables</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="tables-gridjs.php">Grid Js</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarIcons" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarIcons">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:ufo-2-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Icons </span>
                    </a>
                    <div class="collapse" id="sidebarIcons">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="icons-boxicons.php">Boxicons</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="icons-solar.php">Solar Icons</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarMaps" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMaps">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:streets-map-point-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Maps </span>
                    </a>
                    <div class="collapse" id="sidebarMaps">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="maps-google.php">Google Maps</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="maps-vector.php">Vector Maps</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0);">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:volleyball-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text">Badge Menu</span>
                         <span class="badge bg-danger badge-pill text-end">1</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarMultiLevelDemo" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMultiLevelDemo">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:share-circle-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Menu Item </span>
                    </a>
                    <div class="collapse" id="sidebarMultiLevelDemo">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="javascript:void(0);">Menu Item 1</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link  menu-arrow" href="#sidebarItemDemoSubItem" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarItemDemoSubItem">
                                        <span> Menu Item 2 </span>
                                   </a>
                                   <div class="collapse" id="sidebarItemDemoSubItem">
                                        <ul class="nav sub-navbar-nav">
                                             <li class="sub-nav-item">
                                                  <a class="sub-nav-link" href="javascript:void(0);">Menu Sub item</a>
                                             </li>
                                        </ul>
                                   </div>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link disabled" href="javascript:void(0);">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:user-block-rounded-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Disable Item </span>
                    </a>
               </li> --}}
          </ul>
     </div>
</div>

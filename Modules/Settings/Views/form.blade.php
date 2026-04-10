@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" enctype="multipart/form-data" action="{{ $activeSection ? route('admin.settings.section.update', $activeSection) : route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="d-flex commons-ticky-template-toolbar justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $activeSection ? $sections[$activeSection]['title'] : 'Edit Settings' }}</h4>
                    <p class="text-muted mb-0">{{ $activeSection ? $sections[$activeSection]['description'] : 'Update each settings section here and save when you’re ready.' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.settings.show', $activeSection ? ['section' => $activeSection] : []) }}" class="btn btn-light">Back to Overview</a>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12" @if($activeSection && $activeSection !== 'mail') style="display:none;" @endif>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-1">Mail (SMTP) Settings</h5>
                            <p class="text-muted mb-0">These values are applied at runtime for enquiry emails and other outgoing mail.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Mail Mailer</label>
                                    <select name="mail_mailer" class="form-select @error('mail_mailer') is-invalid @enderror">
                                        @foreach($mailers as $value => $label)
                                            <option value="{{ $value }}" @selected(old('mail_mailer', $settings['mail_mailer'] ?? 'smtp') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('mail_mailer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mail Host</label>
                                    <input type="text" name="mail_host" class="form-control @error('mail_host') is-invalid @enderror" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}">
                                    @error('mail_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mail Port</label>
                                    <input type="number" name="mail_port" class="form-control @error('mail_port') is-invalid @enderror" value="{{ old('mail_port', $settings['mail_port'] ?? '') }}">
                                    @error('mail_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mail Username</label>
                                    <input type="text" name="mail_username" class="form-control @error('mail_username') is-invalid @enderror" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}">
                                    @error('mail_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mail Password</label>
                                    <div class="input-group">
                                        <input type="password" name="mail_password" id="mail-password-input" class="form-control @error('mail_password') is-invalid @enderror" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" autocomplete="new-password">
                                        <button type="button" class="btn d-flex align-items-center" id="toggle-mail-password" aria-label="Show mail password" style="border:1px solid #d8dfe7;">
                                            <iconify-icon icon="solar:eye-line-duotone" width="18" height="18"></iconify-icon>
                                        </button>
                                        @error('mail_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-text">Saved securely in encrypted form. Use the eye button to show or hide it while editing.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mail Encryption</label>
                                    <select name="mail_encryption" class="form-select @error('mail_encryption') is-invalid @enderror">
                                        @foreach($encryptions as $value => $label)
                                            <option value="{{ $value }}" @selected((string) old('mail_encryption', $settings['mail_encryption'] ?? '') === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('mail_encryption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mail From Address</label>
                                    <input type="email" name="mail_from_address" class="form-control @error('mail_from_address') is-invalid @enderror" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}">
                                    @error('mail_from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mail From Name</label>
                                    <input type="text" name="mail_from_name" class="form-control @error('mail_from_name') is-invalid @enderror" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}">
                                    @error('mail_from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            @if($activeSection === 'mail')
                                <hr class="my-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <div>
                                        <h6 class="mb-1">SMTP Test</h6>
                                        <p class="text-muted mb-0">Send a real test email using the currently saved SMTP configuration.</p>
                                    </div>
                                </div>

                                @if(session('mail_test_status'))
                                    @php($mailStatus = session('mail_test_status'))
                                    <div class="alert alert-{{ $mailStatus['type'] === 'danger' ? 'danger' : 'success' }}">
                                        {{ $mailStatus['message'] }}
                                    </div>
                                @endif

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Test Email Address</label>
                                        <input type="email" name="test_email" class="form-control @error('test_email') is-invalid @enderror" value="{{ old('test_email', $settings['mail_from_address'] ?? $settings['site_email'] ?? '') }}">
                                        @error('test_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" name="section_action" value="test_mail" class="btn btn-primary w-60">Send Test Email</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12" @if($activeSection && $activeSection !== 'general') style="display:none;" @endif>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-1">General Settings</h5>
                            <p class="text-muted mb-0">Site identity, contact details, formatting defaults, and localization defaults.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror" value="{{ old('site_name', $settings['site_name'] ?? '') }}">
                                    @error('site_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site Tagline</label>
                                    <input type="text" name="site_tagline" class="form-control @error('site_tagline') is-invalid @enderror" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}">
                                    @error('site_tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site Logo</label>
                                    <input type="file" name="site_logo" class="form-control @error('site_logo') is-invalid @enderror" accept="image/*">
                                    @error('site_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if(!empty($settings['site_logo']))
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Site Logo" class="auto-contrast-logo auto-contrast-logo-preview" style="max-height:60px;">
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site Favicon</label>
                                    <input type="file" name="site_favicon" class="form-control @error('site_favicon') is-invalid @enderror" accept="image/*">
                                    @error('site_favicon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if(!empty($settings['site_favicon']))
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $settings['site_favicon']) }}" alt="Site Favicon" class="auto-contrast-logo auto-contrast-logo-preview" style="max-height:40px;">
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Site Email</label>
                                    <input type="email" name="site_email" class="form-control @error('site_email') is-invalid @enderror" value="{{ old('site_email', $settings['site_email'] ?? '') }}">
                                    @error('site_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Site Phone</label>
                                    <input type="text" name="site_phone" class="form-control @error('site_phone') is-invalid @enderror" value="{{ old('site_phone', $settings['site_phone'] ?? '') }}">
                                    @error('site_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Default Language</label>
                                    <input type="text" name="default_language" class="form-control @error('default_language') is-invalid @enderror" value="{{ old('default_language', $settings['default_language'] ?? 'en') }}" @disabled(true)>
                                    @error('default_language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Timezone</label>
                                    <select name="timezone" class="form-select @error('timezone') is-invalid @enderror">
                                        <option value="">Select timezone</option>
                                        @foreach($timezones as $timezone)
                                            <option value="{{ $timezone }}" @selected(old('timezone', $settings['timezone'] ?? config('app.timezone')) === $timezone)>{{ $timezone }}</option>
                                        @endforeach
                                    </select>
                                    @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date Format</label>
                                    <input type="text" name="date_format" class="form-control @error('date_format') is-invalid @enderror" value="{{ old('date_format', $settings['date_format'] ?? 'd M Y') }}" @disabled(true)>
                                    @error('date_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Time Format</label>
                                    <input type="text" name="time_format" class="form-control @error('time_format') is-invalid @enderror" value="{{ old('time_format', $settings['time_format'] ?? 'h:i A') }}" @disabled(false)>
                                    @error('time_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Site Address</label>
                                    <textarea name="site_address" rows="3" class="form-control @error('site_address') is-invalid @enderror">{{ old('site_address', $settings['site_address'] ?? '') }}</textarea>
                                    @error('site_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Website Footer Copyright</label>
                                    <textarea name="footer_copyright" rows="2" class="form-control @error('footer_copyright') is-invalid @enderror" placeholder="© 2026 Your Company. All rights reserved.">{{ old('footer_copyright', $settings['footer_copyright'] ?? '') }}</textarea>
                                    <div class="form-text">This will be used in the website footer.</div>
                                    @error('footer_copyright')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Custom CSS</label>
                                    <textarea name="custom_css" rows="8" class="form-control @error('custom_css') is-invalid @enderror" placeholder="body {&#10;    background: #f8fafc;&#10;}">{{ old('custom_css', $settings['custom_css'] ?? '') }}</textarea>
                                    <div class="form-text">Use this for frontend website custom CSS.</div>
                                    @error('custom_css')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Custom JS</label>
                                    <textarea name="custom_js" rows="8" class="form-control @error('custom_js') is-invalid @enderror" placeholder="document.addEventListener('DOMContentLoaded', function () {&#10;    console.log('Frontend custom JS loaded');&#10;});">{{ old('custom_js', $settings['custom_js'] ?? '') }}</textarea>
                                    <div class="form-text">Use this for frontend website custom JavaScript.</div>
                                    @error('custom_js')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12" @if($activeSection && $activeSection !== 'system') style="display:none;" @endif>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-1">System Settings</h5>
                            <p class="text-muted mb-0">Application environment, URL, maintenance mode, debug mode, and cache behavior.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Maintenance Mode</label>
                                    <select name="maintenance_mode" class="form-select @error('maintenance_mode') is-invalid @enderror">
                                        <option value="0" @selected((string) old('maintenance_mode', $settings['maintenance_mode'] ?? '0') === '0')>Off</option>
                                        <option value="1" @selected((string) old('maintenance_mode', $settings['maintenance_mode'] ?? '0') === '1')>On</option>
                                    </select>
                                    @error('maintenance_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Debug Mode</label>
                                    <select name="debug_mode" class="form-select @error('debug_mode') is-invalid @enderror">
                                        <option value="0" @selected((string) old('debug_mode', $settings['debug_mode'] ?? '0') === '0')>Off</option>
                                        <option value="1" @selected((string) old('debug_mode', $settings['debug_mode'] ?? '0') === '1')>On</option>
                                    </select>
                                    @error('debug_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cache Enabled</label>
                                    <select name="cache_enabled" class="form-select @error('cache_enabled') is-invalid @enderror">
                                        <option value="1" @selected((string) old('cache_enabled', $settings['cache_enabled'] ?? '1') === '1')>Enabled</option>
                                        <option value="0" @selected((string) old('cache_enabled', $settings['cache_enabled'] ?? '1') === '0')>Disabled</option>
                                    </select>
                                    @error('cache_enabled')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">App Environment</label>
                                    <select name="app_env" class="form-select @error('app_env') is-invalid @enderror">
                                        <option value="">Select environment</option>
                                        <option value="local" @selected(old('app_env', $settings['app_env'] ?? config('app.env')) === 'local')>Local</option>
                                        <option value="development" @selected(old('app_env', $settings['app_env'] ?? config('app.env')) === 'development')>Development</option>
                                        <option value="staging" @selected(old('app_env', $settings['app_env'] ?? config('app.env')) === 'staging')>Staging</option>
                                        <option value="production" @selected(old('app_env', $settings['app_env'] ?? config('app.env')) === 'production')>Production</option>
                                    </select>
                                    @error('app_env')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">App URL</label>
                                    <input type="url" name="app_url" class="form-control @error('app_url') is-invalid @enderror" value="{{ old('app_url', $settings['app_url'] ?? config('app.url')) }}" placeholder="https://example.com">
                                    @error('app_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12" @if($activeSection && $activeSection !== 'admin') style="display:none;" @endif>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-1">Admin Panel Settings</h5>
                            <p class="text-muted mb-0">Backend branding, theme colors, and custom CSS/JS for the admin panel.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Admin Panel Logo</label>
                                    <input type="file" name="admin_logo" class="form-control @error('admin_logo') is-invalid @enderror" accept="image/*">
                                    @error('admin_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if(!empty($settings['admin_logo']))
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $settings['admin_logo']) }}" alt="Admin Panel Logo" class="auto-contrast-logo auto-contrast-logo-preview" style="max-height:60px; max-width:100%;">
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Dark Mode</label>
                                    <select name="admin_dark_mode_enabled" class="form-select @error('admin_dark_mode_enabled') is-invalid @enderror">
                                        <option value="1" @selected((string) old('admin_dark_mode_enabled', $settings['admin_dark_mode_enabled'] ?? '0') === '1')>Enabled</option>
                                        <option value="0" @selected((string) old('admin_dark_mode_enabled', $settings['admin_dark_mode_enabled'] ?? '0') === '0')>Disabled</option>
                                    </select>
                                    <div class="form-text">Enabled applies dark mode. Disabled applies light mode.</div>
                                    @error('admin_dark_mode_enabled')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <h6 class="mb-1">Admin Panel Colors</h6>
                                            <p class="text-muted mb-0">Customize the admin panel accent, topbar, sidebar, and background colors.</p>
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="reset-admin-colors">Reset Colors</button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Primary Color</label>
                                    <input type="color" name="admin_primary_color" class="form-control form-control-color @error('admin_primary_color') is-invalid @enderror" value="{{ old('admin_primary_color', $settings['admin_primary_color'] ?? 'var(--bs-primary)') }}" data-default-color="var(--bs-primary)">
                                    @error('admin_primary_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Topbar Background</label>
                                    <input type="color" name="admin_topbar_bg" class="form-control form-control-color @error('admin_topbar_bg') is-invalid @enderror" value="{{ old('admin_topbar_bg', $settings['admin_topbar_bg'] ?? '#f9f7f7') }}" data-default-color="#f9f7f7">
                                    @error('admin_topbar_bg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Topbar Text</label>
                                    <input type="color" name="admin_topbar_text_color" class="form-control form-control-color @error('admin_topbar_text_color') is-invalid @enderror" value="{{ old('admin_topbar_text_color', $settings['admin_topbar_text_color'] ?? '#707793') }}" data-default-color="#707793">
                                    @error('admin_topbar_text_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Sidebar Background</label>
                                    <input type="color" name="admin_sidebar_bg" class="form-control form-control-color @error('admin_sidebar_bg') is-invalid @enderror" value="{{ old('admin_sidebar_bg', $settings['admin_sidebar_bg'] ?? '#262d34') }}" data-default-color="#262d34">
                                    @error('admin_sidebar_bg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Sidebar Text</label>
                                    <input type="color" name="admin_sidebar_text_color" class="form-control form-control-color @error('admin_sidebar_text_color') is-invalid @enderror" value="{{ old('admin_sidebar_text_color', $settings['admin_sidebar_text_color'] ?? '#9097a7') }}" data-default-color="#9097a7">
                                    @error('admin_sidebar_text_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Sidebar Hover</label>
                                    <input type="color" name="admin_sidebar_hover_color" class="form-control form-control-color @error('admin_sidebar_hover_color') is-invalid @enderror" value="{{ old('admin_sidebar_hover_color', $settings['admin_sidebar_hover_color'] ?? '#ffffff') }}" data-default-color="#ffffff">
                                    @error('admin_sidebar_hover_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Page Background</label>
                                    <input type="color" name="admin_page_bg" class="form-control form-control-color @error('admin_page_bg') is-invalid @enderror" value="{{ old('admin_page_bg', $settings['admin_page_bg'] ?? '#f9f7f7') }}" data-default-color="#f9f7f7">
                                    @error('admin_page_bg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12" @if($activeSection && $activeSection !== 'modules') style="display:none;" @endif>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-1">Module Settings</h5>
                            <p class="text-muted mb-0">Disable modules cleanly so related menu items, dashboard widgets, and routes stop loading.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($moduleDefinitions as $moduleKey => $moduleDefinition)
                                    @php($settingKey = \App\Support\ModuleRegistry::settingKey($moduleKey))
                                    @php($isInstalled = \App\Support\ModuleRegistry::installed($moduleKey))
                                    <div class="col-md-4">
                                        <label class="form-label">{{ $moduleDefinition['name'] }}</label>
                                        <select name="{{ $settingKey }}" class="form-select @error($settingKey) is-invalid @enderror" @disabled(! $isInstalled)>
                                            <option value="1" @selected((string) old($settingKey, $settings[$settingKey] ?? '1') === '1')>Enabled</option>
                                            <option value="0" @selected((string) old($settingKey, $settings[$settingKey] ?? '1') === '0')>Disabled</option>
                                        </select>
                                        <div class="form-text">
                                            {{ $isInstalled ? 'Installed module. Changes are applied on the next request.' : 'Module files were not found, so this option is unavailable.' }}
                                        </div>
                                        @error($settingKey)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6" @if($activeSection && $activeSection !== 'social') style="display:none;" @endif>
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-1">Social Media Settings</h5>
                            <p class="text-muted mb-0">Public profile links used in the website header, footer, or contact sections.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Facebook URL</label>
                                    <input type="url" name="facebook_url" class="form-control @error('facebook_url') is-invalid @enderror" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}" placeholder="https://www.facebook.com/your-page">
                                    @error('facebook_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Instagram URL</label>
                                    <input type="url" name="instagram_url" class="form-control @error('instagram_url') is-invalid @enderror" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}" placeholder="https://www.instagram.com/your-handle">
                                    @error('instagram_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Twitter URL</label>
                                    <input type="url" name="twitter_url" class="form-control @error('twitter_url') is-invalid @enderror" value="{{ old('twitter_url', $settings['twitter_url'] ?? '') }}" placeholder="https://x.com/your-handle">
                                    @error('twitter_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">LinkedIn URL</label>
                                    <input type="url" name="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror" value="{{ old('linkedin_url', $settings['linkedin_url'] ?? '') }}" placeholder="https://www.linkedin.com/company/your-company">
                                    @error('linkedin_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">YouTube URL</label>
                                    <input type="url" name="youtube_url" class="form-control @error('youtube_url') is-invalid @enderror" value="{{ old('youtube_url', $settings['youtube_url'] ?? '') }}" placeholder="https://www.youtube.com/@your-channel">
                                    @error('youtube_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6" @if($activeSection && $activeSection !== 'analytics') style="display:none;" @endif>
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-1">Analytics Settings</h5>
                            <p class="text-muted mb-0">Store analytics IDs here so you can render them on the frontend later from one source of truth.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Google Analytics ID</label>
                                    <input type="text" name="google_analytics_id" class="form-control @error('google_analytics_id') is-invalid @enderror" value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}">
                                    @error('google_analytics_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Google Tag Manager ID</label>
                                    <input type="text" name="google_tag_manager_id" class="form-control @error('google_tag_manager_id') is-invalid @enderror" value="{{ old('google_tag_manager_id', $settings['google_tag_manager_id'] ?? '') }}">
                                    @error('google_tag_manager_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Facebook Pixel ID</label>
                                    <input type="text" name="facebook_pixel_id" class="form-control @error('facebook_pixel_id') is-invalid @enderror" value="{{ old('facebook_pixel_id', $settings['facebook_pixel_id'] ?? '') }}">
                                    @error('facebook_pixel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            @if($activeSection === 'analytics')
                                <hr class="my-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <div>
                                        <h6 class="mb-1">Analytics Validation</h6>
                                        <p class="text-muted mb-0">Check whether the saved IDs are present and match common expected formats.</p>
                                    </div>
                                    <button type="submit" name="section_action" value="validate_analytics" class="btn btn-outline-primary">Validate Analytics</button>
                                </div>

                                @if(session('analytics_validation'))
                                    <div class="row g-3">
                                        @foreach(session('analytics_validation') as $result)
                                            <div class="col-12">
                                                <div class="alert alert-{{ $result['status'] }}">
                                                    <strong>{{ $result['label'] }}:</strong> {{ $result['message'] }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const resetButton = document.getElementById('reset-admin-colors');
    const mailPasswordInput = document.getElementById('mail-password-input');
    const mailPasswordToggle = document.getElementById('toggle-mail-password');

    if (mailPasswordInput && mailPasswordToggle) {
        mailPasswordToggle.addEventListener('click', function () {
            const isHidden = mailPasswordInput.type === 'password';

            mailPasswordInput.type = isHidden ? 'text' : 'password';
            mailPasswordToggle.setAttribute('aria-label', isHidden ? 'Hide mail password' : 'Show mail password');
            mailPasswordToggle.innerHTML = isHidden
                ? '<iconify-icon icon="solar:eye-line-duotone" width="18" height="18"></iconify-icon>'
                : '<iconify-icon icon="solar:eye-closed-line-duotone" width="18" height="18"></iconify-icon>';
        });
    }

    if (!resetButton) {
        return;
    }

    resetButton.addEventListener('click', function () {
        document.querySelectorAll('input[type="color"][data-default-color]').forEach(function (input) {
            input.value = input.dataset.defaultColor;
        });
    });
});
</script>
@endpush

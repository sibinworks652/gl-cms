@php($adminUser = auth('admin')->user())
<div class="d-flex gap-2 flex-wrap mb-4">
    @if($adminUser?->can('email.view'))
        <a href="{{ route('admin.email.settings.edit') }}" class="btn btn-sm {{ request()->routeIs('admin.email.settings.*') ? 'btn-primary' : 'btn-light' }}">General Settings</a>
    @endif
    @if($adminUser?->can('email.templates.view'))
        <a href="{{ route('admin.email.templates.index') }}" class="btn btn-sm {{ request()->routeIs('admin.email.templates.*') ? 'btn-primary' : 'btn-light' }}">Templates</a>
    @endif
    @if($adminUser?->can('email.testing'))
        <a href="{{ route('admin.email.testing.index') }}" class="btn btn-sm {{ request()->routeIs('admin.email.testing.*') ? 'btn-primary' : 'btn-light' }}">Test Email & SMTP</a>
    @endif
</div>

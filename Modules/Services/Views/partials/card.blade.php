@php($targetService = $service ?? null)

@if($targetService)
    <article class="service-card">
        @if($targetService->image_url)
            <a href="{{ route('services.show', $targetService->slug) }}" class="service-card-media">
                <img src="{{ $targetService->image_url }}" alt="{{ $targetService->title }}">
            </a>
        @endif
        <div class="service-card-body">
            @if($targetService->rendered_icon)
                <div class="service-card-icon">{!! $targetService->rendered_icon !!}</div>
            @endif
            @if($targetService->category)
                <div class="service-card-category">{{ $targetService->category->name }}</div>
            @endif
            <h3><a href="{{ route('services.show', $targetService->slug) }}">{{ $targetService->title }}</a></h3>
            <p>{{ $targetService->short_description }}</p>
            <a href="{{ route('services.show', $targetService->slug) }}" class="service-card-link">Explore service</a>
        </div>
    </article>
@endif

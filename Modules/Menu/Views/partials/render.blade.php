@if ($items->isNotEmpty())
    <ul class="{{ $class }}">
        @foreach ($items as $item)

            <li class="{{ $item->childrenRecursive->isNotEmpty() ? 'dropdown' : '' }}">

                <a href="{{ $item->resolved_url }}"
                   @if($item->open_in_new_tab) target="_blank" @endif>

                    {{ $item->title }}

                    @if ($item->childrenRecursive->isNotEmpty())
                        <span class="menu-arrow-header {{ ($class ?? '') === 'submenu' ? 'arrow-right' : 'arrow-down' }}">
                            <iconify-icon 
                                icon="{{ ($class ?? '') === 'submenu' ? 'solar:alt-arrow-right-outline' : 'solar:alt-arrow-down-outline' }}">
                            </iconify-icon>
                        </span>
                    @endif

                </a>

                {{-- CHILDREN --}}
                @if ($item->childrenRecursive->isNotEmpty())
                    @include('menu::partials.render', [
                        'items' => $item->childrenRecursive,
                        'class' => 'submenu'
                    ])
                @endif

            </li>

        @endforeach
    </ul>
@endif
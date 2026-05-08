@props([
    'open' => false,
    'title' => null,
    'icon' => null,
    'meta' => [],
    'size' => 'md',
])

@if($open)
    @php
        $modalClass = trim('evo-ui-modal evo-ui-modal--' . preg_replace('/[^a-z0-9_-]/i', '', (string) $size));
    @endphp

    <div
        class="evo-ui-modal-backdrop"
        role="presentation"
        wire:click.self="closeModal"
        wire:keydown.escape.window="closeModal"
    >
        <section
            {{ $attributes->merge(['class' => $modalClass]) }}
            role="dialog"
            aria-modal="true"
            aria-labelledby="evo-ui-modal-title"
            @click.stop
        >
            <header class="evo-ui-modal__header">
                <div class="evo-ui-modal__title" id="evo-ui-modal-title">
                    @if($icon)
                        <x-evo::icon :name="$icon" />
                    @endif
                    <span>{{ $title }}</span>
                </div>

                <div class="evo-ui-modal__header-actions">
                    @if(!empty($meta))
                        <dl class="evo-ui-modal__meta">
                            @foreach($meta as $item)
                                @php
                                    $label = __((string) ($item['label'] ?? ''));
                                    $value = trim((string) ($item['value'] ?? ''));
                                    $iconName = trim((string) ($item['icon'] ?? ''));
                                @endphp

                                @if($value !== '')
                                    <div class="evo-ui-modal__meta-item" title="{{ $label }}">
                                        @if($iconName !== '')
                                            <x-evo::icon :name="$iconName" />
                                        @endif
                                        <dt>{{ $label }}</dt>
                                        <dd>{{ $value }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    @endif

                    <button type="button" class="evo-ui-modal__close" title="@lang('evo::global.action_cancel')" aria-label="@lang('evo::global.action_cancel')" wire:click="closeModal">
                        <x-evo::icon name="x" />
                    </button>
                </div>
            </header>

            {{ $slot }}
        </section>
    </div>
@endif

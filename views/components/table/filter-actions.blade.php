@props([
    'clearAction' => '',
    'clearable' => true,
])

<div class="evo-ui-filter-menu__actions">
    @if($clearable)
        <button type="button" class="evo-ui-filter-action" title="@lang('evo::global.filter_clear')" aria-label="@lang('evo::global.filter_clear')" @click="{{ $clearAction }}">
            <x-evo::icon name="x" />
        </button>
    @endif
    <x-evo::table.filter-apply />
</div>

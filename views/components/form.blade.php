@props([
    'controller',
    'config',
    'tabs' => [],
    'sections' => [],
    'actions' => [],
    'saved' => false,
    'dirty' => false,
])

@php
    $sectionsByTab = collect($sections)->groupBy(fn ($section) => $section['tab'] ?? ($tabs[0]['name'] ?? 'default'));
    $initialTab = $tabs[0]['name'] ?? 'default';
@endphp

<section
    class="evo-ui-form-surface evo-ui-form-surface--{{ $config['variant'] ?? 'default' }}"
    wire:loading.class="is-loading"
    x-data="{
        selected: @js($initialTab),
        dirty: $wire.entangle('dirty').live,
        initialSnapshot: '',
        init() {
            this.$nextTick(() => {
                this.initialSnapshot = this.formSnapshot();
            });
        },
        formSnapshot() {
            const form = this.$refs.form;

            if (!form) {
                return '';
            }

            const values = Array.from(form.elements)
                .filter((field) => field.name && !field.disabled)
                .map((field) => {
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        return [field.name, field.value, field.checked ? '1' : '0'];
                    }

                    if (field.tagName === 'SELECT' && field.multiple) {
                        return [field.name, Array.from(field.selectedOptions).map((option) => option.value)];
                    }

                    return [field.name, field.value ?? ''];
                })
                .sort((left, right) => JSON.stringify(left).localeCompare(JSON.stringify(right)));

            return JSON.stringify(values);
        },
        captureSnapshot() {
            this.initialSnapshot = this.formSnapshot();
            this.dirty = false;
        },
        markDirty() {
            this.dirty = true;
        },
        afterSaved(event) {
            if (event.detail?.preset && event.detail.preset !== @js($controller->preset)) {
                return;
            }

            this.captureSnapshot();
        },
        afterReset(event) {
            if (event.detail?.preset && event.detail.preset !== @js($controller->preset)) {
                return;
            }

            this.$nextTick(() => this.captureSnapshot());
        }
    }"
    x-bind:data-evo-form-dirty="dirty ? 'true' : 'false'"
    x-on:evo-ui:form.saved.window="afterSaved($event)"
    x-on:evo-ui:form.reset.window="afterReset($event)"
>
    <div class="evo-ui-form-heading">
        <div>
            <h2>
                @if(!empty($config['icon']))
                    <x-evo::icon :name="$config['icon']" />
                @endif
                <span>{{ $controller->formTitle($config) }}</span>
                @if($controller->formMeta($config))
                    <small>{{ $controller->formMeta($config) }}</small>
                @endif
            </h2>
        </div>

        @if($actions)
            <div class="evo-ui-form-toolbar" aria-label="@lang('evo::global.form_actions')">
                @foreach($actions as $action)
                    @if(($action['type'] ?? null) === 'save')
                        <x-evo::button
                            :icon="$action['icon'] ?? 'device-floppy'"
                            :label="__($action['label'] ?? 'evo::global.action_save')"
                            :tone="$action['tone'] ?? 'success'"
                            :variant="$action['variant'] ?? 'soft'"
                            :icon-only="(bool) ($action['icon_only'] ?? true)"
                            type="submit"
                            form="evo-ui-form-{{ $config['key'] ?? 'default' }}"
                            x-bind:disabled="!dirty"
                            x-bind:class="{ 'is-disabled': !dirty }"
                            wire:loading.attr="disabled"
                            wire:target="save"
                        />
                    @elseif(!empty($action['url']))
                        <x-evo::button
                            :icon="$action['icon'] ?? null"
                            :label="__($action['label'] ?? '')"
                            :href="$controller->actionUrl($action)"
                            :tone="$action['tone'] ?? 'neutral'"
                            icon-only
                        />
                    @elseif(($action['type'] ?? null) === 'reset')
                        <x-evo::button
                            :icon="$action['icon'] ?? 'rotate'"
                            :label="__($action['label'] ?? 'evo::global.action_reset')"
                            wire:click="resetForm"
                            icon-only
                            x-bind:disabled="!dirty"
                            x-bind:class="{ 'is-disabled': !dirty }"
                        />
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    @if($saved)
        <div class="evo-ui-alert evo-ui-alert--success" role="status">
            <x-evo::icon name="circle-check" />
            <span>@lang('evo::global.form_saved')</span>
        </div>
    @endif

    <form
        id="evo-ui-form-{{ $config['key'] ?? 'default' }}"
        class="evo-ui-form"
        wire:submit.prevent="save"
        data-evo-form
        x-ref="form"
        x-on:input.debounce.50ms="markDirty()"
        x-on:change="markDirty()"
    >
        @if($tabs)
            <div class="evo-ui-form-tabs" role="tablist">
                @foreach($tabs as $tab)
                    <button
                        type="button"
                        role="tab"
                        class="evo-ui-form-tab"
                        :class="{ 'is-active': selected === @js($tab['name']) }"
                        :aria-selected="selected === @js($tab['name'])"
                        title="{{ __($tab['label']) }}"
                        x-on:click="selected = @js($tab['name'])"
                    >
                        @if(!empty($tab['icon']))
                            <x-evo::icon :name="$tab['icon']" />
                        @endif
                        <span>{{ __($tab['label']) }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        @foreach($sectionsByTab as $tabName => $tabSections)
            <div
                class="evo-ui-form-tab-panel"
                x-show="selected === @js($tabName)"
                data-evo-form-tab-panel="{{ $tabName }}"
                wire:key="form-tab-panel-{{ $tabName }}"
            >
                @foreach($tabSections as $section)
                    @php
                        $showSectionHeader = ($section['show_header'] ?? ($config['section_headers'] ?? true)) !== false;
                        $sectionSpan = (int) ($section['span'] ?? 12);
                        $sectionSpan = in_array($sectionSpan, [3, 4, 6, 8, 12], true) ? $sectionSpan : 12;
                    @endphp

                    <x-evo::card
                        class="evo-ui-form-section evo-ui-form-section--span-{{ $sectionSpan }}"
                        :label="$showSectionHeader ? ($section['label'] ?? null) : null"
                        :icon="$showSectionHeader ? ($section['icon'] ?? null) : null"
                        wire:key="form-section-{{ $section['key'] ?? \Illuminate\Support\Str::slug(__($section['label'] ?? 'section')) }}"
                    >
                        <div class="evo-ui-form-grid">
                            @foreach($section['fields'] ?? [] as $field)
                                <x-evo::form.field :controller="$controller" :field="$field" wire:key="form-field-{{ $field['name'] }}" />
                            @endforeach
                        </div>
                    </x-evo::card>
                @endforeach
            </div>
        @endforeach
    </form>
</section>

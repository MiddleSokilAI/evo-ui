@php
    $assetDir = trim((string) config('evo-ui.assets.path', 'assets/modules/evo-ui'), '/');
    $assetBase = rtrim(EVO_SITE_URL, '/') . '/' . $assetDir . '/';
    $assetPath = rtrim(EVO_BASE_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $assetDir) . DIRECTORY_SEPARATOR;
    $cssVersion = is_file($assetPath . 'evo-ui.css') ? filemtime($assetPath . 'evo-ui.css') : time();
    $jsVersion = is_file($assetPath . 'evo-ui.js') ? filemtime($assetPath . 'evo-ui.js') : time();
    $themeConfig = config('evo-ui.theme', []);
    $themes = $themeConfig['themes'] ?? ['evolight', 'evolightness', 'evodark', 'evodarkness'];
    $darkThemes = $themeConfig['dark_themes'] ?? ['evodark', 'evodarkness'];
    $defaultLight = $themeConfig['default_light'] ?? 'evolight';
    $defaultDark = $themeConfig['default_dark'] ?? 'evodark';
    $managerThemeModes = $themeConfig['manager_modes'] ?? ['', 'lightness', 'light', 'dark', 'darkness'];
@endphp

<link href="{{ $assetBase }}evo-ui.css?v={{ $cssVersion }}" rel="stylesheet">
@if(class_exists(\Livewire\Livewire::class))
    @livewireStyles
@endif
<script>
    window.EvoUI = window.EvoUI || {};
    window.EvoUI.config = Object.assign(window.EvoUI.config || {}, {
        themes: @json($themes),
        darkThemes: @json($darkThemes),
        defaultLight: @json($defaultLight),
        defaultDark: @json($defaultDark),
        managerThemeModes: @json($managerThemeModes),
        labels: Object.assign(window.EvoUI.config && window.EvoUI.config.labels || {}, {
            actionCancel: @json(__('evo::global.action_cancel')),
            actionDelete: @json(__('evo::global.action_delete')),
            deleteConfirmTitle: @json(__('evo::global.delete_confirm_title')),
            deleteConfirmMessage: @json(__('evo::global.delete_confirm_message'))
        })
    });
</script>
<script src="{{ $assetBase }}evo-ui.js?v={{ $jsVersion }}" defer></script>
@if(class_exists(\EvoUI\Support\LivewireAssets::class))
    {!! \EvoUI\Support\LivewireAssets::scripts() !!}
@endif

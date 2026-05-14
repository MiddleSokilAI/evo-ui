<?php

namespace EvoUI\Support;

class ManagerContext
{
    public function language(): string
    {
        $language = session()->get('mgrUsrConfigSet.manager_language');

        if (is_string($language) && $language !== '') {
            return $language;
        }

        $configured = function_exists('evo') ? evo()->getConfig('manager_language') : null;

        return is_string($configured) && $configured !== '' ? $configured : 'en';
    }

    public function role(): ?int
    {
        $role = session()->get('mgrRole');

        return is_numeric($role) ? (int) $role : null;
    }

    public function can(string $ability): bool
    {
        $permissions = session()->get('mgrPermissions', []);

        if (is_array($permissions) && array_key_exists($ability, $permissions)) {
            return (bool) $permissions[$ability];
        }

        return function_exists('evo') && evo()->hasPermission($ability, 'mgr') === 1;
    }

    public function theme(): string
    {
        return match ($this->managerThemeStyle()) {
            'lightness' => 'evolightness',
            'light' => 'evolight',
            'darkness' => 'evodarkness',
            default => 'evodark',
        };
    }

    public function themeMode(?string $theme = null): string
    {
        return in_array($theme ?: $this->theme(), ['evodark', 'evodarkness'], true) ? 'dark' : 'light';
    }

    public function themeClasses(?string $theme = null): string
    {
        $theme = $theme ?: $this->theme();
        $classes = [$this->themeMode($theme)];

        if ($theme === 'evolightness') {
            $classes[] = 'lightness';
        }

        if ($theme === 'evodarkness') {
            $classes[] = 'darkness';
        }

        return implode(' ', $classes);
    }

    public function themeBackground(?string $theme = null): string
    {
        return match ($theme ?: $this->theme()) {
            'evolight', 'evolightness' => 'oklch(100% 0 0)',
            'evodarkness' => 'oklch(18.5% 0.012 265)',
            default => 'oklch(26.5% 0.011 265)',
        };
    }

    protected function managerThemeStyle(): string
    {
        $managerThemeModes = config('evo-ui.theme.manager_modes', ['', 'lightness', 'light', 'dark', 'darkness']);
        $request = request();
        $cookie = is_object($request) && method_exists($request, 'cookie')
            ? $request->cookie('EVO_themeMode')
            : null;
        $cookieMode = is_numeric($cookie) ? (int) $cookie : 0;

        if ($cookieMode > 0 && isset($managerThemeModes[$cookieMode])) {
            return $managerThemeModes[$cookieMode];
        }

        $systemMode = function_exists('evo') ? (int) evo()->getConfig('manager_theme_mode') : 0;

        if ($systemMode > 0 && isset($managerThemeModes[$systemMode])) {
            return $managerThemeModes[$systemMode];
        }

        return 'dark';
    }
}

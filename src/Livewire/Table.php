<?php

namespace EvoUI\Livewire;

use EvoUI\EvoUI;
use EvoUI\Support\Permissions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Livewire invokes public action methods from Blade/runtime.
 *
 * @noinspection PhpUnused
 */
class Table extends Component
{
    public string $preset = 'site_content';

    #[Url(as: 'q', history: true, except: '')]
    public string $search = '';

    #[Url(as: 'page', history: true, except: 1)]
    public int $page = 1;

    #[Url(as: 'sort', history: true, except: '')]
    public string $sort = '';

    #[Url(as: 'dir', history: true, except: 'asc')]
    public string $direction = 'asc';

    #[Url(as: 'f', history: true, except: [])]
    public array $filterState = [];

    #[Url(as: 'view', history: true, except: 'table')]
    public string $viewMode = 'table';

    #[Url(as: 'perPage', history: true, except: 0)]
    public int $perPage = 0;
    public array $selected = [];

    public function mount(string $preset = 'site_content'): void
    {
        $this->preset = $preset;
        $this->syncConfigState();
        $this->fillDefaultFilters();
    }

    public function updatedSearch(): void
    {
        $this->resetPageState();
    }

    public function updatedPerPage(): void
    {
        $this->syncConfigState();
        $this->resetPageState();
    }

    public function setFilter(string $state, string $value): void
    {
        $filter = $this->filterByState($state);
        $allowed = collect($filter['options'] ?? [])->pluck('value')->all();

        $this->filterState[$state] = in_array($value, $allowed, true)
            ? $value
            : (string) ($filter['default'] ?? 'all');

        $this->resetPageState();
        $this->dispatchTableEvent('filter.changed', ['state' => $state]);
    }

    public function applySelectFilter(string $state, string $value): void
    {
        $filter = $this->filterByState($state);
        $allowed = collect($this->optionsFor($filter))->pluck('id')->map(fn ($id) => (string) $id)->all();

        $this->filterState[$state] = in_array($value, $allowed, true)
            ? $value
            : (string) ($filter['default'] ?? '');

        $this->resetPageState();
        $this->dispatchTableEvent('filter.changed', ['state' => $state]);
    }

    public function applyMultiFilter(string $state, array $values): void
    {
        $filter = $this->filterByState($state);
        $allowed = collect($this->optionsFor($filter))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->filterState[$state] = collect($values)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && in_array($id, $allowed, true))
            ->unique()
            ->values()
            ->all();

        $this->resetPageState();
        $this->dispatchTableEvent('filter.changed', ['state' => $state]);
    }

    public function applyDateRangeFilter(string $state, string $from = '', string $to = ''): void
    {
        $this->filterState[$state] = [
            'from' => $this->normalizeDate($from),
            'to' => $this->normalizeDate($to),
        ];

        $this->resetPageState();
        $this->dispatchTableEvent('filter.changed', ['state' => $state]);
    }

    public function toggleFilter(string $state): void
    {
        $filter = $this->filterByState($state);
        $default = $filter['default'] ?? false;
        $selected = $filter['selected'] ?? true;

        $this->filterState[$state] = $this->filterValue($filter) === $selected ? $default : $selected;
        $this->resetPageState();
        $this->dispatchTableEvent('filter.changed', ['state' => $state]);
    }

    public function selectRow(int $id): void
    {
        $this->selected = $this->selected === [$id] ? [] : [$id];
        $this->dispatchTableEvent('selection.changed', ['selected' => $this->selected]);
    }

    public function switchView(string $viewMode): void
    {
        $this->viewMode = in_array($viewMode, $this->tableViews(), true) ? $viewMode : 'table';
        $this->dispatchTableEvent('view.changed', ['view' => $this->viewMode]);
    }

    public function setSort(string $key): void
    {
        $column = $this->sortableColumn($key);

        if (!$column) {
            return;
        }

        if ($this->sort === $key) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $key;
            $this->direction = (string) ($column['default_direction'] ?? 'asc');
        }

        $this->resetPageState();
        $this->dispatchTableEvent('sort.changed', ['sort' => $this->sort, 'direction' => $this->direction]);
    }

    public function firstPage(): void
    {
        $this->page = 1;
    }

    public function previousPage(): void
    {
        $this->page = max(1, $this->page - 1);
    }

    public function goToPage(int $page): void
    {
        $this->page = max(1, min($page, $this->lastPageNumber()));
    }

    public function nextPage(): void
    {
        $this->page = min($this->page + 1, $this->lastPageNumber());
    }

    public function goLastPage(): void
    {
        $this->page = $this->lastPageNumber();
    }

    public function render(): View
    {
        $this->syncConfigState();

        $query = $this->query();
        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $this->perPage));

        if ($this->page > $lastPage) {
            $this->page = $lastPage;
        }

        return view('evo::livewire.table', [
            'controller' => $this,
            'config' => $this->visibleConfig(),
            'preset' => $this->preset,
            'selected' => $this->selected,
            'viewMode' => $this->viewMode,
            'perPage' => $this->perPage,
            'perPageOptions' => $this->perPageOptions(),
            'page' => $this->page,
            'sort' => $this->sort,
            'direction' => $this->direction,
            'rows' => $this->orderedQuery($query)->forPage($this->page, $this->perPage)->get(),
            'filters' => $this->filters(),
            'filterOptions' => $this->filterOptions(),
            'filterLabels' => $this->filterLabels(),
            'total' => $total,
            'lastPage' => $lastPage,
            'paginationItems' => $this->paginationItems($lastPage),
            'managerUrl' => rtrim(EVO_MANAGER_URL, '/') . '/',
        ]);
    }

    public function filterValue(array $filter): mixed
    {
        $state = $filter['state'];

        return $this->filterState[$state] ?? ($filter['default'] ?? null);
    }

    public function actionHref(array $action, ?object $row = null, ?int $selectedId = null): ?string
    {
        if (!$this->allowed($action)) {
            return null;
        }

        $id = $row?->getAttribute('id') ?? $selectedId;

        if (($action['selection'] ?? null) === 'single' && !$id) {
            return null;
        }

        $url = (string) ($action['url'] ?? '');
        $url = str_replace('{id}', (string) $id, $url);

        return rtrim(EVO_MANAGER_URL, '/') . '/' . ltrim($url, '/');
    }

    public function cellValue(object $row, array $column): mixed
    {
        if ($relation = ($column['relation'] ?? null)) {
            $related = $row->getRelationValue($relation);
            $value = $related?->getAttribute($column['field'] ?? 'id');

            return $value !== null && $value !== '' ? $value : ($column['empty'] ?? null);
        }

        return $row->getAttribute($column['field'] ?? $column['key']);
    }

    public function cellDisplay(object $row, array $column): string
    {
        $value = $this->cellValue($row, $column);

        if (($column['cell'] ?? null) === 'date') {
            return $this->formatTimestamp($value, $column['format'] ?? 'd.m.Y H:i');
        }

        return (string) ($value ?? '');
    }

    public function customCellView(array $column): ?string
    {
        $view = app(EvoUI::class)->tableCellView($column);

        return $view && view()->exists($view) ? $view : null;
    }

    public function sortableColumns(): array
    {
        return collect($this->tableConfig('columns', []))
            ->filter(fn ($column) => $this->isEnabled('enabled_columns', $column['key'] ?? null))
            ->filter(fn ($column) => $this->allowed($column))
            ->filter(fn ($column) => ($column['sortable'] ?? false) && !empty($column['key']))
            ->values()
            ->all();
    }

    protected function query(): Builder
    {
        $model = $this->tableConfig('model');
        $query = $model::query();

        foreach ($this->tableConfig('with', []) as $relation) {
            $query->with($relation);
        }

        foreach ($this->tableConfig('where', []) as $where) {
            $this->applyWhere($query, $where);
        }

        foreach ($this->filters() as $filter) {
            $this->applyFilter($query, $filter);
        }

        $search = trim($this->search);
        $searchFields = $this->tableConfig('search.fields', []);

        if ($search !== '' && $searchFields !== []) {
            $query->where(function (Builder $query) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    if ($field === 'id' && is_numeric($search)) {
                        $query->orWhere($field, (int) $search);
                        continue;
                    }

                    if ($field !== 'id') {
                        $query->orWhere($field, 'like', '%' . $search . '%');
                    }
                }
            });
        }

        return $query;
    }

    protected function orderedQuery(Builder $query): Builder
    {
        $sortColumn = $this->sortableColumn($this->sort);

        if ($sortColumn) {
            $query->orderBy($sortColumn['sort_field'] ?? $sortColumn['field'] ?? $sortColumn['key'], $this->direction === 'desc' ? 'desc' : 'asc');
        }

        foreach ($this->tableConfig('order_by', []) as $order) {
            if ($sortColumn && ($order[0] ?? null) === ($sortColumn['sort_field'] ?? $sortColumn['field'] ?? $sortColumn['key'])) {
                continue;
            }

            $query->orderBy($order[0], $order[1] ?? 'asc');
        }

        return $query;
    }

    protected function applyFilter(Builder $query, array $filter): void
    {
        $state = $filter['state'] ?? null;

        if (!$state) {
            return;
        }

        if (($filter['type'] ?? null) === 'select') {
            $value = $this->filterValue($filter);

            if ($value !== null && $value !== '') {
                $query->where($filter['field'], $value);
            }

            return;
        }

        if (($filter['type'] ?? null) === 'multi-select') {
            $values = array_map('intval', (array) $this->filterValue($filter));

            if ($values !== []) {
                $query->whereIn($filter['field'], $values);
            }

            return;
        }

        if (($filter['type'] ?? null) === 'segmented') {
            $value = $this->filterValue($filter);
            $option = collect($filter['options'] ?? [])->firstWhere('value', $value);

            foreach (($option['where'] ?? []) as $where) {
                $this->applyWhere($query, $where);
            }
        }

        if (($filter['type'] ?? null) === 'toggle' && $this->filterValue($filter) === ($filter['selected'] ?? true)) {
            foreach (($filter['where'] ?? []) as $where) {
                $this->applyWhere($query, $where);
            }
        }

        if (($filter['type'] ?? null) === 'date-range') {
            $value = (array) $this->filterValue($filter);
            $from = $this->dateStartTimestamp($value['from'] ?? '');
            $to = $this->dateEndTimestamp($value['to'] ?? '');

            if ($from) {
                $query->where($filter['field'], '>=', $from);
            }

            if ($to) {
                $query->where($filter['field'], '<=', $to);
            }
        }
    }

    protected function applyWhere(Builder $query, array $where): void
    {
        if (Arr::isAssoc($where) || !is_array($where[0] ?? null)) {
            $query->where(...$where);
            return;
        }

        foreach ($where as $item) {
            $this->applyWhere($query, $item);
        }
    }

    protected function filterOptions(): array
    {
        return collect($this->filters())
            ->filter(fn ($filter) => in_array($filter['type'] ?? null, ['select', 'multi-select'], true))
            ->mapWithKeys(fn ($filter) => [$filter['state'] => $this->optionsFor($filter)])
            ->all();
    }

    protected function filterLabels(): array
    {
        return collect($this->filters())
            ->filter(fn ($filter) => in_array($filter['type'] ?? null, ['select', 'multi-select'], true))
            ->mapWithKeys(function ($filter) {
                $state = $filter['state'];
                $selected = array_map('intval', (array) $this->filterValue($filter));

                return [
                    $state => collect($this->optionsFor($filter))
                        ->whereIn('id', $selected)
                        ->pluck('name')
                        ->values()
                        ->all(),
                ];
            })
            ->all();
    }

    protected function optionsFor(array $filter): array
    {
        if (!empty($filter['options'])) {
            return collect($filter['options'])
                ->map(fn ($option) => [
                    'id' => $option['value'],
                    'name' => __($option['label'] ?? $option['value']),
                ])
                ->values()
                ->all();
        }

        $source = $filter['options_source'] ?? [];
        $model = $source['model'] ?? null;

        if (!$model) {
            return [];
        }

        $value = $source['value'] ?? 'id';
        $label = $source['label'] ?? 'name';
        $query = $model::query()->select([$value, $label]);

        foreach (($source['order_by'] ?? [[$label, 'asc']]) as $order) {
            $query->orderBy($order[0], $order[1] ?? 'asc');
        }

        return $query->get()
            ->map(fn ($row) => ['id' => (int) $row->{$value}, 'name' => (string) $row->{$label}])
            ->values()
            ->all();
    }

    protected function filterByState(string $state): array
    {
        return collect($this->filters())->firstWhere('state', $state) ?? [];
    }

    protected function filters(): array
    {
        return collect($this->tableConfig('filters', []))
            ->filter(fn ($filter) => $this->isEnabled('enabled_filters', $filter['state'] ?? null))
            ->filter(fn ($filter) => $this->allowed($filter))
            ->values()
            ->all();
    }

    protected function visibleConfig(): array
    {
        $config = $this->tableConfig();
        $config['columns'] = collect($config['columns'] ?? [])
            ->filter(fn ($column) => $this->isEnabled('enabled_columns', $column['key'] ?? null))
            ->filter(fn ($column) => $this->allowed($column))
            ->values()
            ->all();
        $config['filters'] = $this->filters();
        $config['actions'] = collect($config['actions'] ?? [])
            ->filter(fn ($action) => $this->allowed($action))
            ->values()
            ->all();
        $config['row_actions'] = collect($config['row_actions'] ?? [])
            ->filter(fn ($action) => $this->allowed($action))
            ->values()
            ->all();

        return $config;
    }

    protected function allowed(array $definition): bool
    {
        return app(Permissions::class)->allows($definition);
    }

    protected function isEnabled(string $key, mixed $value): bool
    {
        $enabled = $this->tableConfig($key, []);

        return $enabled === [] || in_array((string) $value, array_map('strval', (array) $enabled), true);
    }

    protected function tableConfig(?string $key = null, mixed $default = null): mixed
    {
        $config = config('evo-ui.tables.' . $this->preset, []);

        return $key ? data_get($config, $key, $default) : $config;
    }

    protected function resetPageState(): void
    {
        $this->page = 1;
    }

    protected function dispatchTableEvent(string $event, array $detail = []): void
    {
        $this->dispatch('evo-ui:table.' . $event, ...['preset' => $this->preset, ...$detail]);
    }

    protected function syncConfigState(): void
    {
        $defaultPerPage = max(1, (int) $this->tableConfig('per_page', 10));
        $perPageOptions = $this->perPageOptions();
        $fallback = in_array($defaultPerPage, $perPageOptions, true) ? $defaultPerPage : $perPageOptions[0];

        $this->perPage = in_array($this->perPage, $perPageOptions, true) ? $this->perPage : $fallback;
        $this->switchView($this->viewMode ?: (string) $this->tableConfig('default_view', 'table'));
    }

    protected function perPageOptions(): array
    {
        $options = (array) $this->tableConfig('per_page_options', [5, 10, 20, 50, 100]);
        $default = max(1, (int) $this->tableConfig('per_page', 10));

        return collect([...$options, $default])
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => in_array($value, [5, 10, 20, 50, 100], true) || $value === $default)
            ->unique()
            ->sort()
            ->values()
            ->all() ?: [5, 10, 20, 50, 100];
    }

    protected function tableViews(): array
    {
        $views = (array) $this->tableConfig('views', ['table']);

        return collect($views)
            ->map(fn ($view) => (string) $view)
            ->filter(fn ($view) => in_array($view, ['table', 'list'], true))
            ->unique()
            ->values()
            ->all() ?: ['table'];
    }

    protected function fillDefaultFilters(): void
    {
        foreach ($this->filters() as $filter) {
            $state = $filter['state'] ?? null;

            if (!$state || array_key_exists($state, $this->filterState)) {
                continue;
            }

            $this->filterState[$state] = ($filter['type'] ?? null) === 'multi-select'
                ? []
                : ($filter['default'] ?? (($filter['type'] ?? null) === 'date-range' ? ['from' => '', 'to' => ''] : null));
        }
    }

    protected function sortableColumn(string $key): ?array
    {
        if ($key === '') {
            return null;
        }

        $column = collect($this->sortableColumns())
            ->first(fn ($column) => ($column['key'] ?? null) === $key);

        return is_array($column) ? $column : null;
    }

    protected function normalizeDate(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
    }

    protected function dateStartTimestamp(string $date): ?int
    {
        return $this->normalizeDate($date) ? strtotime($date . ' 00:00:00') : null;
    }

    protected function dateEndTimestamp(string $date): ?int
    {
        return $this->normalizeDate($date) ? strtotime($date . ' 23:59:59') : null;
    }

    protected function formatTimestamp(mixed $value, string $format): string
    {
        $timestamp = (int) $value;

        return $timestamp > 0 ? date($format, $timestamp) : '-';
    }

    protected function lastPageNumber(): int
    {
        return max(1, (int) ceil($this->query()->count() / $this->perPage));
    }

    protected function paginationItems(int $lastPage): array
    {
        if ($lastPage <= 9) {
            return range(1, $lastPage);
        }

        $pages = collect([1, 2, $lastPage - 1, $lastPage]);

        foreach (range($this->page - 1, $this->page + 1) as $page) {
            if ($page > 0 && $page <= $lastPage) {
                $pages->push($page);
            }
        }

        return $pages
            ->unique()
            ->sort()
            ->values()
            ->reduce(function (array $items, int $page) {
                $previous = end($items);

                if (is_int($previous) && $page - $previous > 1) {
                    $items[] = '...';
                }

                $items[] = $page;

                return $items;
            }, []);
    }
}

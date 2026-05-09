@php
    $wireTarget = $config['wire_target'] ?? 'filters,setCategory,setStatus,setAssignee,switchDisplay,resetFilters';
    $projectOptions = collect($projects)
        ->filter(fn ($project) => (int) ($project['id'] ?? 0) > 0)
        ->map(fn ($project) => [
            'id' => (int) $project['id'],
            'name' => __($project['label'] ?? ''),
            'icon' => (string) ($project['icon'] ?? 'folder'),
        ])
        ->values()
        ->all();
    $categoryOptions = collect($categories)
        ->filter(fn ($category) => (int) ($category['id'] ?? 0) > 0)
        ->map(fn ($category) => ['id' => (int) $category['id'], 'name' => __($category['label'] ?? '')])
        ->values()
        ->all();
    $statusOptions = collect($statuses)
        ->filter(fn ($status) => (int) ($status['id'] ?? 0) > 0)
        ->map(fn ($status) => ['id' => (int) $status['id'], 'name' => __($status['label'] ?? '')])
        ->values()
        ->all();
    $phaseOptions = collect($phases)
        ->filter(fn ($phase) => (int) ($phase['id'] ?? 0) > 0)
        ->map(fn ($phase) => [
            'id' => (int) $phase['id'],
            'name' => __($phase['label'] ?? ''),
            'icon' => (string) ($phase['icon'] ?? 'circle-dot'),
        ])
        ->values()
        ->all();
    $priorityOptions = collect($priorities)
        ->filter(fn ($priority) => (int) ($priority['id'] ?? 0) > 0)
        ->map(fn ($priority) => [
            'id' => (int) $priority['id'],
            'name' => __($priority['label'] ?? ''),
            'icon' => (string) ($priority['icon'] ?? 'minus'),
        ])
        ->values()
        ->all();
    $assigneeOptions = collect($assignees)
        ->filter(fn ($assignee) => (int) ($assignee['id'] ?? $assignee['value'] ?? 0) !== 0)
        ->map(fn ($assignee) => [
            'id' => (int) ($assignee['id'] ?? $assignee['value'] ?? 0),
            'name' => __($assignee['label'] ?? ''),
            'icon' => (string) ($assignee['icon'] ?? 'user'),
            'avatar_url' => (string) ($assignee['avatar_url'] ?? ''),
            'initials' => (string) ($assignee['initials'] ?? ''),
        ])
        ->values()
        ->all();
    $assignmentOptions = $assigneeOptions;
    $projectCount = count((array) ($filters['project_ids'] ?? []));
    $categoryCount = count((array) ($filters['category_ids'] ?? []));
    $statusCount = count((array) ($filters['status_ids'] ?? []));
    $phaseCount = count((array) ($filters['phase_ids'] ?? []));
    $priorityCount = count((array) ($filters['priority_ids'] ?? []));
    $assigneeCount = count((array) ($filters['assignee_ids'] ?? []));
    $projectFilterPayload = [
        'state' => 'project_ids',
        'selected' => array_map('intval', (array) ($filters['project_ids'] ?? [])),
        'options' => $projectOptions,
    ];
    $categoryFilterPayload = [
        'state' => 'category_ids',
        'selected' => array_map('intval', (array) ($filters['category_ids'] ?? [])),
        'options' => $categoryOptions,
    ];
    $statusFilterPayload = [
        'state' => 'status_ids',
        'selected' => array_map('intval', (array) ($filters['status_ids'] ?? [])),
        'options' => $statusOptions,
    ];
    $phaseFilterPayload = [
        'state' => 'phase_ids',
        'selected' => array_map('intval', (array) ($filters['phase_ids'] ?? [])),
        'options' => $phaseOptions,
    ];
    $priorityFilterPayload = [
        'state' => 'priority_ids',
        'selected' => array_map('intval', (array) ($filters['priority_ids'] ?? [])),
        'options' => $priorityOptions,
    ];
    $assigneeFilterPayload = [
        'state' => 'assignee_ids',
        'selected' => array_map('intval', (array) ($filters['assignee_ids'] ?? [])),
        'options' => $assigneeOptions,
    ];
@endphp

<section
    class="evo-ui-issue-workspace"
    data-evo-issue-workspace="{{ $config['key'] ?? $preset }}"
    wire:loading.class="is-loading"
    wire:target="{{ $wireTarget }}"
>
    <div class="evo-ui-issue-workspace__progress" wire:loading aria-hidden="true"></div>

    <div class="evo-ui-table-toolbar">
        <div class="evo-ui-table-actions" aria-label="@lang('evo::global.table_actions')">
            <button type="button" class="evo-ui-btn evo-ui-btn--icon evo-ui-btn--success" title="@lang('evo::global.action_add')" aria-label="@lang('evo::global.action_add')" wire:click="createIssue">
                <x-evo::icon name="plus" class="evo-ui-btn__icon" />
            </button>
        </div>

        <div class="evo-ui-table-filters">
            <details
                class="evo-ui-filter-dropdown"
                x-data='EvoUI.multiFilter(@json($projectFilterPayload))'
                @click.outside="reset(); $root.open = false"
            >
                <summary title="@lang('dIssues::global.all_projects')" aria-label="@lang('dIssues::global.all_projects')">
                    <x-evo::icon name="folder" class="evo-ui-filter-icon" />
                    @if($projectCount > 0)
                        <span class="evo-ui-filter-badge" aria-hidden="true">{{ $projectCount }}</span>
                    @endif
                </summary>

                <div class="evo-ui-filter-menu">
                    <input type="search" class="evo-ui-input" x-model="search" placeholder="@lang('dIssues::global.filter_by_project')" autocomplete="off">

                    <div class="evo-ui-filter-options">
                        @foreach($projectOptions as $option)
                            <label wire:key="issue-project-filter-{{ $option['id'] }}" x-show="visibleOptions().some((option) => option.id === {{ (int) $option['id'] }})">
                                <input type="checkbox" value="{{ $option['id'] }}" :checked="selected.includes({{ (int) $option['id'] }})" @change="toggle({{ (int) $option['id'] }})">
                                <x-evo::icon :name="$option['icon'] ?? 'folder'" />
                                <span>{{ $option['name'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="evo-ui-filter-menu__actions">
                        <button type="button" class="evo-ui-filter-action" :title="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" :aria-label="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" @click="toggleAllVisible">
                            <x-evo::icon name="checks" x-show="!allVisibleSelected()" />
                            <x-evo::icon name="x" x-show="allVisibleSelected()" />
                        </button>
                        <x-evo::table.filter-apply />
                    </div>
                </div>
            </details>

            <details
                class="evo-ui-filter-dropdown"
                x-data='EvoUI.multiFilter(@json($categoryFilterPayload))'
                @click.outside="reset(); $root.open = false"
            >
                <summary title="@lang('dIssues::global.all_categories')" aria-label="@lang('dIssues::global.all_categories')">
                    <x-evo::icon name="tag" class="evo-ui-filter-icon" />
                    @if($categoryCount > 0)
                        <span class="evo-ui-filter-badge" aria-hidden="true">{{ $categoryCount }}</span>
                    @endif
                </summary>

                <div class="evo-ui-filter-menu">
                    <input type="search" class="evo-ui-input" x-model="search" placeholder="@lang('evo::global.filters')" autocomplete="off">

                    <div class="evo-ui-filter-options">
                        @foreach($categoryOptions as $option)
                            <label wire:key="issue-category-filter-{{ $option['id'] }}" x-show="visibleOptions().some((option) => option.id === {{ (int) $option['id'] }})">
                                <input type="checkbox" value="{{ $option['id'] }}" :checked="selected.includes({{ (int) $option['id'] }})" @change="toggle({{ (int) $option['id'] }})">
                                <span>{{ $option['name'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="evo-ui-filter-menu__actions">
                        <button type="button" class="evo-ui-filter-action" :title="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" :aria-label="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" @click="toggleAllVisible">
                            <x-evo::icon name="checks" x-show="!allVisibleSelected()" />
                            <x-evo::icon name="x" x-show="allVisibleSelected()" />
                        </button>
                        <x-evo::table.filter-apply />
                    </div>
                </div>
            </details>

            <details
                class="evo-ui-filter-dropdown"
                x-data='EvoUI.multiFilter(@json($statusFilterPayload))'
                @click.outside="reset(); $root.open = false"
            >
                <summary title="@lang('dIssues::global.all_statuses')" aria-label="@lang('dIssues::global.all_statuses')">
                    <x-evo::icon name="progress" class="evo-ui-filter-icon" />
                    @if($statusCount > 0)
                        <span class="evo-ui-filter-badge" aria-hidden="true">{{ $statusCount }}</span>
                    @endif
                </summary>

                <div class="evo-ui-filter-menu">
                    <input type="search" class="evo-ui-input" x-model="search" placeholder="@lang('evo::global.filters')" autocomplete="off">

                    <div class="evo-ui-filter-options">
                        @foreach($statusOptions as $option)
                            <label wire:key="issue-status-filter-{{ $option['id'] }}" x-show="visibleOptions().some((option) => option.id === {{ (int) $option['id'] }})">
                                <input type="checkbox" value="{{ $option['id'] }}" :checked="selected.includes({{ (int) $option['id'] }})" @change="toggle({{ (int) $option['id'] }})">
                                <span>{{ $option['name'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="evo-ui-filter-menu__actions">
                        <button type="button" class="evo-ui-filter-action" :title="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" :aria-label="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" @click="toggleAllVisible">
                            <x-evo::icon name="checks" x-show="!allVisibleSelected()" />
                            <x-evo::icon name="x" x-show="allVisibleSelected()" />
                        </button>
                        <x-evo::table.filter-apply />
                    </div>
                </div>
            </details>

            <details
                class="evo-ui-filter-dropdown"
                x-data='EvoUI.multiFilter(@json($phaseFilterPayload))'
                @click.outside="reset(); $root.open = false"
            >
                <summary title="@lang('dIssues::global.all_phases')" aria-label="@lang('dIssues::global.all_phases')">
                    <x-evo::icon name="list-checks" class="evo-ui-filter-icon" />
                    @if($phaseCount > 0)
                        <span class="evo-ui-filter-badge" aria-hidden="true">{{ $phaseCount }}</span>
                    @endif
                </summary>

                <div class="evo-ui-filter-menu">
                    <input type="search" class="evo-ui-input" x-model="search" placeholder="@lang('dIssues::global.filter_by_phase')" autocomplete="off">

                    <div class="evo-ui-filter-options">
                        @foreach($phaseOptions as $option)
                            <label wire:key="issue-phase-filter-{{ $option['id'] }}" x-show="visibleOptions().some((option) => option.id === {{ (int) $option['id'] }})">
                                <input type="checkbox" value="{{ $option['id'] }}" :checked="selected.includes({{ (int) $option['id'] }})" @change="toggle({{ (int) $option['id'] }})">
                                <span>{{ $option['name'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="evo-ui-filter-menu__actions">
                        <button type="button" class="evo-ui-filter-action" :title="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" :aria-label="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" @click="toggleAllVisible">
                            <x-evo::icon name="checks" x-show="!allVisibleSelected()" />
                            <x-evo::icon name="x" x-show="allVisibleSelected()" />
                        </button>
                        <x-evo::table.filter-apply />
                    </div>
                </div>
            </details>

            <details
                class="evo-ui-filter-dropdown"
                x-data='EvoUI.multiFilter(@json($priorityFilterPayload))'
                @click.outside="reset(); $root.open = false"
            >
                <summary title="@lang('dIssues::global.all_priorities')" aria-label="@lang('dIssues::global.all_priorities')">
                    <x-evo::icon name="flag" class="evo-ui-filter-icon" />
                    @if($priorityCount > 0)
                        <span class="evo-ui-filter-badge" aria-hidden="true">{{ $priorityCount }}</span>
                    @endif
                </summary>

                <div class="evo-ui-filter-menu">
                    <input type="search" class="evo-ui-input" x-model="search" placeholder="@lang('dIssues::global.filter_by_priority')" autocomplete="off">

                    <div class="evo-ui-filter-options">
                        @foreach($priorityOptions as $option)
                            <label wire:key="issue-priority-filter-{{ $option['id'] }}" x-show="visibleOptions().some((option) => option.id === {{ (int) $option['id'] }})">
                                <input type="checkbox" value="{{ $option['id'] }}" :checked="selected.includes({{ (int) $option['id'] }})" @change="toggle({{ (int) $option['id'] }})">
                                <x-evo::icon :name="$option['icon'] ?? 'minus'" />
                                <span>{{ $option['name'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="evo-ui-filter-menu__actions">
                        <button type="button" class="evo-ui-filter-action" :title="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" :aria-label="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" @click="toggleAllVisible">
                            <x-evo::icon name="checks" x-show="!allVisibleSelected()" />
                            <x-evo::icon name="x" x-show="allVisibleSelected()" />
                        </button>
                        <x-evo::table.filter-apply />
                    </div>
                </div>
            </details>

            <details
                class="evo-ui-filter-dropdown"
                x-data='EvoUI.multiFilter(@json($assigneeFilterPayload))'
                @click.outside="reset(); $root.open = false"
            >
                <summary title="@lang('dIssues::global.all_assignees')" aria-label="@lang('dIssues::global.all_assignees')">
                    <x-evo::icon name="user" class="evo-ui-filter-icon" />
                    @if($assigneeCount > 0)
                        <span class="evo-ui-filter-badge" aria-hidden="true">{{ $assigneeCount }}</span>
                    @endif
                </summary>

                <div class="evo-ui-filter-menu">
                    <input type="search" class="evo-ui-input" x-model="search" placeholder="@lang('dIssues::global.filter_by_assignee')" autocomplete="off">

                    <div class="evo-ui-filter-options">
                        @foreach($assigneeOptions as $option)
                            <label wire:key="issue-assignee-filter-{{ $option['id'] }}" x-show="visibleOptions().some((option) => option.id === {{ (int) $option['id'] }})">
                                <input type="checkbox" value="{{ $option['id'] }}" :checked="selected.includes({{ (int) $option['id'] }})" @change="toggle({{ (int) $option['id'] }})">
                                <span>{{ $option['name'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="evo-ui-filter-menu__actions">
                        <button type="button" class="evo-ui-filter-action" :title="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" :aria-label="allVisibleSelected() ? @js(__('evo::global.filter_clear')) : @js(__('evo::global.filter_all'))" @click="toggleAllVisible">
                            <x-evo::icon name="checks" x-show="!allVisibleSelected()" />
                            <x-evo::icon name="x" x-show="allVisibleSelected()" />
                        </button>
                        <x-evo::table.filter-apply />
                    </div>
                </div>
            </details>
        </div>

        <div class="evo-ui-table-controls">
            <div class="evo-ui-view-toggle" role="group" aria-label="{{ __('dIssues::global.archive_filter') }}">
                @foreach($archiveModes as $archiveMode)
                    @php
                        $archiveValue = (string) ($archiveMode['value'] ?? 'active');
                    @endphp
                    <button
                        type="button"
                        class="{{ ($filters['archive'] ?? 'active') === $archiveValue ? 'is-active' : '' }}"
                        title="{{ __($archiveMode['label'] ?? $archiveValue) }}"
                        aria-label="{{ __($archiveMode['label'] ?? $archiveValue) }}"
                        wire:click="setArchive('{{ $archiveValue }}')"
                    >
                        @if(!empty($archiveMode['icon']))
                            <x-evo::icon :name="$archiveMode['icon']" />
                        @endif
                        <span class="evo-ui-sr-only">{{ __($archiveMode['label'] ?? $archiveValue) }}</span>
                    </button>
                @endforeach
            </div>

            <div class="evo-ui-view-toggle" role="group" aria-label="{{ __('evo::global.issue_display') }}">
                @foreach($displays as $display)
                    @php
                        $value = (string) ($display['value'] ?? 'kanban');
                    @endphp
                    <button
                        type="button"
                        class="{{ $filters['display'] === $value ? 'is-active' : '' }}"
                        title="{{ __($display['label'] ?? $value) }}"
                        aria-label="{{ __($display['label'] ?? $value) }}"
                        wire:click="switchDisplay('{{ $value }}')"
                    >
                        @if(!empty($display['icon']))
                            <x-evo::icon :name="$display['icon']" />
                        @endif
                        <span class="evo-ui-sr-only">{{ __($display['label'] ?? $value) }}</span>
                    </button>
                @endforeach
            </div>

            <label class="evo-ui-search" title="@lang('global.search')">
                <x-evo::icon name="search" class="evo-ui-search__icon" />
                <input type="search" wire:model.live.debounce.300ms="filters.search" autocomplete="off" />
            </label>
        </div>
    </div>

    <div class="evo-ui-issue-workspace__viewport" data-evo-issue-display="{{ $filters['display'] }}">
        @if($filters['display'] === 'kanban')
            <div class="evo-ui-issue-kanban" role="list" aria-label="{{ __('evo::global.view_kanban') }}" data-evo-issue-kanban>
                @forelse($kanbanLanes as $lane)
                    @php
                        $laneColor = (string) ($lane['color'] ?? '');
                        $laneStyle = str_starts_with($laneColor, '#') ? '--evo-issue-accent: ' . $laneColor : '';
                        $laneOwner = (array) ($lane['owner'] ?? []);
                        $laneTotal = (int) ($lane['total'] ?? $lane['count'] ?? 0);
                        $laneLoaded = (int) ($lane['loaded'] ?? count((array) ($lane['issues'] ?? [])));
                    @endphp
                    <section class="evo-ui-issue-kanban__lane" role="listitem" data-evo-issue-status="{{ (int) ($lane['id'] ?? 0) }}" @if($laneStyle) style="{{ $laneStyle }}" @endif>
                        <header class="evo-ui-issue-kanban__lane-header">
                            <div class="evo-ui-issue-kanban__lane-title">
                                <x-evo::icon :name="$lane['icon'] ?? 'circle-dot'" />
                                <span>{{ __($lane['label'] ?? '') }}</span>
                            </div>
                            <div class="evo-ui-issue-kanban__lane-actions">
                                @if(!empty($laneOwner['initials']))
                                    <span class="evo-ui-issue-kanban__lane-owner" title="{{ $laneOwner['label'] ?? '' }}">
                                        @if(!empty($laneOwner['avatar_url']))
                                            <img src="{{ $laneOwner['avatar_url'] }}" alt="{{ $laneOwner['label'] ?? '' }}" loading="lazy">
                                        @else
                                            {{ $laneOwner['initials'] }}
                                        @endif
                                    </span>
                                @endif
                                @if(!empty($lane['can_bulk_archive']))
                                    <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.archive_closed_lane')" aria-label="@lang('dIssues::global.archive_closed_lane')" wire:click="archiveStatusIssues({{ (int) ($lane['id'] ?? 0) }})">
                                        <x-evo::icon name="archive" />
                                    </button>
                                @endif
                                <span class="evo-ui-issue-kanban__count" data-evo-issue-count data-evo-issue-total="{{ $laneTotal }}">{{ $laneTotal }}</span>
                            </div>
                        </header>

                        <div
                            class="evo-ui-issue-kanban__cards"
                            data-evo-issue-lane
                            data-status-id="{{ (int) ($lane['id'] ?? 0) }}"
                            data-evo-issue-total="{{ $laneTotal }}"
                            data-evo-issue-loaded="{{ $laneLoaded }}"
                        >
                            @forelse(($lane['issues'] ?? []) as $issue)
                                @php
                                    $category = (array) ($issue['category'] ?? []);
                                    $phase = (array) ($issue['phase'] ?? []);
                                    $project = (array) ($issue['project'] ?? []);
                                    $assignee = (array) ($issue['assignee'] ?? []);
                                    $subtasks = (array) ($issue['subtasks'] ?? []);
                                    $priority = (array) ($issue['priority'] ?? []);
                                    $categoryColor = (string) ($category['color'] ?? '');
                                    $categoryStyle = str_starts_with($categoryColor, '#') ? '--evo-issue-chip: ' . $categoryColor : '';
                                    $phaseColor = (string) ($phase['color'] ?? '');
                                    $phaseStyle = str_starts_with($phaseColor, '#') ? '--evo-issue-chip: ' . $phaseColor : '';
                                    $priorityColor = (string) ($priority['color'] ?? '');
                                    $priorityStyle = str_starts_with($priorityColor, '#') ? '--evo-issue-chip: ' . $priorityColor : '';
                                    $issueId = (int) ($issue['id'] ?? 0);
                                    $issueTitle = trim((string) ($issue['title'] ?? ''));
                                    $issueBody = trim((string) ($issue['body'] ?? ''));
                                @endphp
                                <button
                                    type="button"
                                    class="evo-ui-issue-card"
                                    draggable="true"
                                    data-evo-issue-card
                                    data-issue-id="{{ $issueId }}"
                                    wire:click="selectIssue({{ $issueId }})"
                                    aria-label="{{ $issueTitle !== '' ? $issueTitle : '#' . $issueId }}"
                                >
                                    <span class="evo-ui-issue-card__content">
                                        <span class="evo-ui-issue-card__title">{{ $issueTitle !== '' ? $issueTitle : '#' . $issueId }}</span>

                                        @if($issueBody !== '')
                                            <span class="evo-ui-issue-card__body">{{ $issueBody }}</span>
                                        @endif
                                    </span>

                                    <span class="evo-ui-issue-card__meta">
                                        @if(!empty($project['label']))
                                            <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--muted">
                                                {{ $project['label'] }}
                                            </span>
                                        @endif

                                        @if(!empty($category['label']))
                                            <span class="evo-ui-issue-card__chip" @if($categoryStyle) style="{{ $categoryStyle }}" @endif>
                                                {{ $category['label'] }}
                                            </span>
                                        @endif

                                        @if(!empty($priority['label']))
                                            <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--priority" @if($priorityStyle) style="{{ $priorityStyle }}" @endif>
                                                <x-evo::icon :name="$priority['icon'] ?? 'flag'" />
                                                <span>{{ $priority['label'] }}</span>
                                            </span>
                                        @endif

                                        @if(!empty($phase['label']))
                                            <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--phase" @if($phaseStyle) style="{{ $phaseStyle }}" @endif>
                                                <x-evo::icon :name="$phase['icon'] ?? 'circle-dot'" />
                                                <span>{{ $phase['label'] }}</span>
                                            </span>
                                        @endif

                                        <span class="evo-ui-issue-card__stat" title="@lang('dIssues::global.comments')">
                                            <x-evo::icon name="message-circle" />
                                            <span>{{ (int) ($issue['comments_count'] ?? 0) }}</span>
                                        </span>

                                        @if(!empty($subtasks['has_children']))
                                            <span class="evo-ui-issue-card__stat" title="@lang('dIssues::global.subtasks')">
                                                <x-evo::icon name="list-checks" />
                                                <span>{{ (int) ($subtasks['closed'] ?? 0) }}/{{ (int) ($subtasks['total'] ?? 0) }}</span>
                                            </span>
                                        @endif

                                        <span class="evo-ui-issue-card__stat" title="@lang('dIssues::global.issue_id')">
                                            <x-evo::icon name="hash" />
                                            <span>{{ (int) ($issue['id'] ?? 0) }}</span>
                                        </span>

                                        @if(!empty($assignee['initials']))
                                            <span class="evo-ui-issue-card__assignee" title="{{ $assignee['label'] ?? '' }}">
                                                @if(!empty($assignee['avatar_url']))
                                                    <img src="{{ $assignee['avatar_url'] }}" alt="{{ $assignee['label'] ?? '' }}" loading="lazy">
                                                @else
                                                    {{ $assignee['initials'] }}
                                                @endif
                                            </span>
                                        @endif
                                    </span>
                                </button>
                            @empty
                                <div class="evo-ui-issue-kanban__empty" data-evo-issue-empty>
                                    <x-evo::icon name="circle-dashed" />
                                    <span>@lang('evo::global.table_empty')</span>
                                </div>
                            @endforelse

                        </div>
                    </section>
                @empty
                    <div class="evo-ui-issue-workspace__stage">
                        <x-evo::icon name="columns-3" />
                    </div>
                @endforelse
            </div>
        @endif

        @if($filters['display'] === 'list')
            <div class="evo-ui-issue-split" data-evo-issue-split>
                <aside class="evo-ui-issue-split__list" aria-label="{{ __('evo::global.view_list') }}" data-evo-issue-list>
                    @forelse($issueList as $issue)
                        @php
                            $listStatus = (array) ($issue['status'] ?? []);
                            $listPhase = (array) ($issue['phase'] ?? []);
                            $listCategory = (array) ($issue['category'] ?? []);
                            $listProject = (array) ($issue['project'] ?? []);
                            $listAssignee = (array) ($issue['assignee'] ?? []);
                            $listSubtasks = (array) ($issue['subtasks'] ?? []);
                            $listPriority = (array) ($issue['priority'] ?? []);
                            $listCategoryColor = (string) ($listCategory['color'] ?? '');
                            $listCategoryStyle = str_starts_with($listCategoryColor, '#') ? '--evo-issue-chip: ' . $listCategoryColor : '';
                            $listStatusColor = (string) ($listStatus['color'] ?? '');
                            $listStatusStyle = str_starts_with($listStatusColor, '#') ? '--evo-issue-chip: ' . $listStatusColor : '';
                            $listPhaseColor = (string) ($listPhase['color'] ?? '');
                            $listPhaseStyle = str_starts_with($listPhaseColor, '#') ? '--evo-issue-chip: ' . $listPhaseColor : '';
                            $listPriorityColor = (string) ($listPriority['color'] ?? '');
                            $listPriorityStyle = str_starts_with($listPriorityColor, '#') ? '--evo-issue-chip: ' . $listPriorityColor : '';
                            $isSelected = (int) ($selectedIssue['id'] ?? 0) === (int) ($issue['id'] ?? 0);
                        @endphp

                        <button
                            type="button"
                            class="evo-ui-issue-list-item {{ $isSelected ? 'is-active' : '' }}"
                            wire:click="selectIssue({{ (int) ($issue['id'] ?? 0) }})"
                            aria-label="{{ $issue['title'] ?? '' }}"
                            aria-current="{{ $isSelected ? 'true' : 'false' }}"
                        >
                            <span class="evo-ui-issue-list-item__title">{{ $issue['title'] ?? '' }}</span>

                            @if(!empty($issue['body']))
                                <span class="evo-ui-issue-list-item__body">{{ $issue['body'] }}</span>
                            @endif

                            <span class="evo-ui-issue-list-item__meta">
                                @if(!empty($listProject['label']))
                                    <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--muted">{{ $listProject['label'] }}</span>
                                @endif

                                @if(!empty($listCategory['label']))
                                    <span class="evo-ui-issue-card__chip" @if($listCategoryStyle) style="{{ $listCategoryStyle }}" @endif>{{ $listCategory['label'] }}</span>
                                @endif

                                @if(!empty($listPriority['label']))
                                    <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--priority" @if($listPriorityStyle) style="{{ $listPriorityStyle }}" @endif>
                                        <x-evo::icon :name="$listPriority['icon'] ?? 'flag'" />
                                        <span>{{ $listPriority['label'] }}</span>
                                    </span>
                                @endif

                                @if(!empty($listStatus['label']))
                                    <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--status" @if($listStatusStyle) style="{{ $listStatusStyle }}" @endif>
                                        <x-evo::icon :name="$listStatus['icon'] ?? 'circle-dot'" />
                                        <span>{{ $listStatus['label'] }}</span>
                                    </span>
                                @endif

                                @if(!empty($listPhase['label']))
                                    <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--phase" @if($listPhaseStyle) style="{{ $listPhaseStyle }}" @endif>
                                        <x-evo::icon :name="$listPhase['icon'] ?? 'circle-dot'" />
                                        <span>{{ $listPhase['label'] }}</span>
                                    </span>
                                @endif

                                <span class="evo-ui-issue-card__stat">
                                    <x-evo::icon name="message-circle" />
                                    <span>{{ (int) ($issue['comments_count'] ?? 0) }}</span>
                                </span>

                                @if(!empty($listSubtasks['has_children']))
                                    <span class="evo-ui-issue-card__stat" title="@lang('dIssues::global.subtasks')">
                                        <x-evo::icon name="list-checks" />
                                        <span>{{ (int) ($listSubtasks['closed'] ?? 0) }}/{{ (int) ($listSubtasks['total'] ?? 0) }}</span>
                                    </span>
                                @endif

                                <span class="evo-ui-issue-card__stat">
                                    <x-evo::icon name="hash" />
                                    <span>{{ (int) ($issue['id'] ?? 0) }}</span>
                                </span>

                                @if(!empty($listAssignee['initials']))
                                    <span class="evo-ui-issue-card__assignee" title="{{ $listAssignee['label'] ?? '' }}">
                                        @if(!empty($listAssignee['avatar_url']))
                                            <img src="{{ $listAssignee['avatar_url'] }}" alt="{{ $listAssignee['label'] ?? '' }}" loading="lazy">
                                        @else
                                            {{ $listAssignee['initials'] }}
                                        @endif
                                    </span>
                                @endif
                            </span>
                        </button>
                    @empty
                        <div class="evo-ui-issue-split__empty">
                            <x-evo::icon name="list" />
                            <span>@lang('evo::global.table_empty')</span>
                        </div>
                    @endforelse

                </aside>

                <section class="evo-ui-issue-split__detail" aria-live="polite">
                    @if($selectedIssue)
                        @php
                            $detailStatus = (array) ($selectedIssue['status'] ?? []);
                            $detailPhase = (array) ($selectedIssue['phase'] ?? []);
                            $detailCategory = (array) ($selectedIssue['category'] ?? []);
                            $detailProject = (array) ($selectedIssue['project'] ?? []);
                            $detailAssignee = (array) ($selectedIssue['assignee'] ?? []);
                            $detailAuthor = (array) ($selectedIssue['author'] ?? []);
                            $detailPriority = (array) ($selectedIssue['priority'] ?? []);
                            $detailActions = (array) ($selectedIssue['actions'] ?? []);
                            $detailComments = (array) ($selectedIssue['comments'] ?? []);
                            $detailTransitions = (array) ($selectedIssue['transitions'] ?? []);
                            $detailChildren = (array) ($selectedIssue['children'] ?? []);
                            $detailParent = (array) ($selectedIssue['parent'] ?? []);
                            $detailSubtasks = (array) ($selectedIssue['subtasks'] ?? []);
                            $detailExternal = (array) ($selectedIssue['external'] ?? []);
                            $detailStatusColor = (string) ($detailStatus['color'] ?? '');
                            $detailStatusStyle = str_starts_with($detailStatusColor, '#') ? '--evo-issue-chip: ' . $detailStatusColor : '';
                            $detailPhaseColor = (string) ($detailPhase['color'] ?? '');
                            $detailPhaseStyle = str_starts_with($detailPhaseColor, '#') ? '--evo-issue-chip: ' . $detailPhaseColor : '';
                            $detailPriorityColor = (string) ($detailPriority['color'] ?? '');
                            $detailPriorityStyle = str_starts_with($detailPriorityColor, '#') ? '--evo-issue-chip: ' . $detailPriorityColor : '';
                            $detailAuthorDuplicatesAssignee = (int) ($detailAuthor['id'] ?? 0) > 0
                                && (int) ($detailAuthor['id'] ?? 0) === (int) ($detailAssignee['id'] ?? 0);
                        @endphp

                        <header class="evo-ui-issue-detail__header">
                            <div class="evo-ui-issue-detail__title">
                                <x-evo::icon :name="$detailStatus['icon'] ?? 'circle-dot'" />
                                <span>{{ $selectedIssue['title'] ?? '' }}</span>
                            </div>

                            <div class="evo-ui-issue-detail__actions">
	                                @if(!empty($detailActions['can_assign']))
	                                    <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.assign_myself')" aria-label="@lang('dIssues::global.assign_myself')" wire:click="assignIssueToMe">
	                                        <x-evo::icon name="user-plus" />
	                                    </button>
                                    <details class="evo-ui-filter-dropdown evo-ui-assignment-dropdown">
                                        <summary title="@lang('dIssues::global.assignee')" aria-label="@lang('dIssues::global.assignee')">
                                            <x-evo::icon name="users" class="evo-ui-filter-icon" />
                                        </summary>

                                        <div class="evo-ui-filter-menu evo-ui-filter-menu--compact">
                                            <div class="evo-ui-filter-options">
                                                @foreach($assignmentOptions as $option)
                                                    @php
                                                        $assignmentId = (int) ($option['id'] ?? 0);
                                                        $isCurrentAssignee = $assignmentId === (int) ($detailAssignee['id'] ?? -9999);
                                                    @endphp
                                                    <button
                                                        type="button"
                                                        @class(['evo-ui-filter-option-button', 'is-active' => $isCurrentAssignee])
                                                        @if($assignmentId > 0)
                                                            wire:click="assignIssue({{ $assignmentId }})"
                                                        @else
                                                            wire:click="unassignIssue"
                                                        @endif
                                                        onclick="this.closest('details').open = false"
                                                    >
                                                        @if(!empty($option['avatar_url']))
                                                            <img src="{{ $option['avatar_url'] }}" alt="{{ $option['name'] }}" loading="lazy">
                                                        @else
                                                            <x-evo::icon :name="$assignmentId > 0 ? ($option['icon'] ?: 'user') : 'user-off'" />
                                                        @endif
                                                        <span>{{ $option['name'] }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
	                                    </details>
	                                @endif

	                                @if(!empty($detailActions['can_edit']) && !$issueBodyEditing)
	                                    <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('evo::global.action_edit')" aria-label="@lang('evo::global.action_edit')" wire:click="startIssueBodyEdit">
	                                        <x-evo::icon name="edit" />
	                                    </button>
	                                @endif

                                    @if(!empty($detailActions['can_create_child']))
                                        <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.create_child_issue')" aria-label="@lang('dIssues::global.create_child_issue')" wire:click="createChildIssue">
                                            <x-evo::icon name="list-plus" />
                                        </button>
                                    @endif

                                    @if(!empty($detailActions['can_move_previous']))
                                        <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.move_previous_status')" aria-label="@lang('dIssues::global.move_previous_status')" wire:click="moveSelectedIssuePrevious">
                                            <x-evo::icon name="arrow-left" />
                                        </button>
                                    @endif

                                    @if(!empty($detailActions['can_move_next']))
                                        <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.move_next_status')" aria-label="@lang('dIssues::global.move_next_status')" wire:click="moveSelectedIssueNext">
                                            <x-evo::icon name="arrow-right" />
                                        </button>
                                    @endif

	                                @if(!empty($detailActions['can_reopen']))
                                    <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.reopen_issue')" aria-label="@lang('dIssues::global.reopen_issue')" wire:click="reopenSelectedIssue">
                                        <x-evo::icon name="refresh-ccw" />
                                    </button>
                                @elseif(!empty($detailActions['can_close']))
                                    <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.close_issue')" aria-label="@lang('dIssues::global.close_issue')" wire:click="closeSelectedIssue">
                                        <x-evo::icon name="check" />
                                    </button>
                                @endif
                            </div>
                        </header>

                        <div class="evo-ui-issue-preview__meta">
                            <span><x-evo::icon name="hash" /> {{ (int) ($selectedIssue['id'] ?? 0) }}</span>
                            @if(!empty($detailStatus['label']))
                                <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--status" @if($detailStatusStyle) style="{{ $detailStatusStyle }}" @endif>
                                    <x-evo::icon :name="$detailStatus['icon'] ?? 'circle-dot'" />
                                    <span>{{ $detailStatus['label'] }}</span>
                                </span>
                            @endif
                            @if(!empty($detailPhase['label']))
                                <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--phase" @if($detailPhaseStyle) style="{{ $detailPhaseStyle }}" @endif>
                                    <x-evo::icon :name="$detailPhase['icon'] ?? 'circle-dot'" />
                                    <span>{{ $detailPhase['label'] }}</span>
                                </span>
                            @endif
                            @if(!empty($detailPriority['label']))
                                <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--priority" @if($detailPriorityStyle) style="{{ $detailPriorityStyle }}" @endif>
                                    <x-evo::icon :name="$detailPriority['icon'] ?? 'flag'" />
                                    <span>{{ $detailPriority['label'] }}</span>
                                </span>
                            @endif
                            @if(!empty($detailProject['label']))
                                <span><x-evo::icon name="folder" /> {{ $detailProject['label'] }}</span>
                            @endif
                            @if(!empty($detailCategory['label']))
                                <span><x-evo::icon name="tag" /> {{ $detailCategory['label'] }}</span>
                            @endif
                            @if(!empty($detailAssignee['label']))
                                <span class="evo-ui-issue-person">
                                    @if(!empty($detailAssignee['avatar_url']))
                                        <img src="{{ $detailAssignee['avatar_url'] }}" alt="{{ $detailAssignee['label'] }}" loading="lazy">
                                    @else
                                        <x-evo::icon name="user-check" />
                                    @endif
                                    {{ $detailAssignee['label'] }}
                                </span>
                            @endif
                            @if(!empty($detailAuthor['label']) && !$detailAuthorDuplicatesAssignee)
                                <span class="evo-ui-issue-person">
                                    @if(!empty($detailAuthor['avatar_url']))
                                        <img src="{{ $detailAuthor['avatar_url'] }}" alt="{{ $detailAuthor['label'] }}" loading="lazy">
                                    @else
                                        <x-evo::icon name="user" />
                                    @endif
                                    {{ $detailAuthor['label'] }}
                                </span>
                            @endif
                            @if(!empty($selectedIssue['created_at']))
                                <span><x-evo::icon name="calendar-plus" /> <span>@lang('dIssues::global.created_at'):</span> {{ $selectedIssue['created_at'] }}</span>
                            @endif
                            @if(!empty($selectedIssue['updated_at']))
                                <span><x-evo::icon name="calendar-clock" /> <span>@lang('dIssues::global.updated_at'):</span> {{ $selectedIssue['updated_at'] }}</span>
                            @endif
                            @if(!empty($detailExternal['url']))
                                <a href="{{ $detailExternal['url'] }}" target="_blank" rel="noopener noreferrer">
                                    <x-evo::icon name="external-link" />
                                    <span>@lang('dIssues::global.external_issue')</span>
                                </a>
                            @endif
                            <span><x-evo::icon name="message-circle" /> {{ (int) ($selectedIssue['comments_count'] ?? 0) }}</span>
                            @if(!empty($detailSubtasks['has_children']))
                                <span><x-evo::icon name="list-checks" /> @lang('dIssues::global.subtasks'): {{ (int) ($detailSubtasks['closed'] ?? 0) }}/{{ (int) ($detailSubtasks['total'] ?? 0) }}</span>
                            @endif
                        </div>

                        @if(!empty($detailParent['id']))
                            <div class="evo-ui-issue-parent">
                                <x-evo::icon name="git-merge" />
                                <span>@lang('dIssues::global.parent_issue') #{{ (int) ($detailParent['id'] ?? 0) }}: {{ $detailParent['title'] ?? '' }}</span>
                            </div>
                        @endif

	                        @if($issueBodyEditing)
	                            @php
	                                $detailBodyEditorId = 'evo-issue-body-' . (int) ($selectedIssue['id'] ?? 0);
	                            @endphp
	                            <form class="evo-ui-issue-body-editor" x-on:submit.prevent="EvoUI.syncRichEditors($el, $wire).then(() => $wire.saveIssueBody())">
	                                <div
	                                    class="evo-ui-editor-field"
	                                    wire:ignore
	                                    wire:key="issue-body-editor-{{ (int) ($selectedIssue['id'] ?? 0) }}"
	                                    x-init="$nextTick(() => EvoUI.initRichEditorField($el))"
	                                >
	                                    <textarea
	                                        id="{{ $detailBodyEditorId }}"
	                                        class="evo-ui-textarea evo-ui-textarea--editor"
	                                        rows="10"
	                                        data-evo-rich-editor
	                                        data-evo-rich-editor-model="issueBodyDraft"
	                                        placeholder="@lang('dIssues::global.issue_body')"
	                                    >{{ $issueBodyDraft }}</textarea>
	                                    {!! $this->issueBodyEditorHtml($detailBodyEditorId) !!}
	                                </div>
	                                <footer class="evo-ui-issue-reply__actions">
	                                    <button type="button" class="evo-ui-btn" wire:click="cancelIssueBodyEdit">
	                                        <x-evo::icon name="x" />
	                                        <span>@lang('evo::global.action_cancel')</span>
	                                    </button>
	                                    <button type="submit" class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled">
	                                        <x-evo::icon name="check" />
	                                        <span>@lang('evo::global.action_save')</span>
	                                    </button>
	                                </footer>
	                            </form>
	                        @else
	                            <article class="evo-ui-issue-preview__content">
	                                {!! $this->issueBodyHtml((string) ($selectedIssue['body_full'] ?? $selectedIssue['body'] ?? '')) !!}
	                            </article>
	                        @endif

                        <section class="evo-ui-issue-subtasks" aria-label="@lang('dIssues::global.subtasks')">
                            <header class="evo-ui-issue-conversation__header">
                                <span>@lang('dIssues::global.subtasks')</span>
                                <span>{{ (int) ($detailSubtasks['closed'] ?? 0) }}/{{ (int) ($detailSubtasks['total'] ?? count($detailChildren)) }}</span>
                            </header>

                            <div class="evo-ui-issue-subtasks__items">
                                @forelse($detailChildren as $child)
                                    @php
                                        $childStatus = (array) ($child['status'] ?? []);
                                        $childPhase = (array) ($child['phase'] ?? []);
                                        $childStatusColor = (string) ($childStatus['color'] ?? '');
                                        $childStatusStyle = str_starts_with($childStatusColor, '#') ? '--evo-issue-chip: ' . $childStatusColor : '';
                                        $childPhaseColor = (string) ($childPhase['color'] ?? '');
                                        $childPhaseStyle = str_starts_with($childPhaseColor, '#') ? '--evo-issue-chip: ' . $childPhaseColor : '';
                                    @endphp
                                    <button type="button" class="evo-ui-issue-subtask" wire:key="issue-child-{{ (int) ($child['id'] ?? 0) }}" wire:click="selectIssue({{ (int) ($child['id'] ?? 0) }})">
                                        <span class="evo-ui-issue-subtask__title">#{{ (int) ($child['id'] ?? 0) }} {{ $child['title'] ?? '' }}</span>
                                        <span class="evo-ui-issue-subtask__meta">
                                            @if(!empty($childStatus['label']))
                                                <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--status" @if($childStatusStyle) style="{{ $childStatusStyle }}" @endif>
                                                    <x-evo::icon :name="$childStatus['icon'] ?? 'circle-dot'" />
                                                    <span>{{ $childStatus['label'] }}</span>
                                                </span>
                                            @endif
                                            @if(!empty($childPhase['label']))
                                                <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--phase" @if($childPhaseStyle) style="{{ $childPhaseStyle }}" @endif>
                                                    <x-evo::icon :name="$childPhase['icon'] ?? 'circle-dot'" />
                                                    <span>{{ $childPhase['label'] }}</span>
                                                </span>
                                            @endif
                                        </span>
                                    </button>
                                @empty
                                    <div class="evo-ui-issue-conversation__empty">@lang('dIssues::global.no_subtasks')</div>
                                @endforelse
                            </div>
                        </section>

                        <section class="evo-ui-issue-timeline" aria-label="@lang('dIssues::global.status_history')">
                            <header class="evo-ui-issue-conversation__header">
                                <span>@lang('dIssues::global.status_history')</span>
                                <span>{{ count($detailTransitions) }}</span>
                            </header>

                            <div class="evo-ui-issue-timeline__items">
                                @forelse($detailTransitions as $transition)
                                    @php
                                        $fromStatus = (array) ($transition['from_status'] ?? []);
                                        $toStatus = (array) ($transition['to_status'] ?? []);
                                        $fromPhase = (array) ($transition['from_phase'] ?? []);
                                        $toPhase = (array) ($transition['to_phase'] ?? []);
                                        $transitionUser = (array) ($transition['user'] ?? []);
                                    @endphp
                                    <article class="evo-ui-issue-timeline__item" wire:key="issue-transition-{{ (int) ($transition['id'] ?? 0) }}">
                                        <x-evo::icon name="git-commit-horizontal" />
                                        <div>
                                            <strong>{{ $fromStatus['label'] ?? '-' }} -> {{ $toStatus['label'] ?? '-' }}</strong>
                                            @if(!empty($fromPhase['label']) || !empty($toPhase['label']))
                                                <span>{{ $fromPhase['label'] ?? '-' }} -> {{ $toPhase['label'] ?? '-' }}</span>
                                            @endif
                                        </div>
                                        <small>{{ $transitionUser['label'] ?? __('dIssues::global.manager') }} · {{ $transition['created_at'] ?? '' }}</small>
                                    </article>
                                @empty
                                    <div class="evo-ui-issue-conversation__empty">@lang('dIssues::global.no_status_history')</div>
                                @endforelse
                            </div>
                        </section>

                        <section class="evo-ui-issue-conversation" aria-label="@lang('dIssues::global.comments')">
                            <header class="evo-ui-issue-conversation__header">
                                <span>@lang('dIssues::global.comments')</span>
                                <span>{{ count($detailComments) }}</span>
                            </header>

                            <div class="evo-ui-issue-comments">
                                @forelse($detailComments as $comment)
                                    @php
                                        $commentAuthor = (array) ($comment['author'] ?? []);
                                    @endphp
                                    <article class="evo-ui-issue-comment" wire:key="issue-comment-{{ (int) ($comment['id'] ?? 0) }}">
                                        <div class="evo-ui-issue-comment__avatar">
                                            @if(!empty($commentAuthor['avatar_url']))
                                                <img src="{{ $commentAuthor['avatar_url'] }}" alt="{{ $commentAuthor['label'] ?? '' }}" loading="lazy">
                                            @else
                                                {{ $commentAuthor['initials'] ?? 'M' }}
                                            @endif
                                        </div>
                                        <div class="evo-ui-issue-comment__body">
                                            <header class="evo-ui-issue-comment__meta">
                                                <strong>{{ $commentAuthor['label'] ?? __('dIssues::global.manager') }}</strong>
                                                <span>{{ $comment['created_at'] ?? '' }}</span>
                                                @if(!empty($detailActions['can_reply']))
                                                    <button type="button" class="evo-ui-issue-comment__reply" title="@lang('dIssues::global.reply_to_comment')" aria-label="@lang('dIssues::global.reply_to_comment')" wire:click="replyToComment({{ (int) ($comment['id'] ?? 0) }})">
                                                        <x-evo::icon name="corner-up-left" />
                                                    </button>
                                                @endif
                                            </header>
                                            @if(!empty($comment['parent']))
                                                @php
                                                    $commentParent = (array) ($comment['parent'] ?? []);
                                                    $commentParentAuthor = (array) ($commentParent['author'] ?? []);
                                                @endphp
                                                <div class="evo-ui-issue-comment__parent">
                                                    <x-evo::icon name="corner-up-left" />
                                                    <span>{{ $commentParentAuthor['label'] ?? __('dIssues::global.manager') }}:</span>
                                                    <span>{{ $commentParent['excerpt'] ?? '' }}</span>
                                                </div>
                                            @endif
                                            <div class="evo-ui-issue-comment__content">
                                                {!! $this->issueBodyHtml((string) ($comment['body'] ?? '')) !!}
                                            </div>
                                        </div>
                                    </article>
                                @empty
                                    <div class="evo-ui-issue-conversation__empty">@lang('dIssues::global.no_comments')</div>
                                @endforelse
                            </div>

                            @if(!empty($detailActions['can_reply']))
                                @php
                                    $detailReplyEditorId = 'evo-issue-reply-' . (int) ($selectedIssue['id'] ?? 0);
                                    $detailReplyContext = collect($detailComments)->first(fn ($comment) => (int) ($comment['id'] ?? 0) === (int) $replyToCommentId);
                                    $detailReplyContextAuthor = (array) data_get($detailReplyContext, 'author', []);
                                @endphp
                                <form class="evo-ui-issue-reply" x-on:submit.prevent="EvoUI.syncRichEditors($el, $wire).then(() => $wire.replyIssue()).then(() => EvoUI.clearRichEditors($el))">
                                    @if($detailReplyContext)
                                        <div class="evo-ui-issue-reply__context">
                                            <x-evo::icon name="corner-up-left" />
                                            <span>@lang('dIssues::global.replying_to') {{ $detailReplyContextAuthor['label'] ?? __('dIssues::global.manager') }}</span>
                                            <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.cancel_reply')" aria-label="@lang('dIssues::global.cancel_reply')" wire:click="cancelReplyToComment">
                                                <x-evo::icon name="x" />
                                            </button>
                                        </div>
                                    @endif
                                    <div
                                        class="evo-ui-editor-field"
                                        wire:ignore
                                        wire:key="issue-reply-editor-{{ (int) ($selectedIssue['id'] ?? 0) }}"
                                        x-init="$nextTick(() => EvoUI.initRichEditorField($el))"
                                    >
                                        <textarea
                                            id="{{ $detailReplyEditorId }}"
                                            class="evo-ui-textarea evo-ui-textarea--editor"
                                            rows="4"
                                            data-evo-rich-editor
                                            data-evo-rich-editor-model="replyBody"
                                            placeholder="@lang('dIssues::global.reply')"
                                        >{{ $replyBody }}</textarea>
                                        {!! $this->replyEditorHtml($detailReplyEditorId) !!}
                                    </div>
                                    <footer class="evo-ui-issue-reply__actions">
                                        <button type="submit" class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled">
                                            <x-evo::icon name="send" />
                                            <span>@lang('dIssues::global.reply')</span>
                                        </button>
                                        <button type="button" class="evo-ui-btn" x-on:click.prevent="EvoUI.syncRichEditors($el.closest('form'), $wire).then(() => $wire.replyAndCloseIssue()).then(() => EvoUI.clearRichEditors($el.closest('form')))">
                                            <x-evo::icon name="check" />
                                            <span>@lang('dIssues::global.reply_and_close')</span>
                                        </button>
                                    </footer>
                                </form>
                            @endif
                        </section>
                    @else
                        <div class="evo-ui-issue-split__empty">
                            <x-evo::icon name="list" />
                            <span>@lang('evo::global.table_empty')</span>
                        </div>
                    @endif
                </section>
            </div>
        @endif
    </div>

    @if($selectedIssue && $filters['display'] === 'kanban')
        @php
            $previewStatus = (array) ($selectedIssue['status'] ?? []);
            $previewCategory = (array) ($selectedIssue['category'] ?? []);
            $previewProject = (array) ($selectedIssue['project'] ?? []);
            $previewAssignee = (array) ($selectedIssue['assignee'] ?? []);
            $previewAuthor = (array) ($selectedIssue['author'] ?? []);
            $previewPriority = (array) ($selectedIssue['priority'] ?? []);
            $previewActions = (array) ($selectedIssue['actions'] ?? []);
            $previewComments = (array) ($selectedIssue['comments'] ?? []);
            $previewTransitions = (array) ($selectedIssue['transitions'] ?? []);
            $previewChildren = (array) ($selectedIssue['children'] ?? []);
            $previewParent = (array) ($selectedIssue['parent'] ?? []);
            $previewSubtasks = (array) ($selectedIssue['subtasks'] ?? []);
            $previewExternal = (array) ($selectedIssue['external'] ?? []);
            $previewStatusColor = (string) ($previewStatus['color'] ?? '');
            $previewStatusStyle = str_starts_with($previewStatusColor, '#') ? '--evo-issue-chip: ' . $previewStatusColor : '';
            $previewPriorityColor = (string) ($previewPriority['color'] ?? '');
            $previewPriorityStyle = str_starts_with($previewPriorityColor, '#') ? '--evo-issue-chip: ' . $previewPriorityColor : '';
            $previewAuthorDuplicatesAssignee = (int) ($previewAuthor['id'] ?? 0) > 0
                && (int) ($previewAuthor['id'] ?? 0) === (int) ($previewAssignee['id'] ?? 0);
        @endphp
        <div
            class="evo-ui-modal-backdrop"
            role="presentation"
            wire:click.self="closeIssuePreview"
            wire:keydown.escape.window="closeIssuePreview"
        >
            <section
                class="evo-ui-modal evo-ui-modal--lg evo-ui-issue-preview"
                role="dialog"
                aria-modal="true"
                aria-labelledby="evo-issue-preview-title-{{ (int) ($selectedIssue['id'] ?? 0) }}"
            >
                <header class="evo-ui-modal__header">
                    <div class="evo-ui-modal__title" id="evo-issue-preview-title-{{ (int) ($selectedIssue['id'] ?? 0) }}">
                        <x-evo::icon :name="$previewStatus['icon'] ?? 'circle-dot'" />
                        <span>{{ $selectedIssue['title'] ?? '' }}</span>
                    </div>

                    <div class="evo-ui-issue-detail__actions">
	                        @if(!empty($previewActions['can_assign']))
                            <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.assign_myself')" aria-label="@lang('dIssues::global.assign_myself')" wire:click="assignIssueToMe">
                                <x-evo::icon name="user-plus" />
                            </button>
                            <details class="evo-ui-filter-dropdown evo-ui-assignment-dropdown">
                                <summary title="@lang('dIssues::global.assignee')" aria-label="@lang('dIssues::global.assignee')">
                                    <x-evo::icon name="users" class="evo-ui-filter-icon" />
                                </summary>

                                <div class="evo-ui-filter-menu evo-ui-filter-menu--compact">
                                    <div class="evo-ui-filter-options">
                                        @foreach($assignmentOptions as $option)
                                            @php
                                                $assignmentId = (int) ($option['id'] ?? 0);
                                                $isCurrentAssignee = $assignmentId === (int) ($previewAssignee['id'] ?? -9999);
                                            @endphp
                                            <button
                                                type="button"
                                                @class(['evo-ui-filter-option-button', 'is-active' => $isCurrentAssignee])
                                                @if($assignmentId > 0)
                                                    wire:click="assignIssue({{ $assignmentId }})"
                                                @else
                                                    wire:click="unassignIssue"
                                                @endif
                                                onclick="this.closest('details').open = false"
                                            >
                                                @if(!empty($option['avatar_url']))
                                                    <img src="{{ $option['avatar_url'] }}" alt="{{ $option['name'] }}" loading="lazy">
                                                @else
                                                    <x-evo::icon :name="$assignmentId > 0 ? ($option['icon'] ?: 'user') : 'user-off'" />
                                                @endif
                                                <span>{{ $option['name'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
	                            </details>
	                        @endif

	                        @if(!empty($previewActions['can_edit']) && !$issueBodyEditing)
	                            <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('evo::global.action_edit')" aria-label="@lang('evo::global.action_edit')" wire:click="startIssueBodyEdit">
	                                <x-evo::icon name="edit" />
	                            </button>
	                        @endif

                            @if(!empty($previewActions['can_create_child']))
                                <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.create_child_issue')" aria-label="@lang('dIssues::global.create_child_issue')" wire:click="createChildIssue">
                                    <x-evo::icon name="list-plus" />
                                </button>
                            @endif

                            @if(!empty($previewActions['can_move_previous']))
                                <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.move_previous_status')" aria-label="@lang('dIssues::global.move_previous_status')" wire:click="moveSelectedIssuePrevious">
                                    <x-evo::icon name="arrow-left" />
                                </button>
                            @endif

                            @if(!empty($previewActions['can_move_next']))
                                <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.move_next_status')" aria-label="@lang('dIssues::global.move_next_status')" wire:click="moveSelectedIssueNext">
                                    <x-evo::icon name="arrow-right" />
                                </button>
                            @endif

	                        @if(!empty($previewActions['can_reopen']))
                            <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.reopen_issue')" aria-label="@lang('dIssues::global.reopen_issue')" wire:click="reopenSelectedIssue">
                                <x-evo::icon name="refresh-ccw" />
                            </button>
                        @elseif(!empty($previewActions['can_close']))
                            <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.close_issue')" aria-label="@lang('dIssues::global.close_issue')" wire:click="closeSelectedIssue">
                                <x-evo::icon name="check" />
                            </button>
                        @endif

                        <button type="button" class="evo-ui-modal__close" title="@lang('evo::global.action_cancel')" aria-label="@lang('evo::global.action_cancel')" wire:click="closeIssuePreview">
                            <x-evo::icon name="x" />
                        </button>
                    </div>
                </header>

                <div class="evo-ui-modal__body evo-ui-issue-preview__body">
                    <div class="evo-ui-issue-preview__meta">
                        <span><x-evo::icon name="hash" /> {{ (int) ($selectedIssue['id'] ?? 0) }}</span>
                        @if(!empty($previewStatus['label']))
                            <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--status" @if($previewStatusStyle) style="{{ $previewStatusStyle }}" @endif>
                                <x-evo::icon :name="$previewStatus['icon'] ?? 'circle-dot'" />
                                <span>{{ $previewStatus['label'] }}</span>
                            </span>
                        @endif
                        @if(!empty($previewPriority['label']))
                            <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--priority" @if($previewPriorityStyle) style="{{ $previewPriorityStyle }}" @endif>
                                <x-evo::icon :name="$previewPriority['icon'] ?? 'flag'" />
                                <span>{{ $previewPriority['label'] }}</span>
                            </span>
                        @endif
                        @if(!empty($previewProject['label']))
                            <span><x-evo::icon name="folder" /> {{ $previewProject['label'] }}</span>
                        @endif
                        @if(!empty($previewCategory['label']))
                            <span><x-evo::icon name="tag" /> {{ $previewCategory['label'] }}</span>
                        @endif
                        @if(!empty($previewAssignee['label']))
                            <span class="evo-ui-issue-person">
                                @if(!empty($previewAssignee['avatar_url']))
                                    <img src="{{ $previewAssignee['avatar_url'] }}" alt="{{ $previewAssignee['label'] }}" loading="lazy">
                                @else
                                    <x-evo::icon name="user-check" />
                                @endif
                                {{ $previewAssignee['label'] }}
                            </span>
                        @endif
                        @if(!empty($previewAuthor['label']) && !$previewAuthorDuplicatesAssignee)
                            <span class="evo-ui-issue-person">
                                @if(!empty($previewAuthor['avatar_url']))
                                    <img src="{{ $previewAuthor['avatar_url'] }}" alt="{{ $previewAuthor['label'] }}" loading="lazy">
                                @else
                                    <x-evo::icon name="user" />
                                @endif
                                {{ $previewAuthor['label'] }}
                            </span>
                        @endif
                        @if(!empty($selectedIssue['created_at']))
                            <span><x-evo::icon name="calendar-plus" /> <span>@lang('dIssues::global.created_at'):</span> {{ $selectedIssue['created_at'] }}</span>
                        @endif
                        @if(!empty($selectedIssue['updated_at']))
                            <span><x-evo::icon name="calendar-clock" /> <span>@lang('dIssues::global.updated_at'):</span> {{ $selectedIssue['updated_at'] }}</span>
                        @endif
                        @if(!empty($previewExternal['url']))
                            <a href="{{ $previewExternal['url'] }}" target="_blank" rel="noopener noreferrer">
                                <x-evo::icon name="external-link" />
                                <span>@lang('dIssues::global.external_issue')</span>
                            </a>
                        @endif
                        <span><x-evo::icon name="message-circle" /> {{ (int) ($selectedIssue['comments_count'] ?? 0) }}</span>
                        @if(!empty($previewSubtasks['has_children']))
                            <span><x-evo::icon name="list-checks" /> @lang('dIssues::global.subtasks'): {{ (int) ($previewSubtasks['closed'] ?? 0) }}/{{ (int) ($previewSubtasks['total'] ?? 0) }}</span>
                        @endif
                    </div>

                    @if(!empty($previewParent['id']))
                        <div class="evo-ui-issue-parent">
                            <x-evo::icon name="git-merge" />
                            <span>@lang('dIssues::global.parent_issue') #{{ (int) ($previewParent['id'] ?? 0) }}: {{ $previewParent['title'] ?? '' }}</span>
                        </div>
                    @endif

	                    @if($issueBodyEditing)
	                        @php
	                            $previewBodyEditorId = 'evo-issue-preview-body-' . (int) ($selectedIssue['id'] ?? 0);
	                        @endphp
	                        <form class="evo-ui-issue-body-editor" x-on:submit.prevent="EvoUI.syncRichEditors($el, $wire).then(() => $wire.saveIssueBody())">
	                            <div
	                                class="evo-ui-editor-field"
	                                wire:ignore
	                                wire:key="issue-preview-body-editor-{{ (int) ($selectedIssue['id'] ?? 0) }}"
	                                x-init="$nextTick(() => EvoUI.initRichEditorField($el))"
	                            >
	                                <textarea
	                                    id="{{ $previewBodyEditorId }}"
	                                    class="evo-ui-textarea evo-ui-textarea--editor"
	                                    rows="10"
	                                    data-evo-rich-editor
	                                    data-evo-rich-editor-model="issueBodyDraft"
	                                    placeholder="@lang('dIssues::global.issue_body')"
	                                >{{ $issueBodyDraft }}</textarea>
	                                {!! $this->issueBodyEditorHtml($previewBodyEditorId) !!}
	                            </div>
	                            <footer class="evo-ui-issue-reply__actions">
	                                <button type="button" class="evo-ui-btn" wire:click="cancelIssueBodyEdit">
	                                    <x-evo::icon name="x" />
	                                    <span>@lang('evo::global.action_cancel')</span>
	                                </button>
	                                <button type="submit" class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled">
	                                    <x-evo::icon name="check" />
	                                    <span>@lang('evo::global.action_save')</span>
	                                </button>
	                            </footer>
	                        </form>
	                    @else
	                        <article class="evo-ui-issue-preview__content">
	                            {!! $this->issueBodyHtml((string) ($selectedIssue['body_full'] ?? $selectedIssue['body'] ?? '')) !!}
	                        </article>
	                    @endif

                    <section class="evo-ui-issue-subtasks" aria-label="@lang('dIssues::global.subtasks')">
                        <header class="evo-ui-issue-conversation__header">
                            <span>@lang('dIssues::global.subtasks')</span>
                            <span>{{ (int) ($previewSubtasks['closed'] ?? 0) }}/{{ (int) ($previewSubtasks['total'] ?? count($previewChildren)) }}</span>
                        </header>

                        <div class="evo-ui-issue-subtasks__items">
                            @forelse($previewChildren as $child)
                                @php
                                    $childStatus = (array) ($child['status'] ?? []);
                                    $childPhase = (array) ($child['phase'] ?? []);
                                    $childStatusColor = (string) ($childStatus['color'] ?? '');
                                    $childStatusStyle = str_starts_with($childStatusColor, '#') ? '--evo-issue-chip: ' . $childStatusColor : '';
                                    $childPhaseColor = (string) ($childPhase['color'] ?? '');
                                    $childPhaseStyle = str_starts_with($childPhaseColor, '#') ? '--evo-issue-chip: ' . $childPhaseColor : '';
                                @endphp
                                <button type="button" class="evo-ui-issue-subtask" wire:key="issue-preview-child-{{ (int) ($child['id'] ?? 0) }}" wire:click="selectIssue({{ (int) ($child['id'] ?? 0) }})">
                                    <span class="evo-ui-issue-subtask__title">#{{ (int) ($child['id'] ?? 0) }} {{ $child['title'] ?? '' }}</span>
                                    <span class="evo-ui-issue-subtask__meta">
                                        @if(!empty($childStatus['label']))
                                            <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--status" @if($childStatusStyle) style="{{ $childStatusStyle }}" @endif>
                                                <x-evo::icon :name="$childStatus['icon'] ?? 'circle-dot'" />
                                                <span>{{ $childStatus['label'] }}</span>
                                            </span>
                                        @endif
                                        @if(!empty($childPhase['label']))
                                            <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--phase" @if($childPhaseStyle) style="{{ $childPhaseStyle }}" @endif>
                                                <x-evo::icon :name="$childPhase['icon'] ?? 'circle-dot'" />
                                                <span>{{ $childPhase['label'] }}</span>
                                            </span>
                                        @endif
                                    </span>
                                </button>
                            @empty
                                <div class="evo-ui-issue-conversation__empty">@lang('dIssues::global.no_subtasks')</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="evo-ui-issue-timeline" aria-label="@lang('dIssues::global.status_history')">
                        <header class="evo-ui-issue-conversation__header">
                            <span>@lang('dIssues::global.status_history')</span>
                            <span>{{ count($previewTransitions) }}</span>
                        </header>

                        <div class="evo-ui-issue-timeline__items">
                            @forelse($previewTransitions as $transition)
                                @php
                                    $fromStatus = (array) ($transition['from_status'] ?? []);
                                    $toStatus = (array) ($transition['to_status'] ?? []);
                                    $fromPhase = (array) ($transition['from_phase'] ?? []);
                                    $toPhase = (array) ($transition['to_phase'] ?? []);
                                    $transitionUser = (array) ($transition['user'] ?? []);
                                @endphp
                                <article class="evo-ui-issue-timeline__item" wire:key="issue-preview-transition-{{ (int) ($transition['id'] ?? 0) }}">
                                    <x-evo::icon name="git-commit-horizontal" />
                                    <div>
                                        <strong>{{ $fromStatus['label'] ?? '-' }} -> {{ $toStatus['label'] ?? '-' }}</strong>
                                        @if(!empty($fromPhase['label']) || !empty($toPhase['label']))
                                            <span>{{ $fromPhase['label'] ?? '-' }} -> {{ $toPhase['label'] ?? '-' }}</span>
                                        @endif
                                    </div>
                                    <small>{{ $transitionUser['label'] ?? __('dIssues::global.manager') }} · {{ $transition['created_at'] ?? '' }}</small>
                                </article>
                            @empty
                                <div class="evo-ui-issue-conversation__empty">@lang('dIssues::global.no_status_history')</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="evo-ui-issue-conversation" aria-label="@lang('dIssues::global.comments')">
                        <header class="evo-ui-issue-conversation__header">
                            <span>@lang('dIssues::global.comments')</span>
                            <span>{{ count($previewComments) }}</span>
                        </header>

                        <div class="evo-ui-issue-comments">
                            @forelse($previewComments as $comment)
                                @php
                                    $commentAuthor = (array) ($comment['author'] ?? []);
                                @endphp
                                <article class="evo-ui-issue-comment" wire:key="issue-preview-comment-{{ (int) ($comment['id'] ?? 0) }}">
                                    <div class="evo-ui-issue-comment__avatar">
                                        @if(!empty($commentAuthor['avatar_url']))
                                            <img src="{{ $commentAuthor['avatar_url'] }}" alt="{{ $commentAuthor['label'] ?? '' }}" loading="lazy">
                                        @else
                                            {{ $commentAuthor['initials'] ?? 'M' }}
                                        @endif
                                    </div>
                                    <div class="evo-ui-issue-comment__body">
                                        <header class="evo-ui-issue-comment__meta">
                                            <strong>{{ $commentAuthor['label'] ?? __('dIssues::global.manager') }}</strong>
                                            <span>{{ $comment['created_at'] ?? '' }}</span>
                                            @if(!empty($previewActions['can_reply']))
                                                <button type="button" class="evo-ui-issue-comment__reply" title="@lang('dIssues::global.reply_to_comment')" aria-label="@lang('dIssues::global.reply_to_comment')" wire:click="replyToComment({{ (int) ($comment['id'] ?? 0) }})">
                                                    <x-evo::icon name="corner-up-left" />
                                                </button>
                                            @endif
                                        </header>
                                        @if(!empty($comment['parent']))
                                            @php
                                                $commentParent = (array) ($comment['parent'] ?? []);
                                                $commentParentAuthor = (array) ($commentParent['author'] ?? []);
                                            @endphp
                                            <div class="evo-ui-issue-comment__parent">
                                                <x-evo::icon name="corner-up-left" />
                                                <span>{{ $commentParentAuthor['label'] ?? __('dIssues::global.manager') }}:</span>
                                                <span>{{ $commentParent['excerpt'] ?? '' }}</span>
                                            </div>
                                        @endif
                                        <div class="evo-ui-issue-comment__content">
                                            {!! $this->issueBodyHtml((string) ($comment['body'] ?? '')) !!}
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="evo-ui-issue-conversation__empty">@lang('dIssues::global.no_comments')</div>
                            @endforelse
                        </div>

                        @if(!empty($previewActions['can_reply']))
                            @php
                                $previewReplyEditorId = 'evo-issue-preview-reply-' . (int) ($selectedIssue['id'] ?? 0);
                                $previewReplyContext = collect($previewComments)->first(fn ($comment) => (int) ($comment['id'] ?? 0) === (int) $replyToCommentId);
                                $previewReplyContextAuthor = (array) data_get($previewReplyContext, 'author', []);
                            @endphp
                            <form class="evo-ui-issue-reply" x-on:submit.prevent="EvoUI.syncRichEditors($el, $wire).then(() => $wire.replyIssue()).then(() => EvoUI.clearRichEditors($el))">
                                @if($previewReplyContext)
                                    <div class="evo-ui-issue-reply__context">
                                        <x-evo::icon name="corner-up-left" />
                                        <span>@lang('dIssues::global.replying_to') {{ $previewReplyContextAuthor['label'] ?? __('dIssues::global.manager') }}</span>
                                        <button type="button" class="evo-ui-btn evo-ui-btn--icon" title="@lang('dIssues::global.cancel_reply')" aria-label="@lang('dIssues::global.cancel_reply')" wire:click="cancelReplyToComment">
                                            <x-evo::icon name="x" />
                                        </button>
                                    </div>
                                @endif
                                <div
                                    class="evo-ui-editor-field"
                                    wire:ignore
                                    wire:key="issue-preview-reply-editor-{{ (int) ($selectedIssue['id'] ?? 0) }}"
                                    x-init="$nextTick(() => EvoUI.initRichEditorField($el))"
                                >
                                    <textarea
                                        id="{{ $previewReplyEditorId }}"
                                        class="evo-ui-textarea evo-ui-textarea--editor"
                                        rows="4"
                                        data-evo-rich-editor
                                        data-evo-rich-editor-model="replyBody"
                                        placeholder="@lang('dIssues::global.reply')"
                                    >{{ $replyBody }}</textarea>
                                    {!! $this->replyEditorHtml($previewReplyEditorId) !!}
                                </div>
                                <footer class="evo-ui-issue-reply__actions">
                                    <button type="submit" class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled">
                                        <x-evo::icon name="send" />
                                        <span>@lang('dIssues::global.reply')</span>
                                    </button>
                                    <button type="button" class="evo-ui-btn" x-on:click.prevent="EvoUI.syncRichEditors($el.closest('form'), $wire).then(() => $wire.replyAndCloseIssue()).then(() => EvoUI.clearRichEditors($el.closest('form')))">
                                        <x-evo::icon name="check" />
                                        <span>@lang('dIssues::global.reply_and_close')</span>
                                    </button>
                                </footer>
                            </form>
                        @elseif(!empty($previewActions['can_reopen']))
                            <button type="button" class="evo-ui-btn" wire:click="reopenSelectedIssue">
                                <x-evo::icon name="refresh-ccw" />
                                <span>@lang('dIssues::global.reopen_issue')</span>
                            </button>
                        @endif
                    </section>
                </div>
            </section>
        </div>
    @endif
</section>

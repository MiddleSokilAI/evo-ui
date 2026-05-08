@php
    $wireTarget = $config['wire_target'] ?? 'filters,setCategory,setStatus,setAssignee,switchDisplay,resetFilters';
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
    $categoryCount = count((array) ($filters['category_ids'] ?? []));
    $statusCount = count((array) ($filters['status_ids'] ?? []));
    $assigneeCount = count((array) ($filters['assignee_ids'] ?? []));
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
                    @endphp
                    <section class="evo-ui-issue-kanban__lane" role="listitem" data-evo-issue-status="{{ (int) ($lane['id'] ?? 0) }}" @if($laneStyle) style="{{ $laneStyle }}" @endif>
                        <header class="evo-ui-issue-kanban__lane-header">
                            <div class="evo-ui-issue-kanban__lane-title">
                                <x-evo::icon :name="$lane['icon'] ?? 'circle-dot'" />
                                <span>{{ __($lane['label'] ?? '') }}</span>
                            </div>
                            <span class="evo-ui-issue-kanban__count" data-evo-issue-count>{{ (int) ($lane['count'] ?? 0) }}</span>
                        </header>

                        <div class="evo-ui-issue-kanban__cards" data-evo-issue-lane data-status-id="{{ (int) ($lane['id'] ?? 0) }}">
                            @forelse(($lane['issues'] ?? []) as $issue)
                                @php
                                    $category = (array) ($issue['category'] ?? []);
                                    $project = (array) ($issue['project'] ?? []);
                                    $assignee = (array) ($issue['assignee'] ?? []);
                                    $categoryColor = (string) ($category['color'] ?? '');
                                    $categoryStyle = str_starts_with($categoryColor, '#') ? '--evo-issue-chip: ' . $categoryColor : '';
                                @endphp
                                <button
                                    type="button"
                                    class="evo-ui-issue-card"
                                    draggable="true"
                                    data-evo-issue-card
                                    data-issue-id="{{ (int) ($issue['id'] ?? 0) }}"
                                    wire:click="selectIssue({{ (int) ($issue['id'] ?? 0) }})"
                                    aria-label="{{ $issue['title'] ?? '' }}"
                                >
                                    <span class="evo-ui-issue-card__title">{{ $issue['title'] ?? '' }}</span>

                                    @if(!empty($issue['body']))
                                        <span class="evo-ui-issue-card__body">{{ $issue['body'] }}</span>
                                    @endif

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

                                        <span class="evo-ui-issue-card__stat" title="Comments">
                                            <x-evo::icon name="message-circle" />
                                            <span>{{ (int) ($issue['comments_count'] ?? 0) }}</span>
                                        </span>

                                        <span class="evo-ui-issue-card__stat" title="Issue ID">
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
                <aside class="evo-ui-issue-split__list" aria-label="{{ __('evo::global.view_list') }}">
                    @forelse($issueList as $issue)
                        @php
                            $listStatus = (array) ($issue['status'] ?? []);
                            $listCategory = (array) ($issue['category'] ?? []);
                            $listProject = (array) ($issue['project'] ?? []);
                            $listAssignee = (array) ($issue['assignee'] ?? []);
                            $listCategoryColor = (string) ($listCategory['color'] ?? '');
                            $listCategoryStyle = str_starts_with($listCategoryColor, '#') ? '--evo-issue-chip: ' . $listCategoryColor : '';
                            $listStatusColor = (string) ($listStatus['color'] ?? '');
                            $listStatusStyle = str_starts_with($listStatusColor, '#') ? '--evo-issue-chip: ' . $listStatusColor : '';
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

                                @if(!empty($listStatus['label']))
                                    <span class="evo-ui-issue-card__chip evo-ui-issue-card__chip--status" @if($listStatusStyle) style="{{ $listStatusStyle }}" @endif>
                                        <x-evo::icon :name="$listStatus['icon'] ?? 'circle-dot'" />
                                        <span>{{ $listStatus['label'] }}</span>
                                    </span>
                                @endif

                                <span class="evo-ui-issue-card__stat">
                                    <x-evo::icon name="message-circle" />
                                    <span>{{ (int) ($issue['comments_count'] ?? 0) }}</span>
                                </span>

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
                            $detailCategory = (array) ($selectedIssue['category'] ?? []);
                            $detailProject = (array) ($selectedIssue['project'] ?? []);
                            $detailAssignee = (array) ($selectedIssue['assignee'] ?? []);
                            $detailAuthor = (array) ($selectedIssue['author'] ?? []);
                            $detailActions = (array) ($selectedIssue['actions'] ?? []);
                            $detailComments = (array) ($selectedIssue['comments'] ?? []);
                            $detailStatusColor = (string) ($detailStatus['color'] ?? '');
                            $detailStatusStyle = str_starts_with($detailStatusColor, '#') ? '--evo-issue-chip: ' . $detailStatusColor : '';
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
                            <span><x-evo::icon name="message-circle" /> {{ (int) ($selectedIssue['comments_count'] ?? 0) }}</span>
                        </div>

                        <article class="evo-ui-issue-preview__content">
                            {!! $this->issueBodyHtml((string) ($selectedIssue['body_full'] ?? $selectedIssue['body'] ?? '')) !!}
                        </article>

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
                                                <strong>{{ $commentAuthor['label'] ?? 'Manager' }}</strong>
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
                                                    <span>{{ $commentParentAuthor['label'] ?? 'Manager' }}:</span>
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
                                            <span>@lang('dIssues::global.replying_to') {{ $detailReplyContextAuthor['label'] ?? 'Manager' }}</span>
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
            $previewActions = (array) ($selectedIssue['actions'] ?? []);
            $previewComments = (array) ($selectedIssue['comments'] ?? []);
            $previewStatusColor = (string) ($previewStatus['color'] ?? '');
            $previewStatusStyle = str_starts_with($previewStatusColor, '#') ? '--evo-issue-chip: ' . $previewStatusColor : '';
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
                        <span><x-evo::icon name="message-circle" /> {{ (int) ($selectedIssue['comments_count'] ?? 0) }}</span>
                    </div>

                    <article class="evo-ui-issue-preview__content">
                        {!! $this->issueBodyHtml((string) ($selectedIssue['body_full'] ?? $selectedIssue['body'] ?? '')) !!}
                    </article>

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
                                            <strong>{{ $commentAuthor['label'] ?? 'Manager' }}</strong>
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
                                                <span>{{ $commentParentAuthor['label'] ?? 'Manager' }}:</span>
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
                                        <span>@lang('dIssues::global.replying_to') {{ $previewReplyContextAuthor['label'] ?? 'Manager' }}</span>
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

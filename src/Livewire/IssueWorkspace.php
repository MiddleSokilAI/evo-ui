<?php

namespace EvoUI\Livewire;

use EvoUI\Contracts\IssueWorkspaceProvider;
use EvoUI\Support\RichTextEditor;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class IssueWorkspace extends Component
{
    public string $preset = '';
    public ?string $provider = null;
    public array $context = [];
    public array $filters = [
        'category_id' => 0,
        'status_id' => 0,
        'project_ids' => [],
        'category_ids' => [],
        'status_ids' => [],
        'phase_ids' => [],
        'priority_ids' => [],
        'assignee_ids' => [],
        'assignee' => 'all',
        'archive' => 'active',
        'display' => 'kanban',
        'search' => '',
    ];
    public ?int $selectedIssueId = null;
    public string $replyBody = '';
    public ?int $replyToCommentId = null;
    public bool $issueBodyEditing = false;
    public string $issueBodyDraft = '';

    public function mount(string $preset, ?string $provider = null, array $context = []): void
    {
        $this->preset = $preset;
        $this->provider = $provider;
        $this->context = $context;
        $this->filters = array_merge($this->filters, (array) $this->workspaceConfig('default_filters', []));
        $this->restoreSessionState();
        $this->normalizeFilters();
    }

    public function updated(string $name): void
    {
        if ($name === 'filters' || str_starts_with($name, 'filters.')) {
            $this->normalizeFilters();
            $this->selectedIssueId = null;
            $this->resetIssueBodyEditor();
            $this->dispatchClientState();
        }
    }

    public function updatedFilters(): void
    {
        $this->normalizeFilters();
        $this->selectedIssueId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function setCategory(int $categoryId): void
    {
        $allowed = collect($this->categories())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->filters['category_id'] = in_array($categoryId, $allowed, true) ? $categoryId : 0;
        $this->selectedIssueId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function setStatus(int $statusId): void
    {
        $allowed = collect($this->statuses())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->filters['status_id'] = in_array($statusId, $allowed, true) ? $statusId : 0;
        $this->selectedIssueId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function applyMultiFilter(string $state, array $values): void
    {
        $options = match ($state) {
            'project_ids' => $this->projects(),
            'category_ids' => $this->categories(),
            'status_ids' => $this->statuses(),
            'phase_ids' => $this->phases(),
            'priority_ids' => $this->priorities(),
            'assignee_ids' => $this->assignees(),
            default => [],
        };

        if ($options === []) {
            return;
        }

        $allowed = collect($options)
            ->map(fn ($option) => (int) ($option['id'] ?? $option['value'] ?? 0))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $state === 'assignee_ids' ? $id !== 0 : $id > 0)
            ->all();

        $this->filters[$state] = collect($values)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => ($state === 'assignee_ids' ? $id !== 0 : $id > 0) && in_array($id, $allowed, true))
            ->unique()
            ->values()
            ->all();

        $this->selectedIssueId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function setAssignee(string $assignee): void
    {
        $allowed = collect($this->assignees())->pluck('value')->map(fn ($value) => (string) $value)->all();
        $this->filters['assignee'] = in_array($assignee, $allowed, true) ? $assignee : 'all';
        $this->selectedIssueId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function switchDisplay(string $display): void
    {
        $allowed = collect($this->displays())->pluck('value')->map(fn ($value) => (string) $value)->all();
        $this->filters['display'] = in_array($display, $allowed, true)
            ? $display
            : (string) $this->workspaceConfig('default_display', 'kanban');

        $this->selectedIssueId = null;
        $this->dispatchClientState();
    }

    public function setArchive(string $archive): void
    {
        $allowed = collect($this->archiveModes())->pluck('value')->map(fn ($value) => (string) $value)->all();
        $this->filters['archive'] = in_array($archive, $allowed, true) ? $archive : 'active';
        $this->selectedIssueId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function resetFilters(): void
    {
        $this->filters = array_merge([
            'category_id' => 0,
            'status_id' => 0,
            'project_ids' => [],
            'category_ids' => [],
            'status_ids' => [],
            'phase_ids' => [],
            'priority_ids' => [],
            'assignee_ids' => [],
            'assignee' => 'all',
            'archive' => 'active',
            'display' => (string) $this->workspaceConfig('default_display', 'kanban'),
            'search' => '',
        ], (array) $this->workspaceConfig('default_filters', []));

        $this->normalizeFilters();
        $this->selectedIssueId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function selectIssue(int $issueId): void
    {
        $this->selectedIssueId = $issueId > 0 ? $issueId : null;
        $this->replyBody = '';
        $this->replyToCommentId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function closeIssuePreview(): void
    {
        $this->selectedIssueId = null;
        $this->replyBody = '';
        $this->replyToCommentId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function createIssue(): void
    {
        $this->selectedIssueId = null;
        $this->replyBody = '';
        $this->replyToCommentId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
        $this->dispatch('evo-ui:issue.create', preset: $this->preset);
    }

    public function startIssueBodyEdit(): void
    {
        $issue = $this->selectedIssueId ? $this->issuePreview($this->selectedIssueId) : null;

        if (!$issue) {
            return;
        }

        $this->issueBodyDraft = (string) ($issue['body_full'] ?? $issue['body'] ?? '');
        $this->issueBodyEditing = true;
    }

    public function cancelIssueBodyEdit(): void
    {
        $this->resetIssueBodyEditor();
    }

    public function saveIssueBody(): void
    {
        if (!$this->selectedIssueId || !$this->issueBodyEditing) {
            return;
        }

        $this->callIssueProviderAction('updateIssueBody', $this->issueBodyDraft);
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function assignIssueToMe(): void
    {
        $this->callIssueProviderAction('assignIssueToMe');
        $this->dispatchClientState();
    }

    public function assignIssue(int $userId): void
    {
        if ($userId < 1) {
            return;
        }

        $this->callIssueProviderAction('assignIssue', $userId);
        $this->dispatchClientState();
    }

    public function unassignIssue(): void
    {
        $this->callIssueProviderAction('unassignIssue');
        $this->dispatchClientState();
    }

    public function createChildIssue(): void
    {
        if (!$this->selectedIssueId) {
            return;
        }

        $provider = $this->provider();

        if (!$provider || !method_exists($provider, 'createChildIssue')) {
            return;
        }

        $childId = (int) $provider->createChildIssue($this->selectedIssueId);

        if ($childId > 0) {
            $this->selectedIssueId = $childId;
        }

        $this->replyBody = '';
        $this->replyToCommentId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function replyToComment(int $commentId): void
    {
        $this->replyToCommentId = $commentId > 0 ? $commentId : null;
        $this->dispatchClientState();
    }

    public function cancelReplyToComment(): void
    {
        $this->replyToCommentId = null;
        $this->dispatchClientState();
    }

    public function replyIssue(): void
    {
        if (trim($this->replyBody) === '') {
            return;
        }

        $this->callIssueProviderAction('replyIssue', $this->replyBody, $this->replyToCommentId);
        $this->replyBody = '';
        $this->replyToCommentId = null;
        $this->dispatchClientState();
    }

    public function replyAndCloseIssue(): void
    {
        if (trim($this->replyBody) === '') {
            return;
        }

        $this->callIssueProviderAction('replyAndCloseIssue', $this->replyBody, $this->replyToCommentId);
        $this->replyBody = '';
        $this->replyToCommentId = null;
        $this->dispatchClientState();
    }

    public function closeSelectedIssue(): void
    {
        $this->callIssueProviderAction('closeIssue');
    }

    public function reopenSelectedIssue(): void
    {
        $this->callIssueProviderAction('reopenIssue');
    }

    public function moveSelectedIssuePrevious(): void
    {
        $this->callIssueProviderAction('moveIssuePrevious');
        $this->dispatchClientState();
    }

    public function moveSelectedIssueNext(): void
    {
        $this->callIssueProviderAction('moveIssueNext');
        $this->dispatchClientState();
    }

    public function archiveStatusIssues(int $statusId): void
    {
        $provider = $this->provider();

        if (!$provider || !method_exists($provider, 'archiveStatusIssues')) {
            return;
        }

        $provider->archiveStatusIssues(max(0, $statusId));
        $this->selectedIssueId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function sortKanbanLanes(array $lanes): void
    {
        $provider = $this->provider();

        if (!$provider || !method_exists($provider, 'sortKanbanLanes')) {
            return;
        }

        $normalized = collect($lanes)
            ->map(function ($lane) {
                $lane = is_array($lane) ? $lane : [];

                return [
                    'status_id' => max(0, (int) ($lane['status_id'] ?? 0)),
                    'issue_ids' => $this->normalizeIdList((array) ($lane['issue_ids'] ?? [])),
                ];
            })
            ->filter(fn (array $lane) => $lane['status_id'] > 0)
            ->values()
            ->all();

        if ($normalized === []) {
            return;
        }

        $provider->sortKanbanLanes($normalized);
        $this->selectedIssueId = null;
        $this->resetIssueBodyEditor();
        $this->dispatchClientState();
    }

    public function issueBodyHtml(string $body): string
    {
        $body = trim($body);

        if ($body === '') {
            return '';
        }

        if ($body === strip_tags($body)) {
            return nl2br(htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }

        $allowed = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><pre><code><h1><h2><h3><h4><hr><a><img><table><thead><tbody><tr><th><td>';
        $html = strip_tags($body, $allowed);
        $html = (string) preg_replace("/\\s+(href|src)\\s*=\\s*(\"|')\\s*javascript:[^\"']*\\2/i", '', $html);

        return (string) preg_replace("/\\s+on[a-z]+\\s*=\\s*(\"[^\"]*\"|'[^']*'|[^\\s>]+)/i", '', $html);
    }

    public function replyEditorHtml(string $fieldId): string
    {
        $provider = $this->provider();

        if ($provider && method_exists($provider, 'replyEditorHtml')) {
            return (string) $provider->replyEditorHtml($fieldId, [
                'height' => (string) $this->workspaceConfig('reply_editor.height', '220px'),
                'editor' => (string) $this->workspaceConfig('reply_editor.editor', 'system'),
                'content_type' => (string) $this->workspaceConfig('reply_editor.content_type', 'htmlmixed'),
            ]);
        }

        return RichTextEditor::html(
            ids: $fieldId,
            height: (string) $this->workspaceConfig('reply_editor.height', '220px'),
            editor: (string) $this->workspaceConfig('reply_editor.editor', 'system'),
            contentType: (string) $this->workspaceConfig('reply_editor.content_type', 'htmlmixed'),
        );
    }

    public function issueBodyEditorHtml(string $fieldId): string
    {
        $provider = $this->provider();

        if ($provider && method_exists($provider, 'issueBodyEditorHtml')) {
            return (string) $provider->issueBodyEditorHtml($fieldId, [
                'height' => (string) $this->workspaceConfig('body_editor.height', '360px'),
                'editor' => (string) $this->workspaceConfig('body_editor.editor', $this->workspaceConfig('reply_editor.editor', 'system')),
                'content_type' => (string) $this->workspaceConfig('body_editor.content_type', 'htmlmixed'),
            ]);
        }

        return RichTextEditor::html(
            ids: $fieldId,
            height: (string) $this->workspaceConfig('body_editor.height', '360px'),
            editor: (string) $this->workspaceConfig('body_editor.editor', $this->workspaceConfig('reply_editor.editor', 'system')),
            contentType: (string) $this->workspaceConfig('body_editor.content_type', 'htmlmixed'),
        );
    }

    public function render(): View
    {
        $issueList = $this->filters['display'] === 'list' ? $this->issueList() : [];

        return view('evo::livewire.issue-workspace', [
            'config' => $this->workspaceConfig(),
            'projects' => $this->projects(),
            'categories' => $this->categories(),
            'statuses' => $this->statuses(),
            'phases' => $this->phases(),
            'priorities' => $this->priorities(),
            'assignees' => $this->assignees(),
            'displays' => $this->displays(),
            'archiveModes' => $this->archiveModes(),
            'metrics' => $this->metrics(),
            'filters' => $this->filters,
            'showStatusFilter' => $this->filters['display'] === 'list',
            'kanbanLanes' => $this->filters['display'] === 'kanban' ? $this->kanbanLanes() : [],
            'issueList' => $issueList,
            'selectedIssue' => $this->selectedIssue($issueList),
        ]);
    }

    protected function normalizeFilters(): void
    {
        $this->filters['category_id'] = max(0, (int) ($this->filters['category_id'] ?? 0));
        $this->filters['status_id'] = max(0, (int) ($this->filters['status_id'] ?? 0));
        $this->filters['project_ids'] = $this->normalizeIdList((array) ($this->filters['project_ids'] ?? []));
        $this->filters['category_ids'] = $this->normalizeIdList((array) ($this->filters['category_ids'] ?? []));
        $this->filters['status_ids'] = $this->normalizeIdList((array) ($this->filters['status_ids'] ?? []));
        $this->filters['phase_ids'] = $this->normalizeIdList((array) ($this->filters['phase_ids'] ?? []));
        $this->filters['priority_ids'] = $this->normalizeIdList((array) ($this->filters['priority_ids'] ?? []));
        $this->filters['assignee_ids'] = $this->normalizeAssigneeIdList((array) ($this->filters['assignee_ids'] ?? []));
        $this->filters['search'] = trim(strip_tags((string) ($this->filters['search'] ?? '')));

        $allowedAssignees = collect($this->assignees())->pluck('value')->map(fn ($value) => (string) $value)->all();
        $assignee = (string) ($this->filters['assignee'] ?? 'all');
        $this->filters['assignee'] = in_array($assignee, $allowedAssignees, true) ? $assignee : 'all';
        $allowedAssigneeIds = collect($this->assignees())
            ->map(fn ($option) => (int) ($option['id'] ?? $option['value'] ?? 0))
            ->filter(fn ($id) => $id !== 0)
            ->values()
            ->all();
        $this->filters['assignee_ids'] = collect($this->filters['assignee_ids'])
            ->filter(fn (int $id) => in_array($id, $allowedAssigneeIds, true))
            ->values()
            ->all();

        $allowedArchiveModes = collect($this->archiveModes())->pluck('value')->map(fn ($value) => (string) $value)->all();
        $archive = (string) ($this->filters['archive'] ?? 'active');
        $this->filters['archive'] = in_array($archive, $allowedArchiveModes, true) ? $archive : 'active';

        $allowedDisplays = collect($this->displays())->pluck('value')->map(fn ($value) => (string) $value)->all();
        $defaultDisplay = (string) $this->workspaceConfig('default_display', 'kanban');
        $display = (string) ($this->filters['display'] ?? $defaultDisplay);
        $this->filters['display'] = in_array($display, $allowedDisplays, true)
            ? $display
            : (in_array($defaultDisplay, $allowedDisplays, true) ? $defaultDisplay : ($allowedDisplays[0] ?? 'kanban'));

        $allowedProjects = collect($this->projects())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->filters['project_ids'] = collect($this->filters['project_ids'])
            ->filter(fn (int $id) => in_array($id, $allowedProjects, true))
            ->values()
            ->all();

        $allowedCategories = collect($this->categories())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->filters['category_id'] = in_array((int) $this->filters['category_id'], $allowedCategories, true)
            ? (int) $this->filters['category_id']
            : 0;
        $this->filters['category_ids'] = collect($this->filters['category_ids'])
            ->filter(fn (int $id) => in_array($id, $allowedCategories, true))
            ->values()
            ->all();

        $allowedStatuses = collect($this->statuses())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->filters['status_id'] = in_array((int) $this->filters['status_id'], $allowedStatuses, true)
            ? (int) $this->filters['status_id']
            : 0;
        $this->filters['status_ids'] = collect($this->filters['status_ids'])
            ->filter(fn (int $id) => in_array($id, $allowedStatuses, true))
            ->values()
            ->all();

        $allowedPhases = collect($this->phases())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->filters['phase_ids'] = collect($this->filters['phase_ids'])
            ->filter(fn (int $id) => in_array($id, $allowedPhases, true))
            ->values()
            ->all();

        $allowedPriorities = collect($this->priorities())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->filters['priority_ids'] = collect($this->filters['priority_ids'])
            ->filter(fn (int $id) => in_array($id, $allowedPriorities, true))
            ->values()
            ->all();
    }

    protected function normalizeIdList(array $values): array
    {
        return collect($values)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizeAssigneeIdList(array $values): array
    {
        return collect($values)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id !== 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function workspaceConfig(?string $key = null, mixed $default = null): mixed
    {
        $config = config($this->preset . '.workspace', []);

        return $key === null ? $config : data_get($config, $key, $default);
    }

    public function storageKey(): string
    {
        $configured = (string) $this->workspaceConfig('storage_key', '');

        if ($configured !== '') {
            return $configured;
        }

        $context = collect($this->context)
            ->only(['type', 'site', 'module'])
            ->filter(fn ($value) => is_scalar($value) && (string) $value !== '')
            ->all();

        return 'evo-ui.issue-workspace.' . sha1($this->preset . '|' . json_encode($context));
    }

    public function persistedState(): array
    {
        return [
            'filters' => $this->filters,
            'selectedIssueId' => $this->selectedIssueId,
            'replyToCommentId' => $this->replyToCommentId,
        ];
    }

    protected function restoreSessionState(): void
    {
        $state = session()->get($this->storageKey());

        if (!is_array($state)) {
            return;
        }

        if (array_key_exists('filters', $state) && is_array($state['filters'])) {
            $this->filters = array_merge($this->filters, $state['filters']);
        }

        if (array_key_exists('selectedIssueId', $state)) {
            $selectedIssueId = (int) $state['selectedIssueId'];
            $this->selectedIssueId = $selectedIssueId > 0 ? $selectedIssueId : null;
        }

        if (array_key_exists('replyToCommentId', $state)) {
            $replyToCommentId = (int) $state['replyToCommentId'];
            $this->replyToCommentId = $replyToCommentId > 0 ? $replyToCommentId : null;
        }

    }

    protected function dispatchClientState(): void
    {
        $this->persistServerState();
    }

    protected function persistServerState(): void
    {
        session()->put($this->storageKey(), $this->persistedState());
    }

    protected function provider(): ?IssueWorkspaceProvider
    {
        $class = $this->provider ?: $this->workspaceConfig('provider');

        if (!$class || !class_exists($class)) {
            return null;
        }

        $provider = new $class(
            context: $this->context,
            filters: $this->filters,
            config: $this->workspaceConfig(),
        );

        return $provider instanceof IssueWorkspaceProvider ? $provider : null;
    }

    protected function categories(): array
    {
        $provider = $this->provider();

        return $provider && method_exists($provider, 'categories') ? (array) $provider->categories() : [];
    }

    protected function projects(): array
    {
        $provider = $this->provider();

        return $provider && method_exists($provider, 'projects') ? (array) $provider->projects() : [];
    }

    protected function statuses(): array
    {
        $provider = $this->provider();

        return $provider && method_exists($provider, 'statuses') ? (array) $provider->statuses() : [];
    }

    protected function phases(): array
    {
        $provider = $this->provider();

        return $provider && method_exists($provider, 'phases') ? (array) $provider->phases() : [];
    }

    protected function priorities(): array
    {
        $provider = $this->provider();

        if ($provider && method_exists($provider, 'priorities')) {
            return (array) $provider->priorities();
        }

        return (array) $this->workspaceConfig('priorities', []);
    }

    protected function assignees(): array
    {
        $provider = $this->provider();

        if ($provider && method_exists($provider, 'assignees')) {
            return (array) $provider->assignees();
        }

        return (array) $this->workspaceConfig('assignees', []);
    }

    protected function displays(): array
    {
        return (array) $this->workspaceConfig('displays', [
            ['value' => 'list', 'icon' => 'list', 'label' => 'evo::global.view_list'],
            ['value' => 'kanban', 'icon' => 'columns-3', 'label' => 'evo::global.view_kanban'],
        ]);
    }

    protected function archiveModes(): array
    {
        return (array) $this->workspaceConfig('archive_modes', [
            ['value' => 'active', 'icon' => 'inbox', 'label' => 'dIssues::global.archive_active'],
            ['value' => 'archived', 'icon' => 'archive', 'label' => 'dIssues::global.archive_archived'],
        ]);
    }

    protected function metrics(): array
    {
        $provider = $this->provider();

        return $provider && method_exists($provider, 'metrics') ? (array) $provider->metrics($this->filters) : [];
    }

    protected function kanbanLanes(): array
    {
        $provider = $this->provider();

        return $provider && method_exists($provider, 'kanbanLanes') ? (array) $provider->kanbanLanes($this->filters) : [];
    }

    protected function issueList(): array
    {
        $provider = $this->provider();

        return $provider && method_exists($provider, 'issueList') ? (array) $provider->issueList($this->filters) : [];
    }

    protected function selectedIssue(array $issueList = []): ?array
    {
        if ($this->selectedIssueId) {
            return $this->issuePreview($this->selectedIssueId);
        }

        if ($this->filters['display'] !== 'list') {
            return null;
        }

        $firstIssueId = (int) ($issueList[0]['id'] ?? 0);

        return $firstIssueId > 0 ? $this->issuePreview($firstIssueId) : null;
    }

    protected function issuePreview(int $issueId): ?array
    {
        $provider = $this->provider();

        if (!$provider || !method_exists($provider, 'issuePreview')) {
            return null;
        }

        $issue = $provider->issuePreview($issueId);

        return is_array($issue) ? $issue : null;
    }

    protected function callIssueProviderAction(string $method, mixed ...$arguments): void
    {
        if (!$this->selectedIssueId) {
            return;
        }

        $provider = $this->provider();

        if (!$provider || !method_exists($provider, $method)) {
            return;
        }

        $provider->{$method}($this->selectedIssueId, ...$arguments);
    }

    protected function resetIssueBodyEditor(): void
    {
        $this->issueBodyEditing = false;
        $this->issueBodyDraft = '';
    }
}

<?php

namespace EvoUI\Contracts;

interface IssueWorkspaceProvider
{
    public function projects(): array;

    public function categories(): array;

    public function statuses(): array;

    public function assignees(): array;

    public function metrics(array $filters = []): array;

    public function issueList(array $filters = []): array;

    public function listRows(array $filters = []): array;

    public function kanbanLanes(array $filters = []): array;

    public function issuePreview(int $issueId): ?array;

    public function issueDetail(int $issueId): ?array;

    public function comments(int $issueId): array;

    public function sortKanbanLanes(array $lanes): void;

    public function assignIssueToMe(int $issueId): void;

    public function assignIssue(int $issueId, int $userId): void;

    public function unassignIssue(int $issueId): void;

    public function replyIssue(int $issueId, string $body, ?int $parentCommentId = null): void;

    public function replyAndCloseIssue(int $issueId, string $body, ?int $parentCommentId = null): void;

    public function closeIssue(int $issueId): void;

    public function reopenIssue(int $issueId): void;

    public function diagnostics(array $filters = []): array;
}

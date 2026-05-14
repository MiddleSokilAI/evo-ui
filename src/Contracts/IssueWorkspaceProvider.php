<?php

namespace EvoUI\Contracts;

interface IssueWorkspaceProvider
{
    /** @return list<array<string, mixed>> */
    public function projects(): array;

    /** @return list<array<string, mixed>> */
    public function categories(): array;

    /** @return list<array<string, mixed>> */
    public function statuses(): array;

    /** @return list<array<string, mixed>> */
    public function assignees(): array;

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function metrics(array $filters = []): array;

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function issueList(array $filters = []): array;

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function listRows(array $filters = []): array;

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    public function kanbanLanes(array $filters = []): array;

    /** @return array<string, mixed>|null */
    public function issuePreview(int $issueId): ?array;

    /** @return array<string, mixed>|null */
    public function issueDetail(int $issueId): ?array;

    /** @return list<array<string, mixed>> */
    public function comments(int $issueId): array;

    /** @param list<array<string, mixed>> $lanes */
    public function sortKanbanLanes(array $lanes): void;

    public function assignIssueToMe(int $issueId): void;

    public function assignIssue(int $issueId, int $userId): void;

    public function unassignIssue(int $issueId): void;

    public function replyIssue(int $issueId, string $body, ?int $parentCommentId = null): void;

    public function replyAndCloseIssue(int $issueId, string $body, ?int $parentCommentId = null): void;

    public function closeIssue(int $issueId): void;

    public function reopenIssue(int $issueId): void;

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function diagnostics(array $filters = []): array;
}

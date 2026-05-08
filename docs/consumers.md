# evoUI Consumers

This page documents the two real consumers that shape the first evoUI release.

## sArticles

`sArticles` uses evoUI as the main manager UI runtime:

- module tabs rendered inside an evoUI-owned iframe;
- config-driven tables for publications, authors, tags, comments, polls,
  categories, features, TV parameters, and settings dictionaries;
- modal create/edit forms with inline choices, image fields, rich text fields,
  content builder blocks, delete confirmation, duplicate and publish actions;
- session-persistent table state for filters, search, pagination, sorting and
  table/list mode;
- config form for base settings and publication types;
- rich editor selection through module settings, not per-field UI clutter;
- integration surfaces for `sSeo` and `sLang`.

The sArticles rule is important for future modules: module-specific hooks stay
in the module. evoUI receives only normalized config, provider methods and
generic UI behavior.

## dIssues

`dIssues` uses evoUI as a second independent consumer:

- issue table with filters, search, list/table views and modal editing;
- settings forms and dictionary tables;
- provider-backed `evo-ui.issue-workspace`;
- kanban/list display switching;
- drag/drop kanban lane sorting;
- conversation preview and issue actions;
- session-persistent workspace state for display, filters, search and selected
  issue.

This proves evoUI is not only an article UI helper. It is a reusable manager
surface package for data-heavy modules and workflow modules.

## Release Implication

Before tagging evoUI, both consumers should pass their smoke checks. A change in
evoUI must be considered unsafe if it only works for `sArticles` but regresses
`dIssues`, or the reverse.

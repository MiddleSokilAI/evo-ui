<?php

namespace EvoUI;

class EvoUI
{
    protected array $components = [];
    protected array $tableCells = [];
    protected array $filters = [];
    protected array $actions = [];
    protected array $formFields = [];

    public function registerComponent(string $name, string $class): void
    {
        $this->components[$name] = $class;
    }

    public function registerTableCell(string $name, string $class): void
    {
        $this->tableCells[$name] = $class;
    }

    public function registerFilter(string $name, string $class): void
    {
        $this->filters[$name] = $class;
    }

    public function registerAction(string $name, string $class): void
    {
        $this->actions[$name] = $class;
    }

    public function registerFormField(string $name, string $view): void
    {
        $this->formFields[$name] = $view;
    }

    public function components(): array
    {
        return $this->components;
    }

    public function tableCells(): array
    {
        return $this->tableCells;
    }

    public function filters(): array
    {
        return $this->filters;
    }

    public function actions(): array
    {
        return $this->actions;
    }

    public function formFields(): array
    {
        return $this->formFields;
    }

    public function formFieldView(array $field): ?string
    {
        $name = (string) ($field['name'] ?? '');
        $type = (string) ($field['type'] ?? '');

        return $this->formFields[$name]
            ?? $this->formFields[$type]
            ?? ($field['view'] ?? null);
    }

    public function tableCellView(array $column): ?string
    {
        $cell = (string) ($column['cell'] ?? '');

        return $this->tableCells[$cell] ?? ($column['view'] ?? null);
    }
}

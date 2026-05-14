<?php

namespace EvoUI;

class EvoUI
{
    /** @var array<string, string> */
    protected array $components = [];
    /** @var array<string, string> */
    protected array $tableCells = [];
    /** @var array<string, string> */
    protected array $filters = [];
    /** @var array<string, string> */
    protected array $actions = [];
    /** @var array<string, string> */
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

    /**
     * @return array<string, string>
     */
    public function components(): array
    {
        return $this->components;
    }

    /**
     * @return array<string, string>
     */
    public function tableCells(): array
    {
        return $this->tableCells;
    }

    /**
     * @return array<string, string>
     */
    public function filters(): array
    {
        return $this->filters;
    }

    /**
     * @return array<string, string>
     */
    public function actions(): array
    {
        return $this->actions;
    }

    /**
     * @return array<string, string>
     */
    public function formFields(): array
    {
        return $this->formFields;
    }

    /**
     * @param array<string, mixed> $field
     */
    public function formFieldView(array $field): ?string
    {
        $name = (string) ($field['name'] ?? '');
        $type = (string) ($field['type'] ?? '');

        return $this->formFields[$name]
            ?? $this->formFields[$type]
            ?? ($field['view'] ?? null);
    }

    /**
     * @param array<string, mixed> $column
     */
    public function tableCellView(array $column): ?string
    {
        $cell = (string) ($column['cell'] ?? '');

        return $this->tableCells[$cell] ?? ($column['view'] ?? null);
    }
}

<?php

namespace EvoUI\Livewire\Foundation\Auth\Access;

class Response
{
    public function __construct(
        protected bool $allowed = false,
        protected ?string $message = null
    ) {
    }

    public static function allow(?string $message = null): self
    {
        return new self(true, $message);
    }

    public static function deny(?string $message = null): self
    {
        return new self(false, $message);
    }

    public function allowed(): bool
    {
        return $this->allowed;
    }

    public function denied(): bool
    {
        return !$this->allowed;
    }

    public function message(): ?string
    {
        return $this->message;
    }
}

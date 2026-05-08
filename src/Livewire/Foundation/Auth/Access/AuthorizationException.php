<?php

namespace EvoUI\Livewire\Foundation\Auth\Access;

class AuthorizationException extends \RuntimeException
{
    protected int $status = 403;

    public function status(): int
    {
        return $this->status;
    }
}

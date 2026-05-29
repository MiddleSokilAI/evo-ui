<?php

namespace EvoUI\Livewire\Foundation\Testing\Concerns;

trait InteractsWithExceptionHandling
{
    /**
     * @param list<class-string<\Throwable>> $except
     */
    public function withoutExceptionHandling(array $except = []): static
    {
        return $this;
    }
}

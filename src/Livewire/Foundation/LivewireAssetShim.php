<?php

namespace EvoUI\Livewire\Foundation;

class LivewireAssetShim
{
    protected ?string $cspNonce = null;

    public function useScriptTagAttributes($attributes): static
    {
        return $this;
    }

    public function useStyleTagAttributes($attributes): static
    {
        return $this;
    }

    public function useCspNonce(?string $nonce = null): static
    {
        $this->cspNonce = $nonce;

        return $this;
    }

    public function cspNonce(): ?string
    {
        return $this->cspNonce;
    }
}

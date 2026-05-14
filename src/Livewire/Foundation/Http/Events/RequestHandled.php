<?php

namespace EvoUI\Livewire\Foundation\Http\Events;

class RequestHandled
{
    public function __construct(
        public mixed $app,
        public mixed $request,
        public mixed $response
    ) {
    }
}

<?php

namespace EvoUI\Livewire\Foundation\Http\Events;

class RequestHandled
{
    public function __construct(
        public $app,
        public $request,
        public $response
    ) {
    }
}

<?php

namespace Asciisd\Knet\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KnetResponseReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * The response payload.
     *
     * @var array
     */
    public $payload;

    /**
     * Create a new event instance.
     *
     * @param array $payload
     * @return void
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}

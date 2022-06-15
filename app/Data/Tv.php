<?php

namespace App\Data;

use App\Enums\Channel;

final class Tv
{
    /**
     * @param \App\Enums\Channel $channel
     * @param iterable<\App\Data\Program> $programs
     */
    public function __construct(
        public Channel $channel,
        public iterable $programs,
    )
    {
    }
}

<?php

namespace App\Data;

use Carbon\CarbonInterface;

final class Program
{
    public function __construct(
        public string           $title,
        public ?string          $description,
        public ?string          $icon,
        public CarbonInterface  $start,
        public ?CarbonInterface $stop,
    )
    {
    }
}

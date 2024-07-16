<?php

namespace App\Data;

use Carbon\CarbonInterface;

final class Program
{
    public function __construct(
        public string $title,
        public ?string $subtitle,
        public ?string $description,
        public ?string $icon,
        public CarbonInterface $start,
        public ?CarbonInterface $stop,
        public ?int $season,
        public ?int $episode,
        public array $categories = [],
    ) {}
}

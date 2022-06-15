<?php

namespace App\Crawlers;

use App\Data\Tv;
use App\Enums\Channel;

abstract class Crawler
{
    abstract protected function channel(): Channel;

    abstract public function crawl(): Tv;
}

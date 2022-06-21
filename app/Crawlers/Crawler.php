<?php

namespace App\Crawlers;

use App\Data\Program;
use App\Data\Tv;
use App\Enums\Channel;

abstract class Crawler
{
    abstract protected function channel(): Channel;

    abstract protected function tap(Program $program): Program;

    abstract public function crawl(): Tv;
}

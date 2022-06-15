<?php

namespace App\Crawlers;

use App\Data\Tv;

abstract class Crawler
{
    abstract public function crawl(): Tv;
}

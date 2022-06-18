<?php

namespace App\Crawlers;

use App\Enums\Channel;

class KikaCrawler extends BaseArdCrawler
{
    protected function channel(): Channel
    {
        return Channel::KIKA();
    }

    protected function sender(): int
    {
        return 28008;
    }
}

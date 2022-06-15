<?php

namespace App\Crawlers;

use App\Enums\Channel;

class ArdCrawler extends BaseArdCrawler
{
    protected function sender(): int
    {
        return 28106;
    }

    protected function channel(): Channel
    {
        return Channel::ARD();
    }
}

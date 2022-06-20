<?php

namespace App\Crawlers;

use App\Enums\Channel;

class NdrCrawler extends BaseArdCrawler
{
    protected function sender(): int
    {
        return -28226;
    }

    protected function channel(): Channel
    {
        return Channel::NDR();
    }
}

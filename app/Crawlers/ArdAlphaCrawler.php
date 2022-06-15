<?php

namespace App\Crawlers;

use App\Enums\Channel;

class ArdAlphaCrawler extends BaseArdCrawler
{
    protected function sender(): int
    {
        return 28487;
    }

    protected function channel(): Channel
    {
        return Channel::ARD_ALPHA();
    }
}

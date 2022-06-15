<?php

namespace App\Crawlers;

use App\Enums\Channel;

class ArdOneCrawler extends BaseArdCrawler
{
    protected function sender(): int
    {
        return 28722;
    }

    protected function channel(): Channel
    {
        return Channel::ARD_ONE();
    }
}

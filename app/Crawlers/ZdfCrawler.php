<?php

namespace App\Crawlers;

use App\Enums\Channel;

class ZdfCrawler extends BaseZdfCrawler
{
    protected function channel(): Channel
    {
        return Channel::ZDF();
    }

    protected function timeline(): string
    {
        return 'ZDF';
    }
}

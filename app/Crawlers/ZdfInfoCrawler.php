<?php

namespace App\Crawlers;

use App\Enums\Channel;

class ZdfInfoCrawler extends BaseZdfCrawler
{
    protected function channel(): Channel
    {
        return Channel::ZDF_INFO();
    }

    protected function timeline(): string
    {
        return 'ZDFinfo';
    }
}

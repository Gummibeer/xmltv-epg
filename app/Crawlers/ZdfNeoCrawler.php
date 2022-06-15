<?php

namespace App\Crawlers;

use App\Enums\Channel;

class ZdfNeoCrawler extends BaseZdfCrawler
{
    protected function channel(): Channel
    {
        return Channel::ZDF_NEO();
    }

    protected function timeline(): string
    {
        return 'ZDFneo';
    }
}

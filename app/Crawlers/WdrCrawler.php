<?php

namespace App\Crawlers;

use App\Data\Program;
use App\Enums\Channel;
use Illuminate\Support\Str;

class WdrCrawler extends BaseArdCrawler
{
    protected function sender(): int
    {
        return -28111;
    }

    protected function channel(): Channel
    {
        return Channel::WDR();
    }

    protected function tap(Program $program): Program
    {
        $program = parent::tap($program);

        if ($program->subtitle) {
            $program->subtitle = (string) Str::of($program->subtitle)->replaceLast('WDR Fernsehen', '')->trim('| ');
        }

        if (in_array($program->title, ['Regionalprogramm', 'Aktuelle Stunde', 'Hier und heute']) || str_starts_with($program->title, 'Lokalzeit')) {
            $program->categories[] = 'news';
        }

        return $program;
    }
}

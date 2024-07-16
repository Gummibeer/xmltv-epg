<?php

namespace App\Crawlers;

use App\Data\Program;
use App\Enums\Channel;
use Illuminate\Support\Str;

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

    protected function tap(Program $program): Program
    {
        $program = parent::tap($program);

        if ($program->subtitle) {
            $program->subtitle = (string) Str::of($program->subtitle)->replaceLast('NDR Fernsehen', '')->trim('| ');
        }

        return $program;
    }
}

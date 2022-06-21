<?php

namespace App\Crawlers;

use App\Data\Program;
use App\Enums\Channel;
use Illuminate\Support\Str;

class KikaCrawler extends BaseArdCrawler
{
    protected function channel(): Channel
    {
        return Channel::KIKA();
    }

    protected function tap(Program $program): Program
    {
        $program = parent::tap($program);

        $program->categories[] = 'children';

        if($program->subtitle) {
            $program->subtitle = (string)Str::of($program->subtitle)->replaceLast('| KiKA', '')->trim();
        }

        return $program;
    }

    protected function sender(): int
    {
        return 28008;
    }
}

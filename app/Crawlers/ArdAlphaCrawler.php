<?php

namespace App\Crawlers;

use App\Data\Program;
use App\Enums\Channel;
use Illuminate\Support\Str;

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

    protected function tap(Program $program): Program
    {
        $program = parent::tap($program);

        if ($program->subtitle) {
            $program->subtitle = (string) Str::of($program->subtitle)->replaceLast('ARD alpha', '')->trim('| ');
        }

        return $program;
    }
}

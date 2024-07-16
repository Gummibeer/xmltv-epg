<?php

namespace App\Crawlers;

use App\Data\Program;
use App\Enums\Channel;
use Illuminate\Support\Str;

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

    protected function tap(Program $program): Program
    {
        $program = parent::tap($program);

        if ($program->subtitle) {
            $program->subtitle = (string) Str::of($program->subtitle)->replaceLast('ONE', '')->trim('| ');
        }

        return $program;
    }
}

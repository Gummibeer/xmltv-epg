<?php

namespace App\Crawlers;

use App\Data\Program;
use App\Enums\Channel;

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

        return $program;
    }

    protected function sender(): int
    {
        return 28008;
    }
}

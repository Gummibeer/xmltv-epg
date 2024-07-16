<?php

namespace App\Crawlers;

use App\Data\Program;
use App\Data\Tv;
use App\Enums\Channel;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

abstract class Crawler
{
    abstract protected function channel(): Channel;

    abstract protected function tap(Program $program): Program;

    abstract public function crawl(): Tv;

    /**
     * @return \Illuminate\Support\Collection<\Carbon\CarbonImmutable>
     */
    protected function dates(): Collection
    {
        $date = CarbonImmutable::now('Europe/Berlin');

        return collect(CarbonPeriod::since($date->startOfDay())->days()->until($date->addDays(10)->endOfDay()))
            ->map(fn (CarbonInterface $carbon) => CarbonImmutable::instance($carbon));
    }
}

<?php

namespace App\Crawlers;

use App\Data\Program;
use App\Data\Tv;
use App\Enums\Channel;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class NickelodeonCrawler extends Crawler
{
    protected function channel(): Channel
    {
        return Channel::NICKELODEON();
    }

    protected function tap(Program $program): Program
    {
        $program->categories[] = 'children';

        return $program;
    }

    public function crawl(): Tv
    {
        $programs = collect(Http::pool(function (Pool $pool): array {
            return $this->dates()
                ->map(fn (DateTimeInterface $date) => $pool->accept('application/json')->get("https://www.nick.de/api/more/tvschedule/{$date->format('Ymd')}/nickelodeon-deutschland"))
                ->all();
        }))
            ->map(static function (Response $response): Collection {
                return collect($response->json('tvSchedules'))
                    ->map(static function (array $data): Program {
                        $start = CarbonImmutable::parse($data['airTime'], 'Europe/Berlin');
                        $stop = $start->addMinutes($data['duration']);
                        $title = $data['seriesTitle'];
                        $subtitle = data_get($data, 'episodeTitle');
                        $description = data_get($data, 'meta.description');

                        return new Program(
                            title: $title,
                            subtitle: $subtitle,
                            description: $description,
                            icon: null,
                            start: $start,
                            stop: $stop,
                            season: null,
                            episode: null,
                        );
                    });
            })
            ->collapse()
            ->map(fn (Program $program) => $this->tap($program))
            ->sortBy('start')
            ->values();

        return new Tv($this->channel(), $programs);
    }
}

<?php

namespace App\Crawlers;

use App\Data\Program;
use App\Data\Tv;
use App\Enums\Channel;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use DateTimeInterface;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\DomCrawler\Link;

abstract class BaseArdCrawler extends Crawler
{
    abstract protected function sender(): int;

    abstract protected function channel(): Channel;

    public function crawl(): Tv
    {
        $links = collect(Http::pool(function (Pool $pool): array {
            $date = CarbonImmutable::now('Europe/Berlin');

            return collect(CarbonPeriod::since($date->subDay()->startOfWeek())->days()->until($date->addDays(7)->endOfWeek()))
                ->map(fn(DateTimeInterface $date) => $pool->accept('text/html')->get('https://programm.ard.de/TV/Programm/Sender', [
                    'datum' => $date->format('d.m.Y'),
                    'sender' => $this->sender(),
                ]))
                ->all();
        }))
            ->map(static function(Response $response): array {
                $html = $response->body();

                $crawler = new DomCrawler($html, $response->effectiveUri());

                return $crawler->filter('body .event-list li[data-action="Sendung"] a.sendungslink')->links();
            })
            ->collapse()
            ->map(fn (Link $link): UriInterface => new Uri($link->getUri()))
            ->values();


        $programs = $links
            ->chunk(10)
            ->map(static function(Collection $links): array {
                return Http::pool(static function (Pool $pool) use ($links): array {
                    return $links
                        ->map(fn(UriInterface $uri) => $pool->accept('text/html')->get($uri))
                        ->all();
                });
            })
            ->collapse()
            ->map(static function(Response $response): Program {
                $html = $response->body();

                $crawler = new DomCrawler($html, $response->effectiveUri());

                $main = $crawler->filter('body .event-list li[data-action="Sendung"]')->first();

                $start = CarbonImmutable::createFromFormat(
                    'd.m.Y H:i',
                    (string) Str::of($main->filter('.date')->html())
                        ->trim()
                        ->replace('Uhr', '')
                        ->replace('<', ' <')
                        ->replace('>', '> ')
                        ->stripTags()
                        ->replaceMatches('/\s+/', ' ')
                        ->trim(),
                    'Europe/Berlin'
                );

                $title = $main->filter('.title')->innerText();
                $description = $main->filter('.eventText')->text();
                $image = rescue(fn() => (string) new Uri($main->filter('.gallery img')->image()->getUri()), null, false);

                return new Program(
                    title: $title,
                    description: $description,
                    icon: $image,
                    start: $start,
                    stop: null,
                );
            });

        $programs = $programs
            ->sortBy('start')
            ->values()
            ->map(static function(Program $program) use($programs): Program {
                if ($program->stop === null) {
                    $next = $programs->after($program);

                    if ($next instanceof Program) {
                        $program->stop = CarbonImmutable::instance($next->start);
                    }
                }

                return $program;
            })
            ->reject(fn(Program $program) => $program->stop === null)
            ->values();

        return new Tv($this->channel(), $programs);
    }
}

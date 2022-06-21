<?php

namespace App\Crawlers;

use App\Data\Program;
use App\Data\Tv;
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
use Symfony\Component\DomCrawler\UriResolver;

abstract class BaseZdfCrawler extends Crawler
{
    abstract protected function timeline(): string;

    protected function tap(Program $program): Program
    {
        if(str_contains($program->subtitle ?? '', 'Spielfilm') || str_contains($program->subtitle ?? '', 'Fernsehfilm')) {
            $program->categories[] = 'movie';
        }

        if(in_array($program->title, ['ZDF-Mittagsmagazin', 'heute - in Deutschland', 'heute - in Europa', 'heute Xpress', 'heute', 'Wetter'])) {
            $program->categories[] = 'news';
        }

        return $program;
    }

    public function crawl(): Tv
    {
        $links = collect(Http::pool(function (Pool $pool): array {
            $date = CarbonImmutable::now('Europe/Berlin');

            return collect(CarbonPeriod::since($date->subDay()->startOfDay())->days()->until($date->addDays(7)->endOfWeek()))
                ->map(fn(DateTimeInterface $date) => $pool->accept('text/html')->get('https://www.zdf.de/live-tv', [
                    'airtimeDate' => $date->format('Y-m-d'),
                ]))
                ->all();
        }))
            ->map(function(Response $response): array {
                $html = $response->body();

                $crawler = new DomCrawler($html, $response->effectiveUri());

                return collect($crawler->filter("body .timeline-list .timeline-{$this->timeline()} li a")->extract(['data-dialog']))
                    ->map(fn(string $data): UriInterface => new Uri(UriResolver::resolve(
                        json_decode($data, true)['contentUrl'],
                        $response->effectiveUri()
                    )))
                    ->all();
            })
            ->collapse()
            ->reject(fn(UriInterface $uri): bool => trim($uri->getPath(), '/') === 'broadcasts')
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

                $main = $crawler->filter('section')->first();

                $link = rescue(fn() => new Uri($main->filter('a.teaser-title-link')->link()->getUri()), null, false);

                $details = rescue(fn() => (new DomCrawler(Http::get($link)->body(), $link))->filter('body .content-box .teaser-cat')->first()->text(), null, false);
                $season = null;
                $episode = null;

                if(filled($details)) {
                    $season = Str::match('/Staffel (\d+), Folge \d+/', $details) ?: null;
                    if(filled($season)) {
                        $episode = Str::match('/Staffel \d+, Folge (\d+)/', $details) ?: null;
                    }
                }

                $start = CarbonImmutable::parse($main->filter('time[datetime]')->attr('datetime'));
                $title = trim($main->filter('.teaser-title')->text(), "\xC2\xA0");
                $subtitle = rescue(fn() => $main->filter('.overlay-subtitle')->text(), null, false);
                $description = $main->filter('.overlay-text')->text();
                $image = rescue(fn() => (string) new Uri(UriResolver::resolve(
                    $main->filter('img.overlay-img[data-src]')->attr('data-src'),
                    $response->effectiveUri()
                )), null, false);

                return new Program(
                    title: $title,
                    subtitle: $subtitle,
                    description: $description,
                    icon: $image,
                    start: $start,
                    stop: null,
                    season: $season,
                    episode: $episode
                );
            })
            ->map(fn(Program $program) => $this->tap($program));

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

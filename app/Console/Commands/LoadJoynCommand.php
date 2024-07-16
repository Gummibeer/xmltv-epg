<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Saloon\XmlWrangler\Data\Element;
use Saloon\XmlWrangler\Data\RootElement;
use Saloon\XmlWrangler\XmlWriter;

class LoadJoynCommand extends Command
{
    protected $signature = 'load:joyn';

    protected $description = 'Load joyn epg';

    public function handle(): int
    {
        $streams = Http::withHeaders([
            'x-api-key' => config('services.joyn.api_key'),
            'Joyn-Platform' => 'web',
        ])->get('https://api.joyn.de/graphql', [
            'operationName' => 'LiveChannelsAndEPG',
            'query' => File::get(resource_path('graqphql/joyn/LiveChannelsAndEPG.graphql')),
        ])->collect('data.liveStreams');

        $xmltv = XmlWriter::make()->write(
            rootElement: new RootElement(
                name: 'tv',
                attributes: [
                    'date' => now()->format('YmdHis O'),
                ]
            ),
            content: [
                'channel' => $streams
                    ->map(fn (array $stream) => Element::make([
                        'display-name' => Element::make(data_get($stream, 'brand.title'))->addAttribute('lang', 'de'),
                        'icon' => Element::make(data_get($stream, 'brand.livestream.logo.url'))->addAttribute('width', 183)->addAttribute('height', 75),
                    ])->addAttribute('id', data_get($stream, 'id')))
                    ->all(),
                'programme' => $streams
                    ->map(fn (array $stream) => collect($stream['epgEvents'])->map(function (array $event) use ($stream): Element {
                        $start = CarbonImmutable::createFromTimestampUTC(data_get($event, 'startDate'));
                        $stop = CarbonImmutable::createFromTimestampUTC(data_get($event, 'endDate'));

                        return Element::make([
                            'title' => Element::make(data_get($event, 'program.title'))->addAttribute('lang', 'de'),
                            'date' => $start->format('Ymd'),
                            'length' => Element::make($start->diffInMinutes($stop))->addAttribute('units', 'minutes'),
                            'icon' => Element::make(data_get($event, 'program.image.url'))->addAttribute('width', 503)->addAttribute('height', 283),
                        ])
                            ->addAttribute('channel', data_get($stream, 'id'))
                            ->addAttribute('start', $start->format('YmdHis O'))
                            ->addAttribute('stop', $stop->format('YmdHis O'));
                    }
                    ))
                    ->collapse()
                    ->all(),
            ]
        );

        Storage::disk('local')->put('joyn.de.xml', $xmltv);

        return self::SUCCESS;
    }
}

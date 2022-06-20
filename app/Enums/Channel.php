<?php

namespace App\Enums;

use App\Crawlers\ArdAlphaCrawler;
use App\Crawlers\ArdCrawler;
use App\Crawlers\ArdOneCrawler;
use App\Crawlers\Crawler;
use App\Crawlers\KikaCrawler;
use App\Crawlers\NdrCrawler;
use App\Crawlers\ZdfCrawler;
use App\Crawlers\ZdfInfoCrawler;
use App\Crawlers\ZdfNeoCrawler;
use App\Data\Tv;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DOMDocument;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Storage;
use Spatie\Enum\Laravel\Enum;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method static self ARD()
 * @method static self ARD_ALPHA()
 * @method static self ARD_ONE()
 * @method static self ZDF()
 * @method static self ZDF_INFO()
 * @method static self ZDF_NEO()
 * @method static self KIKA()
 * @method static self NDR()
 */
final class Channel extends Enum implements Responsable
{
    protected static function values(): array
    {
        return [
            'ARD' => 'ard.de',
            'ARD_ALPHA' => 'alpha.br-online.de',
            'ARD_ONE' => 'one.ard.de',
            'ZDF' => 'zdf.de',
            'ZDF_INFO' => 'info.zdf.de',
            'ZDF_NEO' => 'neo.zdf.de',
            'KIKA' => 'kika.de',
            'NDR' => 'ndr.de',
        ];
    }

    protected static function labels(): array
    {
        return [
            'ARD' => 'Das Erste',
            'ARD_ALPHA' => 'ARD alpha',
            'ARD_ONE' => 'ARD one',
            'ZDF' => 'ZDF',
            'ZDF_INFO' => 'ZDFinfo',
            'ZDF_NEO' => 'ZDFneo',
            'KIKA' => 'KiKA',
            'NDR' => 'NDR',
        ];
    }

    public function locale(): string
    {
        return match ($this) {
            self::ARD() => 'de',
            self::ARD_ALPHA() => 'de',
            self::ZDF() => 'de',
            self::ZDF_INFO() => 'de',
            self::ZDF_NEO() => 'de',
            self::KIKA() => 'de',
            self::NDR() => 'de',
            default => app()->getLocale(),
        };
    }

    public function website(): ?string
    {
        return match ($this) {
            self::ARD() => 'https://www.ard.de',
            self::ARD_ALPHA() => 'https://www.ardalpha.de',
            self::ARD_ONE() => 'https://one.ard.de',
            self::ZDF() => 'https://www.zdf.de',
            self::ZDF_INFO() => 'https://info.zdf.de',
            self::ZDF_NEO() => 'https://neo.zdf.de',
            self::KIKA() => 'https://kika.de',
            self::NDR() => 'https://ndr.de',
            default => null,
        };
    }

    public function logo(): ?string
    {
        return match ($this) {
            self::ARD() => asset('img/ard.png'),
            default => null,
        };
    }

    public function stream(): string
    {
        return match ($this) {
            self::ARD() => 'https://mcdn.daserste.de/daserste/de/master_1920p_5128.m3u8',
            self::ARD_ALPHA() => 'https://mcdn.br.de/br/fs/ard_alpha/hls/de/master_1280p_3328.m3u8',
            self::ARD_ONE() => 'https://mcdn.one.ard.de/ardone/hls/master-1080p-5128.m3u8',
            self::ZDF() => 'https://zdf-hls-15.akamaized.net/hls/live/2016498/de/veryhigh/master.m3u8',
            self::ZDF_INFO() => 'https://zdf-hls-17.akamaized.net/hls/live/2016500/de/veryhigh/master.m3u8',
            self::ZDF_NEO() => 'https://zdf-hls-16.akamaized.net/hls/live/2016499/de/veryhigh/master.m3u8',
            self::KIKA() => 'https://kikageohls.akamaized.net/hls/live/2022693/livetvkika_de/master-1080p-5128.m3u8',
            self::NDR() => 'https://mcdn.ndr.de/ndr/hls/ndr_fs/ndr_hh/master_720.m3u8',
        };
    }

    public function epg(): string
    {
        $filename = $this->filename();
        $disk = $this->disk();

        $modifiedAt = $this->lastModifiedAt();

        if($modifiedAt === null || !$modifiedAt->isSameDay(now())) {
            $disk->put($filename, $this->crawl());
        }

        return $disk->get($filename);
    }

    public function crawl(): string
    {
        $crawler = $this->crawler();

        $tv = $crawler
            ? $crawler->crawl()
            : new Tv($this, []);

        $xml = view('xmltv', [
            'tv' => $tv,
        ])->render();

        $domDocument = new DOMDocument('1.0');
        $domDocument->preserveWhiteSpace = false;
        $domDocument->formatOutput = true;

        $domDocument->loadXML($xml);

        return $domDocument->saveXML();
    }

    public function toResponse($request): Response
    {
        $this->epg();

        if(!$this->disk()->exists($this->filename())) {
            throw new NotFoundHttpException();
        }

        return response()->file($this->disk()->path($this->filename()));
    }

    public function lastModifiedAt(): ?CarbonInterface
    {
        return $this->disk()->exists($this->filename())
            ? CarbonImmutable::createFromTimestampUTC($this->disk()->lastModified($this->filename()))
            : null;
    }

    private function crawler(): ?Crawler
    {
        return match ($this) {
            self::ARD() => new ArdCrawler(),
            self::ARD_ALPHA() => new ArdAlphaCrawler(),
            self::ARD_ONE() => new ArdOneCrawler(),
            self::ZDF() => new ZdfCrawler(),
            self::ZDF_INFO() => new ZdfInfoCrawler(),
            self::ZDF_NEO() => new ZdfNeoCrawler(),
            self::KIKA() => new KikaCrawler(),
            self::NDR() => new NdrCrawler(),
            default => null,
        };
    }

    private function filename(): string
    {
        return "{$this->value}.xml";
    }

    private function disk(): Filesystem
    {
        return Storage::disk('local');
    }
}

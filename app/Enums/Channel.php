<?php

namespace App\Enums;

use App\Crawlers\ArdCrawler;
use App\Data\Tv;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DOMDocument;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Storage;
use Spatie\Enum\Laravel\Enum;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method static self ARD()
 * @method static self ZDF()
 */
final class Channel extends Enum implements Responsable
{
    protected static function values(): array
    {
        return [
            'ARD' => 'ard.de',
            'ZDF' => 'zdf.de',
        ];
    }

    protected static function labels(): array
    {
        return [
            'ARD' => 'Das Erste',
            'ZDF' => 'ZDF',
        ];
    }

    public function locale(): string
    {
        return match ($this) {
            self::ARD() => 'de',
            self::ZDF() => 'de',
            default => app()->getLocale(),
        };
    }

    public function website(): ?string
    {
        return match ($this) {
            self::ARD() => 'https://www.ard.de',
            self::ZDF() => 'https://www.zdf.de',
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

    public function epg(): string
    {
        $filename = $this->filename();
        $disk = $this->disk();

        $modifiedAt = $this->modifiedAt();

        if($modifiedAt === null || !$modifiedAt->isSameDay(now())) {
            $disk->put($filename, $this->crawl());
        }

        return $disk->get($filename);
    }

    public function crawl(): string
    {
        $crawler = match ($this) {
            self::ARD() => new ArdCrawler(),
            default => null,
        };

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
        return response()->file($this->disk()->path($this->filename()));
    }

    private function filename(): string
    {
        return "{$this->value}.xml";
    }

    private function disk(): Filesystem
    {
        return Storage::disk('local');
    }

    private function modifiedAt(): ?CarbonInterface
    {
        return $this->disk()->exists($this->filename())
            ? CarbonImmutable::createFromTimestampUTC($this->disk()->lastModified($this->filename()))
            : null;
    }
}

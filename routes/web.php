<?php

use App\Enums\Channel;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', static function (): Responsable|Response {
    return response(view('sitemap')->render())
        ->header('Content-Type', 'text/xml');
})->name('sitemap');

Route::get('/streams.m3u8', static function (): Responsable|Response {
    $channels = collect(Channel::cases())
        ->map(fn (Channel $channel) => <<<TXT
        #EXTINF:-1,{$channel->label}
        {$channel->stream()}
        TXT)
        ->implode(PHP_EOL);

    return response(<<<TXT
    #EXTM3U
    {$channels}
    TXT)
        ->header('Content-Type', 'application/x-mpegURL');
})->name('streams');

Route::get('/joyn.de', static function (): Response {
    return Storage::disk('local')->response('joyn.de.xml');
})->name('joyn.de');

Route::get('/{channel}', static function (string $name): Responsable|Response {
    return Channel::from($name);
})
    ->whereIn('channel', Channel::toValues())
    ->name('channel');

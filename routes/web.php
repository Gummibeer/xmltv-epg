<?php

use App\Enums\Channel;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', static function(): Responsable|Response {
    return response(view('sitemap')->render())
        ->header('Content-Type', 'text/xml');
})->name('sitemap');

Route::get('/{channel}', static function (string $name): Responsable|Response {
    return Channel::from($name);
})
    ->whereIn('channel', Channel::toValues())
    ->name('channel');

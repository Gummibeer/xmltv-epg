<?php

use App\Enums\Channel;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::get('/{channel}', static function (string $name): Responsable|Response {
    return Channel::from($name);
});

{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach(\App\Enums\Channel::cases() as $channel)
    <url>
        <loc>{{ route('channel', $channel->value) }}</loc>
        <lastmod>{{ $channel->lastModifiedAt()?->toW3cString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.5</priority>
    </url>
    @endforeach
</urlset>
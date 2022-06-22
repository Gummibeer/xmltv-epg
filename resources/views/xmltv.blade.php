{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<!DOCTYPE tv SYSTEM "https://raw.githubusercontent.com/XMLTV/xmltv/master/xmltv.dtd">
<tv>
    <channel id="{{ $tv->channel->value }}">
        <display-name>{{ $tv->channel->label }}</display-name>

        @if($tv->channel->logo())
            <icon src="{{ $tv->channel->logo() }}"/>
        @endif

        @if($tv->channel->website())
            <url>{{ $tv->channel->website() }}</url>
        @endif
    </channel>

    @foreach($tv->programs as $program)
        <programme
            channel="{{ $tv->channel->value }}"
            start="{{ $program->start->format('YmdHis O') }}"
            stop="{{ $program->stop->format('YmdHis O') }}"
        >
            <title lang="{{ $tv->channel->locale() }}">{{ $program->title }}</title>

            @if($program->subtitle)
                <sub-title lang="{{ $tv->channel->locale() }}">{{ $program->subtitle }}</sub-title>
            @endif

            @if($program->description)
                <desc lang="{{ $tv->channel->locale() }}">{{ $program->description }}</desc>
            @endif

            <date>{{ $program->start->format('Ymd') }}</date>

            @foreach(collect($program->categories)->unique()->values() as $category)
                <category>{{ $category }}</category>
            @endforeach

            <length units="minutes">{{ $program->start->diffInMinutes($program->stop) }}</length>

            @if($program->icon)
                <icon src="{{ $program->icon }}"/>
            @endif

            @if($program->season && $program->episode)
                <episode-num system="xmltv_ns">{{ $program->season - 1 }}.{{ $program->episode - 1 }}.0</episode-num>
                <episode-num system="onscreen">S{{ str_pad($program->season, 2, '0', STR_PAD_LEFT) }}E{{ str_pad($program->episode, 2, '0', STR_PAD_LEFT) }}</episode-num>
            @endif

            {{--
            title+,
            sub-title*,
            desc*,
            credits?,
            date?,
            category*,
            keyword*,
            language?,
            orig-language?,
            length?,
            icon*,
            url*,
            country*,
            episode-num*,
            video?,
            audio?,
            previously-shown?,
            premiere?,
            last-chance?,
            new?,
            subtitles*,
            rating*,
            star-rating*,
            review*,
            image*
            --}}
        </programme>
    @endforeach
</tv>

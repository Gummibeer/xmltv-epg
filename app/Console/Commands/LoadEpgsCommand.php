<?php

namespace App\Console\Commands;

use App\Enums\Channel;
use Illuminate\Console\Command;

class LoadEpgsCommand extends Command
{
    protected $signature = 'load:epgs {--force}';

    protected $description = 'Load all EPGs.';

    public function handle(): int
    {
        foreach (Channel::cases() as $channel) {
            $this->line($channel->label);

            if ($this->option('force')) {
                $channel->disk()->put($channel->filename(), $channel->crawl());
            } else {
                $channel->epg();
            }
        }

        return self::SUCCESS;
    }
}

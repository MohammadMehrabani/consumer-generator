<?php

namespace MohammadMehrabani\ConsumerGenerator\Console\Commands;

use Illuminate\Console\Command;

class Worker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consumer:worker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run consumers (receiver)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $files = glob(config('consumer-generator.consumer_directory').'*.php');

        foreach ($files as $file) {

            $class = config('consumer-generator.consumer_namespace').'\\'.file_get_php_classes($file)[0];
            (new $class)->listen();

        }
    }
}

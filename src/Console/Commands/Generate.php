<?php

namespace MohammadMehrabani\ConsumerGenerator\Console\Commands;

use Illuminate\Console\Command;
use MohammadMehrabani\ConsumerGenerator\Exceptions\FileException;
use MohammadMehrabani\ConsumerGenerator\Exceptions\StubException;

class Generate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consumer:generate {consumer} {--queue=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quickly generating consumer (receiver)';

    /**
     * Overriding existing files.
     *
     * @var bool
     */
    protected $override = false;

    /**
     * Execute the console command.
     *
     * @throws FileException
     * @return void
     */
    public function handle()
    {
        // Create consumer folder if it's necessary.
        $this->createFolder(config('consumer-generator.consumer_directory'));

        // Check consumer folder permissions.
        $this->checkConsumerPermissions();

        $consumerClassName = $this->argument('consumer');
        $listenQueue = $this->option('queue');

        // Add suffixes
        $consumer = suffix($consumerClassName, 'Consumer');

        // Current consumer file name
        $consumerFile = $this->consumerPath($consumer.'.php');

        // Get existing consumer file names.
        $existingConsumerFiles = glob($this->consumerPath('*.php'));

        // Ask for overriding, If there are files in consumer directory.
        if (count($existingConsumerFiles) > 0) {

            foreach ($existingConsumerFiles as $existingConsumerFile) {
                if(strstr($existingConsumerFile, $consumerFile)) {
                    $this->alert(' Exist consumer file: ');
                    $this->info($existingConsumerFile);
                    if ($this->confirm('Do you want to overwrite the existing consumer files?')) {
                        $this->override = true;
                    }
                }
            }
        }

        // Get stub file templates.
        $consumerStub = $this->getStub('Consumer');

        // consumer stub values those should be changed by command.
        $consumerStubValues = [
            '{{namespace}}',
            '{{class}}',
            '{{interface}}',
            '{{queue}}',
        ];

        // Fillable consumer values for generating real files
        $consumerValues = [
            config('consumer-generator.consumer_namespace'),
            $consumer,
            '\\'.str_replace('::class', '', config('consumer-generator.main_interface_class')),
            $listenQueue
        ];

        // Generate body of the consumer file
        $consumerContent = str_replace(
            $consumerStubValues,
            $consumerValues,
            $consumerStub
        );

        if (in_array($consumerFile, $existingConsumerFiles)) {
            if ($this->override) {
                $this->writeFile($consumerFile, $consumerContent);
                $this->info('Overridden consumer file: '.$consumer);
            }
        } else {
            $this->writeFile($consumerFile, $consumerContent);
            $this->info('Created consumer file: '.$consumer);
        }
    }

    /**
     * Get stub content.
     *
     * @param $file
     * @return bool|string
     * @throws StubException
     */
    private function getStub($file)
    {
        $stub = __DIR__.'/../Stubs/'.$file.'.stub';

        if (file_exists($stub)) {
            return file_get_contents($stub);
        }

        throw StubException::fileNotFound($file);
    }

    /**
     * Get consumer path.
     *
     * @param null $path
     * @return string
     */
    private function consumerPath($path = null)
    {
        return config('consumer-generator.consumer_directory').$path;
    }

    /**
     * Get parent path of consumer of interface folder.
     *
     * @param string $child
     * @return string
     */
    private function parentPath($child = 'consumer')
    {
        $childPath = $child.'Path';
        $childPath = $this->$childPath();
        return dirname($childPath);
    }

    /**
     * Generate/override a file.
     *
     * @param $file
     * @param $content
     */
    private function writeFile($file, $content)
    {
        file_put_contents($file, $content);
    }

    /**
     * Check consumer folder permissions.
     *
     * @throws FileException
     */
    private function checkConsumerPermissions()
    {
        // Get full path of consumer directory.
        $consumerPath = $this->consumerPath();

        // Get parent directory of consumer path.
        $consumerParentPath = $this->parentPath('consumer');

        // Check parent of consumer directory is writable.
        if (! file_exists($consumerPath) && ! is_writable($consumerParentPath)) {
            throw FileException::notWritableDirectory($consumerParentPath);
        }

        // Check consumer directory permissions.
        if (file_exists($consumerPath) && ! is_writable($consumerPath)) {
            throw FileException::notWritableDirectory($consumerPath);
        }
    }

    private function createFolder($folder)
    {
        if (! file_exists($folder)) {
            mkdir($folder, 0755, true);
        }
    }
}

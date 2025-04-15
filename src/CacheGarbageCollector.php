<?php

namespace src;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CacheGarbageCollector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:file-garbage-collector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old cache files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $file_cache_path = config('cache.stores.file.path');
        // Get all folders within cache folder
        $directories = glob($file_cache_path . '/*');
        foreach ($directories as $directory) {
            // Get all files within the directory
            $cache_files    = \File::allFiles($directory);
            $files_counts   = 0;
            $deleted_counts = 0;
            // Remove cache files if lifetime is passed
            foreach ($cache_files as $cache_file) {
                $files_counts++;
                $file_path              = $cache_file->getPathname();
                $cache_content          = file_get_contents($file_path);
                $cache_expire_timestamp = substr($cache_content, 0, 10);
                if ($cache_expire_timestamp < time()) {
                    $deleted_counts++;
                    \File::delete($file_path);
                }
            }
            // Remove empty directories
            if ($files_counts == $deleted_counts) {
                foreach (scandir($directory) as $item) {
                    if ($item != '.' && $item != '..') {
                        rmdir($directory . DIRECTORY_SEPARATOR . $item);
                    }
                }
                rmdir($directory);
            }
        }
        return CommandAlias::SUCCESS;
    }
}

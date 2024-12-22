<?php

namespace SquirrelForge\Laravel\CoreSupport\Console\Commands;

use Illuminate\Console\Command;

class MovePublicDirectoryCommand extends Command
{
    /** @var string $signature The name and signature of the console command. */
    protected $signature = 'sqfcs:mvpub';

    /** @var string $description The console command description. */
    protected $description = 'Setups folders and env config outside of laravel root';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        // $this->argument('name');
        // $root = base_path();
        $this->info('info');
        $this->error('error');
        $this->comment('comment');
    }
}

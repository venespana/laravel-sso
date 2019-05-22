<?php

namespace Venespana\Sso\Console\Commands\Sso;

use Illuminate\Console\Command;
use Venespana\Sso\Models\Broker;

class Create extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sso:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commands description';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->ask('The broker name');

        $data = Broker::create(['name' => $name])->toArray();

        $this->info('Create new broker successfully!');
        $this->table(array_keys($data), [$data]);
    }
}

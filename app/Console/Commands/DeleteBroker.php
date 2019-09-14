<?php

namespace App\Console\Commands;

use App\Models\Broker;
use Illuminate\Console\Command;

class DeleteBroker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broker:delete {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete broker with specified name';

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
        $broker = Broker::where('name', $this->argument('name'))->firstOrFail();
        $broker->delete();

        $this->info('Broker with name `' . $this->argument('name') . '` successfully deleted.');
    }
}

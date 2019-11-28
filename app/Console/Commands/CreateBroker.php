<?php

namespace App\Console\Commands;

use App\Models\Broker;
use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateBroker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broker:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating new SSO broker';

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
        $broker = new Broker();
        $broker->name = $this->argument('name');
        $broker->url = $this->ask('Insert the Broker URL', config('app.url') . "/{$broker->name}");
        $broker->secret = Str::random(40);

        $broker->save();
        Permission::create([
            'name' => $broker->name,
        ]);

        $this->info('Broker with name `' . $this->argument('name') . '` successfully created.');
        $this->info('Secret: ' . $broker->secret);
    }
}

<?php

namespace App\Console\Commands;

use App\Repositories\AnswersRepository;
use App\Repositories\IncidentReportRepository;
use App\Repositories\InputFieldsRepository;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateDummyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:dummy_incident {count=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create dummy incident reports.';

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
        $incidentRepo = app()->make(IncidentReportRepository::class);
        $faker = Factory::create();
        $count = $this->argument("count");

        while ($count > 0) {
            $incidentRepo->create([
                'name' => $faker->name,
                'message' => $faker->catchPhrase .' '.$faker->bs. ' '. $faker->catchPhrase .' '.$faker->bs. ' '. $faker->catchPhrase .' '.$faker->bs,
                'media' => "null",
                'status' => 'confirmed'
            ]);
            $count--;
        }
    }
}
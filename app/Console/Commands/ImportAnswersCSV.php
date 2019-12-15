<?php

namespace App\Console\Commands;

use App\Repositories\AnswersRepository;
use App\Repositories\InputFieldsRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportAnswersCSV extends Command
{
    const Q_1 = 1;
    const Q_2 = 7;
    const Q_3 = 8;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:answers {file_url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export users answers from CSV file.';

    public $inputFieldsRepo;

    public $answersRepo;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->inputFieldsRepo = app()->make(InputFieldsRepository::class);
        $this->answersRepo = app()->make(AnswersRepository::class);
    }

    public function searchInputField($fieldLabel, $secondLabelOption = null)
    {
        $firstOption = optional($this->inputFieldsRepo->search([
            ['label', $fieldLabel]
        ])->first())->id;

        return !$firstOption && $secondLabelOption ? optional($this->inputFieldsRepo->search([
            ['label', $secondLabelOption]
        ])->first())->id : $firstOption;
    }

    public function cleanRank($answer)
    {
        return $answer === 'Not Applicable' ? $answer : preg_replace("/[a-zA-Z\s]/", "", $answer);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fileUrl = $this->argument('file_url');
        if (!$fileUrl) {
            $this->error(
                'No arguments passed. Kindly include the file name.'
            );
            return;
        }

        $nameField = $this->searchInputField("Name", "Pangalan");
        $genderField = $this->searchInputField("Kasarian", "Gender");
        $ageField = $this->searchInputField("Edad", "Age");
        $statusField = $this->searchInputField("Marital Status");
        $addressField = $this->searchInputField("Address", "Barangay");
        $categoryField = $this->searchInputField("Respondents Category");
        $floodField = $this->searchInputField("Flood");
        $landslideField = $this->searchInputField("Landslide");
        $mudslideField = $this->searchInputField("Mudslide");
        $stormSurgeField = $this->searchInputField("Storm Surge");
        $tsunamiField = $this->searchInputField("Tsunami");
        $earthquakeField = $this->searchInputField("Earthquake");
        $qualitativeField = $this->searchInputField("Answer");

        $fileData = file_get_contents($fileUrl);
        $rows = explode("\n", $fileData);

        foreach ($rows as $index => $row) {
            if (!isset($header)) {
                $header = str_getcsv($row);
                continue;
            }
            if (!strlen($row)) continue;
            $data = str_getcsv($row);
            if (count($data) < 14) {
                continue;
            }
            try {
                $deviceAddress = Str::random(16);
                $createdAt = Carbon::parse($data[0])->format("Y-m-d H:i:s");
                // Name
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_1,
                    'field_id' => $nameField,
                    'answer' => ucwords($data[1]),
                    'created_at' => $createdAt
                ]);
                // Age
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_1,
                    'field_id' => $ageField,
                    'answer' => $data[2],
                    'created_at' => $createdAt
                ]);
                // Gender
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_1,
                    'field_id' => $genderField,
                    'answer' => $data[3],
                    'created_at' => $createdAt
                ]);
                // Status
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_1,
                    'field_id' => $statusField,
                    'answer' => $data[4],
                    'created_at' => $createdAt
                ]);
                // Address
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_1,
                    'field_id' => $addressField,
                    'answer' => $data[6],
                    'created_at' => $createdAt
                ]);
                // Category
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_1,
                    'field_id' => $categoryField,
                    'answer' => $data[7],
                    'created_at' => $createdAt
                ]);
                // Flood
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_2,
                    'field_id' => $floodField,
                    'answer' => $this->cleanRank($data[8]),
                    'created_at' => $createdAt
                ]);
                // Landslide
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_2,
                    'field_id' => $landslideField,
                    'answer' => $this->cleanRank($data[9]),
                    'created_at' => $createdAt
                ]);
                // Mudflow
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_2,
                    'field_id' => $mudslideField,
                    'answer' => $this->cleanRank($data[10]),
                    'created_at' => $createdAt
                ]);
                // Storm Surge
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_2,
                    'field_id' => $stormSurgeField,
                    'answer' => $this->cleanRank($data[11]),
                    'created_at' => $createdAt
                ]);
                // Tsunami
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_2,
                    'field_id' => $tsunamiField,
                    'answer' => $this->cleanRank($data[12]),
                    'created_at' => $createdAt
                ]);
                // Earthquake
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_2,
                    'field_id' => $earthquakeField,
                    'answer' => $this->cleanRank($data[13]),
                    'created_at' => $createdAt
                ]);
                // Qualitative
                $this->answersRepo->create([
                    'device_address' => $deviceAddress,
                    'questionnaire_id' => self::Q_3,
                    'field_id' => $qualitativeField,
                    'answer' => $data[14],
                    'created_at' => $createdAt
                ]);
                $this->info("Import successful row {$index}");
            } catch (\Exception $e) {
                $this->error("Error inserting row {$index}");
                $this->error($e->getMessage());
                continue;
            }
        }
    }
}
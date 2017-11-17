<?php

namespace App\Commands;

use Closure;
use Exception;
use App\PhoneNumberValidator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Exception\RuntimeException;

class ValidateUKMobileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'validate:uk-mobile
                            {source* : A list of numbers or files to validate against}
                            {--file : Specifies that the source is a list of files}
                            {--output= : Specifies that the output path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate UK mobile numbers and ouput to a CSV';

    /**
     * @var mixed
     */
    protected $source;

    /**
     * @var array
     */
    protected $validCountryCodes = [
        'GB', 'GG', 'JE'
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $date = Carbon::now()->format('Ymd_his');
        $rand = mb_strtolower(str_random(6));

        $this->fileName = $date.'-'.$rand.'.csv';
        $this->outputPath = base_path('output');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $this->setSource();
        $this->validateOutputPath();

        $this->buildCsvFromRows(
            $this->compileRowsFromSource($this->source)
        );

        $this->info('Total: '.count($this->source));
        $this->info('Filename: '.$this->fileName);
    }

    /**
     * Build a CSV from the rows.
     *
     * @param  Collection $rows
     * @return void
     */
    protected function buildCsvFromRows(Collection $rows)
    {
        $path = $this->outputPath.'/'.$this->fileName;
        File::put($path, implode("\n", $rows->toArray()));
    }

    /**
     * Process a list of numbers.
     *
     * @param array $source
     * @return Illuminate\Support\Collection
     */
    protected function compileRowsFromSource(array $source): Collection
    {
        return collect($source)
            ->map(function ($number) {
                return app(PhoneNumberValidator::class)->make($number, 'GB');
            })->map(function ($number) {
                return $this->buildRowFromValidator($number);
            })
            ->prepend(['phone number','carrier','status'])
            ->map(function ($row) {
                return implode(',', $row);
            });
    }

    /**
     * Build a row.
     *
     * @param  App\PhoneNumberValidator $validator
     * @return array
     */
    protected function buildRowFromValidator(PhoneNumberValidator $validator): array
    {
        $row = [$validator->getNumber(), '', 'not valid'];

        if ($validator->isValidMobile() && $validator->isValidForCountry($this->validCountryCodes)) {
            $row[1] = $validator->getCarrierName();
            $row[2] = 'valid';
        }

        return $row;
    }

    /**
     * Set the source.
     */
    protected function setSource(): void
    {
        $this->source = $this->option('file')
            ? $this->getSourceFromFile(head($this->argument('source')))
            : array_wrap($this->argument('source'));

        $this->info('Source valid: '.($this->option('file') ? 'File' : 'List'));
    }

    /**
     * Return the contents of the source file.
     *
     * @return array
     */
    protected function getSourceFromFile($file): array
    {
        throw_unless(
            File::exists($file),
            RuntimeException::class,
            "Source does not exist [{$file}]"
        );

        return array_wrap(explode("\n", File::get($file)));
    }

    /**
     * Set the output path.
     */
    protected function validateOutputPath(): void
    {
        if ($this->option('output')) {
            $this->outputPath = $this->option('output');
        }

        throw_unless(
            is_dir($this->outputPath),
            RuntimeException::class,
            "The output path does not exist [{$this->outputPath}]"
        );
    }
}

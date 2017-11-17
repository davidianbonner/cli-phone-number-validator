<?php

namespace App\Commands;

use Carbon\Carbon;
use App\PhoneNumberValidator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Exception\RuntimeException;

abstract class BaseValidatorCommand extends Command
{
    /**
     * @var mixed
     */
    protected $source;

    /**
     * Make a validator for a given number.
     *
     * @param  mixed $number
     * @return App\PhoneNumberValidator
     */
    abstract public function makeValidatorForNumer($number): PhoneNumberValidator;

    /**
     * Is the number valid?
     *
     * @param  App\PhoneNumberValidator $validator
     * @return bool
     */
    abstract public function isNumberValid(PhoneNumberValidator $validator): bool;

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
        File::put(
            $this->outputPath.'/'.$this->fileName,
            implode("\n", $rows->toArray())
        );
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
            ->map([$this, 'makeValidatorForNumer'])
            ->map([$this, 'buildRowFromValidator'])
            ->prepend(['phone number', 'carrier', 'status'])
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
    public function buildRowFromValidator(PhoneNumberValidator $validator): array
    {
        $row = [$validator->getNumber(), '', 'not valid'];

        if ($this->isNumberValid($validator)) {
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
            ? $this->getSourceFromFile($this->argument('source'))
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
        $file = head(array_wrap($file));

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

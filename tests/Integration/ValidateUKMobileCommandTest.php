<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use App\Commands\ValidateUKMobileCommand;
use Symfony\Component\Console\Exception\RuntimeException;

class ValidateUKMobileCommandTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->userDefinedLocation = base_path('tests/data/output');

        $this->command = (new ValidateUKMobileCommand)->getName();
        $this->numbers = require dirname(__DIR__).'/data/numbers.php';
        $this->filePath = dirname(__DIR__).'/data/numbers.txt';

        collect(File::files(base_path('output')))->each(function ($file) {
            File::delete($file);
        });

        collect(File::files($this->userDefinedLocation))->each(function ($file) {
            File::delete($file);
        });
    }

    /**
     * Get the CSV path
     *
     * @param  string $fileName
     * @return string
     */
    protected function getCsvPath($output, $basePath = null): string
    {
        $path = str_replace('Filename: ', '', substr($output, strpos($output, 'Filename: ')));

        return ($basePath ? $basePath : base_path('output')).'/'.trim($path);
    }

    /** @test */
    public function it_fails_when_no_source_is_provided(): void
    {
        $this->expectException(RuntimeException::class);
        $this->app->call($this->command);
    }

    /** @test */
    function it_can_accept_a_list_of_numbers_as_arguments()
    {
        // Valid an array of numbers
        $this->app->call($this->command, ['source' => $this->numbers['numbers']]);
        $this->assertTrue(strpos($this->app->output(), 'Source valid: List') !== false);

        // Validate a single number
        $this->app->call($this->command, ['source' => $this->numbers['numbers'][0]]);
        $this->assertTrue(strpos($this->app->output(), 'Source valid: List') !== false);
    }

    /** @test */
    function it_can_accept_a_file_path()
    {
        $this->app->call($this->command, ['source' => $this->filePath, '--file' => true]);
        $this->assertTrue(strpos($this->app->output(), 'Source valid: File') !== false);
    }

    /** @test */
    function it_can_process_a_list_of_numbers_and_output_csv()
    {
        $this->app->call($this->command, ['source' => $this->numbers['numbers']]);
        $this->makeProcessAssertions($this->app->output());
    }

    /** @test */
    function it_can_process_a_file_of_numbers_and_output_csv()
    {
        $this->app->call($this->command, ['source' => $this->filePath, '--file' => true]);
        $this->makeProcessAssertions($this->app->output());
    }

    /** @test */
    function it_can_output_a_csv_to_a_user_defined_location()
    {
        $this->app->call($this->command, [
            'source' => $this->filePath,
            '--file' => true,
            '--output' => $this->userDefinedLocation
        ]);

        $output = $this->app->output();

        $this->assertTrue(File::exists($this->getCsvPath($output, $this->userDefinedLocation)));
    }

    protected function makeProcessAssertions($output)
    {
        $this->assertTrue(strpos($output, "Total: 9") !== false);

        $this->assertTrue(File::exists(
            $path = $this->getCsvPath($output)
        ));

        $rows = collect(explode("\n", File::get($path)))
            ->filter(function ($row) {
                return strpos($row, 'status') === false;
            })
            ->map(function ($row) {
                return explode(',', $row);
            })
            ->values()
            ->each(function ($row, $key) {
                $this->assertEquals($this->numbers['map'][$key], mb_strtolower($row[2]));
            });

        $this->assertCount(9, $rows);
    }
}

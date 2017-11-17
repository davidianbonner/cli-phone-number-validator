<?php

namespace App\Commands;

use App\PhoneNumberValidator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Exception\RuntimeException;

class ValidateUKMobileCommand extends BaseValidatorCommand
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
     * @var array
     */
    protected $validCountryCodes = [
        'GB', 'GG', 'JE',
    ];

    /**
     * Make a validator for a given number.
     *
     * @param  mixed $number
     * @return App\PhoneNumberValidator
     */
    public function makeValidatorForNumer($number): PhoneNumberValidator
    {
        return app(PhoneNumberValidator::class)->make($number, 'GB');
    }

    /**
     * Is the number valid?
     *
     * @param  App\PhoneNumberValidator $validator
     * @return bool
     */
    public function isNumberValid(PhoneNumberValidator $validator): bool
    {
        return ($validator->isValidMobile() && $validator->isValidForCountry($this->validCountryCodes));
    }
}

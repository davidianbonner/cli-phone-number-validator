# A CLI Phone Number Validator

[![Author](http://img.shields.io/badge/author-@dbonner1987-blue.svg?style=flat-square)](https://twitter.com/dbonner1987)
[![Build Status](https://img.shields.io/travis/davidianbonner/cli-phone-number-validator/master.svg?style=flat-square)](https://travis-ci.org/davidianbonner/cli-phone-number-validator)
[![Quality Score](https://img.shields.io/scrutinizer/g/davidianbonner/cli-phone-number-validator.svg?style=flat-square)](https://scrutinizer-ci.com/g/davidianbonner/cli-phone-number-validator)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/davidianbonner/cli-phone-number-validator/blob/master/LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/davidianbonner/cli-phone-number-validator.svg?style=flat-square)](https://packagist.org/packages/davidianbonner/cli-phone-number-validator)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/davidianbonner/cli-phone-number-validator.svg?style=flat-square)](https://scrutinizer-ci.com/g/davidianbonner/cli-phone-number-validator/code-structure)


### Installation

Install the application with composer:

```bash
$ composer create-project davidianbonner/cli-phone-number-validator
```

To create a standalone PHAR of the application for deployment, run:

```bash
$ php number-validator app:build number-validator
```

This will output a .PHAR to the `build` directory.

### Usage

The validator comes with a pre-built GB & Channel Islands mobile command: `validate:uk-mobile`. There are two ways to process and validate numbers:

##### List source

Pass a list of phone numbers to the validator.

```bash
$ php number-validator validate:uk-mobile "07712345678" "07712341234" "07283 123 32"
```

##### File source

Pass a file with a phone number per-line.

```bash
$ php number-validator validate:uk-mobile ./path/to/file --file
```

##### Output directory

An output directory is required when using the standalone .PHAR:

```bash
$ php number-validator validate:uk-mobile ./path/to/file --file --output=/path/to/output/directory
```

### Adding new validators

New validators can be added with minimal effort. First create a new command in `app/Commands` and extends the `BaseValidatorCommand`.

```php
<?php

namespace App\Commands;

class ValidateESFixedLineCommand extends BaseValidatorCommand
{
    protected $signature = 'validate:es-fixedline
                            {source* : A list of numbers or files to validate against}
                            {--file : Specifies that the source is a list of files}
                            {--output= : Specifies that the output path}';

    protected $description = 'Validate Spanish fixed line numbers and ouput to a CSV';

    public function makeValidatorForNumer($number): PhoneNumberValidator
    {
        // The country code is only required when no dialing code is present in the number.
        return app(PhoneNumberValidator::class)->make($number, 'ES');
    }

    public function isNumberValid(PhoneNumberValidator $validator): bool
    {
        return ($validator->isValidFixedLine() && $validator->isValidForCountry('ES'));
    }
}
```

## License

CLI Phone Number Validator is an open-sourced software licensed under the [MIT license](https://github.com/davidianbonner/cli-phone-number-validator/blob/stable/LICENSE.md).

<?php

namespace Tests\Unit\Contracts;

use Tests\TestCase;
use App\PhoneNumberValidator;

trait ValidatorContractTest
{
    protected $validMobileNumber = ['07912345678', 'GB'];
    protected $validFixedLineNumber = ['+44 141 123 4567', 'GB'];
    protected $validMobileNumberPrefixed = ['+44 07912 345 678', 'GB'];
    protected $invalidNumber = ['+456 017234 3745 273', 'GB'];
    protected $validGuernseyNumber = '07781 123 456';

    /** @test */
    function it_can_make_and_return_a_valid_validator_instance()
    {
        $validator = call_user_func_array([$this->getValidatorInstance(), 'make'], $this->validMobileNumber);
        $this->assertInstanceOf(PhoneNumberValidator::class, $validator);
    }

    /** @test */
    function it_can_return_a_validator_for_a_dialing_code_prefixed_number()
    {
        $validator = call_user_func_array(
            [$this->getValidatorInstance(), 'make'], $this->validMobileNumberPrefixed
        );

        $this->assertInstanceOf(PhoneNumberValidator::class, $validator);
    }

    /** @test */
    function it_can_return_a_validator_for_a_prefixed_number_and_no_country_code()
    {
        $validator = $this->getValidatorInstance()->make($this->validMobileNumberPrefixed[0]);
        $this->assertInstanceOf(PhoneNumberValidator::class, $validator);
    }

    /** @test */
    function it_can_return_a_validator_when_an_invalid_country_code_is_given()
    {
        $validator = $this->getValidatorInstance()->make($this->validMobileNumber[0], 'ZZ');
        $this->assertInstanceOf(PhoneNumberValidator::class, $validator);
    }

    /** @test */
    function it_can_return_a_validator_when_an_invalid_number_is_given()
    {
        $validator = $this->getValidatorInstance()->make($this->invalidNumber[0], $this->invalidNumber[1]);
        $this->assertInstanceOf(PhoneNumberValidator::class, $validator);
    }

    /** @test */
    function it_can_validate_a_number_against_a_given_country_code()
    {
        $validator = $this->getValidatorInstance();
        $validator->make($this->validMobileNumber[0], $this->validMobileNumber[1]);

        $this->assertTrue($validator->isValidForCountry('GB'));
        $this->assertFalse($validator->isValidForCountry('ES'));
    }

    /** @test */
    function it_can_validate_a_number_against_an_array_of_country_codes()
    {
        $validator = $this->getValidatorInstance();
        $validator->make($this->validGuernseyNumber, 'GB');

        $this->assertTrue($validator->isValidForCountry(['GG']));
        $this->assertFalse($validator->isValidForCountry(['ES', 'IT']));
    }

    /** @test */
    function it_can_validate_a_number_is_a_mobile()
    {
        $this->assertTrue(
            $this->getValidatorInstance()->make($this->validGuernseyNumber, 'GG')->isValidMobile()
        );
    }

    /** @test */
    function it_can_validate_a_number_is_not_a_mobile()
    {
        $this->assertFalse(
            $this->getValidatorInstance()->make($this->invalidNumber[0])->isValidMobile()
        );
    }

    /** @test */
    function it_can_validate_a_number_is_a_fixed_line()
    {
        $this->assertTrue(
            $this->getValidatorInstance()->make($this->validFixedLineNumber[0])->isValidFixedLine()
        );
    }

    /** @test */
    function it_can_validate_a_number_is_not_a_fixed_line()
    {
        $this->assertFalse(
            $this->getValidatorInstance()->make($this->invalidNumber[0])->isValidFixedLine()
        );
    }

    /** @test */
    function it_can_return_the_carrier_name_for_a_valid_number()
    {
        $this->assertEquals(
            'O2', $this->getValidatorInstance()->make('07712345678', 'GB')->getCarrierName()
        );
    }

    /** @test */
    function it_can_return_an_empty_string_as_a_carrier_name_for_an_invalid_number()
    {
        $this->assertEquals(
            '', $this->getValidatorInstance()->make('0912345678', 'GB')->getCarrierName()
        );
    }

    /** @test */
    function it_can_return_the_phone_number()
    {
        $actual = $this->getValidatorInstance()
            ->make($this->validMobileNumber[0], $this->validMobileNumber[1])
            ->getNumber();

        $this->assertEquals('+447912345678', str_replace(' ', '', $actual));
    }
}

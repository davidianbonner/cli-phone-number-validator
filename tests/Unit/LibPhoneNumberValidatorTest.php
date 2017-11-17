<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\LibPhoneNumberValidator;
use libphonenumber\PhoneNumberToCarrierMapper;
use Tests\Unit\Contracts\ValidatorContractTest;

class LibPhoneNumberValidatorTest extends TestCase
{
    use ValidatorContractTest;

    /**
     * Return a validator instance.
     *
     * @return string
     */
    public function getValidatorInstance()
    {
        return LibPhoneNumberValidator::getInstance();
    }

    /**
     * Return the validators class name.
     *
     * @return string
     */
    public function getValidatorInstanceString()
    {
        return LibPhoneNumberValidator::class;
    }

    /** @test */
    function it_can_return_a_new_instance_of_the_validator_with_a_static_helper()
    {
        $this->assertInstanceOf(
            LibPhoneNumberValidator::class, LibPhoneNumberValidator::getInstance()
        );
    }
}
<?php

namespace App;

use Exception;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;

class LibPhoneNumberValidator implements PhoneNumberValidator
{
    /**
     * @var libphonenumber\PhoneNumberUtil
     */
    protected $util;

    /**
     * @var libphonenumber\PhoneNumberToCarrierMapper
     */
    protected $carrierMapper;

    /**
     * Number to validate
     * @var libphonenumber\PhoneNumber
     */
    protected $number;

    /**
     * @var boolean
     */
    protected $isValid = false;

    /**
     * Instantiate the class and get an instance
     * of the libphonenumber util.
     */
    public function __construct(PhoneNumberUtil $util, PhoneNumberToCarrierMapper $carrierMapper)
    {
        $this->util = $util;
        $this->carrierMapper = $carrierMapper;

        // Default the phone number to prevent exceptions and allow control flow
        // to be handled by the developer with the 'isValid...' methods
        $this->number = new PhoneNumber;
    }

    /**
     * Validate the given number.
     *
     * @param  mixed        $number
     * @param  null|string  $countryCode
     * @return App\PhoneNumberValidator
     */
    public function make($number, string $countryCode = null): PhoneNumberValidator
    {
        try {
            $this->number = $this->util->parse($number, $countryCode);
        } catch (Exception $e) {
            // Let the 'isValid' methods handle the control-flow.
        }

        return $this;
    }

    /**
     * Is the number valid for the given country code.
     *
     * @param  mixed  $countryCode
     * @return boolean
     */
    public function isValidForCountry($countryCode): bool
    {
        foreach (array_wrap($countryCode) as $code) {
            if ($this->util->isValidNumberForRegion($this->number, mb_strtoupper($code))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is the number a valid mobile.
     *
     * @return boolean
     */
    public function isValidMobile(): bool
    {
        return $this->util->getNumberType($this->number) === PhoneNumberType::MOBILE;
    }

    /**
     * Is the number a valid landline/fixed-line.
     *
     * @return boolean
     */
    public function isValidFixedLine(): bool
    {
        return $this->util->getNumberType($this->number) === PhoneNumberType::FIXED_LINE;
    }

    /**
     * Return the carrier name.
     *
     * @return string
     */
    public function getCarrierName(): string
    {
        return $this->carrierMapper->getNameForNumber($this->number, 'en');
    }

    /**
     * Return the phone number.
     *
     * @return string
     */
    public function getNumber(): string
    {
        return $this->util->format($this->number,  PhoneNumberFormat::INTERNATIONAL);
    }

    /**
     * Get a new instance of the validator
     *
     * @param libphonenumber\PhoneNumberToCarrierMapper $mapper
     */
    public static function getInstance()
    {
        return new static(PhoneNumberUtil::getInstance(), PhoneNumberToCarrierMapper::getInstance());
    }
}

<?php

namespace App;

interface PhoneNumberValidator
{
    /**
     * Validate the given number.
     *
     * @param  mixed        $number
     * @param  null|string  $countryCode
     * @return self
     */
    public function make($number, string $countryCode = null): self;

    /**
     * Is the number a valid mobile.
     *
     * @return bool
     */
    public function isValidMobile(): bool;

    /**
     * Is the number a valid landline/fixed-line.
     *
     * @return bool
     */
    public function isValidFixedLine(): bool;

    /**
     * Is the number valid for the given country code.
     *
     * @param  mixed  $countryCode
     * @return bool
     */
    public function isValidForCountry($countryCode): bool;

    /**
     * Return the carrier name.
     *
     * @return array
     */
    public function getCarrierName(): string;
}

<?php

declare(strict_types=1);

interface PhoneNumberInterface {
    public static function isValidFormat(string $phoneNumber): bool;
}

readonly class MobilePhoneNumber implements PhoneNumberInterface
{
    public string $phoneNumber;

    public function __construct(#[SensitiveParameter] string $phoneNumber)
    {
        if (self::isValidFormat($phoneNumber) === false) {
            throw new InvalidArgumentException("Invalid phone number format");
        }
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return bool
     */
    public static function isValidFormat(#[SensitiveParameter] string $phoneNumber): bool
    {
        $mobilePhoneNumberFormat = '/^[0-9]{3}\-[0-9]{4}\-[0-9]{4}$/';
        $is_valid = preg_match($mobilePhoneNumberFormat, $phoneNumber);
        return $is_valid === 1;
    }
}

readonly class ServiceProviderNumberFormat implements PhoneNumberInterface
{
    public string $phoneNumber;
    public function __construct(#[SensitiveParameter] string $phoneNumber)
    {
        if (self::isValidFormat($phoneNumber) === false) {
            throw new InvalidArgumentException("Invalid phone number format");
        }
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return bool
     */
    public static function isValidFormat(#[SensitiveParameter] string $phoneNumber): bool
    {
        $serviceProviderNumberFormat = '/^[0-9]{4}\-[0-9]{3}\-[0-9]{3}$/';
        $is_valid = preg_match($serviceProviderNumberFormat, $phoneNumber);
        return $is_valid === 1;
    }
}

readonly class PhoneNumberFactory
{
    /**
     * @param string $phoneNumber
     * @return PhoneNumberInterface
     */
    public static function createPhoneNumber(#[SensitiveParameter] string $phoneNumber): PhoneNumberInterface
    {
        if (MobilePhoneNumber::isValidFormat($phoneNumber)) {
            return new MobilePhoneNumber($phoneNumber);
        }
        if (ServiceProviderNumberFormat::isValidFormat($phoneNumber)) {
            return new ServiceProviderNumberFormat($phoneNumber);
        }
        throw new InvalidArgumentException("Invalid phone number format: ". $phoneNumber);
    }
}

readonly class PhoneNumberNormalizer
{
    /**
     * @param string $phoneNumber
     * @return string
     */
    public static function normalizePhoneNumber(#[SensitiveParameter] string $phoneNumber): string
    {
        // Convert full-width digits to half-width
        $phoneNumberWithoutFullWidthDigit = mb_convert_kana($phoneNumber, 'n');

        // Remove any non-digit characters
        return preg_replace('/[^0-9\-]/', '', $phoneNumberWithoutFullWidthDigit);
    }
}

$valid_case = [
    "0000-000-000",
    "000-0000-0000",
    "０0０-１11１-2２２2",
];

for ($i = 0, $iMax = count($valid_case); $i < $iMax; $i++) {
    $normalizedPhoneNumber = PhoneNumberNormalizer::normalizePhoneNumber($valid_case[$i]);
    try {
        $phoneNumber = PhoneNumberFactory::createPhoneNumber($normalizedPhoneNumber);
        echo "[matched] Valid case: " . $phoneNumber->phoneNumber . "\n";
    } catch (InvalidArgumentException $e) {
        echo "[not matched] " . $e->getMessage() . "\n";
    }
}

$invalid_case = [
    "0000-0000-0000",
    "0000000000",
    "000-000-000",
];

for ($i = 0, $iMax = count($invalid_case); $i < $iMax; $i++) {
    try {
        $normalizedPhoneNumber = PhoneNumberNormalizer::normalizePhoneNumber($invalid_case[$i]);
        $phoneNumber = PhoneNumberFactory::createPhoneNumber($normalizedPhoneNumber);
        echo "[matched] Invalid case: " . $phoneNumber->phoneNumber . "\n";
    } catch (InvalidArgumentException $e) {
        echo "[not matched] " . $e->getMessage() . "\n";
    }
}

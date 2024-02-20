<?php

declare(strict_types=1);

readonly class NormalizedInputPhoneNumber
{
    public string $value;

    public function __construct(#[SensitiveParameter] string $phoneNumber)
    {
        $phoneNumberWithoutFullWidthDigit = mb_convert_kana($phoneNumber, 'n');

        $normalizedInputPhoneNumber = preg_replace(
            pattern: '/[^0-9\-]/',
            replacement: '',
            subject: $phoneNumberWithoutFullWidthDigit
        );

        $this->value = $normalizedInputPhoneNumber;
    }
}

interface PhoneNumberInterface {
    public static function isValidFormat(NormalizedInputPhoneNumber $phoneNumber): bool;
}

readonly class MobilePhoneNumber implements PhoneNumberInterface
{
    public string $phoneNumber;

    public function __construct(#[SensitiveParameter] NormalizedInputPhoneNumber $phoneNumber)
    {
        if (self::isValidFormat($phoneNumber) === false) {
            throw new InvalidArgumentException("Invalid phone number format");
        }
        $this->phoneNumber = $phoneNumber->value;
    }

    /**
     * @param NormalizedInputPhoneNumber $phoneNumber
     * @return bool
     */
    public static function isValidFormat(#[SensitiveParameter] NormalizedInputPhoneNumber $phoneNumber): bool
    {
        // NormalizedInputPhoneNumberのvalueは半角数字とハイフンのみ
        // この関数は半角数字とハイフンの組み合わせだけを判断する
        $mobilePhoneNumberFormat = '/^[0-9]{3}\-[0-9]{4}\-[0-9]{4}$/';
        $is_valid = preg_match($mobilePhoneNumberFormat, $phoneNumber->value);
        return $is_valid === 1;
    }
}

readonly class ServiceProviderNumberFormat implements PhoneNumberInterface
{
    public string $phoneNumber;
    public function __construct(#[SensitiveParameter] NormalizedInputPhoneNumber $phoneNumber)
    {
        if (self::isValidFormat($phoneNumber) === false) {
            throw new InvalidArgumentException("Invalid phone number format");
        }
        $this->phoneNumber = $phoneNumber->value;
    }

    /**
     * @param NormalizedInputPhoneNumber $phoneNumber
     * @return bool
     */
    public static function isValidFormat(#[SensitiveParameter] NormalizedInputPhoneNumber $phoneNumber): bool
    {
        // NormalizedInputPhoneNumberのvalueは半角数字とハイフンのみ
        // この関数は半角数字とハイフンの組み合わせだけを判断する
        $serviceProviderNumberFormat = '/^[0-9]{4}\-[0-9]{3}\-[0-9]{3}$/';
        $is_valid = preg_match($serviceProviderNumberFormat, $phoneNumber->value);
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
        $normalizedPhoneNumber = new NormalizedInputPhoneNumber($phoneNumber);
        if (MobilePhoneNumber::isValidFormat($normalizedPhoneNumber)) {
            return new MobilePhoneNumber($normalizedPhoneNumber);
        }
        if (ServiceProviderNumberFormat::isValidFormat($normalizedPhoneNumber)) {
            return new ServiceProviderNumberFormat($normalizedPhoneNumber);
        }
        throw new InvalidArgumentException("error (check your input): ". $phoneNumber);
    }
}

$valid_case = [
    "0000-000-000",
    "000-0000-0000",
    "０0０-１11１-2２２2",
];

for ($i = 0, $iMax = count($valid_case); $i < $iMax; $i++) {
    try {
        $phoneNumber = PhoneNumberFactory::createPhoneNumber($valid_case[$i]);
        echo "[ok] valid case matched: " . $phoneNumber->phoneNumber . "\n";
    } catch (InvalidArgumentException $e) {
        echo "[fail] valid case did not matched: " . $e->getMessage() . "\n";
    }
}

$invalid_case = [
    "0000-0000-0000",
    "0000000000",
    "000-000-000",
    "0000-000-000-",
];

for ($i = 0, $iMax = count($invalid_case); $i < $iMax; $i++) {
    try {
        $phoneNumber = PhoneNumberFactory::createPhoneNumber($invalid_case[$i]);
        echo "[fail] invalid case matched: " . $phoneNumber->phoneNumber . "\n";
    } catch (InvalidArgumentException $e) {
        echo "[ok] invalid case did not matched: " . $e->getMessage() . "\n";
    }
}

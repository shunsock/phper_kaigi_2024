<?php

declare(strict_types=1);

include("TestCase.php");

readonly class ValidatedInputPhoneNumber
{
    public string $value;

    public function __construct(#[SensitiveParameter] string $phoneNumber)
    {
        $validCharacterOfPhoneNumber = '/[^０-９0-9\-ー]+/u';
        if (preg_match($validCharacterOfPhoneNumber, $phoneNumber)) {
            throw new InvalidArgumentException("Invalid phone number format");
        }

        $this->value = $phoneNumber;
    }
}

readonly class NormalizedInputPhoneNumber
{
    public string $value;

    public function __construct(#[SensitiveParameter] ValidatedInputPhoneNumber $phoneNumber)
    {
        // 全角数字を半角数字に変換 (例: ０ -> 0)
        // 厳密には異なるので注意
        $phoneNumberWithoutFullWidthDigit = mb_convert_kana($phoneNumber->value, 'n');

        $normalizedInputPhoneNumber = preg_replace(
            pattern: '/[^0-9\-]/',
            replacement: '',
            subject: $phoneNumberWithoutFullWidthDigit
        );

        $this->value = $normalizedInputPhoneNumber;
    }
}

// Interfaceには I{Name} とつける流派もあるが今回の例では使わない
// 理由は I 接頭辞をつけると IPhoneNumber という名前になるが、iPhoneと混同される可能性があるため
// 参照: https://qiita.com/suin/items/00656d9dbd2f26dd8b91
interface PhoneNumberInterface {
    public static function matchFormat(NormalizedInputPhoneNumber $phoneNumber): bool;
}


readonly class MobilePhoneNumber implements PhoneNumberInterface
{
    public string $phoneNumber;

    public function __construct(#[SensitiveParameter] NormalizedInputPhoneNumber $phoneNumber)
    {
        if (self::matchFormat($phoneNumber) === false) {
            throw new InvalidArgumentException("Invalid phone number format");
        }
        $this->phoneNumber = $phoneNumber->value;
    }

    /**
     * @param NormalizedInputPhoneNumber $phoneNumber
     * @return bool
     */
    public static function matchFormat(#[SensitiveParameter] NormalizedInputPhoneNumber $phoneNumber): bool
    {
        // [good] NormalizedInputPhoneNumberのvalueは半角数字とハイフンのみ
        // [good] この関数は半角数字とハイフンの並び順だけを判断する
        $mobilePhoneNumberFormat = '/^[0-9]{3}\-[0-9]{4}\-[0-9]{4}$/';
        $is_valid = preg_match($mobilePhoneNumberFormat, $phoneNumber->value);
        return $is_valid === 1;
    }
}

readonly class ServiceProviderNumber implements PhoneNumberInterface
{
    public string $phoneNumber;
    public function __construct(#[SensitiveParameter] NormalizedInputPhoneNumber $phoneNumber)
    {
        if (self::matchFormat($phoneNumber) === false) {
            throw new InvalidArgumentException("Invalid phone number format");
        }
        $this->phoneNumber = $phoneNumber->value;
    }

    /**
     * @param NormalizedInputPhoneNumber $phoneNumber
     * @return bool
     */
    public static function matchFormat(#[SensitiveParameter] NormalizedInputPhoneNumber $phoneNumber): bool
    {
        // [good] NormalizedInputPhoneNumberのvalueは半角数字とハイフンのみ
        // [good] この関数は半角数字とハイフンの並び順だけを判断する
        $serviceProviderNumberFormat = '/^[0-9]{4}\-[0-9]{3}\-[0-9]{3}$/';
        $is_valid = preg_match($serviceProviderNumberFormat, $phoneNumber->value);
        return $is_valid === 1;
    }
}

readonly class PhoneNumberFactory
{
    /**
     * @param NormalizedInputPhoneNumber $phoneNumber
     * @return MobilePhoneNumber|ServiceProviderNumber
     */
    public static function createPhoneNumber(#[SensitiveParameter] NormalizedInputPhoneNumber $phoneNumber): MobilePhoneNumber|ServiceProviderNumber
    {
        // [good] regexを確認するUnitな関数を並べておくとテストしやすい
        // (補足) function(function(function(string text)))のようにネストするとテストが難しい
        return match (true) {
            MobilePhoneNumber::matchFormat($phoneNumber) => new MobilePhoneNumber($phoneNumber),
            ServiceProviderNumber::matchFormat($phoneNumber) => new ServiceProviderNumber($phoneNumber),
            default => throw new InvalidArgumentException("error (check your input): ". $phoneNumber->value),
        };
    }
}

$test_cases = new TestCase();

foreach ($test_cases->valid_case as $phoneNumberTestCase) {
    try {
        // [good] 正しい文字だけで構成されていることを保証 => この後の関数は入力値を信頼できる
        // ここはエラーを分けておくべきだが, 今回はテストの都合上省略
        $validatedPhoneNumber = new ValidatedInputPhoneNumber($phoneNumberTestCase);
        // [good] 半角数字だけで構成されていることを保証 => 電話番号のフォーマット確認の時に半角か全角かを気にしなくて良い
        $normalizedPhoneNumber = new NormalizedInputPhoneNumber($validatedPhoneNumber);
        // [good] 半角英数字とハイフンのみの並び方だけを確認する
        $phoneNumber = PhoneNumberFactory::createPhoneNumber($normalizedPhoneNumber);
        echo "[ok] valid case matched: " . $phoneNumber->phoneNumber . "\n";
    } catch (InvalidArgumentException $e) {
        echo "[fail] valid case did not matched: " . $e->getMessage() . "\n";
    }
}

foreach ($test_cases->invalid_case as $phoneNumberTestCase) {
    try {
        // [good] 正しい文字だけで構成されていることを保証 => この後の関数は入力値を信頼できる
        // ここはエラーを分けておくべきだが, 今回はテストの都合上省略
        $validatedPhoneNumber = new ValidatedInputPhoneNumber($phoneNumberTestCase);
        // [good] 半角数字だけで構成されていることを保証 => 電話番号のフォーマット確認の時に半角か全角かを気にしなくて良い
        $normalizedPhoneNumber = new NormalizedInputPhoneNumber($validatedPhoneNumber);
        // [good] 半角英数字とハイフンのみの並び方だけを確認する
        $phoneNumber = PhoneNumberFactory::createPhoneNumber($normalizedPhoneNumber);
        echo "[fail] invalid case matched: " . $phoneNumber->phoneNumber . "\n";
    } catch (InvalidArgumentException $e) {
        echo "[ok] invalid case did not matched: " . $e->getMessage() . "\n";
    }
}

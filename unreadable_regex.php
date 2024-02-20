<?php

declare(strict_types=1);

class unreadable_regex
{
    public static function isValidPhoneNumber(#[SensitiveParameter] string $phoneNumber): bool
    {
        // stringなので全角か半角かがわからない
        // フォーマットが複数あるので if, forで対応
        // \dは言語ごとに仕様が違うので、非推奨
        $regex = '/^([\d]{3,4}-?){3}$/';
        if (preg_match($regex, $phoneNumber) === false) {
            throw new InvalidArgumentException("Invalid phone number format");
        }
        return true;
    }
}

$valid_case = [
    "0000-000-000",
    "000-0000-0000",
    "０0０-１11１-2２２2",
];

for ($i = 0, $iMax = count($valid_case); $i < $iMax; $i++) {
    try {
        unreadable_regex::isValidPhoneNumber($valid_case[$i]);
        echo "[ok] valid case matched: " . $valid_case[$i] . "\n";
    } catch (InvalidArgumentException $e) {
        echo "[fail] valid case did not matched: " . $valid_case[$i] . "\n";
    }
}

$invalid_case = [
    "0000-0000-0000",  // {3, 4}
    "0000000000",      // -?
    "000-000-000",
    "0000-000-000-",
];

for ($i = 0, $iMax = count($invalid_case); $i < $iMax; $i++) {
    try {
        unreadable_regex::isValidPhoneNumber($invalid_case[$i]);
        echo "[fail] invalid case matched: " . $invalid_case[$i] . "\n";
    } catch (InvalidArgumentException $e) {
        echo "[ok] invalid case did not matched: " . $e->getMessage() . "\n";
    }
}

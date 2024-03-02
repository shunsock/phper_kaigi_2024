<?php

declare(strict_types=1);

include("TestCase.php");

class unreadable_regex
{
    /**
     * @param string $phoneNumber
     * @return bool
     */
    public static function isValidPhoneNumber(#[SensitiveParameter] string $phoneNumber): bool
    {
        // この関数は、電話番号のフォーマットが正しいかどうかをチェックする
        // [bad] 関心が大きすぎて管理不可能 (下記詳細)
        //
        // 1. 入力値が信頼できない
        // - stringなので全角か半角どちらにも対応する必要が出る
        // - また、数字以外の文字が含まれている場合もある
        // 2. 複数のフォーマットを一度に判別しようとしている
        // - 000-000-0000, 0000-000-0000, 000-0000-0000, 119など
        // 3. 1, 2により正規表現の複雑度が上がる
        // - フォーマットが複数あるので if, forに当たる演算子で対応
        // - \dは言語ごとに仕様が違うので、非推奨
        //
        // 1, 2, 3によりテストが困難. 信頼できない.
        $regex = '/^([\d]{3,4}-?){3}$/';
        if (preg_match($regex, $phoneNumber) === false) {
            throw new InvalidArgumentException("Invalid phone number format");
        }
        return true;
    }
}

$test_cases = new TestCase();

foreach ($test_cases->valid_case as $phoneNumberTestCase) {
    try {
        // [bad] この$phoneNumberTestCaseはstringなので、信頼できない (このプログラムでは信頼している)
        unreadable_regex::isValidPhoneNumber($phoneNumberTestCase);
        echo "[ok] valid case matched: " . $phoneNumberTestCase . "\n";
    } catch (InvalidArgumentException $e) {
        echo "[fail] valid case did not matched: " . $e->getMessage() . "\n";
    }
}

foreach ($test_cases->invalid_case as $phoneNumberTestCase) {
    try {
        // [bad] この$phoneNumberTestCaseはstringなので、信頼できない (このプログラムでは信頼している)
        unreadable_regex::isValidPhoneNumber($phoneNumberTestCase);
        echo "[fail] invalid case matched: " . $phoneNumberTestCase . "\n";
    } catch (InvalidArgumentException $e) {
        echo "[ok] invalid case did not matched: " . $e->getMessage() . "\n";
    }
}

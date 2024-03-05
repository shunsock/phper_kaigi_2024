<?php

declare(strict_types=1);

readonly class TestCase
{
    public array $valid_case;

    public array $invalid_case;

    public function __construct()
    {
        $this->valid_case = [
            "0000-000-000",
            "000-0000-0000",
            "０0０-１11１-2２２2",
        ];

        $this->invalid_case = [
            "0000-0000-0000",
            "0000000000",
            "000-000-000",
            "0000-000-000-",
        ];
    }
}

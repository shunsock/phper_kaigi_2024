<?php

declare(strict_types=1);

$regex = '/^([0-9０-９]{3,4}-?){3}$/';

$valid_case = [
  "0000-000-000",
  "000-0000-0000",
  "０0０-１11１-2２２2",
];

for ($i = 0; $i < count($valid_case); $i++) {
  if (preg_match($regex, $valid_case[$i])) {
    echo "[matched] Valid case: " . $valid_case[$i] . "\n";
  }
}

$invalid_case = [
  "0000-0000-0000",  // {3, 4}
  "0000000000",      // -?
];

for ($i = 0; $i < count($invalid_case); $i++) {
  if (preg_match($regex, $invalid_case[$i])) {
    echo "[matched] Invalid case: " . $invalid_case[$i] . "\n";
  }
}

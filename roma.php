<?php

// Written by Claude (claude.ai)

function generateRandomSymbol(int $length = 1, string $charset = ''): string
{
    if ($charset === '') {
        $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
    }

    $result = '';
    $max = strlen($charset) - 1;

    for ($i = 0; $i < $length; $i++) {
        $result .= $charset[random_int(0, $max)];
    }

    return $result;
}

$symbol = generateRandomSymbol();
$string = generateRandomSymbol(16);

echo "Random symbol: " . $symbol . PHP_EOL;
echo "Random string (16): " . $string . PHP_EOL;

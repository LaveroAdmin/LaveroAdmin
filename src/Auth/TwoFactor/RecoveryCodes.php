<?php

namespace LaveroAdmin\Admin\Auth\TwoFactor;

use Illuminate\Support\Str;

class RecoveryCodes
{
    /**
     * Generate a new set of formatted recovery codes.
     *
     * @param int $count
     * @return array
     */
    public static function generate(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(Str::random(10));
            $formatted = substr($code, 0, 5) . '-' . substr($code, 5, 5);
            $codes[] = $formatted;
        }

        return $codes;
    }

    /**
     * Normalize a recovery code (strip dashes and whitespace, uppercase).
     *
     * @param string $code
     * @return string
     */
    public static function normalize(string $code): string
    {
        return strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $code));
    }

    /**
     * Verify and consume a recovery code from the array of existing codes.
     *
     * @param array $existingCodes
     * @param string $inputCode
     * @return array|false Returns remaining codes on success, false on failure
     */
    public static function verifyAndConsume(array $existingCodes, string $inputCode)
    {
        $normalizedInput = self::normalize($inputCode);

        foreach ($existingCodes as $index => $existing) {
            if (hash_equals(self::normalize($existing), $normalizedInput)) {
                unset($existingCodes[$index]);
                return array_values($existingCodes);
            }
        }

        return false;
    }
}

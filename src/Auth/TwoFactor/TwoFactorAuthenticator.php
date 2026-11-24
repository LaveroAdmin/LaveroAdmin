<?php

namespace LaveroAdmin\Admin\Auth\TwoFactor;

class TwoFactorAuthenticator
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random Base32 secret key.
     *
     * @param int $length
     * @return string
     */
    public function generateSecretKey(int $length = 16): string
    {
        $secret = '';
        $alphabetLength = strlen(self::BASE32_ALPHABET);

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $secret;
    }

    /**
     * Calculate current One Time Password (OTP).
     *
     * @param string $secret
     * @param int|null $timeSlice
     * @return string
     */
    public function getCurrentOtp(string $secret, ?int $timeSlice = null): string
    {
        if ($timeSlice === null) {
            $timeSlice = (int) floor(time() / 30);
        }

        $secretBytes = $this->base32Decode($secret);
        $timeBytes = pack('N*', 0) . pack('N*', $timeSlice);

        $hmac = hash_hmac('sha1', $timeBytes, $secretBytes, true);
        $offset = ord(substr($hmac, -1)) & 0x0F;

        $unpacked = unpack('N', substr($hmac, $offset, 4));
        $value = ($unpacked[1] & 0x7FFFFFFF) % 1000000;

        return str_pad((string) $value, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify an OTP code against a secret key with time drift tolerance.
     *
     * @param string $secret
     * @param string $code
     * @param int $discrepancy
     * @return bool
     */
    public function verifyKey(string $secret, string $code, int $discrepancy = 1): bool
    {
        $code = trim($code);
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }

        $currentTimeSlice = (int) floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedOtp = $this->getCurrentOtp($secret, $currentTimeSlice + $i);
            if (hash_equals($calculatedOtp, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the standard otpauth URL for authenticator apps.
     *
     * @param string $company
     * @param string $holder
     * @param string $secret
     * @return string
     */
    public function getOtpAuthUrl(string $company, string $holder, string $secret): string
    {
        $issuer = rawurlencode($company);
        $account = rawurlencode($holder);

        return "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Get QR Code image URL for easy scanning.
     *
     * @param string $company
     * @param string $holder
     * @param string $secret
     * @param int $size
     * @return string
     */
    public function getQrCodeUrl(string $company, string $holder, string $secret, int $size = 200): string
    {
        $otpauth = $this->getOtpAuthUrl($company, $holder, $secret);

        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($otpauth);
    }

    /**
     * Decode a Base32 string to binary data.
     *
     * @param string $b32
     * @return string
     */
    protected function base32Decode(string $b32): string
    {
        $b32 = strtoupper(trim($b32));
        $buffer = 0;
        $bitsLeft = 0;
        $binary = '';

        for ($i = 0; $i < strlen($b32); $i++) {
            $val = strpos(self::BASE32_ALPHABET, $b32[$i]);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binary .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $binary;
    }
}

<?php

if (!function_exists('encrypt_field')) {
    /**
     * Encrypt a field value using AES-256 via Laravel's encrypter
     * with a dedicated ENCRYPTION_KEY (not APP_KEY).
     */
    function encrypt_field(?string $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $key = base64_decode(config('app.encryption_key'));
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return base64_encode($iv . $encrypted);
    }
}

if (!function_exists('decrypt_field')) {
    /**
     * Decrypt a field value encrypted by encrypt_field().
     */
    function decrypt_field(?string $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $key = base64_decode(config('app.encryption_key'));
        $decoded = base64_decode($value);
        $iv = substr($decoded, 0, 16);
        $encrypted = substr($decoded, 16);

        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return $decrypted === false ? null : $decrypted;
    }
}

if (!function_exists('hmac_hash')) {
    /**
     * Generate a deterministic HMAC-SHA256 hash for searching/deduplication.
     * Use this for NIK, NPWP fields where exact-match search is needed.
     */
    function hmac_hash(?string $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return hash_hmac('sha256', $value, base64_decode(config('app.hmac_key')));
    }
}

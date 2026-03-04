<?php

use function Pest\Laravel\{get, post, put, delete};

describe('encrypt/decrypt', function () {
    it('roundtrip: decrypt(encrypt(x)) === x', function () {
        $values = ['hello', '1234567890', 'NIK-3171234567890001', 'special chars: @#$%'];

        foreach ($values as $val) {
            $encrypted = encrypt_field($val);
            expect($encrypted)->not->toBe($val);
            expect(decrypt_field($encrypted))->toBe($val);
        }
    });

    it('encrypt_field(null) returns null', function () {
        expect(encrypt_field(null))->toBeNull();
    });

    it('decrypt_field(null) returns null', function () {
        expect(decrypt_field(null))->toBeNull();
    });

    it('setiap enkripsi menghasilkan ciphertext berbeda (random IV)', function () {
        $a = encrypt_field('same value');
        $b = encrypt_field('same value');
        expect($a)->not->toBe($b); // karena IV random
        expect(decrypt_field($a))->toBe(decrypt_field($b)); // tapi plaintext sama
    });
});

describe('hmac_hash', function () {
    it('deterministik: input sama → output sama', function () {
        $hash1 = hmac_hash('3171234567890001');
        $hash2 = hmac_hash('3171234567890001');
        expect($hash1)->toBe($hash2);
    });

    it('input berbeda → hash berbeda', function () {
        $h1 = hmac_hash('3171234567890001');
        $h2 = hmac_hash('3171234567890002');
        expect($h1)->not->toBe($h2);
    });

    it('menghasilkan string tidak kosong', function () {
        expect(hmac_hash('test'))->toBeString()->not->toBeEmpty();
    });
});

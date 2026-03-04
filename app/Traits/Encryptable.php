<?php

namespace App\Traits;

/**
 * Auto-encrypt on setAttribute, auto-decrypt on getAttribute.
 *
 * Model must define:
 *   protected array $encryptable = ['nik', 'phone', ...];
 *
 * Kolom DB: {field}_encrypted
 * Hash kolom DB: {field}_hash (opsional — untuk NIK, NPWP)
 */
trait Encryptable
{
    public function getAttribute($key): mixed
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable ?? [])) {
            $encrypted = parent::getAttribute($key . '_encrypted');
            return $encrypted ? decrypt_field($encrypted) : null;
        }

        return $value;
    }

    public function setAttribute($key, $value): mixed
    {
        if (in_array($key, $this->encryptable ?? [])) {
            // Simpan encrypted ke kolom {field}_encrypted
            parent::setAttribute($key . '_encrypted', $value ? encrypt_field($value) : null);

            // Jika ada kolom hash (untuk uniqueness check), simpan juga
            if ($this->hasColumn($key . '_hash') && $value) {
                parent::setAttribute($key . '_hash', hmac_hash($value));
            }

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /** Fill encrypted field langsung dari raw value */
    public function fillEncrypted(string $field, mixed $value): static
    {
        $this->setAttribute($field, $value);
        return $this;
    }

    private function hasColumn(string $column): bool
    {
        static $cache = [];
        $table = $this->getTable();
        $key = "{$table}.{$column}";

        if (!isset($cache[$key])) {
            $cache[$key] = \Schema::hasColumn($table, $column);
        }

        return $cache[$key];
    }
}

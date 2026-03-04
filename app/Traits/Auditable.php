<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->createAuditLog('created', null, $model->getAuditableAttributes());
        });

        static::updated(function ($model) {
            $changed = $model->getChangedAuditableAttributes();
            if (!empty($changed['new'])) {
                $model->createAuditLog('updated', $changed['old'], $changed['new']);
            }
        });

        static::deleted(function ($model) {
            $model->createAuditLog('deleted', $model->getAuditableAttributes(), null);
        });
    }

    protected function createAuditLog(string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'action' => $action,
            'actor_id' => Auth::id(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    protected function getAuditableAttributes(): array
    {
        $attributes = $this->attributes;

        // Mask encrypted fields — jangan simpan value asli di audit log
        foreach ($this->getEncryptedFields() as $field) {
            if (isset($attributes[$field])) {
                $attributes[$field] = '[encrypted]';
            }
        }

        return $attributes;
    }

    protected function getChangedAuditableAttributes(): array
    {
        $dirty = $this->getDirty();
        $old = [];
        $new = [];

        foreach ($dirty as $key => $newVal) {
            $encrypted = $this->getEncryptedFields();

            $old[$key] = in_array($key, $encrypted) ? '[encrypted]' : $this->getOriginal($key);
            $new[$key] = in_array($key, $encrypted) ? '[encrypted]' : $newVal;
        }

        return ['old' => $old, 'new' => $new];
    }

    protected function getEncryptedFields(): array
    {
        return property_exists($this, 'encryptedFields') ? $this->encryptedFields : [];
    }
}

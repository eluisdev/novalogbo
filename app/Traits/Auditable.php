<?php

namespace App\Traits;

use App\Models\Audit;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the trait for a model.
     */
    protected static function bootAuditable()
    {
        // Registrar evento al crear un modelo
        static::created(function ($model) {
            self::registerAudit('created', $model, null, $model->getAttributes());
        });

        // Registrar evento al actualizar un modelo
        static::updated(function ($model) {
            $oldValues = array_intersect_key($model->getOriginal(), $model->getChanges());
            $newValues = $model->getChanges();

            if (!empty($newValues)) {
                self::registerAudit('updated', $model, $oldValues, $newValues);
            }
        });

        // Registrar evento al eliminar un modelo
        static::deleted(function ($model) {
            self::registerAudit('deleted', $model, $model->getAttributes(), null);
        });

        // Si el modelo usa SoftDeletes, registrar también softDeleted
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(static::class))) {
            static::softDeleted(function ($model) {
                self::registerAudit('soft_deleted', $model, $model->getAttributes(), null);
            });

            static::restored(function ($model) {
                self::registerAudit('restored', $model, null, $model->getAttributes());
            });
        }
    }

    protected static function registerAudit($action, $model, $oldValues = null, $newValues = null)
    {
        Audit::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'user_id' => Auth::id(),
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
        ]);
    }
}

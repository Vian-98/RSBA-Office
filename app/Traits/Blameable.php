<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait Blameable
{
    protected static array $blameableColumns = [];

    protected static function hasColumnCached($model, $column)
    {
        $table = $model->getTable();

        if (!isset(self::$blameableColumns[$table])) {
            self::$blameableColumns[$table] = Schema::getColumnListing($table);
        }

        return in_array($column, self::$blameableColumns[$table]);
    }

    protected static function bootBlameable()
    {
        static::creating(function ($model) {
            if (!Auth::check()) return;

            if (self::hasColumnCached($model, 'created_by')) {
                $model->created_by = Auth::id();
            }

            if (self::hasColumnCached($model, 'updated_by')) {
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (!Auth::check()) return;

            if (self::hasColumnCached($model, 'updated_by')) {
                $model->updated_by = Auth::id();
            }
        });
    }
}

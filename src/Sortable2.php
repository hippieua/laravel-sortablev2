<?php

namespace Hippieua\Sortable2;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait Sortable2
{
    /**
     * @throws Exception
     */
    public static function bootSortable2(): void
    {
        $events = ['creating', 'saving', 'updating', 'deleting', 'retrieved'];

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))) {
            $events[] = 'restoring'; // Only add restoring if SoftDeletes is used
        }

        foreach ($events as $event) {
            static::$event(fn (Model $model) => self::checkSortableIntegrity($model));
        }

        static::creating(fn (Model $model) => self::initializeSortableField($model));
    }

    protected function getSortableField(): string
    {
        return 'order_id';
    }

    protected function getSortableRelation(): ?BelongsTo
    {
        return null;
    }

    /**
     * Check the integrity of the sortable field and relation.
     *
     * @throws Exception
     */
    protected static function checkSortableIntegrity(Model $model): void
    {
        // Check if the sortable field exists in the model's table
        $sortableField = $model->getSortableField();
        if (! Schema::hasColumn($model->getTable(), $sortableField)) {
            throw new Exception(sprintf('The sortable field "%s" does not exist in the database table "%s".', $sortableField,
                $model->getTable()));
        }

        // Check if the sortable relation's table exists
        $relation = $model->getSortableRelation();
        if ($relation && ! Schema::hasTable($relation->getRelated()->getTable())) {
            throw new Exception(sprintf('The sortable relation table "%s" does not exist.', $relation->getRelated()->getTable()));
        }
    }

    /**
     * Initialize the sortable field for a creating model.
     */
    protected static function initializeSortableField(Model $model): void
    {
        $sortableField = $model->getSortableField();
        $relation = $model->getSortableRelation();

        $query = $model->newQuery();

        if ($relation) {
            $foreignKey = $relation->getForeignKeyName();
            $query->where($foreignKey, $model->{$foreignKey});
        }

        $highestValue = $query->max($sortableField);
        $model->{$sortableField} = $highestValue + 1;
    }

    public function moveDown(): void
    {
        DB::transaction(function () {
            $sortableField = $this->getSortableField();
            $relation = $this->getSortableRelation();

            $query = static::query(); // Start with a base query

            if ($relation) {
                $query->where($relation->getForeignKeyName(), $relation->getParentKey());
            }

            $next = $query->where($sortableField, '>', $this->{$sortableField})
                ->orderBy($sortableField)
                ->first();

            if ($next) {
                $temp = $this->{$sortableField};
                $this->{$sortableField} = $next->{$sortableField};
                $next->{$sortableField} = $temp;

                $this->save();
                $next->save();
            }
        });
    }

    public function moveUp(): void
    {
        DB::transaction(function () {
            $sortableField = $this->getSortableField();
            $relation = $this->getSortableRelation();

            $query = static::query(); // Start with a base query

            if ($relation) {
                $query->where($relation->getForeignKeyName(), $relation->getParentKey());
            }

            $previous = $query->where($sortableField, '<', $this->{$sortableField})
                ->orderByDesc($sortableField)
                ->first();

            if ($previous) {
                $temp = $this->{$sortableField};
                $this->{$sortableField} = $previous->{$sortableField};
                $previous->{$sortableField} = $temp;

                $this->save();
                $previous->save();
            }
        });
    }
}

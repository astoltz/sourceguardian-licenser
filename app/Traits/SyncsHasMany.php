<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait to handle synchronization of HasMany relationships.
 *
 * This trait provides a method to sync a collection of items with a HasMany relationship.
 * It handles creating new items, updating existing items, and deleting items that are
 * no longer present in the input array.
 */
trait SyncsHasMany
{
    /**
     * Sync the given items with the HasMany relationship.
     *
     * @param HasMany $relation The HasMany relationship instance.
     * @param array $items The array of items to sync. Each item should be an associative array.
     *                     If an item has an 'id' key, it will be updated. Otherwise, it will be created.
     *                     Items in the database that are not in this array will be deleted.
     */
    protected function syncHasMany(HasMany $relation, array $items)
    {
        // Get existing models keyed by ID
        $existing = $relation->get()->keyBy('id');

        $inputIds = [];

        foreach ($items as $item) {
            if (isset($item['id']) && $existing->has($item['id'])) {
                $inputIds[] = $item['id'];
                $existing->get($item['id'])->update($item);
            } else {
                // Remove ID if it was passed but doesn't exist to ensure a new record is created
                unset($item['id']);
                $relation->create($item);
            }
        }

        // Delete missing
        $existing->except($inputIds)->each->delete();
    }
}

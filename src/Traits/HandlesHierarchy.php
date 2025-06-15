<?php

namespace BenColmer\NovaResourceHierarchy\Traits;

use ArrayAccess;

/**
 * Methods for building hierarchy from a flat array.
 */
trait HandlesHierarchy
{
    /**
     * Build a hierarchy from a source using depth first tree traversal.
     */
    private function buildHierarchy(
        array|ArrayAccess $source,
        callable $formatItem,
        string $idField = 'id',
        string $parentField = 'parent_id',
        mixed $parentId = null
    ): array {
        $children = [];
        foreach ($source as $key => $item) {
            if (! isset($item[$idField])) continue;

            // represent any falsy parent ID as a top-level node
            $itemParentId = $item[$parentField] ?? null;
            if (! $itemParentId) $itemParentId = null;

            // only parse direct children
            if ($itemParentId != $parentId) continue;

            // remove visited node from collection
            unset($source[$key]);

            // format and parse children
            $formatted = $formatItem($item);
            if (! is_array($formatted)) continue;

            $formatted['children'] = $this->buildHierarchy(
                $source,
                $formatItem,
                $idField,
                $parentField,
                $item[$idField]
            );
            $children[] = $formatted;
        }

        return $children;
    }
}

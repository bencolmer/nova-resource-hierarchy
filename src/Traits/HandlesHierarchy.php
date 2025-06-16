<?php

namespace BenColmer\NovaResourceHierarchy\Traits;

use ArrayAccess;

/**
 * Methods for building hierarchy from a flat array.
 */
trait HandlesHierarchy
{
    /**
     * Build a hierarchy from a source.
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

    /**
     * Parse a hierarchy to a flat array.
     */
    private function parseHierarchy(
        array $hierarchy,
        callable $formatItem,
        string $idField = 'id',
        mixed $parentId = null,
        array $result = []
    ): array {
        $rank = 0;
        foreach ($hierarchy as $item) {
            if (! isset($item[$idField])) continue;

            // parse current item
            $formatted = $formatItem($item, $parentId, $rank);
            if ($formatted) $result[] = $formatted;

            // parse children
            if (isset($item['children']) && is_array($item['children'])) {
                $result = $this->parseHierarchy(
                    $item['children'],
                    $formatItem,
                    $idField,
                    $item[$idField],
                    $result
                );
            }

            $rank++;
        }

        return $result;
    }
}

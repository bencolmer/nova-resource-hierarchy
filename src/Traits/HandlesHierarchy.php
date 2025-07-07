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
        ?callable $orderItems = null,
        string $idKey = 'id',
        string $parentKey = 'parent_id',
        mixed $parentId = null
    ): array {
        $children = [];
        foreach ($source as $key => $item) {
            if (! isset($item[$idKey])) continue;

            // represent any falsy parent ID as a top-level node
            $itemParentId = $item[$parentKey] ?? null;
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
                $orderItems,
                $idKey,
                $parentKey,
                $item[$idKey]
            );
            $children[] = $formatted;
        }

        if ($orderItems) $children = $orderItems($children);

        return $children;
    }

    /**
     * Parse a hierarchy to a flat array.
     */
    private function parseHierarchy(
        array $hierarchy,
        callable $formatItem,
        string $idKey = 'id',
        mixed $parentId = null,
        array $result = []
    ): array {
        $rank = 0;
        foreach ($hierarchy as $item) {
            if (! isset($item[$idKey])) continue;

            // parse current item
            $formatted = $formatItem($item, $parentId, $rank);
            if ($formatted) $result[] = $formatted;

            // parse children
            if (isset($item['children']) && is_array($item['children'])) {
                $result = $this->parseHierarchy(
                    $item['children'],
                    $formatItem,
                    $idKey,
                    $item[$idKey],
                    $result
                );
            }

            $rank++;
        }

        return $result;
    }
}

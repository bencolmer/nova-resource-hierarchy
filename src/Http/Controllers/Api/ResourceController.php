<?php

namespace BenColmer\NovaResourceHierarchy\Http\Controllers\Api;

use BenColmer\NovaResourceHierarchy\Http\Requests\HierarchyRequest;
use BenColmer\NovaResourceHierarchy\Http\Requests\UpdateHierarchyRequest;
use BenColmer\NovaResourceHierarchy\Traits\HandlesHierarchy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;

class ResourceController extends Controller
{
    use HandlesHierarchy;

    /**
     * Get the resource hierarchy.
     */
    public function index(HierarchyRequest $request): JsonResponse
    {
        $tool = $request->tool();
        $orderField = 'rank';
        $results = $request->newQuery()
            ->with($tool->resource::$with)
            ->orderBy($orderField)
            ->get();

        $total = count($results);
        $hierarchy = $this->buildHierarchy(
            $results,
            fn(Model $item) => $item->toArray(),
            'id',
            'parent_id'
        );

        return new JsonResponse([
            'total' => $total,
            'hierarchy' => $hierarchy,
        ]);
    }

    /**
     * Update the resource hierarchy.
     */
    public function update(UpdateHierarchyRequest $request): JsonResponse
    {
        $hierarchy = $request->validated('hierarchy');

        // parse hierarchy to a flat array
        $idField = 'id';
        $parentIdField = 'parent_id';
        $orderField = 'rank';
        $parsed = $this->parseHierarchy(
            $hierarchy,
            function($item, $parentId, $rank) use ($idField, $parentIdField, $orderField) {
                $formatted = [
                    $idField => $item[$idField],
                    $parentIdField => $parentId,
                ];
                if ($orderField) $formatted[$orderField] = $rank;

                return $formatted;
            }
        );

        // get models in the hierarchy
        $models = $request->newQuery()
            ->whereIn($idField, Arr::pluck($parsed, $idField))
            ->get();

        // update all parents and orders, ensuring we trigger model events
        foreach ($parsed as $item) {
            if (! isset($item[$idField])) continue;

            $model = $models->firstWhere($idField, '=', $item[$idField]);
            if (! $model) continue;

            $model->$parentIdField = $item[$parentIdField];
            if ($orderField) $model->$orderField = $item[$orderField];

            $model->save();
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }
}

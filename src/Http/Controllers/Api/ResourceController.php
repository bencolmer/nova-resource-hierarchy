<?php

namespace BenColmer\NovaResourceHierarchy\Http\Controllers\Api;

use BenColmer\NovaResourceHierarchy\Http\Requests\IndexHierarchyRequest;
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
    public function index(IndexHierarchyRequest $request): JsonResponse
    {
        $tool = $request->tool();
        $keyNames = $tool->getKeyNames();

        $results = $request->newQuery()
            ->with($tool->resource::$with ?? [])
            ->get();

        $total = 0;
        $hierarchy = $this->buildHierarchy(
            $results,
            function(Model $item) use ($tool, $keyNames, $request, &$total) {
                $resource = new $tool->resource($item);
                $total++;

                $title = isset($tool->formatItemTitle) && is_callable($tool->formatItemTitle) ?
                    call_user_func($tool->formatItemTitle, $item) :
                    $resource->title();

                // sort using the order key value if configured, otherwise use the title value
                $sortValue = isset($keyNames['orderKey']) && $keyNames['orderKey'] ?
                    $item->{$keyNames['orderKey']} :
                    $title;

                return [
                    $keyNames['idKey'] => $item->{$keyNames['idKey']},
                    'title' => $title,
                    'sortValue' => $sortValue,
                    'authorizedToView' => $resource->authorizedToView($request),
                    'authorizedToUpdate' => $resource->authorizedToUpdate($request),
                    'authorizedToDelete' => $resource->authorizedToDelete($request),
                ];
            },
            function(array $children) {
                // sort by the sort value
                usort($children, fn($a, $b) => $a['sortValue'] <=> $b['sortValue']);

                return array_values($children);
            },
            $keyNames['idKey'],
            $keyNames['parentKey']
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
        $keyNames = $request->tool()->getKeyNames();
        $idKey = $keyNames['idKey'];
        $parentKey = $keyNames['parentKey'];
        $orderKey = $keyNames['orderKey'];
        $parsed = $this->parseHierarchy(
            $hierarchy,
            fn($item, $parentId, $rank) => [
                $idKey => $item[$idKey] ?? null,
                $parentKey => $parentId,
                $orderKey => $rank,
            ],
            $idKey
        );

        // get models in the hierarchy
        $models = $request->newQuery()
            ->whereIn($idKey, Arr::pluck($parsed, $idKey))
            ->get();

        // update all parents and orders, ensuring we trigger model events
        foreach ($parsed as $item) {
            if (! isset($item[$idKey])) continue;

            $model = $models->firstWhere($idKey, '=', $item[$idKey]);
            if (! $model) continue;

            $model->$parentKey = $item[$parentKey];
            $model->$orderKey = $item[$orderKey];

            $model->save();
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }
}

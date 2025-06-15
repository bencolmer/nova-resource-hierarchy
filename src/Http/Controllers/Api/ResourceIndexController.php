<?php

namespace BenColmer\NovaResourceHierarchy\Http\Controllers\Api;

use BenColmer\NovaResourceHierarchy\Http\Requests\HierarchyRequest;
use BenColmer\NovaResourceHierarchy\Traits\HandlesHierarchy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ResourceIndexController extends Controller
{
    use HandlesHierarchy;

    /**
     * Get the resource hierarchy.
     */
    public function index(HierarchyRequest $request): JsonResponse
    {
        $tool = $request->tool();
        $results = $request->newQuery()
            ->with($tool->resource::$with)
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
}

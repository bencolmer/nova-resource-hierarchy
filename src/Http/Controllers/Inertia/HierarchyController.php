<?php

namespace BenColmer\NovaResourceHierarchy\Http\Controllers\Inertia;

use BenColmer\NovaResourceHierarchy\Http\Requests\IndexHierarchyRequest;
use Illuminate\Routing\Controller;

class HierarchyController extends Controller
{
    /**
     * Render the tool's index view.
     */
    public function index(IndexHierarchyRequest $request)
    {
        $tool = $request->tool();

        return inertia('ResourceHierarchy', array_merge($tool->getConfig(), [
            'authorizedToCreate' => $tool->resource->authorizedToCreate($request),
            'authorizedToReorderHierarchy' => $tool->authorizedToReorderHierarchy($request),
        ]));
    }
}

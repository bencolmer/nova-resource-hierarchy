<?php

namespace BenColmer\NovaResourceHierarchy\Http\Controllers\Inertia;

use BenColmer\NovaResourceHierarchy\Http\Requests\HierarchyRequest;
use Illuminate\Routing\Controller;

class HierarchyController extends Controller
{
    /**
     * Render the tool's index view.
     */
    public function index(HierarchyRequest $request)
    {
        $tool = $request->tool();

        return inertia('ResourceHierarchy', [
            'title' => $tool->title(),
            'resourceUriKey' => $tool->resource->uriKey(),
            'enableOrdering' => $tool->enableOrdering,
        ]);
    }
}

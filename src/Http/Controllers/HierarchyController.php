<?php

namespace BenColmer\NovaResourceHierarchy\Http\Controllers;

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
        abort_if(!$tool, 404);

        return inertia('ResourceHierarchy', [
            'title' => $tool->title(),
        ]);
    }
}

<?php

namespace BenColmer\NovaResourceHierarchy\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Laravel\Nova\Util;

/**
 * Adds additional hierarchy authorization methods to a resource.
 */
trait AuthorizesHierarchy
{
    /**
     * Determine if the current user can create new resources.
     */
    public function authorizedToReorderHierarchy(Request $request): bool
    {
        $resource = Util::resolveResourceOrModelForAuthorization($this);

        return static::authorizable()
            ? Gate::forUser(Nova::user($request))->check('reorderHierarchy', $resource)
            : true;
    }
}

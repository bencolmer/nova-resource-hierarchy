<?php

namespace BenColmer\NovaResourceHierarchy\Http\Requests;

use BenColmer\NovaResourceHierarchy\ResourceHierarchy;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class HierarchyRequest extends NovaRequest
{
    /**
     * Get the tool registered to the resource.
     */
    public function tool(): ?ResourceHierarchy
    {
        $requestResource = $this->resource();
        $tools = Nova::availableTools($this);
        foreach ($tools as $tool) {
            if (! $tool instanceOf ResourceHierarchy) continue;
            if ($tool->resource::class !== $requestResource) continue;

            return $tool;
        }

        return null;
    }
}

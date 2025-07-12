<?php

namespace BenColmer\NovaResourceHierarchy\Http\Requests;

class IndexHierarchyRequest extends HierarchyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! $this->tool()->authorizedToSee($this)) return false;

        return parent::authorize();
    }
}

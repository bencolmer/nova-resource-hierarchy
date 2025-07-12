<?php

namespace BenColmer\NovaResourceHierarchy\Http\Requests;

class UpdateHierarchyRequest extends HierarchyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $tool = $this->tool();
        $config = $tool->getConfig();

        // disallow hierarchy changes if we haven't set the order key
        if (! (isset($config['orderKey']) && $config['orderKey'])) {
            return false;
        }

        // disallow hierarchy changes if not enabled
        $enableReordering = (bool) ($config['enableReordering'] ?? false);
        if (! $enableReordering) return false;

        // disallow if not authorized to reorder
        if (! $tool->authorizedToReorderHierarchy($this)) return false;

        return parent::authorize();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hierarchy' => ['required', 'array'],
        ];
    }
}

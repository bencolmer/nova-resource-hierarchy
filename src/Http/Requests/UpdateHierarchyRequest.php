<?php

namespace BenColmer\NovaResourceHierarchy\Http\Requests;

class UpdateHierarchyRequest extends HierarchyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // disallow hierarchy changes if not enabled
        $config = $this->tool()->getConfig();
        $enableReordering = (bool) ($config['enableReordering'] ?? false);
        if (! $enableReordering) return false;

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

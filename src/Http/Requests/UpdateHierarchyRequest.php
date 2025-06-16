<?php

namespace BenColmer\NovaResourceHierarchy\Http\Requests;

class UpdateHierarchyRequest extends HierarchyRequest
{
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

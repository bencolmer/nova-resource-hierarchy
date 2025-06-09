<?php

namespace BenColmer\NovaResourceHierarchy;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class ResourceHierarchy extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     */
    public function boot(): void
    {
        Nova::mix('nova-resource-hierarchy', __DIR__.'/../dist/mix-manifest.json');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     */
    public function menu(Request $request): MenuSection
    {
        return MenuSection::make('Nova Resource Hierarchy')
            ->path('/nova-resource-hierarchy')
            ->icon('server');
    }
}

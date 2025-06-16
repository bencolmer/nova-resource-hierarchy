<?php

namespace BenColmer\NovaResourceHierarchy;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Tool;

class ResourceHierarchy extends Tool
{
    /**
     * The resource to be managed.
     */
    public Resource $resource;

    /**
     * Enable resource ordering.
     */
    public bool $enableOrdering = false;

    /**
     * The tool icon.
     */
    protected string $icon = 'square-3-stack-3d';

    public function __construct(string $resourceClass)
    {
        if (! is_subclass_of($resourceClass, Resource::class)) {
            throw new InvalidArgumentException(
                'The $resourceClass must be an instance of ' . Resource::class . '.'
            );
        }

        $this->resource = $resourceClass::newResource();
    }

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
        return MenuSection::make($this->title())
            ->path("/nova-resource-hierarchy/{$this->resource->uriKey()}")
            ->icon($this->icon);
    }

    /**
     * Get the tool's title.
     */
    public function title(): string
    {
        return $this->resource->singularLabel() . ' Hierarchy';
    }

    /**
     * Enable resource ordering.
     */
    public function enableOrdering(bool $enableOrdering = true): self
    {
        $this->enableOrdering = $enableOrdering;

        return $this;
    }
}

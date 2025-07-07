<?php

namespace BenColmer\NovaResourceHierarchy;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Tool;

/**
 * @method static static make(string $resourceClass)
 */
class ResourceHierarchy extends Tool
{
    /**
     * The resource to be managed.
     */
    public Resource $resource;

    /**
     * The formatter for hierarchy item display titles.
     *
     * @var callable|null
     */
    public mixed $formatItemTitle = null;

    /**
     * The custom hierarchy item key to column name mappings.
     *
     * @var array{
     *  idKey: string|null,
     *  parentKey: string|null,
     *  orderKey: string|null
     * }
     */
    protected array $customKeyMappings = [
        'idKey' => null,
        'parentKey' => null,
        'orderKey' => null,
    ];

    /**
     * Hide the tool navigation menu entry.
     */
    protected bool $hideMenu = false;

    /**
     * The tool menu title.
     */
    protected ?string $menuTitle = null;

    /**
     * The tool menu icon.
     */
    protected string $menuIcon = 'square-3-stack-3d';

    /**
     * The tool page title.
     */
    protected ?string $pageTitle = null;

    /**
     * The tool page description.
     */
    protected ?string $pageDescription = null;

    /**
     * The maximum hierarchy depth.
     */
    protected int $maxDepth = 10;

    /**
     * Enable resource reordering.
     */
    protected bool $enableReordering = false;

    /**
     * The available resource actions on the tool page.
     */
    protected array $actions = [
        'create',
        'view',
        'update',
        'delete',
    ];

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
    public function menu(Request $request): ?MenuSection
    {
        if ($this->hideMenu) return null;

        return MenuSection::make($this->getMenuTitle())
            ->path("/nova-resource-hierarchy/{$this->resource->uriKey()}")
            ->icon($this->menuIcon);
    }

    /**
     * Get the tool menu title.
     */
    protected function getMenuTitle(): string
    {
        // use a custom title if configured
        if (isset($this->menuTitle)) return $this->menuTitle;

        return $this->getPageTitle();
    }

    /**
     * Get the tool page title.
     */
    protected function getPageTitle(): string
    {
        // use a custom title if configured
        if (isset($this->pageTitle)) return $this->pageTitle;

        return $this->resource->singularLabel() . ' Hierarchy';
    }

    /**
     * Get the available tool configuration.
     */
    public function getConfig(): array
    {
        $keyNames = $this->getKeyNames();

        return array_merge($keyNames, [
            'pageTitle' => $this->getPageTitle(),
            'pageDescription' => $this->pageDescription,
            'resourceUriKey' => $this->resource->uriKey(),
            'maxDepth' => $this->maxDepth,
            'enableRtl' => Nova::rtlEnabled(),
            'enableReordering' => $this->enableReordering,
            'enableCreateAction' => in_array('create', $this->actions),
            'enableViewAction' => in_array('view', $this->actions),
            'enableUpdateAction' => in_array('update', $this->actions),
            'enableDeleteAction' => in_array('delete', $this->actions),
        ]);
    }

    /**
     * Get the model key names.
     *
     * @return array{
     *  idKey: string,
     *  parentKey: string,
     *  orderKey: string
     * }
     */
    public function getKeyNames(): array
    {
        return [
            'idKey' => $this->customKeyMappings['idKey'] ?? $this->resource->model()->getKeyName(),
            'parentKey' => $this->customKeyMappings['parentKey'] ?? 'parent_id',
            'orderKey' => $this->customKeyMappings['orderKey'] ?? null,
        ];
    }

    /**
     * Set the function for customizing the item title within the hierarchy list.
     *
     * @param callable(\Illuminate\Database\Eloquent\Model $item): \Stringable|string $formatItemTitle
     */
    public function formatItemTitle(callable $formatItemTitle): self
    {
        $this->formatItemTitle = $formatItemTitle;

        return $this;
    }

    /**
     * Set the key names for the resource.
     */
    public function keyNames(
        string $idKey = 'id',
        string $parentKey = 'parent_id',
        ?string $orderKey = null
    ): self {
        $this->customKeyMappings = [
            'idKey' => $idKey,
            'parentKey' => $parentKey,
            'orderKey' => $orderKey,
        ];

        return $this;
    }

    /**
     * Hide the tool navigation menu entry.
     */
    public function hideMenu(bool $hideMenu = true): self
    {
        $this->hideMenu = $hideMenu;

        return $this;
    }

    /**
     * Set the tool menu title.
     */
    public function menuTitle(string $menuTitle): self
    {
        $this->menuTitle = $menuTitle;

        return $this;
    }

    /**
     * Set the tool menu icon.
     */
    public function menuIcon(string $menuIcon): self
    {
        $this->menuIcon = $menuIcon;

        return $this;
    }

    /**
     * Set the tool page title.
     */
    public function pageTitle(string $pageTitle): self
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    /**
     * Set the tool page description.
     */
    public function pageDescription(string $pageDescription): self
    {
        $this->pageDescription = $pageDescription;

        return $this;
    }

    /**
     * Set the maximum hierarchy depth.
     */
    public function maxDepth(int $maxDepth): self
    {
        $this->maxDepth = $maxDepth > 0 ? $maxDepth : 0;

        return $this;
    }

    /**
     * Enable resource reordering.
     */
    public function enableReordering(bool $enableReordering = true): self
    {
        $this->enableReordering = $enableReordering;

        // default to using 'rank' as the order key if custom mappings haven't been set
        if (! isset($this->customKeyMappings['orderKey'])) {
            $this->customKeyMappings['orderKey'] = 'rank';
        }

        return $this;
    }

    /**
     * Set the available actions on the tool page.
     *
     * @param array<string> $actions
     */
    public function actions(array $actions): self
    {
        $this->actions = array_map(
            fn($action) => strtolower((string) $action),
            $actions
        );

        return $this;
    }
}

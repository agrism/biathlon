<?php

namespace App\Helpers\Generic;

use App\Helpers\InstanceTrait;
use App\ValueObjects\Helpers\Generic\FilterValueObject;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class GenericViewIndexHelper
{
    use InstanceTrait;

    protected ?string $title = null;
    protected array $tableHeaders = [];
    protected array $dataKeys = [];
    protected array $dataUrlKeys = [];
    protected bool $doNotUseLayout = false;
    protected bool $useHtmx = false;
    protected ?string $htmxTargetElement = null;

    /**
     * @var FilterValueObject[]
     */
    protected array $filters = [];

    protected ?LengthAwarePaginator $data = null;
    protected bool $showExportBtn = false;
    protected ?string $showRouteName = null;

    public function setShowExportBtn(bool $show = true): self
    {
        $this->showExportBtn = $show;
        return $this;
    }

    public function setData(LengthAwarePaginator $data): self
    {
        $this->data = $data;
        return $this;
    }


    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string[] $headers
     */
    public function setHeaders(array $headers = []): self
    {
        $this->tableHeaders = array_filter($headers);

        return $this;
    }

    /**
     * @param string|callable[] $keys
     */
    public function setDataKeys(array $keys = []): self
    {
        $this->dataKeys = $keys;

        return $this;
    }

    public function doNotUseLayout(): self
    {
        $this->doNotUseLayout = true;
        return $this;
    }

    public function useHtmx(): self
    {
        $this->useHtmx = true;
        return $this;
    }

    public function htmxTargetElement(string $targetElement): self
    {
        $this->htmxTargetElement = $targetElement;
        return $this;
    }


    /**
     * @param string[] $keys
     */
    public function setDataUrlKeys(array $keys = []): self
    {
        $this->dataUrlKeys = $keys;

        return $this;
    }

    public function setShowRouteName(string $routeName): self
    {
        $this->showRouteName = $routeName;
        return $this;
    }

    public function setFilters( array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function saveFilterDataAll(Request $request, array $keys): self
    {
        foreach ($keys as $key){
            $this->saveFilterData(request: $request, key: $key);
        }

        return $this;
    }

    public function saveFilterData(Request $request, string $key): self
    {
        if($request->has($key)){
            if($request->get('clear')){
                session()->put($key);
            } else {
                session()->put($key, $request->{$key});
            }

            session()->save();
        }

        return $this;
    }

    public function getFilterValue(string $key): string
    {
        return strval(session()->get($key));
    }

    public function showRouteName(): ?string
    {
        if (!Route::has($this->showRouteName)) {
            return null;
        }
        return $this->showRouteName;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    /**
     * @return string[]
     */
    public function headers(): array
    {
        return $this->tableHeaders;
    }

    public function data(): ?LengthAwarePaginator
    {
        return $this->data;
    }

    public function dataKeys(): array
    {
        return $this->dataKeys;
    }

    public function dataUrlKeys(): array
    {
        return $this->dataUrlKeys;
    }

    /**
     * @return FilterValueObject[]
     */
    public function filters(): array
    {
        return $this->filters;
    }

    public function isExportButtonVisible(): bool
    {
        return $this->showExportBtn;
    }

    public function render(): View
    {
        $blade = $this->doNotUseLayout ? 'generic.view.partials.index-content' : 'generic.view.index';

        view('generic.view.index');

        return view($blade, [
            'helper' => $this,
            'doNotUseLayout' => $this->doNotUseLayout,
            'useHtmx' => $this->useHtmx,
            'htmxTargetElement' => $this->htmxTargetElement,
            'subTitle' => $this->doNotUseLayout ? $this->title : null,
        ]);
    }

    public function dataButton(string $route, string $title, string $routeHttpMethod = 'get'): string
    {
        return '<button
            class="float-right bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-0 px-2 border border-blue-500 hover:border-transparent rounded"
            type="button"
            hx-'.$routeHttpMethod.'="'.$route.'"
            hx-target="#show"
        >'.$title.'</button>';
    }
}

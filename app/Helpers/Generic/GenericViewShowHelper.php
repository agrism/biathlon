<?php

namespace App\Helpers\Generic;

use App\Helpers\InstanceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class GenericViewShowHelper
{
    use InstanceTrait;

    protected ?string $title = null;
    protected array $tableHeaders = [];
    protected array $dataKeys = [];
    protected array $dataUrlKeys = [];

    protected ?Model $data = null;

    protected ?string $closeRouteName = null;


    public function setData(Model $data): self
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
        $this->tableHeaders = $headers;

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

    /**
     * @param string[] $keys
     */
    public function setDataUrlKeys(array $keys = []): self
    {
        $this->dataUrlKeys = $keys;

        return $this;
    }

    public function setCloseRouteName(string $routeName): self
    {
        $this->closeRouteName = $routeName;
        return $this;
    }

    public function title(): string
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

    public function data(): ?Model
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

    public function getCloseRouteName(): ?string
    {
        if (!Route::has($this->closeRouteName)) {
            return null;
        }
        return $this->closeRouteName;
    }

    public function render(): View
    {
        return view('generic.view.show', ['helper' => $this]);
    }
}

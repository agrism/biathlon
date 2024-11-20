<?php

namespace App\Helpers;

use App\ValueObjects\BreadCrumbValueObject;

class BreadCrumbHelper
{
    use InstanceTrait;

    protected string $storageKey = 'breadcrumb';

    /** @var BreadCrumbValueObject[] */
    protected array $objects = [];

    public function register(BreadCrumbValueObject $object): self
    {
        $this->read();

        $isFirstHome = !isset($this->objects['home']);
        if ($isFirstHome) {
            $this->objects = [];
            $this->objects['home'] = new BreadCrumbValueObject(name: 'home', route: route('home'), title: 'Home');
        }

        $new = [];
        foreach ($this->objects as $objName => $existingObj) {
            if ($objName === $object->name) {
                break;
            }
            $new[$objName] = $existingObj;
        }

        $new[$object->name] = $object;
        $this->objects = $new;

        return $this->store();
    }

    public function render(): string
    {
        $this->read();

        return implode(' <span style="font-size: 14px;font-weight: bold">&#10095;</span> ', array_map(
            fn($object) => '<a href="' . $object->route . '">' . $object->title . '</a>',
            $this->objects
        ));
    }

    protected function read(): self
    {
        $data = session($this->storageKey);
        $this->objects = is_array($data) ? $data : [];

        return $this;
    }

    protected function store(): self
    {
        session()->put($this->storageKey, $this->objects);

        return $this;
    }
}

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

        // Do not render breadcrumbs if only on the Home page
        if (count($this->objects) <= 1 && (isset($this->objects['home']) || empty($this->objects))) {
            return '';
        }

        $items = array_values($this->objects);
        $total = count($items);

        $html = '<nav class="flex items-center text-xs font-medium text-slate-500 py-2 px-3.5 bg-white/80 backdrop-blur-md border border-slate-200/80 rounded-2xl shadow-2xs w-fit mb-5" aria-label="Breadcrumb">';
        $html .= '<ol class="inline-flex items-center gap-1.5 flex-wrap">';

        foreach ($items as $index => $object) {
            $isLast = ($index === $total - 1);
            $isFirst = ($index === 0);

            // Clean title
            $title = str_replace(':', ': ', $object->title);
            $title = preg_replace('/\s+/', ' ', $title);

            if ($index > 0) {
                $html .= '<li class="text-slate-300 text-[10px] flex items-center select-none" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></li>';
            }

            if ($isLast) {
                $html .= '<li class="inline-flex items-center font-bold text-slate-900 bg-slate-100/90 px-2.5 py-0.5 rounded-lg tracking-tight" aria-current="page">';
                $html .= e($title);
                $html .= '</li>';
            } else {
                $html .= '<li class="inline-flex items-center">';
                $html .= '<a href="' . e($object->route) . '" class="inline-flex items-center gap-1.5 text-slate-500 hover:text-sky-600 transition-colors font-medium">';
                if ($isFirst) {
                    $html .= '<i class="fa-solid fa-house text-[11px] text-slate-400"></i>';
                }
                $html .= '<span>' . e($title) . '</span>';
                $html .= '</a>';
                $html .= '</li>';
            }
        }

        $html .= '</ol>';
        $html .= '</nav>';

        return $html;
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

<?php

namespace App\Helpers;

use App\Enums\SystemTenantEnum;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JsonStorageHelper
{
    use InstanceTrait;

    protected string $disk = 'local';
    protected string $file = 'scraping-phone-data.json';

    public function setStorageDisk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;
        return $this;
    }

    public function append(array $newData): self
    {
        $data = $this->read();
        $data[] = $newData;
        Storage::disk($this->disk)->put($this->file, json_encode($data));
        return $this;
    }

    public function prepend(array $newData): self
    {
        $data = $this->read();
        array_unshift($data, $newData);
        Storage::disk($this->disk)->put($this->file, json_encode($data));
        return $this;
    }

    public function get(int $page = 1, int $perPage = 100): LengthAwarePaginator
    {
        $data = $this->read();

//        return array_slice($data, ($page -1) * $perPage, $perPage);

        $collection = Collection::make($data)->reverse();

        $currentPageItems = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentPageItems, // Only the items for the current page
            $collection->count(), // Total number of items
            $perPage, // Items per page
            $page, // Current page
            ['path' => request()->url(), 'query' => request()->query()] // Pagination URL options
        );
    }

    private function read(): array
    {
        $data = Storage::disk($this->disk)->get($this->file);
        $data = json_decode($data, true);
        return is_array($data) ? $data : [];
    }
}

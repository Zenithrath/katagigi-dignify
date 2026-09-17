<?php

namespace App\Services\General;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class CategoryService
{
    public function readAllCategories(): Collection|Exception
    {
        try {
            return DB::table('categories')
                ->join('services', 'categories.id', '=', 'services.category_id', 'left')
                ->select(['categories.id', 'categories.name', 'categories.code', DB::raw('count(services.id) as services')])
                ->groupBy('categories.id')
                ->get();
        } catch (Exception $err) {
            dd($err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function readCategoryById(string $id): object
    {
        try {
            return DB::table('categories')->where('id', $id)->first();
        } catch (Exception $err) {
            return new Exception($err->getMessage(), 500);
        }
    }

    public function createCategory(string $name, string $code): Exception|bool
    {
        try {
            return DB::table('categories')->insert([
                'id' => Uuid::uuid4(),
                'name' => $name,
                'code' => $code,
            ]);
        } catch (Exception $err) {
            return new Exception($err->getMessage(), 500);
        }
    }

    public function updateCategory(string $id, string $name, string $code): Exception|bool
    {
        try {
            return DB::table('categories')->where('id', $id)->update([
                'name' => $name,
                'code' => $code,
            ]);
        } catch (Exception $err) {
            return new Exception($err->getMessage(), 500);
        }
    }

    public function deleteCategory(string $id): int|Exception
    {
        try {
            return DB::table('categories')->where('id', $id)->delete();
        } catch (Exception $err) {
            return new Exception($err->getMessage(), 500);
        }
    }
}

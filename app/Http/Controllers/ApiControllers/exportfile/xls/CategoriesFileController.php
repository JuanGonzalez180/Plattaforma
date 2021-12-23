<?php

namespace App\Http\Controllers\ApiControllers\exportfile\xls;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Exports\Sheets\CategoriesParentSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Http\Controllers\ApiControllers\ApiController;

class CategoriesFileController implements WithMultipleSheets
{
    use Exportable;

    public function getCategoryParents()
    {
        return Category::whereNull('parent_id')
            ->orderBy('name')
            ->pluck('id');
    }

    public function export()
    {
        return $this;
    }   

    public function sheets(): array
    {
        return collect($this->getCategoryParents())->map(function($id){
            return new CategoriesParentSheet($id);
        })->toArray();
    }
}

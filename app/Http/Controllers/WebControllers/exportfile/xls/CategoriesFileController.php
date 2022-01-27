<?php

namespace App\Http\Controllers\WebControllers\exportfile\xls;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Exports\Sheets\CategoriesParentSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

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

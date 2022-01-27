<?php

namespace App\Exports\Sheets;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoriesParentSheet implements FromQuery, WithTitle, WithHeadings 
{
    public function __construct($parent_id)
    {
        $this->parent_id = $parent_id;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function query()
    {
        $childs = DB::select('call get_child_type_categoty("' . $this->parent_id . '")');

        $child_array = [];
        foreach ($childs as $value) {
            $child_array[] = $value->id;
        }

        return Category::select('id','description','parent_id')->whereIn('id', $child_array);
    }

    public function title(): string
    {
        return Category::find($this->parent_id)->name;
    }

    public function headings(): array {
        return [
            "Código","Categorías","Padre"
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->setHeight(array(
                    1     =>  50,
                    2     =>  25
                ));
            },
        ];
    }
}

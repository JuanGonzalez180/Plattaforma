<?php

namespace App\Http\Controllers\WebControllers\remark;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Company;
use App\Models\Remarks;
use App\Models\Tenders;
use App\Models\Products;
use App\Models\Projects;
use App\Models\TendersCompanies;
use App\Models\QuotesCompanies;

class RemarkController extends Controller
{
    public function index($class, $id)
    {
        list ($header, $class) = $this->getClassInfo($class, $id);

        $remarks = Remarks::where('remarksable_type', $class)
            ->where('remarksable_id', $id)
            ->orderBy('updated_at','asc')
            ->get();

        return view('remark.index', compact(['remarks','header']));
    }

    public function getClassInfo($class, $id)
    {
        if ($class == 'company') {
            $class      = Company::class;
            $sql        = Company::find($id);
            $name       = $sql->name;
            $header     = "<p class='font-weight-light'><b>Compañia</b> | ".$sql->name."</p>";
        } elseif ($class == 'product') {
            $class      = Products::class;
            $sql        = Products::find($id);
            $header     = "<p class='font-weight-light'><b>Producto</b> | ".$sql->name."</p>";
        } elseif ($class == 'project') {
            $class      = Projects::class;
            $sql        = Projects::find($id);
            $header     = "<p class='font-weight-light'><b>Proyecto</b> | ".$sql->name."</p>";
        } elseif ($class == 'tender') {
            $class      = Tenders::class;
            $sql        = Tenders::find($id);
            $header     = "<p class='font-weight-light'><b>Licitación</b> | ".$sql->name."</p>";
        } elseif ($class == 'tendercompany') {
            $class      = TendersCompanies::class;
            $sql        = TendersCompanies::find($id);
            $header     = "<p class='font-weight-light'><b>Compañia licitante</b> | ".$sql->company->name."<br><b>Licitación</b> | ".$sql->tender->name."</p>";
        } elseif ($class == 'quotecompany') {
            $class      = QuotesCompanies::class;
            $sql        = QuotesCompanies::find($id);
            $header     = "<p class='font-weight-light'><b>Compañia cotizante</b> | ".$sql->company->name."<br><b>Cotización</b> | ".$sql->quote->name."</p>";
        };

        return array($header, $class);
    }
}

<?php

namespace App\Http\Controllers\WebControllers\company\providers;

use DataTables;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyProvidersController extends Controller
{
    public function index()
    {
        $status = $this->getStatus();

        return view('company.providers.index', compact('status'));
    }

    public function getStatus()
    {
        $status[Company::COMPANY_CREATED]   = 'Nueva';
        $status[Company::COMPANY_APPROVED]  = Company::COMPANY_APPROVED;
        $status[Company::COMPANY_REJECTED]  = Company::COMPANY_REJECTED;
        $status[Company::COMPANY_BANNED]    = Company::COMPANY_BANNED;

        return $status;
    }

    public function getCountStatus()
    {
        $status_count   = [];

        $status = $this->getStatus();

        foreach ($status as $key => $value) {
            $status_count[] = $this->companyStatusCount($key);
        }
        $status_count[] = $this->companyStatusCount('all');

        return response()->json($status_count, 200);
    }

    public function getCompany(Request $request)
    {
        $status     = $request->status;
        $size       = $request->size;

        $companies  = Company::select('companies.*');

        if ($status != 'all')
            $companies  = $companies->where('companies.status', '=', $status);

        $companies  = $companies->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.name', '=', 'oferta')
            ->orderBy('companies.updated_at', 'desc');

        $companies  = $companies->get();

        foreach ($companies as $company) {
            $company['size_company'] = $company->fileSizeTotal();
        }


        $companies = collect($companies)->sortBy([['size_company', $size]]);

        return DataTables::of($companies)
            ->addColumn('entity', function (Company $value) {
                return $value->type_entity->name;
            })
            ->addColumn('action', function (Company $value) {
                $action = '<div class="btn-group" role="group" aria-label="Button group with nested dropdown">';
                $action = $action . '<a type="button" href="' . route('companies.show', $value->id) . '" class="btn btn-success btn-sm"><i class="far fa-eye"></i></a>';
                $action = $action . '<div class="btn-group" role="group">';
                $action = $action . '<button id="btnGroupDrop1" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="fas fa-ellipsis-v" title="Ver" aria-hidden="true"></span></button>';
                $action = $action . '<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">';
                //Productos
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('product-company-id', $value->id) . '">Productos <span class="badge badge-primary">' . count($value->products) . '</span></a>';
                //Marcas
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('company-brand-id', $value->id) . '">Marcas <span class="badge badge-primary">' . count($value->brands) . '</span></a>';
                //Equipo
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('teams-company-id', $value->id) . '">Equipo <span class="badge badge-primary">' . count($value->teams) . '</span></a>';
                //Publicaciones
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('blog.company.id', $value->id) . '">Publicaciones <span class="badge badge-primary">' . count($value->blogs) . '</span></a>';
                //Portafolio
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('portfolio.company.id', $value->id) . '">Portafolio <span class="badge badge-primary">' . count($value->portfolios) . '</span></a>';
                //  Reseñas
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('remark.class.id', ['company', $value->id]) . '">Reseñas <span class="badge badge-primary">' . count($value->remarks) . '</span></a>';
                $action = $action . '</div>';
                $action = $action . '</div>';
                $action = $action . '</div>';

                return $action;
            })
            ->addColumn('date', function (Company $value) {
                return $value->created_at->toFormattedDateString();
            })
            ->editColumn('size_company', function (Company $value) {
                return "<span class='badge badge-primary' style='width: 100%;'>" . $this->formatSize($value->size_company) . "</span>";
            })
            ->editColumn('status', function (Company $value) {

                switch ($value->status) {
                    case Company::COMPANY_CREATED:
                        $status = '<button type="button" class="btn btn-info btn-sm item-full-width" onclick="editStatusCreated(' . $value->id . ')"><i class="fas fa-plus"></i>&nbsp;Nueva</button>';
                        break;
                    case Company::COMPANY_APPROVED:
                        $status = '<button type="button" class="btn btn-success btn-sm item-full-width"><i class="fas fa-check"></i>&nbsp;' . Company::COMPANY_APPROVED . '</button>';
                        break;
                    case Company::COMPANY_REJECTED:
                        $status = '<button type="button" class="btn btn-danger btn-sm item-full-width" onclick="editStatusRejected(' . $value->id . ')"><i class="fas fa-times"></i>&nbsp;' . Company::COMPANY_REJECTED . '</button>';
                        break;
                    default:
                        $status = 'Sin definir';
                }

                return $status;
            })
            ->rawColumns(['entity', 'action', 'size_company', 'status', 'date'])
            ->toJson();
    }


    public function companyStatusCountArray($status)
    {
        $status_count   = [];

        $status_count[] = $this->companyStatusCount('all');
        foreach ($status as $value) {
            $status_count[] = $this->companyStatusCount($value);
        }

        return $status_count;
    }

    public function companyStatusCount($status)
    {
        $companies  = Company::select('companies.*');

        if ($status != 'all')
            $companies  = $companies->where('companies.status', '=', $status);

        $companies  = $companies->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.name', '=', 'Oferta')
            ->orderBy('companies.updated_at', 'desc');

        $companies  = $companies->count();

        return $companies;
    }

    public function formatSize($file_size)
    {
        if (round(($file_size / pow(1024, 2)), 3) < '1') {
            $file = round(($file_size * 0.00097426203), 1) . ' KB';
        } else if (round(($file_size / pow(1024, 2)), 1) < '1024') {
            $file = round(($file_size / pow(1024, 2)), 1) . ' MB';
        } else if (round(($file_size / pow(1024, 2)), 1) >= '1024') {
            $file = round(($file_size / pow(1024, 2)), 1) . ' GB';
        }

        return $file;
    }
}

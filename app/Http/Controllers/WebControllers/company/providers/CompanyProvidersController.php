<?php

namespace App\Http\Controllers\WebControllers\company\providers;

use DataTables;
use Carbon\Carbon;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyProvidersController extends Controller
{
    public function index()
    {
        $status = $this->getStatus();

        $order['CREATED_DESC']      =   'Registro mas reciente';
        $order['CREATED_ASC']       =   'Registro mas antiguo';
        $order['SIZE_DESC']         =   'Mayor tamaño de archivos';
        $order['SIZE_ASC']          =   'Menor tamaño de archivos'; 
        $order['ALPHABETICAL_DESC'] =   'Alfabetico de A-Z';
        $order['ALPHABETICAL_ASC']  =   'Alfabetico de Z-A'; 

        return view('company.providers.index', compact(['status','order']));
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
        $order       = $request->size;

        $companies  = Company::select('companies.*');

        if ($status != 'all')
            $companies  = $companies->where('companies.status', '=', $status);

        $companies  = $companies->join('types_entities', 'types_entities.id', '=', 'companies.type_entity_id')
            ->join('types', 'types.id', '=', 'types_entities.type_id')
            ->where('types.name', '=', 'oferta')
            ->orderBy('companies.updated_at', 'desc');

        $companies  = $companies->get();


        $companies->map(function ($item, $key) {
            return $item->size_company = $item->fileSizeTotal();
        });

        if($order == 'CREATED_DESC')
        {
            $companies = collect($companies)->sortBy([['created_at', 'desc']]);
        }
        else if($order == 'CREATED_ASC')
        {
            $companies = collect($companies)->sortBy([['created_at', 'asc']]);
        }
        else if($order == 'SIZE_DESC')
        {
            $companies = collect($companies)->sortBy([['size_company', 'desc']]);
        }
        else if($order == 'SIZE_ASC')
        {
            $companies = collect($companies)->sortBy([['size_company', 'asc']]);
        }
        else if($order == 'ALPHABETICAL_DESC')
        {
            $companies = collect($companies)->sortBy([['name', 'asc']]);
        }
        else if($order == 'ALPHABETICAL_ASC')
        {
            $companies = collect($companies)->sortBy([['name', 'desc']]);
        }

        Carbon::setLocale(config('app.locale'));
        setlocale(LC_ALL, 'es_PA', 'es');

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
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('product-company-id', $value->id) . '">Productos &nbsp;<span class="badge badge-primary">' . count($value->products) . '</span></a>';
                //Marcas
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('company-brand-id', $value->id) . '">Marcas &nbsp;<span class="badge badge-primary">' . count($value->brands) . '</span></a>';
                //Equipo
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('teams-company-id', $value->id) . '">Equipo &nbsp;<span class="badge badge-primary">' . count($value->teams) . '</span></a>';
                //Publicaciones
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('blog.company.id', $value->id) . '">Publicaciones &nbsp;<span class="badge badge-primary">' . count($value->blogs) . '</span></a>';
                //Portafolio
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('portfolio.company.id', $value->id) . '">Portafolio &nbsp;<span class="badge badge-primary">' . count($value->portfolios) . '</span></a>';
                //Catalogo
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('catalog.company.id', $value->id) . '">Catalogos &nbsp;<span class="badge badge-primary">' . count($value->catalogs) . '</span></a>';
                //  Reseñas
                $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('remark.class.id', ['company', $value->id]) . '">Reseñas &nbsp;<span class="badge badge-primary">' . count($value->remarks) . '</span></a>';
                //Eliminar Compañia
                // if ($value->status == Company::COMPANY_BANNED) {
                //     $action = $action . '<div class="dropdown-divider"></div>';
                //     $action = $action . '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' . route('company-delete', $value->id) . '"><p class="text-danger"><i class="fas fa-trash-alt"></i>&nbsp;Eliminar Compañia</p></a>';
                // }
                $action = $action . '</div>';
                $action = $action . '</div>';
                $action = $action . '</div>';

                return $action;
            })
            ->addColumn('date', function (Company $value) {
                return $value->created_at->formatLocalized('%d %b %Y %H:%M %p')."<br>"."<span class='badge badge-light'>".$value->created_at->diffForHumans()."</span>";
            })
            ->editColumn('name', function (Company $value) {
                return $value->name . "<br><span class='badge badge-secondary'><i class='far fa-envelope'></i></span> <b>" . $value->user->email . "</b>";
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
                        $status = '<button type="button" class="btn btn-success btn-sm item-full-width" onclick="editStatusLock(' . $value->id . ')"><i class="fas fa-check"></i>&nbsp;' . Company::COMPANY_APPROVED . '</button>';
                        break;
                    case Company::COMPANY_REJECTED:
                        $status = '<button type="button" class="btn btn-danger btn-sm item-full-width" onclick="editStatusRejected(' . $value->id . ')"><i class="fas fa-times"></i>&nbsp;' . Company::COMPANY_REJECTED . '</button>';
                        break;
                    case Company::COMPANY_BANNED:
                        $status = '<button type="button" class="btn btn-dark btn-sm item-full-width" onclick="editStatusUnlock(' . $value->id . ')"><i class="fas fa-ban"></i>&nbsp;' . Company::COMPANY_BANNED . '</button>';
                        break;
                    default:
                        $status = 'Sin definir';
                }
                return $status;
            })
            ->rawColumns(['name', 'entity', 'action', 'size_company', 'status', 'date'])
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

<?php

namespace App\Http\Controllers\ApiControllers\company;

use JWTAuth;
use App\Models\Tags;
use App\Models\Blog;
use App\Models\Team;
use App\Models\User;
use App\Models\Image;
use App\Models\Company;
use App\Models\Tenders;
use App\Models\Remarks;
use App\Models\Products;
use App\Models\Projects;
use App\Models\Catalogs;
use App\Models\Portfolio;
use App\Models\TypesEntity;
use Illuminate\Http\Request;
use App\Mail\CreatedAccount;
use App\Models\TendersVersions;
use App\Models\TendersCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str as Str;
use Illuminate\Support\Facades\Mail;
use App\Transformers\UserTransformer;
use Illuminate\Support\Facades\Storage;
use App\Transformers\TendersTransformer;
use TaylorNetwork\UsernameGenerator\Generator;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyController extends ApiController
{
    public $routeFile       = 'public/';
    public $routeCompanies  = 'images/company/';

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    /**
     * Handle the incoming request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            // Country https://restcountries.eu/
            'country_code'      => 'required',
            'country_backend'   => 'required',
            'user_name'         => 'required',
            'lastname'          => 'nullable',
            'email'             => 'required|email|unique:users',
            'name'              => 'required',
            'nit'               => 'nullable',
            'password'          => 'required|min:6|confirmed',
            'terms'             => 'required',
            'type_entity_id'    => 'required',
            // 'web' => 'nullable|url',
            'phone'             => 'required'
        ];

        $this->validate($request, $rules);

        // Traer los tipos registrados
        $type = TypesEntity::find($request['type_entity_id']);

        //Verificar que este registrado y adicionar otras validaciones
        $errors = [];

        //Verificar si existen errores
        if (!empty($errors))
            return $this->errorResponse($errors, 500);

        // Generar Username y Validar que no exista en BD
        // Armar username Parametro $userFields['username']
        $generator = new Generator();
        $userFields['username'] = false;
        $usernameCreated = $request['name'];
        $i = 0;
        while (!$userFields['username']) {
            // 1ra vez
            $username = $generator->generate($usernameCreated);
            $userExist = DB::table('users')->where('username', $username)->first();
            if ($username && !$userExist) {
                $userFields['username'] = $username;
            } elseif ($i == 0) {
                // 2ra vez
                $usernameCreated = $generator->usingEmail()->generate($request['email']);
            } else {
                $usernameCreated = $generator->generate($request['name'] . uniqid());
            }
            $i++;
        }

        $userFields['name']     = strtolower($request['user_name']);
        $userFields['lastname'] = strtolower($request['lastname']);
        $userFields['email']    = strtolower($request['email']);
        $userFields['password'] = bcrypt($request->password);
        $userFields['verified'] = User::USER_NO_VERIFIED;
        // $userFields['validated'] = User::USER_NO_VALIDATED;
        $userFields['verification_token'] = User::generateVerificationToken();
        $userFields['admin'] = User::USER_REGULAR;

        // Iniciar Transacción
        DB::beginTransaction();
        $errorCompany = false;
        try {
            // Crear Usuario
            $user = User::create($userFields);

            $companyFields = [
                'name'              => $request['name'],
                'type_entity_id'    => $request['type_entity_id'],
                'nit'               => $request['nit'],
                'country_code'      => $request['country_code'],
                'web'               => $request['web'],
                'user_id'           => $user['id'],
                'phone'             => $request['phone'],
                'slug'              => Str::slug($request['name']),
            ];

            try {
                // Crear la compañia
                $company = Company::create($companyFields);

                // Ingresar País en una Compañía
                $company->countries()->attach($request['country_backend']);
            } catch (\Throwable $th) {
                // Si existe algún error al generar la compañía
                $errorCompany = true;
                DB::rollBack();

                $companyError = ['company' => 'Error, no se ha podido crear la compañia'];

                if ($th->getCode() == 23000 && $th->errorInfo[1] == 1062) {
                    $companyError = ['company' => 'Error, ya se encuentra registrada la compañia'];
                }

                return $this->errorResponse($companyError, 500);
            }
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorCompany = true;
            DB::rollBack();
            $userError = ['user' => 'Error, no se ha podido crear el usuario o ya existe el nombre de la empresa'];
            return $this->errorResponse($userError, 500);
        }

        if (!$errorCompany) {
            DB::commit();
            try {
                // Generar el correo de Verificación.
                Mail::to(trim($user->email))->send(new CreatedAccount($company, $user, $type['type']['slug']));
            } catch (\Throwable $th) {
            }
        }

        $user['status_company'] = $this->statusCompanyUser($user);

        // Aquí debe devolver el usuario con el TOKEN.
        return $this->showOne($user, 201);
    }

    public function statusCompanyUser($user)
    {
        if ($user->isAdminFrontEnd()) {
            $company = $user->company[0];
        } elseif ($user->team) {
            $company = $user->team->company;
        }

        return $company->companyStatusPayment();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $user = $this->validateUser();
        // Compañía del usuario que está logueado
        $userCompanyId = $user->companyId();

        $company = Company::where('slug', $slug)->first();

        if (!$company) {
            $companyError = ['company' => 'Error, no se ha encontrado ninguna compañia'];
            return $this->errorResponse($companyError, 500);
        }

        $userTransform = new UserTransformer();
        $tendersTransform = new TendersTransformer();

        // Banner
        $company->coverpage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();


        // 8 Integrantes del equipo
        $company->team = Team::where('company_id', $company->id)
            ->where('status', Team::TEAM_APPROVED)
            ->orderBy('id', 'desc')
            ->skip(0)->take(8)
            ->get();

        if ($userCompanyId == $company->id) {

            // Traer Proyectos últimos 6
            $company->projects = $company->projects
                ->sortBy([['updated_at', 'desc']])
                ->skip(0)->take(6);

            // Traer Licitaciones últimas 6
            $company->tenders = Tenders::select('tenders.*', 'comp.status AS company_status')
                ->where('tenders.company_id', $company->id)
                ->join('projects', 'projects.id', '=', 'tenders.project_id')
                // ->where('projects.visible', Projects::PROJECTS_VISIBLE)
                ->leftjoin('tenders_companies AS comp', function ($join) use ($userCompanyId) {
                    $join->on('tenders.id', '=', 'comp.tender_id');
                    $join->where('comp.company_id', '=', $userCompanyId);
                })
                ->orderBy('tenders.updated_at', 'desc')
                ->skip(0)->take(6)
                ->get();

            // Traer Productos últimos 6
            $company->products = $company->products
                // ->where('status', Products::PRODUCT_PUBLISH)
                ->sortBy([['updated_at', 'desc']])
                ->skip(0)->take(6);

            // Traer Publicaciones últimas 6
            $company->blogs = $company->blogs
                // ->where('status', Blog::BLOG_PUBLISH)
                ->sortBy([['updated_at', 'desc']])
                ->skip(0)->take(6);

            // Traer Portafolios últimos 8
            $company->portfolios = $company->portfolios
                // ->where('status', Portfolio::PORTFOLIO_PUBLISH)
                ->sortBy([['updated_at', 'desc']])
                ->skip(0)->take(8);

            // Traer Catalogos últimos 8
            $company->catalogs = $company->catalogs
                // ->where('status', Catalogs::CATALOG_PUBLISH)
                ->sortBy([['updated_at', 'desc']])
                ->skip(0)->take(8);
        } else {
            // Traer Proyectos últimos 6
            $company->projects = $company->projects
                ->where('visible', Projects::PROJECTS_VISIBLE)
                ->sortBy([['updated_at', 'desc']])
                ->skip(0)->take(6);

            // Traer Licitaciones últimas 6
            $company->tenders = $this->getTenderCompany($company->id, $userCompanyId);

            // Traer Productos últimos 6
            $company->products = $company->products
                ->where('status', Products::PRODUCT_PUBLISH)
                ->sortBy([['updated_at', 'desc']])
                ->skip(0)->take(6);

            // Traer Publicaciones últimas 6
            $company->blogs = $company->blogs
                ->where('status', Blog::BLOG_PUBLISH)
                ->sortBy([['updated_at', 'desc']])
                ->skip(0)->take(6);

            // Traer Portafolios últimos 8
            $company->portfolios = $company->portfolios
                ->where('status', Portfolio::PORTFOLIO_PUBLISH)
                ->sortBy([['updated_at', 'desc']])
                ->skip(0)->take(8);

            // Traer Catalogos últimos 8
            $company->catalogs = $company->catalogs
                ->where('status', Catalogs::CATALOG_PUBLISH)
                ->sortBy([['updated_at', 'desc']])
                ->skip(0)->take(8);
        }

        // Recorre los proyectos
        foreach ($company->projects as $key => $project) {
            $user = $userTransform->transform($project->user);
            unset($project->user);
            $project->user = $user;
            $project->image;
        }

        // Recorre las licitaciones
        $tenders = [];
        foreach ($company->tenders as $key => $tender) {
            $user = $tender->user;
            unset($tender->user);
            $tender->user = $user;

            $version = $tender->tendersVersionLastPublish();
            if ($version) {
                $tender->tags = $version->tags;
            }
            $tender->project;

            $tenders[] = $tendersTransform->transform($tender);
        }
        unset($company->tenders);
        $company->tenders = $tenders;

        //recorre los productos
        foreach ($company->products as $key => $product) {
            $user = $userTransform->transform($product->user);
            unset($product->user);
            $product->user = $user;
            $product->tags = Tags::where('tagsable_id',$product->id)
                ->where('tagsable_type', Products::class)
                ->take(6)
                ->get();
            $product->image;
        }

        //recorre las publicaciones
        foreach ($company->blogs as $key => $blog) {
            $user = $userTransform->transform($blog->user);
            unset($blog->user);
            $blog->user = $user;
            $blog->image;
            $blog->files;
        }

        //recorre los portafolios
        foreach ($company->portfolios as $key => $portfolio) {
            $portfolio->image;
            $portfolio->files;
        }

        //recorre los catalogos
        foreach ($company->catalogs as $key => $catalog) {
            $catalog->image;
            $catalog->files;
            $catalog->tags = $catalog->tagsLimit;
        }

        // Calificaciones.
        $company->remarks = Remarks::select('remarks.*')
            ->where('remarks.company_id', $company->id)
            ->orderBy('id', 'desc')
            ->skip(0)->take(8)
            ->get();
        foreach ($company->remarks as $key => $remark) {
            $user = $userTransform->transform($remark->user);
            unset($remark->user);
            $remark->user = $user;
        }

        return $this->showOneTransform($company, 200);
    }

    public function getTenderCompany($company_id, $user_company_id)
    {
        // $tendersCompanies = Tenders::select('tenders.*', 'comp.status AS company_status')
        //         ->where('tenders.company_id', $company_id)
        //         ->join('projects', 'projects.id', '=', 'tenders.project_id')
        //         ->where('projects.visible', Projects::PROJECTS_VISIBLE)
        //         ->leftjoin('tenders_companies AS comp', function ($join) use ($user_company_id) {
        //             $join->on('tenders.id', '=', 'comp.tender_id');
        //             $join->where('comp.company_id', '=', $user_company_id);
        //         })
        //         ->orderBy('tenders.updated_at', 'desc')
        //         ->skip(0)->take(6)
        //         ->get();

        // return $tendersCompanies;

        $tendersCompanies = Tenders::select('tenders.*', 'comp.status AS company_status')
                ->where('tenders.company_id', $company_id)
                ->join('projects', 'projects.id', '=', 'tenders.project_id')
                ->where('projects.visible', Projects::PROJECTS_VISIBLE)
                ->leftjoin('tenders_companies AS comp', function ($join) use ($user_company_id) {
                    $join->on('tenders.id', '=', 'comp.tender_id');
                    $join->where('comp.company_id', '=', $user_company_id);
                })
                ->orderBy('tenders.updated_at', 'desc')
                ->pluck('tenders.id');

        $tendersParticipate = $this->getTenderParticipate();

        $tenderArray = array_intersect(json_decode($tendersCompanies), json_decode($tendersParticipate));

        $tenders = Tenders::select('tenders.*', 'comp.status AS company_status')
                ->where('tenders.company_id', $company_id)
                ->whereIn('tenders.id', $tenderArray)
                ->join('projects', 'projects.id', '=', 'tenders.project_id')
                ->where('projects.visible', Projects::PROJECTS_VISIBLE)
                ->leftjoin('tenders_companies AS comp', function ($join) use ($user_company_id) {
                    $join->on('tenders.id', '=', 'comp.tender_id');
                    $join->where('comp.company_id', '=', $user_company_id);
                })
                ->where('comp.status','=', TendersCompanies::STATUS_PARTICIPATING)
                ->orderBy('tenders.updated_at', 'desc')
                ->skip(0)->take(6)
                ->get();

        return $tenders;
    }

    public function getTenderParticipate()
    {
        $user = $this->validateUser();
        // Compañía del usuario que está logueado
        $userCompanyId = $user->companyId();

        return TendersCompanies::where('company_id', $userCompanyId)
            ->whereIn('tender_id', $this->getTendersPublish())
            ->pluck('tender_id');
    }

    public function getTendersPublish()
    {
        return DB::table('tenders_versions as a')
            ->select(DB::raw('max(a.created_at), a.tenders_id'))
            ->where('a.status', TendersVersions::LICITACION_PUBLISH)
            ->where((function ($query) {
                $query->select(
                    DB::raw("COUNT(*) from `tenders_versions` as `b` 
                    where `b`.`status` != '" . TendersVersions::LICITACION_PUBLISH . "'  
                    and `b`.`tenders_id` = a.tenders_id")
                );
            }), '=', 0)
            ->groupBy('a.tenders_id')
            ->pluck('a.tenders_id');
    }

    public function detail($slug)
    {
        $company = Company::where('slug', $slug)->first();
        if (!$company) {
            $companyError = ['company' => 'Error, no se ha encontrado ninguna compañia'];
            return $this->errorResponse($companyError, 500);
        }

        return $this->showOneTransformNormal($company, 200);
    }

    public function statusCompany()
    {
        $user = $this->validateUser();
        if ($user->isAdminFrontEnd()) {
            $company = $user->company[0];
        } elseif ($user->team) {
            $company = $user->team->company;
        }

        if ($company->companyStatusPayment()) {
            return $this->showOneData(['message' => 'true', 'code' => 200], 200);
        } else {
            return $this->showOneData(['message' => 'false', 'code' => 200], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function updateItem(Request $request)
    {
        $user       = $this->validateUser();
        $company    = Company::findOrFail($user->company[0]->id);

        if (isset($request->description)) {
            DB::beginTransaction();
            try {
                $company['description'] = $request->description;
                $company->save();
            } catch (\Exception $e) {
                DB::rollBack();
            }
            DB::commit();
        }

        if (isset($request->image)) {

            $png_url = "company-" . time() . ".jpg";
            $img = $request->image;
            $img = substr($img, strpos($img, ",") + 1);
            $data = base64_decode($img);
            $routeFile = $this->routeCompanies . $company->id . '/' . $png_url;

            Storage::disk('local')->put($this->routeFile . $routeFile, $data);

            if ($company->image) {
                Storage::disk('local')->delete($this->routeFile . $company->image->url);
                $company->image()->update(['url' => $routeFile]);
            } else {
                $company->image()->create(['url' => $routeFile]);
            }
        }

        if (isset($request->imageCoverPage)) {
            $png_url = "company-coverpage-" . time() . ".jpg";
            $img = $request->imageCoverPage;
            $img = substr($img, strpos($img, ",") + 1);
            $data = base64_decode($img);

            $routeFile = 'images/company/' . $company->id . '/' . $png_url;
            Storage::disk('local')->put($this->routeFile . $routeFile, $data);

            $imageCoverPage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
            if (!$imageCoverPage) {
                $imageCoverPage = Image::create(['url' => $routeFile, 'imageable_id' => $company->id, 'imageable_type' => 'App\Models\Company\CoverPage']);
            } else {
                Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->update(['url' => $routeFile]);
                Storage::disk('local')->delete($this->routeFile . $imageCoverPage->url);
            }
        }

        if (isset($request->latitud) && isset($request->longitud)) {
            if (!$company->address) {
                $company->address()->create([
                    'address' => '',
                    'latitud' => $request->latitud,
                    'longitud' => $request->longitud
                ]);
            } else {
                $company->address()->update([
                    'latitud' => $request->latitud,
                    'longitud' => $request->longitud
                ]);
            }
        } else {
            if (!$company->address) {
                $company->address()->create([
                    'address'   => '',
                    'latitud'   => '8.9814453',
                    'longitud'  => '-79.5188013'
                ]);
            } else {
                $company->address()->update([
                    'latitud'   => '8.9814453',
                    'longitud'  => '-79.5188013'
                ]);
            }
        }

        $companyNew = Company::findOrFail($company->id);
        $companyNew->imageCoverPage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
        $companyNew->image;
        return $this->showOne($companyNew, 200);
    }
}

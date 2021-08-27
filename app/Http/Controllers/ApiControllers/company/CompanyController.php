<?php

namespace App\Http\Controllers\ApiControllers\company;

use JWTAuth;
use App\Models\Blog;
use App\Models\Company;
use App\Models\Image;
use App\Models\Remarks;
use App\Models\Products;
use App\Models\Projects;
use App\Models\Portfolio;
use App\Models\Team;
use App\Models\Tenders;
use App\Models\TypesEntity;
use App\Models\User;
use App\Mail\CreatedAccount;
use App\Transformers\UserTransformer;
use App\Transformers\TendersTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str as Str;
use TaylorNetwork\UsernameGenerator\Generator;
use App\Http\Controllers\ApiControllers\ApiController;

class CompanyController extends ApiController
{
    
    public function validateUser(){
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
        //
        $rules = [
            // Country https://restcountries.eu/
            'country_code' => 'required',
            'country_backend' => 'required',
            'email' => 'required|email|unique:users',
            'name' => ['required', 'regex:/^[a-zA-Z0-9\s]*$/'],
            'nit' => 'nullable',
            'password' => 'required|min:6|confirmed',
            'terms' => 'required',
            'type_entity_id' => 'required',
            'web' => 'nullable|url'
        ];

        $this->validate( $request, $rules );

        // Traer los tipos registrados
        $type = TypesEntity::find( $request['type_entity_id'] );

        //Verificar que este registrado y adicionar otras validaciones
        $errors = [];
        
        /*if ( $type['type']['slug'] == 'demanda' ) {

            if ( !$request['nit'] )
                $errors['nit'] = ['El campo nit es obligatorio'];

            if ( !$request['web'] )
                $errors['web'] = ['El campo web es obligatorio'];
        }*/

        //Verificar si existen errores
        if ( !empty( $errors ) )
            return $this->errorResponse( $errors, 500 );

        // Generar Username y Validar que no exista en BD
        // Armar username Parametro $userFields['username']
        $generator = new Generator();
        $userFields['username'] = false;
        $usernameCreated = $request['name'];
        $i=0;
        while( !$userFields['username'] ){
            // 1ra vez
            $username = $generator->generate( $usernameCreated );
            $userExist = DB::table('users')->where('username', $username)->first();
            if( $username && !$userExist ){
                $userFields['username'] = $username;
            }elseif($i==0){
                // 2ra vez
                $usernameCreated = $generator->usingEmail()->generate($request['email']);
            }else{
                $usernameCreated = $generator->generate( $request['name'].uniqid() );
            }
            $i++;
        }

        $userFields['email'] = strtolower($request['email']);
        $userFields['password'] = bcrypt( $request->password );
        $userFields['verified'] = User::USER_NO_VERIFIED;
        // $userFields['validated'] = User::USER_NO_VALIDATED;
        $userFields['verification_token'] = User::generateVerificationToken();
        $userFields['admin'] = User::USER_REGULAR;

        // Iniciar Transacción
        DB::beginTransaction();
        $errorUser = false;
        try{
            // Crear Usuario
            $user = User::create( $userFields );
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorUser = true;
            DB::rollBack();
            $userError = [ 'user' => 'Error, no se ha podido crear el usuario o ya existe el nombre de la empresa' ];
            return $this->errorResponse( $userError, 500 );
        }
        
        if( !$errorUser ){
            $companyFields = [
                'name' => $request['name'],
                'type_entity_id' => $request['type_entity_id'],
                'nit' => $request['nit'],
                'country_code' => $request['country_code'],
                'web' => $request['web'],
                'user_id' => $user['id'],
                'slug' => Str::slug($request['name']), 
            ];
            
            
            try {
                // Crear la compañia
                $company = Company::create( $companyFields );
                
                // Ingresar País en una Compañía
                $company->countries()->attach($request['country_backend']);

                DB::commit();
            } catch (\Throwable $th) {
                // Si existe algún error al generar la compañía
                DB::rollBack();
                $companyError = [ 'company' => 'Error, no se ha podido crear la compañia' ];

                if ( $th->getCode()==23000 && $th->errorInfo[1] == 1062 ) {
                    $companyError = [ 'company' => 'Error, ya se encuentra registrada la compañia'];
                }

                return $this->errorResponse( $companyError, 500 );
            }
        }
        
        // Generar el correo de Verificación.
        Mail::to($user->email)->send(new CreatedAccount( $company, $user, $type['type']['slug'] ));

        // Aquí debe devolver el usuario con el TOKEN.
        return $this->showOne($user,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        //
        $user = $this->validateUser();
        // Compañía del usuario que está logueado
        $userCompanyId = $user->companyId();
        
        $company = Company::where('slug', $slug)->first();
        if( !$company ){
            $companyError = [ 'company' => 'Error, no se ha encontrado ninguna compañia' ];
            return $this->errorResponse( $companyError, 500 );
        }
        
        $userTransform = new UserTransformer();
        $tendersTransform = new TendersTransformer();

        // Banner
        $company->coverpage = Image::where('imageable_id', $company->id)->where('imageable_type', 'App\Models\Company\CoverPage')->first();
        
        // 8 Integrantes del equipo
        $company->team = Team::where('company_id', $company->id)
                                ->where('status', Team::TEAM_APPROVED)
                                ->skip(0)->take(8)
                                ->orderBy('id', 'desc')
                                ->get();
        
        // Traer Proyectos últimos 6
        $company->projects = $company->projects
                        ->where('visible', Projects::PROJECTS_VISIBLE)
                        ->skip(0)->take(6)
                        ->sortBy([ ['updated_at', 'desc'] ]);

        foreach ( $company->projects as $key => $project) {
            $user = $userTransform->transform($project->user);
            unset( $project->user );
            $project->user = $user;
            $project->image;
        }

        // Traer Licitaciones últimas 6
        $company->tenders = Tenders::select('tenders.*', 'comp.status AS company_status')
                        ->where('tenders.company_id', $company->id)
                        ->join( 'projects', 'projects.id', '=', 'tenders.project_id' )
                        ->where('projects.visible', Projects::PROJECTS_VISIBLE)
                        ->leftjoin('tenders_companies AS comp', function($join) use($userCompanyId){
                                $join->on('tenders.id', '=', 'comp.tender_id');
                                $join->where('comp.company_id', '=', $userCompanyId);
                            })
                        ->skip(0)->take(6)
                        ->orderBy('tenders.updated_at', 'desc')
                        ->get();
        
        $tenders = [];
        foreach ( $company->tenders as $key => $tender) {
            $user = $tender->user;
            unset( $tender->user );
            $tender->user = $user;

            $version = $tender->tendersVersionLastPublish();
            if( $version ){
                $tender->tags = $version->tags;
            }
            $tender->project;
            
            $tenders[] = $tendersTransform->transform($tender);
        }
        unset( $company->tenders );
        $company->tenders = $tenders;
        

        // Traer Productos últimos 6
        $company->products = $company->products
                                ->where('status', Products::PRODUCT_PUBLISH)
                                ->skip(0)->take(6)
                                ->sortBy([ ['updated_at', 'desc'] ]);

        foreach ( $company->products as $key => $product) {
            $user = $userTransform->transform($product->user);
            unset( $product->user );
            $product->user = $user;
            $product->tags;
            $product->image;
        }

        // Traer Publicaciones últimas 6
        $company->blogs = $company->blogs
                                ->where('status', Blog::BLOG_PUBLISH)
                                ->skip(0)->take(6)
                                ->sortBy([ ['updated_at', 'desc'] ]);

        foreach ( $company->blogs as $key => $blog) {
            $user = $userTransform->transform($blog->user);
            unset( $blog->user );
            $blog->user = $user;
            $blog->image;
            $blog->files;
        }
        
        // Traer Portafolios últimos 8
        $company->portfolios = $company->portfolios
                                ->where('status', Portfolio::PORTFOLIO_PUBLISH)
                                ->skip(0)->take(8)
                                ->sortBy([ ['updated_at', 'desc'] ]);

        foreach ( $company->portfolios as $key => $portfolio) {
            $portfolio->image;
            $portfolio->files;
        }

        // Calificaciones.
        $company->remarks = Remarks::select('remarks.*')
                    ->where('remarks.company_id', $company->id )
                    ->skip(0)->take(8)
                    ->orderBy('id', 'desc')
                    ->get();
        foreach ( $company->remarks as $key => $remark) {
            $user = $userTransform->transform($remark->user);
            unset( $remark->user );
            $remark->user = $user;
        }

        return $this->showOneTransform($company, 200);
    }

    public function detail($slug)
    {
        //
        $user = $this->validateUser();
        
        $company = Company::where('slug', $slug)->first();
        if( !$company ){
            $companyError = [ 'company' => 'Error, no se ha encontrado ninguna compañia' ];
            return $this->errorResponse( $companyError, 500 );
        }

        return $this->showOneTransformNormal($company, 200);
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
}

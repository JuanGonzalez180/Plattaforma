<?php

namespace App\Http\Controllers\ApiControllers\portfolios;

use JWTAuth;
use App\Models\Image;
use App\Models\User;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class PortfoliosController extends ApiController
{
    public $routeFile = 'public/';
    public $routePortfolios = 'images/portfolios/';

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index()
    {
        $user = $this->validateUser();
        $companyId = $user->companyId();

        $portfolio = Portfolio::where('company_id','=',$companyId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->showAllPaginate($portfolio);
    }

    public function show($id)
    {
        $portfolio = Portfolio::find($id);
        return $portfolio;
    }

    public function edit($id)
    {
        $portfolio = Portfolio::findOrFail($id);
        $portfolio->image;
        $portfolio->files;
        $portfolio->user;
        $portfolio->user->image;

        return $this->showOne($portfolio,200);
    }

    public function store(Request $request)
    {
        $user = $this->validateUser();
        $companyID = $user->companyId();

        $rules = [
            'name' => 'required'
        ];

        $this->validate( $request, $rules );

        $portfolioFields = $request->all();
        $portfolioFields['name'] = $request->name;
        $portfolioFields['description_short'] = $request->description_short;
        $portfolioFields['description'] = $request->description;
        $portfolioFields['status'] = $request->status ?? Portfolio::PORTFOLIO_ERASER;
        $portfolioFields['user_id'] = $request['user'] ?? $user->id;
        $portfolioFields['company_id'] = $companyID;
        
        try{
            $portfolio = Portfolio::create( $portfolioFields );
        }catch(\Throwable $th){
            $errorPortfolio = true;
            DB::rollBack();
            $portfolioError = [ 'portfolio' => 'Error, no se ha podido crear el portafolio' ];
            return $this->errorResponse( $portfolioError, 500 );
        }

        if($portfolio){
            if( $request->image ){
                $png_url = "portfolio-".time().".jpg";
                $img = $request->image;
                $img = substr($img, strpos($img, ",")+1);
                $data = base64_decode($img);
                
                $routeFile = $this->routePortfolios.$portfolio->id.'/'.$png_url;
                Storage::disk('local')->put( $this->routeFile . $routeFile, $data);
                $portfolio->image()->create(['url' => $routeFile]);
            }
        }

        DB::commit();

        return $this->showOne($portfolio,201);
    }

    public function update(Request $request, int $id)
    {
        $user = $this->validateUser();

        $rules = [
            'name' => ['required', Rule::unique('portfolios')->ignore($id) ]
        ];

        // var_dump($request);
        
        $this->validate( $request, $rules );

        $portfolio = Portfolio::findOrFail($id);

        //Datos
        $portfolioFields['name'] = $request['name'];
        $portfolioFields['description_short'] = $request['description_short'];
        $portfolioFields['description'] = $request['description']; 
        $portfolioFields['user_id'] = $request['user'] ?? $user->id;
        $portfolioFields['status'] = $request['status'] ?? Portfolio::PORTFOLIO_ERASER;

        if( $request->image ){
            $png_url = "portfolio-".time().".jpg";
            $img = $request->image;
            $img = substr($img, strpos($img, ",")+1);
            $data = base64_decode($img);
            $routeFile = $this->routePortfolios.$portfolio->id.'/'.$png_url;
            
            Storage::disk('local')->put( $this->routeFile . $routeFile, $data);

            if( $portfolio->image ){
                Storage::disk('local')->delete( $this->routeFile . $portfolio->image->url );
                $portfolio->image()->update(['url' => $routeFile ]);
            }else{
                $portfolio->image()->create(['url' => $routeFile]);
            }
        }

        $portfolio->update( $portfolioFields );

        return $this->showOne($portfolio,200);
    }

    public function destroy(Request $request, int $id)
    {
        $portfolio = Portfolio::find($id);

        if(!$portfolio)
        {
            return $this->errorResponse('El portafolio no existe o ha sido eliminado.', 500);
        }

        if( $portfolio->image ){
            Storage::disk('local')->delete( $this->routeFile . $portfolio->image->url );
            Image::where('imageable_id', $portfolio->id)
                ->where('imageable_type',Portfolio::class)
                ->delete();
        }

        if( $portfolio->files ){
            foreach ($portfolio->files as $key => $file) {
                Storage::disk('local')->delete( $this->routeFile . $file->url );
                $file->delete();
            }
        }

        $portfolio->delete();

        return $this->showOneData( ['success' => 'Se ha eliminado correctamente el portafolio', 'code' => 200 ], 200);
    }
}

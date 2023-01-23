<?php

namespace App\Http\Controllers\ApiControllers\catalogs;

use JWTAuth;
use App\Models\User;
use App\Models\Image;
use App\Models\Catalogs;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class CatalogsControllers extends ApiController
{
    public $routeFile = 'public/';
    public $routeCatalog = 'images/catalogs/';

    public function validateUser()
    {
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

        $catalog = Catalogs::where('company_id', '=', $companyId);
        
        if (!$user->isAdminFrontEnd())
        {
            $catalog = $catalog->where('user_id', $user->id);
        }
        
        $catalog = $catalog->orderBy('created_at', 'desc')
            ->get();

        return $this->showAllPaginate($catalog);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $this->validateUser();
        $companyID = $user->companyId();

        $rules = [
            'name' => 'required'
        ];

        $this->validate($request, $rules);

        $catalogFileds = $request->all();
        $catalogFileds['name'] = $request->name;
        $catalogFileds['description_short'] = $request->description_short;
        $catalogFileds['description'] = $request->description;
        $catalogFileds['status'] = $request->status ?? Catalogs::CATALOG_ERASER;
        $catalogFileds['user_id'] = $request['user'] ?? $user->id;
        $catalogFileds['company_id'] = $companyID;

        try {
            $catalog = Catalogs::create($catalogFileds);
        } catch (\Throwable $th) {
            $erroCatalog = true;
            DB::rollBack();
            $catalogError = ['catalog' => 'Error, no se ha podido crear el catalogo'];
            return $this->errorResponse($catalogError, 500);
        }

        if ($catalog) {
            if ($request->image) {
                $png_url = "catalog-" . time() . ".jpg";
                $img = $request->image;
                $img = substr($img, strpos($img, ",") + 1);
                $data = base64_decode($img);

                $routeFile = $this->routeCatalog . $catalog->id . '/' . $png_url;
                Storage::disk('local')->put($this->routeFile . $routeFile, $data);
                $catalog->image()->create(['url' => $routeFile]);
            }

            if ($request->tags) {
                foreach ($request->tags as $key => $tag) {
                    $catalog->tags()->create(['name' => $tag['displayValue']]);
                }
            }
        }

        DB::commit();

        return $this->showOne($catalog, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $catalog = Catalogs::find($id);
        return $catalog;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $catalog = Catalogs::findOrFail($id);
        $catalog->image;
        $catalog->files;
        $catalog->user;
        $catalog->user->image;
        $catalog->tags;

        return $this->showOne($catalog, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $user = $this->validateUser();

        $rules = [
            'name' => ['required', Rule::unique('catalogs')->ignore($id)]
        ];

        // var_dump($request);

        $this->validate($request, $rules);

        $catalog = Catalogs::findOrFail($id);

        //Datos
        $catalogFileds['name'] = $request['name'];
        $catalogFileds['description_short'] = $request['description_short'];
        $catalogFileds['description'] = $request['description'];
        $catalogFileds['user_id'] = $request['user'] ?? $user->id;
        $catalogFileds['status'] = $request['status'] ?? Catalogs::CATALOG_ERASER;

        $catalog->update($catalogFileds);
        
        if ($request->image) {
            $png_url = "catalog-" . time() . ".jpg";
            $img = $request->image;
            $img = substr($img, strpos($img, ",") + 1);
            $data = base64_decode($img);
            $routeFile = $this->routeCatalog . $catalog->id . '/' . $png_url;

            Storage::disk('local')->put($this->routeFile . $routeFile, $data);

            if ($catalog->image) {
                Storage::disk('local')->delete($this->routeFile . $catalog->image->url);
                $catalog->image()->update(['url' => $routeFile]);
            } else {
                $catalog->image()->create(['url' => $routeFile]);
            }
        }

        foreach ($catalog->tags as $key => $tag) {
            $tag->delete();
        }

        if ($request->tags) {
            foreach ($request->tags as $key => $tag) {
                $catalog->tags()->create(['name' => $tag['displayValue']]);
            }
        }        

        return $this->showOne($catalog, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $id)
    {
        $catalog = Catalogs::find($id);

        if(!$catalog)
        {
            return $this->errorResponse('El catálogo del producto no existe o ha sido eliminado.', 500);
        }

        if ($catalog->image) {
            Storage::disk('local')->delete($this->routeFile . $catalog->image->url);
            Image::where('imageable_id', $catalog->id)
                ->where('imageable_type', Catalogs::class)
                ->delete();
        }

        if ($catalog->files) {
            foreach ($catalog->files as $key => $file) {
                Storage::disk('local')->delete($this->routeFile . $file->url);
                $file->delete();
            }
        }

        $catalog->delete();

        return $this->showOneData(['success' => 'Se ha eliminado correctamente el catalogo', 'code' => 200], 200);
    }
}

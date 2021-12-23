<?php

namespace App\Http\Controllers\ApiControllers\portfolios;


use JWTAuth;
use App\Models\User;
use App\Models\Files;
use App\Models\Company;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Imagick;

use App\Http\Controllers\ApiControllers\ApiController;

class PortfoliosDocumentsController extends ApiController
{
    public $routeFile = 'public/';
    public $routePortfolios = 'images/portfolios/';
    public $allowed = ['pdf'];

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function filesType($Portfolio)
    {
        $files = Files::where('filesable_id', $Portfolio->id)
            ->where('type', 'documents')
            ->where('filesable_type', Portfolio::class)
            ->get();

        return $files;
    }

    public function index(Request $request)
    {
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate($request, $rules);

        $Portfolio = Portfolio::findOrFail($request->id);
        return $this->showAll($this->filesType($Portfolio), 200);
    }

    public function store(Request $request)
    {

        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate($request, $rules);

        $portfolio = Portfolio::findOrFail($request->id);

        if ($request->hasFile('files')) {
            $completeFileName = $request->file('files')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extension = strtolower($request->file('files')->getClientOriginalExtension());

            if (in_array($extension, $this->allowed)) {
                $fileInServer = 'document' . '-' . rand() . '-' . time() . '.' . $extension;
                $routeFile = $this->routePortfolios . $portfolio->id . '/documents/';
                $request->file('files')->storeAs($this->routeFile . $routeFile, $fileInServer);
                $portfolio->files()->create(['name' => $fileInServer, 'type' => 'documents', 'url' => $routeFile . $fileInServer]);
            } else {
                return $this->errorResponse(['error' => ['El tipo de archivo no es válido']], 500);
            }
        }



        // $imagick = new Imagick();
  
        // $imagick->readImage(public_path($routeFile . $fileInServer));
  
        // $saveImagePath = public_path('converted.jpg');
        // $imagick->writeImages($saveImagePath, true);
  
        

        return $this->showAll($this->filesType($portfolio), 200);
    }

    public function update(Request $request, int $fileId)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $rules = [
            'id' => 'required',
            'name' => 'required',
            // 'product' => 'required',
        ];

        $this->validate($request, $rules);

        // Datos
        $filePortfolio = Files::where('id', $fileId)
            ->where('filesable_type', Portfolio::class)
            ->first();

        $portfolio = Portfolio::findOrFail($filePortfolio->filesable_id);

        $tmp = explode('.', $filePortfolio->name);
        $extension = end($tmp);

        $routeFile = $this->routePortfolios . $portfolio->id . '/documents/';
        $file['name'] = preg_replace("/[^A-Za-z0-9]/", '', $request['name']) . "." . $extension;
        $file['url'] = $routeFile . $file['name'];

        if (Storage::disk('local')->exists($this->routeFile . $routeFile . $file['name'])) {
            $userError = ['name' => ['Error, el nombre de archivo ya existe']];
            return $this->errorResponse($userError, 500);
        }

        if (Storage::disk('local')->exists($this->routeFile . $routeFile . $filePortfolio->name)) {
            if (Storage::move(
                $this->routeFile . $routeFile . $filePortfolio->name,
                $this->routeFile . $routeFile . $file['name']
            )) {
                $filePortfolio->update($file);
            }
        }

        return $this->showAll($this->filesType($portfolio), 200);
    }

    public function destroy(Request $request, int $fileId)
    {
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate($request, $rules);

        $portfolio = Portfolio::findOrFail($request->id);

        $filePortfolio = Files::where('id', $fileId)->where('filesable_type', Portfolio::class)->first();
        // Eliminar archivo de los datos
        Storage::disk('local')->delete($this->routeFile . $filePortfolio->url);
        // Eliminar archivo de la BD
        $filePortfolio->delete();

        // return $this->showOneData( ['success' => 'Se ha eliminado el archivo correctamente', 'code' => 200 ], 200);
        return $this->showAll($this->filesType($portfolio), 200);
    }
}

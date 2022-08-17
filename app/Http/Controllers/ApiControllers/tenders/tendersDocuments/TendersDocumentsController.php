<?php

namespace App\Http\Controllers\ApiControllers\tenders\tendersDocuments;

use JWTAuth;
use App\Models\User;
use App\Models\Files;
use App\Models\Company;
use App\Models\TendersVersions;
use Illuminate\Http\Request;
use Illuminate\Support\Str as Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class TendersDocumentsController extends ApiController
{
    //
    public $routeFile       = 'public/';
    public $routeTenders    = 'images/tenders/';
    public $allowed         = ['pdf', 'zip', 'png', 'jpg', 'jpeg'];

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function filesType($tender)
    {
        $files = Files::where('filesable_id', $tender->id)->where('type', 'documents')->where('filesable_type', TendersVersions::class)->get();
        return $files;
    }

    public function index(Request $request)
    {
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate($request, $rules);

        $tender = TendersVersions::findOrFail($request->id);
        return $this->showAll($this->filesType($tender), 200);
    }

    public function store(Request $request)
    {
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate($request, $rules);

        $tender = TendersVersions::findOrFail($request->id);

        if ($request->hasFile('files')) {
            $completeFileName = $request->file('files')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extension = strtolower($request->file('files')->getClientOriginalExtension());

            if (in_array($extension, $this->allowed)) {
                $fileInServer = Str::slug(Str::substr($completeFileName, 0, 70)) . '-' . time() . '.' . $extension;
                $routeFile = $this->routeTenders . $tender->id . '/documents/';
                $request->file('files')->storeAs($this->routeFile . $routeFile, $fileInServer);
                $tender->files()->create(['name' => $fileInServer, 'type' => 'documents', 'url' => $routeFile . $fileInServer]);
            } else {
                return $this->errorResponse(['error' => ['El tipo de archivo no es válido']], 500);
            }
        }

        return $this->showAll($this->filesType($tender), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $idProject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $fileId)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $rules = [
            'id' => 'required',
            'name' => 'required',
            // 'tender' => 'required',
        ];

        $this->validate($request, $rules);

        // Datos
        $fileProduct = Files::where('id', $fileId)->where('filesable_type', TendersVersions::class)->first();
        $tender = TendersVersions::findOrFail($fileProduct->filesable_id);

        $tmp = explode('.', $fileProduct->name);
        $extension = end($tmp);

        $routeFile = $this->routeTenders . $tender->id . '/documents/';
        $file['name'] = preg_replace("/[^A-Za-z0-9]/", '', $request['name']) . "." . $extension;
        $file['url'] = $routeFile . $file['name'];

        if (Storage::disk('local')->exists($this->routeFile . $routeFile . $file['name'])) {
            $userError = ['name' => ['Error, el nombre de archivo ya existe']];
            return $this->errorResponse($userError, 500);
        }

        if (Storage::disk('local')->exists($this->routeFile . $routeFile . $fileProduct->name)) {
            if (Storage::move(
                $this->routeFile . $routeFile . $fileProduct->name,
                $this->routeFile . $routeFile . $file['name']
            )) {
                $fileProduct->update($file);
            }
        }

        return $this->showAll($this->filesType($tender), 200);
    }

    public function destroy(Request $request, int $fileId)
    {
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate($request, $rules);

        $tender = TendersVersions::findOrFail($request->id);

        $fileProduct = Files::where('id', $fileId)->where('filesable_type', TendersVersions::class)->first();
        // Eliminar archivo de los datos
        Storage::disk('local')->delete($this->routeFile . $fileProduct->url);
        // Eliminar archivo de la BD
        $fileProduct->delete();

        // return $this->showOneData( ['success' => 'Se ha eliminado el archivo correctamente', 'code' => 200 ], 200);
        return $this->showAll($this->filesType($tender), 200);
    }
}

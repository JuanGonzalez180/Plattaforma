<?php

namespace App\Http\Controllers\ApiControllers\quotes\quotesCompanies;


use JWTAuth;
use App\Models\Files;
use Illuminate\Http\Request;
use App\Models\QuotesCompanies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class QuotesCompaniesDocumentsController extends ApiController
{
    public $routeFile           = 'public/';
    public $routeQuoteCompany  = 'images/quotecompany/';
    public $allowed             = ['pdf'];

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function filesType($quoteCompany)
    {
        $files = Files::where('filesable_id', $quoteCompany->id)
            ->where('type', 'documents')
            ->where('filesable_type', QuotesCompanies::class)
            ->get();

        return $files;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];
        $this->validate($request, $rules);

        $quotesCompanies = QuotesCompanies::findOrFail($request->id);
        return $this->showAll($this->filesType($quotesCompanies), 200);
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

        $rules = [
            'id' => 'required'
        ];

        $this->validate($request, $rules);

        $quotesCompanies = QuotesCompanies::findOrFail($request->id);

        if ($request->hasFile('files')) {
            $completeFileName   = $request->file('files')->getClientOriginalName();
            $fileNameOnly       = pathinfo($completeFileName, PATHINFO_FILENAME);
            $extension          = strtolower($request->file('files')->getClientOriginalExtension());

            try {
                $fileInServer = 'document' . '-' . rand() . '-' . time() . '.' . $extension;
                $routeFile = $this->routeQuoteCompany . $quotesCompanies->id . '/documents/';
                $request->file('files')->storeAs($this->routeFile . $routeFile, $fileInServer);
                $quotesCompanies->files()->create(['name' => $fileInServer, 'type' => 'documents', 'url' => $routeFile . $fileInServer]);
            } catch (\Throwable $th) {
                return $this->errorResponse(['error' => ['El tipo de archivo no es válido']], 500);
            }
        }

        return $this->showAll($this->filesType($quotesCompanies), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
        $fileQuotesCompanies = Files::where('id', $fileId)
            ->where('filesable_type', QuotesCompanies::class)
            ->first();

        $quotesCompanies = QuotesCompanies::findOrFail($fileQuotesCompanies->filesable_id);

        $tmp = explode('.', $fileQuotesCompanies->name);
        $extension = end($tmp);

        $routeFile = $this->routeQuoteCompany . $quotesCompanies->id . '/documents/';

        $file['name'] = preg_replace("/[^A-Za-z0-9]/", '', $request['name']) . "." . $extension;
        $file['url'] = $routeFile . $file['name'];

        if (Storage::disk('local')->exists($this->routeFile . $routeFile . $file['name'])) {
            $userError = ['name' => ['Error, el nombre de archivo ya existe']];
            return $this->errorResponse($userError, 500);
        }

        if (Storage::disk('local')->exists($this->routeFile . $routeFile . $fileQuotesCompanies->name)) {
            if (Storage::move(
                $this->routeFile . $routeFile . $fileQuotesCompanies->name,
                $this->routeFile . $routeFile . $file['name']
            )) {
                $fileQuotesCompanies->update($file);
            }
        }

        return $this->showAll($this->filesType($quotesCompanies), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $fileId)
    {
        $user = $this->validateUser();

        $rules = [
            'id' => 'required'
        ];

        $this->validate($request, $rules);

        $quotesCompanies = QuotesCompanies::findOrFail($request->id);

        $fileQuotesCompanies = Files::where('id', $fileId)->where('filesable_type', QuotesCompanies::class)->first();
        // Eliminar archivo de los datos
        Storage::disk('local')->delete($this->routeFile . $fileQuotesCompanies->url);
        // Eliminar archivo de la BD
        $fileQuotesCompanies->delete();

        return $this->showAll($this->filesType($quotesCompanies), 200);
    }
}

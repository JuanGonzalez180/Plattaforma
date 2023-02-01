<?php

namespace App\Http\Controllers\WebControllers\companyEntity;

use App\Models\Company;
use App\Models\TypesEntity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class companyEntityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        var_dump('index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        var_dump('create');
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
        var_dump('store');
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
        var_dump('show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company        = $this->getCompanyId($id);
        // $entitylist     = $this->getTypesEntity();
        $entitylist     = $this->getTypesEntity();

        return view('entitycompany.edit', compact('company', 'entitylist'));
    }

    public function getCompanyId($id)
    {
        return Company::find($id);
    }

    public function getTypesEntity()
    {
        return TypesEntity::select('id','name')->where('status', TypesEntity::ENTITY_PUBLISH)
            ->orderBy('name', 'asc')
            ->get();
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
        var_dump('update');
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
        var_dump('destroy');
    }
}

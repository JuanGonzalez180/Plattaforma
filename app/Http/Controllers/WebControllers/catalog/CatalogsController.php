<?php

namespace App\Http\Controllers\WebControllers\catalog;

use DataTables;
use App\Models\Catalogs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CatalogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($company_id)
    {
        return view('catalog.index', compact('company_id'));
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
        //
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
        return view('catalog.show', compact('catalog'));
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

    public function getCompanyCatalogs(Request $request)
    {

        $company_id     = $request->company_id;
        $catalogs       = Catalogs::where('company_id', $company_id)->orderBy('id', 'asc');

        return DataTables::of($catalogs)
            ->editColumn('company_id', function (Catalogs $value) {
                return $value->company->name;
            })
            ->editColumn('user_id', function (Catalogs $value) {
                return $value->user->name;
            })
            ->addColumn('actions', 'catalog.datatables.action')
            ->rawColumns(['actions'])
            ->toJson();
    }
}

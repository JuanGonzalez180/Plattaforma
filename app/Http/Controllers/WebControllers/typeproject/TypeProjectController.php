<?php

namespace App\Http\Controllers\WebControllers\typeproject;

use App\Http\Controllers\Controller;
use App\Models\TypeProject;
use Illuminate\Http\Request;

class TypeProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $typeprojects = TypeProject::all();
        return view('typeproject.index', compact('typeprojects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $typeProjectOptions = TypeProject::get();
        $typeproject = new TypeProject;
        return view('typeproject.create', compact('typeproject','typeProjectOptions'));
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
            'name' => 'required',
            'description' => 'required'
        ];

        $this->validate( $request, $rules );

        $fields = $request->all();
        TypeProject::create( $fields );

        return redirect()->route('typeproject.index')->with('success', 'Tipo de proyecto creado satisfactoriamente');
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
        $typeproject = TypeProject::findOrFail($id);
        $typeProjectOptions = TypeProject::get();
        return view('typeproject.edit', compact('typeproject', 'typeProjectOptions'));
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
        $rules = [
            'name' => 'required',
            'description' => 'required'
        ];

        $this->validate( $request, $rules );
        $fields = $request->all();

        $typeproject = TypeProject::findOrFail($id);
        $typeproject->update( $fields );

        return redirect()->route('typeproject.index');
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
        TypeProject::find($id)->delete();
        return redirect()->route('typeproject.index')->with('success', 'Tipo de proyecto eliminada satisfactoriamente');
    }
}

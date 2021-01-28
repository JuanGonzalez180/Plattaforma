<?php

namespace App\Http\Controllers\WebControllers\typesentity;

use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Models\TypesEntity;
use Illuminate\Http\Request;

class TypesEntityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $typesEntity = TypesEntity::get();
        return view('typesentity.index', compact('typesEntity'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $typeOptions = Type::get();
        $typeEntity = new TypesEntity();
        return view('typesentity.create', compact('typeOptions', 'typeEntity'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $requestValidated = $request->validate([
            'name' => ['required'],
            'type_id' => ['required'],
        ]);
            
        TypesEntity::create( $requestValidated );
        return redirect()->route('typesentity.index')->with('status', 'create');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(TypesEntity $typeEntity)
    {
        $typeOptions = Type::get();
        return view('typesentity.edit', compact('typeOptions', 'typeEntity'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TypesEntity $typeEntity)
    {
        $requestValidated = $request->validate([
            'name' => ['required'],
            'type_id' => ['required'],
        ]);
            
        $typeEntity->update( $requestValidated );
        return redirect()->route('typesentity.index')->with('status', 'edit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TypesEntity $typeEntity)
    {
        $typeEntity->delete();
        return redirect()->route('typesentity.index')->with('status', 'delete');
    }
}

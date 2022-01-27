<?php

namespace App\Http\Controllers\WebControllers\typeproject;


use DataTables;
use App\Models\Image;
use App\Models\TypeProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use TaylorNetwork\UsernameGenerator\Generator;

class TypeProjectController extends Controller
{
    public $routeFile = 'public/';
    public $routeFileBD = 'images/typeprojects/';
    public $modelIcon = 'App\Models\TypeProject\Icon';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $parents = TypeProject::select('id','name')
            ->whereNull('parent_id')
            ->orderBy('name','asc')
            ->get();

        return view('typeproject.index', compact('parents'));
    }

    public function getTypeProyectChilds(Request $request)
    {
        $parent_id  = $request->parent_id;
        $category   = TypeProject::select('id','name','parent_id','status');

        if ($parent_id != 'all') {
            $childs     = DB::select('call get_child_type_project_admin("'.$parent_id.'")');
            $ids        = array_column($childs, 'id');
            $category = (count($ids) <= 0)
                ? $category->where('id', $parent_id) ->orderBy('id','asc')
                : $category->whereIn('id', $ids)->orderBy('id','asc');
        } else {
            $category   = $category->orderBy('id','asc');
        }

        return DataTables::of($category)
            ->editColumn('parent_id', function(TypeProject $value){
                return (is_null($value->parent_id))
                    ? '<span class="badge badge-warning"><i class="fas fa-circle"></i> Padre</span>'
                    : $value->parent['name'];
            })
            ->addColumn('actions','typeproject.datatables.action')
            ->rawColumns(['actions','parent_id'])
            ->toJson();
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
        $typeproject        = new TypeProject;
        $status             = [TypeProject::TYPEPROJECT_ERASER, TypeProject::TYPEPROJECT_PUBLISH];
        return view('typeproject.create', compact('typeproject','typeProjectOptions', 'status'));
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
            'name'          => 'required',
            'description'   => 'required',
            'status'        => 'required',
            'image'         => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $this->validate( $request, $rules );

        $fields = $request->all();
        $typeproject = TypeProject::create( $fields );

        $generator = new Generator();
        if( $request->image ){
            $imageName = $generator->generate( $request->name );
            $imageName = $imageName . '-' . uniqid().'.'.$request->image->extension();
            $request->image->storeAs( $this->routeFile.$this->routeFileBD, $imageName);
            $typeproject->image()->create(['url' => $this->routeFileBD.$imageName ]);
        }

        if( $request->icon ){
            $iconName = $generator->generate( $request->name );
            $iconName = $iconName . '-icon-' . uniqid().'.'.$request->icon->extension();
            $request->icon->storeAs( $this->routeFile.$this->routeFileBD, $iconName);

            Image::create(['url' => $this->routeFileBD.$iconName, 'imageable_id' => $typeproject->id, 'imageable_type' => $this->modelIcon]);
        }

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
        $typeproject        = TypeProject::findOrFail($id);
        $typeproject->icon  = Image::where('imageable_id', $typeproject->id)->where('imageable_type', $this->modelIcon)->first();
        $typeProjectOptions = TypeProject::get();
        $status             = [TypeProject::TYPEPROJECT_ERASER, TypeProject::TYPEPROJECT_PUBLISH];
        return view('typeproject.edit', compact('typeproject', 'typeProjectOptions', 'status'));
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
            'status' => 'required',
            'description' => 'required'
        ];

        $this->validate( $request, $rules );
        $fields = $request->all();

        $typeproject = TypeProject::findOrFail($id);
        $typeproject->update( $fields );

        $generator = new Generator();
        if( $request->image ){
            $imageName = $generator->generate( $request->name );
            $imageName = $imageName . '-' . uniqid().'.'.$request->image->extension();
            
            if( $typeproject->image ){
                Storage::disk('local')->delete( $this->routeFile . $typeproject->image->url );
                $typeproject->image()->update(['url' => $this->routeFileBD.$imageName ]);
            }else{
                $typeproject->image()->create(['url' => $this->routeFileBD.$imageName ]);
            }
            $request->image->storeAs( $this->routeFile.$this->routeFileBD, $imageName);
            $typeproject->save();
        }

        if( $request->icon ){
            $iconName = $generator->generate( $request->name );
            $iconName = $iconName . '-icon-' . uniqid().'.'.$request->icon->extension();

            $imageIcon = Image::where('imageable_id', $typeproject->id)->where('imageable_type', $this->modelIcon)->first();
            if( !$imageIcon ){
                Image::create(['url' => $this->routeFileBD.$iconName, 'imageable_id' => $typeproject->id, 'imageable_type' => $this->modelIcon]);
            }else{
                Image::where('imageable_id', $typeproject->id)->where('imageable_type', $this->modelIcon)->update(['url' => $this->routeFileBD.$iconName]);
                Storage::disk('local')->delete( $this->routeFile . $imageIcon->url );
            }
            $request->icon->storeAs( $this->routeFile.$this->routeFileBD, $iconName);
        }

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
        $typeproject = TypeProject::find($id);
        
        // Delete Icon
        $imageIcon = Image::where('imageable_id', $typeproject->id)->where('imageable_type', $this->modelIcon)->first();
        if( $imageIcon ){
            Storage::disk('local')->delete( $this->routeFile . $imageIcon->url );
            Image::where('imageable_id', $typeproject->id)->where('imageable_type', $this->modelIcon)->delete();
        }

        // Delete Image
        if( $typeproject->image ){
            Storage::disk('local')->delete( $this->routeFile . $typeproject->image->url );
            Image::where('imageable_id', $typeproject->id)->where('imageable_type', TypeProject::class)->delete();
        }
        
        // Delete Type Project
        $typeproject->delete();

        return redirect()->route('typeproject.index')->with('success', 'Tipo de proyecto eliminada satisfactoriamente');
    }
}

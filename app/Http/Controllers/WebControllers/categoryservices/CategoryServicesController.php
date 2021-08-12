<?php

namespace App\Http\Controllers\WebControllers\categoryservices;

use DataTables;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Models\CategoryService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use TaylorNetwork\UsernameGenerator\Generator;

class CategoryServicesController extends Controller
{
    public $routeFile = 'public/';
    public $routeFileBD = 'images/categories/';
    public $modelIcon = 'App\Models\CategoryService\Icon';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $parents = CategoryService::select('id','name')
            ->whereNull('parent_id')
            ->orderBy('name','asc')
            ->get();

        return view('categoryservices.index', compact('parents'));
    }

    public function getCategoryServiceChilds(Request $request)
    {
        $parent_id  = $request->parent_id;
        $childs     = DB::select('call get_child_type_category_service("'.$parent_id.'")');
        $ids        = array_column($childs, 'id');

        $category   = CategoryService::select('id','name','parent_id','status');

        $category = (count($ids) <= 0)
            ? $category->where('id', $parent_id) ->orderBy('id','asc')
            : $category->whereIn('id', $ids)->orderBy('id','asc');

        return DataTables::of($category)
            ->editColumn('parent_id', function(CategoryService $value){
                return (is_null($value->parent_id))
                    ? '<span class="badge badge-warning"><i class="fas fa-circle"></i> Padre</span>'
                    : $value->parent['name'];
            })
            ->addColumn('actions','categoryservices.datatables.action')
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
        $categoryOptions = CategoryService::get();
        $category = new CategoryService;
        return view('categoryservices.create', compact('category','categoryOptions'));
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
            'description' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $this->validate( $request, $rules );


        $fields = $request->all();
        $category = CategoryService::create( $fields );

        $generator = new Generator();
        if( $request->image ){
            $imageName = $generator->generate( $request->name );
            $imageName = $imageName . '-' . uniqid().'.'.$request->image->extension();
            $request->image->storeAs( $this->routeFile.$this->routeFileBD, $imageName);
            $category->image()->create(['url' => $this->routeFileBD.$imageName ]);
        }

        if( $request->icon ){
            $iconName = $generator->generate( $request->name );
            $iconName = $iconName . '-icon-' . uniqid().'.'.$request->icon->extension();
            $request->icon->storeAs( $this->routeFile.$this->routeFileBD, $iconName);

            Image::create(['url' => $this->routeFileBD.$iconName, 'imageable_id' => $category->id, 'imageable_type' => $this->modelIcon]);
        }

        return redirect()->route('categoryservices.index')->with('success', 'Categoría creada satisfactoriamente');
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
        $category = CategoryService::findOrFail($id);
        $category->icon = Image::where('imageable_id', $category->id)->where('imageable_type', $this->modelIcon)->first();
        $categoryOptions = CategoryService::get();
        return view('categoryservices.edit', compact('category', 'categoryOptions'));
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
            'description' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $this->validate( $request, $rules );
        $fields = $request->all();

        $category = CategoryService::findOrFail($id);
        $category->update( $fields );

        $generator = new Generator();
        if( $request->image ){
            $imageName = $generator->generate( $request->name );
            $imageName = $imageName . '-' . uniqid().'.'.$request->image->extension();
            
            if( $category->image ){
                Storage::disk('local')->delete( $this->routeFile . $category->image->url );
                $category->image()->update(['url' => $this->routeFileBD.$imageName ]);
            }else{
                $category->image()->create(['url' => $this->routeFileBD.$imageName ]);
            }
            $request->image->storeAs( $this->routeFile.$this->routeFileBD, $imageName);
            $category->save();
        }

        if( $request->icon ){
            $iconName = $generator->generate( $request->name );
            $iconName = $iconName . '-icon-' . uniqid().'.'.$request->icon->extension();

            $imageIcon = Image::where('imageable_id', $category->id)->where('imageable_type', $this->modelIcon)->first();
            if( !$imageIcon ){
                Image::create(['url' => $this->routeFileBD.$iconName, 'imageable_id' => $category->id, 'imageable_type' => $this->modelIcon]);
            }else{
                Image::where('imageable_id', $category->id)->where('imageable_type', $this->modelIcon)->update(['url' => $this->routeFileBD.$iconName]);
                Storage::disk('local')->delete( $this->routeFile . $imageIcon->url );
            }
            $request->icon->storeAs( $this->routeFile.$this->routeFileBD, $iconName);
        }

        return redirect()->route('categoryservices.index');
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
        $category = CategoryService::find($id);
        // Delete Icon
        $imageIcon = Image::where('imageable_id', $category->id)->where('imageable_type', $this->modelIcon)->first();
        if($imageIcon){
            Storage::disk('local')->delete( $this->routeFile . $imageIcon->url );
            Image::where('imageable_id', $category->id)->where('imageable_type', $this->modelIcon)->delete();
        }

        // Delete Image
        if( $category->image ){
            Storage::disk('local')->delete( $this->routeFile . $category->image->url );
            Image::where('imageable_id', $category->id)->where('imageable_type', CategoryService::class)->delete();
        }
        
        // Delete Type Project
        $category->delete();

        return redirect()->route('categoryservices.index')->with('success', 'Categoría eliminada satisfactoriamente');
    }
}

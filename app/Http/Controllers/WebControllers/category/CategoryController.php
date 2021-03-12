<?php

namespace App\Http\Controllers\WebControllers\category;

use App\Models\Image;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use TaylorNetwork\UsernameGenerator\Generator;

class CategoryController extends Controller
{
    public $routeFile = 'public/';
    public $routeFileBD = 'images/categories/';
    public $modelIcon = 'App\Models\Category\Icon';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();
        return view('category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $categoryOptions = Category::get();
        $category = new Category;
        return view('category.create', compact('category','categoryOptions'));
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
        $category = Category::create( $fields );

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

        return redirect()->route('category.index')->with('success', 'Categoría creada satisfactoriamente');
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
        $category = Category::findOrFail($id);
        $category->icon = Image::where('imageable_id', $category->id)->where('imageable_type', $this->modelIcon)->first();
        $categoryOptions = Category::get();
        return view('category.edit', compact('category', 'categoryOptions'));
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

        $category = Category::findOrFail($id);
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

        return redirect()->route('category.index');
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
        $category = Category::find($id);
        // Delete Icon
        $imageIcon = Image::where('imageable_id', $category->id)->where('imageable_type', $this->modelIcon)->first();
        if($imageIcon){
            Storage::disk('local')->delete( $this->routeFile . $imageIcon->url );
            Image::where('imageable_id', $category->id)->where('imageable_type', $this->modelIcon)->delete();
        }

        // Delete Image
        if( $category->image ){
            Storage::disk('local')->delete( $this->routeFile . $category->image->url );
            Image::where('imageable_id', $category->id)->where('imageable_type', TypeProject::class)->delete();
        }
        
        // Delete Type Project
        $category->delete();

        return redirect()->route('category.index')->with('success', 'Categoría eliminada satisfactoriamente');
    }
}

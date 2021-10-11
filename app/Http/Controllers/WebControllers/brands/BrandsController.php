<?php

namespace App\Http\Controllers\WebControllers\brands;

use App\Models\Image;
use App\Models\Brands;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use TaylorNetwork\UsernameGenerator\Generator;

class BrandsController extends Controller
{
    public $routeFile = 'public/';
    public $routeFileBD = 'images/brands/';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $brands     = Brands::all();
        $enabled    = Brands::BRAND_ENABLED;
        return view('brand.index', compact('brands', 'enabled'));
    }

    public function indexCompanyBrand($id)
    {
        $brands     = Brands::where('company_id', $id)->get();
        $enabled    = Brands::BRAND_ENABLED;
        $type       = 'Company';
        return view('brand.index', compact('brands', 'enabled', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $brand = new Brands;
        return view('brand.create', compact('brand'));
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
            'name' => 'required|unique:brands',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];


        $this->validate($request, $rules);
        $fields = $request->all();

        $fields['name']     = ucwords($request->name);
        $fields['user_id']  = auth()->user()->id;
        $brand = Brands::create($fields);

        $generator = new Generator();
        if ($request->image) {
            $imageName = $generator->generate($request->name);
            $imageName = $imageName . '-' . uniqid() . '.' . $request->image->extension();
            $request->image->storeAs($this->routeFile . $this->routeFileBD, $imageName);

            $size = round((filesize('storage/' . $this->routeFileBD . $imageName) / pow(1024, 2)), 5);

            $brand->image()->create(['url' => $this->routeFileBD . $imageName, 'size' => $size]);
        }

        return redirect()->route('brand.index')->with('success', 'La Marca creada satisfactoriamente');
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
        $brand = Brands::findOrFail($id);
        return view('brand.edit', compact('brand'));
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
        $request->name = ucwords($request->name);

        $rules = [
            // 'name' => 'required|unique:brands',
            'name' => ['required', Rule::unique('brands')->ignore($id)],
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $this->validate($request, $rules);
        $fields = $request->all();

        $brand = Brands::findOrFail($id);
        $brand->update($fields);

        $generator = new Generator();
        if ($request->image) {
            $imageName  = $generator->generate($request->name);
            $imageName  = $imageName . '-' . uniqid() . '.' . $request->image->extension();

            $size = round((filesize($request->image) / pow(1024, 2)), 5);


            if ($brand->image) {
                Storage::disk('local')->delete($this->routeFile . $brand->image->url);
                $brand->image()->update(['url' => $this->routeFileBD . $imageName, 'size' => $size]);
            } else {
                $brand->image()->create(['url' => $this->routeFileBD . $imageName, 'size' => $size]);
            }
            $request->image->storeAs($this->routeFile . $this->routeFileBD, $imageName);
            $brand->save();
        }

        return redirect()->route('brand.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $brand = Brands::find($id);
        $status = ($brand->status == Brands::BRAND_ENABLED) ? Brands::BRAND_DISABLED : Brands::BRAND_ENABLED;
        $brand->status = $status;
        $brand->save();

        return redirect()->route('brand.index')->with('success', 'Se ha cambiado el estado de la Marca');
    }
}

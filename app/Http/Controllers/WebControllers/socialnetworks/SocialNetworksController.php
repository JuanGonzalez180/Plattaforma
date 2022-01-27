<?php

namespace App\Http\Controllers\WebControllers\socialnetworks;

use App\Models\Image;
use Illuminate\Http\Request;
use App\Models\SocialNetworks;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use TaylorNetwork\UsernameGenerator\Generator;

class SocialNetworksController extends Controller
{
    //
    public $routeFile = 'public/';
    public $routeFileBD = 'images/socialnetworks/';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $socialnetworks = SocialNetworks::all();
        return view('socialnetwork.index', compact('socialnetworks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $socialnetworks = new SocialNetworks;
        return view('socialnetwork.create', compact('socialnetworks'));
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
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $this->validate( $request, $rules );


        $fields = $request->all();
        $socialnetworks = SocialNetworks::create( $fields );

        $generator = new Generator();
        if( $request->image ){
            $imageName = $generator->generate( $request->name );
            $imageName = $imageName . '-' . uniqid().'.'.$request->image->extension();
            $request->image->storeAs( $this->routeFile.$this->routeFileBD, $imageName);
            $socialnetworks->image()->create(['url' => $this->routeFileBD.$imageName ]);
        }

        return redirect()->route('socialnetwork.index')->with('success', 'Red Social creada satisfactoriamente');
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
        $socialnetworks = SocialNetworks::findOrFail($id);
        $socialnetworks->image;
        return view('socialnetwork.edit', compact('socialnetworks'));
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
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $this->validate( $request, $rules );
        $fields = $request->all();

        $socialnetworks = SocialNetworks::findOrFail($id);
        $socialnetworks->update( $fields );

        $generator = new Generator();
        if( $request->image ){
            $imageName = $generator->generate( $request->name );
            $imageName = $imageName . '-' . uniqid().'.'.$request->image->extension();
            
            if( $socialnetworks->image ){
                Storage::disk('local')->delete( $this->routeFile . $socialnetworks->image->url );
                $socialnetworks->image()->update(['url' => $this->routeFileBD.$imageName ]);
            }else{
                $socialnetworks->image()->create(['url' => $this->routeFileBD.$imageName ]);
            }
            $request->image->storeAs( $this->routeFile.$this->routeFileBD, $imageName);
            $socialnetworks->save();
        }

        return redirect()->route('socialnetwork.index');
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
        $socialnetworks = SocialNetworks::find($id);
        // Delete Image
        if( $socialnetworks->image ){
            Storage::disk('local')->delete( $this->routeFile . $socialnetworks->image->url );
            Image::where('imageable_id', $socialnetworks->id)->where('imageable_type', TypeProject::class)->delete();
        }
        // Delete Type Project
        $socialnetworks->delete();
        return redirect()->route('socialnetwork.index')->with('success', 'Red Social eliminada satisfactoriamente');
    }
}

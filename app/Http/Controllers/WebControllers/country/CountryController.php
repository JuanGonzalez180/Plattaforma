<?php

namespace App\Http\Controllers\WebControllers\country;

use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CountryController extends Controller
{
    /**
     * Title sent in notification
     */
    private $sectionTitle = 'Country';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $countries = Country::get();
        return view('countries.index', compact('countries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $country = new Country();
        return view('countries.create', compact('country'));
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
            'alpha2Code' => ['required'],
        ]);
            
        Country::create( $requestValidated );
        return redirect()->route('countries.index')->with([
            'status' => 'create',
            'title' => __( $this->sectionTitle ),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  StaticContent  $staticContent
     * @return \Illuminate\Http\Response
     */
    public function edit(Country $country)
    {
        return view('countries.edit', compact('country'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  StaticContent  $staticContent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Country $country)
    {
        $requestValidated = $request->validate([
            'name' => ['required'],
            'alpha2Code' => ['required'],
        ]);
            
        $country->update( $requestValidated );
        return redirect()->route('countries.index')->with([
            'status' => 'edit',
            'title' => __( $this->sectionTitle ),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Country $country)
    {
        $country->delete();
        return redirect()->route('countries.index')->with([
            'status' => 'delete',
            'title' => __( $this->sectionTitle ),
        ]);
    }
}

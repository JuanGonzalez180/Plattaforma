<?php

namespace App\Http\Controllers\WebControllers\staticcontent;

use App\Models\StaticContent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StaticContentController extends Controller
{
    /**
     * Title sent in notification
     */
    private $sectionTitle = 'Static content';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $staticContents = StaticContent::get();
        return view('staticcontent.index', compact('staticContents'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $staticContent = new StaticContent();
        return view('staticcontent.create', compact('staticContent'));
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
            'title' => ['required'],
            'content' => ['required'],
        ]);
            
        StaticContent::create( $requestValidated );
        return redirect()->route('staticcontent.index')->with([
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
    public function edit(StaticContent $staticContent)
    {
        return view('staticcontent.edit', compact('staticContent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  StaticContent  $staticContent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StaticContent $staticContent)
    {
        $requestValidated = $request->validate([
            'title' => ['required'],
            'content' => ['required'],
        ]);
            
        $staticContent->update( $requestValidated );
        return redirect()->route('staticcontent.index')->with([
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
    public function destroy(StaticContent $staticContent)
    {
        $staticContent->delete();
        return redirect()->route('staticcontent.index')->with([
            'status' => 'delete',
            'title' => __( $this->sectionTitle ),
        ]);
    }
}

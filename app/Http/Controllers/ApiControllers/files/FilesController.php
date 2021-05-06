<?php

namespace App\Http\Controllers\ApiControllers\files;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiControllers\ApiController;

class FilesController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
        // if( $request->hasFile('image') ) {
            // $completeFileName = $request->file('image')->getClientOriginalName();
            // $fileNameOnly = pathinfo($completeFileName, PATHINFO_FILENAME);
            // $extension = $request->file('image')->getClientOriginalExtension();
            // $fileInServer = str_replace(' ', '_', $fileNameOnly) . '-' . rand() . '-' . time() . '.' . $extension;

            // Subir el archivo
            // $request->file('image')->storeAs('public/posts', $fileInServer);
            // $post->image = $fileInServer;
        // }

        // if( $post->save() ) {
        return [
            'status' => true,
            'message' => 'Publicación guardada!'
        ];
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
}

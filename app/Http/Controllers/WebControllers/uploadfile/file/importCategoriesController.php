<?php

namespace App\Http\Controllers\WebControllers\uploadfile\file;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class importCategoriesController extends Controller
{
    public $routeFile           = 'public/';
    public $routeFileBD         = 'temp/';
    public $routeFileTemplate   = 'template/cotegory_txt/';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('importfile.categories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'file_txt' => 'required|mimes:txt'
        ];

        $this->validate($request, $rules);

        $fileName = uniqid() . '.' . $request->file_txt->getClientOriginalExtension();
        $request->file_txt->storeAs($this->routeFile . $this->routeFileBD, $fileName);
        $file_txt       = 'storage/' . $this->routeFileBD . $fileName;


        $handle = fopen($file_txt, "r");
        $grandFather = $i = $father = 0;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $i++;
                if (strpos($line, '## ') !== false) {
                    $father = $i;

                    $lineBetter = trim(str_replace('## ', '', $line));

                    Category::create([
                        'name'          => $lineBetter,
                        'description'   => $lineBetter,
                        'parent_id'     => $grandFather,
                        'status'        => Category::CATEGORY_PUBLISH
                    ]);
                } elseif (strpos($line, '# ') !== false) {
                    $grandFather = $i;

                    $lineBetter = trim(str_replace('# ', '', $line));

                    Category::create([
                        'name'          => strtoupper($lineBetter),
                        'description'   => strtoupper($lineBetter),
                        'status'        => Category::CATEGORY_PUBLISH
                    ]);
                } elseif (strpos($line, '* ') !== false) {

                    $lineBetter = trim(str_replace('* ', '', $line));

                    Category::create([
                        'name'          => $lineBetter,
                        'description'   => $lineBetter,
                        'parent_id'     => $grandFather,
                        'status'        => Category::CATEGORY_PUBLISH
                    ]);
                } else {
                    $i--;
                }
            }
        } else {
        }

        unlink($file_txt);

        return redirect()->route('category.index')->with('success', 'Las categorias se ha registrado con exito');
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

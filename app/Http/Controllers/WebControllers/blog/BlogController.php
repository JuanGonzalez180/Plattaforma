<?php

namespace App\Http\Controllers\WebControllers\blog;

use App\Models\Blog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    public function index($id)
    {
        $blogs = Blog::where('company_id',$id)
            ->orderBy('updated_at','asc')
            ->get();

        return view('blog.index', compact('blogs'));
    }
    
    public function show($id)
    {
        $blog = Blog::find($id);

        $status = array(
            Blog::BLOG_ERASER,
            Blog::BLOG_PUBLISH, 
        );

        return view('blog.show', compact(['blog','status']));
    }

    public function update(Request $request)
    {
        $tenderCompany = Blog::find($request->id);
        $tenderCompany->status = $request->status;
        $tenderCompany->save();

        $message = "Se ha modificado el estado con exito";
        switch ($request->status) {
            case Blog::BLOG_ERASER:
                //
                break;
            case Blog::BLOG_PUBLISH:
                //
                break;
        };

        return response()->json(['message' => $message], 200);
    }
}

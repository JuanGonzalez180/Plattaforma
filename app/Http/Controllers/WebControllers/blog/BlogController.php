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
        return view('blog.show', compact('blog'));
    }
}

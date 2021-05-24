<?php

namespace App\Http\Controllers\ApiControllers\blog;

use JWTAuth;
use App\Models\User;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class BlogController extends ApiController
{
    public $routeFile = 'public/';
    public $routeBlogs = 'images/blogs/';

    public function validateUser(){
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = $this->validateUser();
        $companyID = $user->companyId();

        $blogs = Blog::where('company_id','=',$companyID)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $this->showAllPaginate($blogs);
    }

    public function show($id)
    {
        $blog = Blog::find($id);
        return $blog;
    }

    public function store(Request $request)
    {
        $user = $this->validateUser();
        $companyID = $user->companyId();

        $rules = [
            'name' => 'required',
            'description' => 'required',
            'description_short' => 'required'
        ];

        $this->validate( $request, $rules );

        $blogFields = $request->all();
        $blogFields['name'] = $request->name;
        $blogFields['description_short'] = $request->description_short;
        $blogFields['description'] = $request->description;
        $blogFields['status'] = $request->status ?? Blog::BLOG_ERASER;
        $blogFields['user_id'] = $request['user'] ?? $user->id;
        $blogFields['company_id'] = $companyID;
        
        try{
            $blog = Blog::create( $blogFields );
        }catch(\Throwable $th){
            $errorblog = true;
            DB::rollBack();
            $blogError = [ 'blog' => 'Error, no se ha podido crear el blog' ];
            return $this->errorResponse( $blogError, 500 );
        }

        if($blog){
            if( $request->image ){
                $png_url = "blog-".time().".jpg";
                $img = $request->image;
                $img = substr($img, strpos($img, ",")+1);
                $data = base64_decode($img);
                
                $routeFile = $this->routeBlogs.$blog->id.'/'.$png_url;
                Storage::disk('local')->put( $this->routeFile . $routeFile, $data);
                $blog->image()->create(['url' => $routeFile]);
            }
        }

        DB::commit();

        return $this->showOne($blog,201);
    }

    public function edit($id)
    {
        $blog = Blog::findOrFail($id);
        $blog->image;
        $blog->user;
        $blog->user->image;

        return $this->showOne($blog,200);
    }

    public function update(Request $request, int $id)
    {
        $user = $this->validateUser();

        $rules = [
            'name' => ['required', Rule::unique('blogs')->ignore($id) ],
            'description' => 'required',
            'description_short' => 'required'
        ];

        // var_dump($request);
        
        $this->validate( $request, $rules );

        $blog = Blog::findOrFail($id);

        //Datos
        $blogFields['name'] = $request['name'];
        $blogFields['description_short'] = $request['description_short'];
        $blogFields['description'] = $request['description']; 
        $blogFields['user_id'] = $request['user'] ?? $user->id;
        $blogFields['status'] = $request['status'] ?? Blog::BLOG_ERASER;

        if( $request->image ){
            $png_url = "blog-".time().".jpg";
            $img = $request->image;
            $img = substr($img, strpos($img, ",")+1);
            $data = base64_decode($img);
            $routeFile = $this->routeBlogs.$blog->id.'/'.$png_url;
            
            Storage::disk('local')->put( $this->routeFile . $routeFile, $data);

            if( $blog->image ){
                Storage::disk('local')->delete( $this->routeFile . $blog->image->url );
                $blog->image()->update(['url' => $routeFile ]);
            }else{
                $blog->image()->create(['url' => $routeFile]);
            }
        }

        $blog->update( $blogFields );

        return $this->showOne($blog,200);
    }

    public function destroy(Request $request, int $id)
    {

        $blog = Blog::findOrFail($id);

        if( $blog->image ){
            Blog::disk('local')->delete( $this->routeFile . $blog->image->url );
        }

        $blog->delete();

        return $this->showOneData( ['success' => 'Se ha eliminado correctamente el blog', 'code' => 200 ], 200);
    }
}

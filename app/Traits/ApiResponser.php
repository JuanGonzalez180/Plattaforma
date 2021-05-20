<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

trait ApiResponser{
    private function successResponse($data, $code){
        return response()->json($data, $code);
    }

    protected function errorResponse($message, $code){
        return response()->json(['error' => $message, 'code' => $code], $code);
    }

    protected function showAll(Collection $collection, $code = 200){
        return $this->successResponse(['data'=>$collection], $code);
    }

    protected function showOne(Model $instance, $code = 200){
        return $this->successResponse(['data'=>$instance], $code);
    }

    protected function showOneData($data, $code = 200){
        return $this->successResponse(['data'=>$data], $code);
    }

    protected function showOneTransform(Model $instance, $code = 200){
        $transformer = $instance->transformer;
        $instance = $this->transformData($instance, $transformer);
        return $this->successResponse($instance, $code);
    }

    protected function showAllPaginate(Collection $collection, $code = 200){
        if( $collection->isEmpty() ){
            return $this->successResponse(['data'=>$collection], $code);    
        }

        $transformer = $collection->first()->transformer;

        $collection = $this->paginate($collection);
        $collection = $this->transformData($collection, $transformer);

        return $this->successResponse($collection, $code);
    }

    protected function transformData($data, $transformer){
        $transformation = fractal( $data, new $transformer );
        return $transformation->toArray();
    }

    // Paginate
    protected function paginate( Collection $collection ){
        // 
        $rules = [
            'per_page' => 'integer|min:1|max:50'
        ];
        Validator::validate( request()->all(), $rules );

        $page = LengthAwarePaginator::resolveCurrentPage();
        
        $perPage = 15;
        if( request()->has('per_page') ){
            $perPage = (int)request()->per_page;
        }

        $results = $collection->slice( ($page-1) * $perPage, $perPage )->values();

        $paginated = new LengthAwarePaginator( $results, $collection->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginated->appends( request()->all() );

        return $paginated;
    }
}
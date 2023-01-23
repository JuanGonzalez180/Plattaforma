<?php

namespace App\Http\Controllers\ApiControllers\projects;

use JWTAuth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Image;
use App\Models\Company;
use App\Models\Projects;
use App\Models\MetaData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiControllers\ApiController;

class ProjectsController extends ApiController
{
    public $routeFile = 'public/';
    public $routeProjects = 'images/projects/';

    public function validateUser()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        }
        return $this->user;
    }

    public function index()
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $companyID = $user->companyId();

        if ($companyID && $user->userType() == 'demanda') {
            if ($user->isAdminFrontEnd()) {
                // IS ADMIN
                $projects = Projects::where('company_id', $companyID)
                    ->orderBy('id', 'desc')
                    ->get();
            } else {
                $projects = Projects::where('company_id', $companyID)
                    ->where('user_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->get();
            }

            return $this->showAllPaginate($projects);
        }

        return [];
    }

    public function all()
    {
        $user       = $this->validateUser();
        $companyID  = $user->companyId();
        $projects   = [];

        $date_now   = Carbon::now()->format('Y-m-d');

        // if( $companyID && $user->userType() == 'demanda' ){
        //     if( $user->isAdminFrontEnd() ){
        //         // IS ADMIN
        //         $projects = Projects::where('company_id', $companyID)
        //                             ->orderBy('id', 'desc')
        //                             ->get();
        //     }else{
        //         $projects = Projects::where('company_id', $companyID)
        //                                 ->where('user_id', $user->id)
        //                                 ->orderBy('id', 'desc')
        //                                 ->get();
        //     }
        // }

        $projects = Projects::where('company_id', $companyID)
            ->where('date_start', '<=', $date_now)->where('date_end', '>=', $date_now)
            ->orderBy('id', 'desc')
            ->get();


        $projects->map(function ($item, $key) {
            return $item->status_date = $this->getStatusDate($item->date_start, $item->date_end);
        });



        return $this->showAll($projects);
    }

    public function getStatusDate($date_start, $date_end)
    {
        $date_start = Carbon::parse($date_start);
        $date_end   = Carbon::parse($date_end);
        $date_now   = Carbon::now()->format('Y-m-d');

        $status = "";

        if ($date_now < $date_start) {
            $status = Projects::NOT_STARTED;
        } else if (($date_now >= $date_start) && ($date_now <= $date_end)) {
            $status = Projects::IN_PROGRESS;
        } else if ($date_now > $date_end) {
            $status = Projects::FINALIZED;
        }

        return $status;
    }

    public function store(Request $request)
    {
        $user = $this->validateUser();

        $rules = [
            'name' => 'required',
            'description' => 'min:0|max:255',
            'type' => 'required',
            'status' => 'required',
        ];

        $this->validate($request, $rules);

        // Iniciar Transacción
        DB::beginTransaction();

        // Datos
        $projectFields['name'] = $request['name'];
        // $projectFields['type_projects_id'] = $request['type'];
        $projectFields['user_id'] = $request['user'] ? $request['user'] : $user->id;
        $projectFields['company_id'] = $user->companyId();
        $projectFields['description'] = $request['description'];
        $projectFields['date_start'] = $request['dateStarting'];
        $projectFields['date_end'] = $request['dateEnding'];
        $projectFields['meters'] = $request['meters'];
        $projectFields['status'] = $request['status'];

        try {
            // Crear Project
            $project = Projects::create($projectFields);
        } catch (\Throwable $th) {
            // Si existe algún error al momento de crear el usuario
            $errorProject = true;
            DB::rollBack();
            $projectError = ['project' => 'Error, no se ha podido crear el proyecto'];
            return $this->errorResponse($projectError, 500);
        }

        if ($project) {
            if ($request->type) {
                foreach ($request->type as $key => $typeId) {
                    $project->projectTypeProject()->attach($typeId);
                }
            }

            if ($request->image) {
                $png_url = "project-" . time() . ".jpg";
                $img = $request->image;
                $img = substr($img, strpos($img, ",") + 1);
                $data = base64_decode($img);

                $routeFile = $this->routeProjects . $project->id . '/' . $png_url;
                Storage::disk('local')->put($this->routeFile . $routeFile, $data);
                $project->image()->create(['url' => $routeFile]);
            }

            if ($request->metadata) {
                foreach ($request->metadata as $key => $metadata) {
                    if ($metadata['name']) {
                        $project->metadata()->create(['name' => $metadata['name'], 'value' => $metadata['value']]);
                    }
                }
            }

            $address = null;

            if (!isset($request->address_map) && isset($request->address)) {
                $address = (strtolower($request->address) == strtolower('Panamá') || strtolower($request->address) == strtolower('Panama')) ? 'Ciudad de Panamá, Panamá' : $request->address;
            } else if (isset($request->address_map) && !isset($request->address)) {
                $address = $request->address_map;
            }

            if ($request->latitud || $request->longitud) {
                $project->address()->create([
                    'address'   => $address,
                    'latitud'   => $request->latitud,
                    'longitud'  => $request->longitud
                ]);
            } else {
                // $project->address()->create([
                //     'address'   => 'Panama',
                //     'latitud'   => '9.0360681854164',
                //     'longitud'  => '-79.49169507915'
                // ]);
                $project->address()->create([
                    'address'   => 'Ciudad de Panamá, Panamá',
                    'latitud'   => '8.9814453',
                    'longitud'  => '-79.5188013'
                ]);
            }
        }

        DB::commit();

        return $this->showOne($project, 201);
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
        $user = $this->validateUser();

        $project = Projects::findOrFail($id);
        $project->image;
        $project->projectTypeProject;
        $project->address;
        $project->user;
        $project->user->image;
        return $this->showOne($project, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $idProject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $rules = [
            'name' => 'required',
            'description' => 'min:0|max:255',
            'type' => 'required',
            'status' => 'required',
        ];

        $this->validate($request, $rules);

        // Datos
        $project = Projects::findOrFail($id);

        $projectFields['name'] = $request['name'];
        $projectFields['user_id'] = $request['user'] ? $request['user'] : $user->id;
        $projectFields['description'] = $request['description'];
        $projectFields['date_start'] = $request['dateStarting'];
        $projectFields['date_end'] = $request['dateEnding'];
        $projectFields['meters'] = $request['meters'];
        $projectFields['status'] = $request['status'];

        $project->update($projectFields);

        // Tipos de proyectos
        // Eliminar los anteriores
        foreach ($project->projectTypeProject as $key => $typeProject) {
            $project->projectTypeProject()->detach($typeProject->id);
        }

        // Editar los nuevos
        if ($request->type) {
            foreach ($request->type as $key => $typeId) {
                $project->projectTypeProject()->attach($typeId);
            }
        }

        // Imágenes
        if ($request->image) {
            $png_url = "project-" . time() . ".jpg";
            $img = $request->image;
            $img = substr($img, strpos($img, ",") + 1);
            $data = base64_decode($img);
            $routeFile = $this->routeProjects . $project->id . '/' . $png_url;

            Storage::disk('local')->put($this->routeFile . $routeFile, $data);

            if ($project->image) {
                Storage::disk('local')->delete($this->routeFile . $project->image->url);
                $project->image()->update(['url' => $routeFile]);
            } else {
                $project->image()->create(['url' => $routeFile]);
            }
        }


        $address = $request->address_map;

        if (!isset($request->address_map) && isset($request->address)) {
            $address = (strtolower($request->address) == strtolower('Panamá') || strtolower($request->address) == strtolower('Panama')) ? 'Ciudad de Panamá, Panamá' : $request->address;
        } else if (isset($request->address_map) && !isset($request->address)) {
            $address = $request->address_map;
        }

        // Dirección, Latitud y Longitud
        if ($request->latitud || $request->longitud) {
            $project->address()->update([
                'address' => $address,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud
            ]);
        } else {
            $project->address()->update([
                'address'   => 'Ciudad de Panamá, Panamá',
                'latitud'   => '8.9814453',
                'longitud'  => '-79.5188013'
            ]);
        }
        // if ($request->latitud || $request->longitud) {
        //     if (!$project->address) {
        //         $project->address()->update([
        //             'address' => $request->address,
        //             'latitud' => $request->latitud,
        //             'longitud' => $request->longitud
        //         ]);
        //     } else {
        //         $project->address()->update([
        //             'address' => $request->address,
        //             'latitud' => $request->latitud,
        //             'longitud' => $request->longitud
        //         ]);
        //     }
        // }

        return $this->showOne($project, 200);
    }

    public function changevisible(Request $request, int $id)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $rules = [
            'visible' => 'required'
        ];

        $this->validate($request, $rules);

        // Datos
        $project = Projects::findOrFail($id);
        if ($request->visible == Projects::PROJECTS_VISIBLE) {
            $request->visible = Projects::PROJECTS_VISIBLE_NO;
        } else {
            $request->visible = Projects::PROJECTS_VISIBLE;
        }
        $projectFields['visible'] = $request->visible;
        $project->update($projectFields);

        return $this->showOne($project, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $id)
    {
        // Validamos TOKEN del usuario
        $user = $this->validateUser();

        $project = Projects::find($id);


        // Tipos de errores.
        if(!$project)
        {
            return $this->errorResponse('El proyecto no existe o ha sido eliminado.', 500);
        }else if((($project->tenders)->count() > 0) || (($project->quotes)->count() > 0))
        {
            return $this->errorResponse('No se ha podido eliminar el proyecto, porque tiene cotizaciones y licitaciones en proceso.', 500);
        }
        

        if ($project->image) {
            Storage::disk('local')->delete($this->routeFile . $project->image->url);
            Image::where('imageable_id', $project->id)
                ->where('imageable_type', Projects::class)
                ->delete();
        }

        $project->address()->delete();
        foreach ($project->projectTypeProject as $key => $typeProject) {
            $project->projectTypeProject()->detach($typeProject->id);
        }

        if ($project->files) {
            foreach ($project->files as $key => $file) {
                Storage::disk('local')->delete($this->routeFile . $file->url);
                $file->delete();
            }
        }

        $project->delete();

        return $this->showOneData(['success' => 'Se ha eliminado correctamente el proyecto', 'code' => 200], 200);
    }
}
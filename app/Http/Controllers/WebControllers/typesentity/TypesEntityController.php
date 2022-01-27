<?php

namespace App\Http\Controllers\WebControllers\typesentity;

use DataTables;
use App\Models\Type;
use App\Models\TypesEntity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TypesEntityController extends Controller
{
    /**
     * Title sent in notification
     */
    private $sectionTitle = 'Entity type';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $typesEntity    = TypesEntity::get();
        $types          = Type::orderBy('name', 'asc')->get();

        return view('typesentity.index', compact('typesEntity', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $typeOptions = Type::get();
        $typeEntity = new TypesEntity();
        return view('typesentity.create', compact('typeOptions', 'typeEntity'));
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
            'type_id' => ['required'],
        ]);

        TypesEntity::create($requestValidated);
        return redirect()->route('typesentity.index')->with([
            'status' => 'create',
            'title' => __($this->sectionTitle),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(TypesEntity $typeEntity)
    {
        $typeOptions    = Type::get();
        $status         = [TypesEntity::ENTITY_ERASER, TypesEntity::ENTITY_PUBLISH];
        return view('typesentity.edit', compact('typeOptions', 'typeEntity', 'status'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TypesEntity $typeEntity)
    {
        $requestValidated = $request->validate([
            'name'      => ['required'],
            'type_id'   => ['required'],
            'status'    => ['required']
        ]);

        $typeEntity->name       = $requestValidated["name"];
        $typeEntity->type_id    = $requestValidated["type_id"];
        $typeEntity->status     = $requestValidated["status"];

        $typeEntity->save();

        return redirect()->route('typesentity.index')->with([
            'status' => 'edit',
            'title' => __($this->sectionTitle),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TypesEntity $typeEntity)
    {
        $typeEntity->delete();
        return redirect()->route('typesentity.index')->with([
            'status' => 'delete',
            'title' => __($this->sectionTitle),
        ]);
    }

    public function getTypeEntityType(Request $request)
    {
        $type       = $request->type_id;
        $typeEntity = TypesEntity::select('types_entities.*');

        if ($type != 'all') {
            $typeEntity = $typeEntity->where('type_id', $type);
        }

        $typeEntity = $typeEntity->orderBy('updated_at', 'desc');

        return DataTables::of($typeEntity)
            ->addColumn('type', function (TypesEntity $value) {
                return $value->type->renameType();
            })
            ->editColumn('status', function (TypesEntity $value) {
                switch ($value->status) {
                    case TypesEntity::ENTITY_ERASER:
                        $status =  "<span class='badge badge-pill badge-light'><i class='fas fa-eraser'></i>  Borrador</span>";
                        break;
                    case TypesEntity::ENTITY_PUBLISH:
                        $status =  "<span class='badge badge-success'><i class='fas fa-check'></i>  Publicado</span>";
                        break;
                    default:
                        $status =  "Sin definir";
                        break;
                }
                return $status;
            })
            ->addColumn('action', function (TypesEntity $value) {
                return "<a type='button' href='".route( 'typesentity.edit', $value )."' class='btn btn-dark btn-sm'><i class='fas fa-pencil-alt'></i></a>";
            })
            ->rawColumns(['action', 'type', 'status'])
            ->toJson();
    }
}

<?php

namespace App\Http\Controllers\WebControllers\publicity\imagesadvertisingplan;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\ImagesAdvertisingPlans;

class ImagesAdvertisingPlansController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans = ImagesAdvertisingPlans::all();
        return view('publicity.imagesadvertisingplan.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $plan   = new ImagesAdvertisingPlans();
        $types  = [
            ImagesAdvertisingPlans::DESK_TYPE,
            ImagesAdvertisingPlans::TABLET_TYPE,
            ImagesAdvertisingPlans::MOBILE_TYPE
        ];

        return view('publicity.imagesadvertisingplan.create', compact('plan', 'types'));
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
            'name'  => ['required', 'unique:images_advertising_plans,name'],
            'width' => 'required|integer|min:0',
            'high'  => 'required|integer|min:0'
        ];

        $this->validate($request, $rules);

        $fields['name']     = ucwords($request->name);
        $fields['width']    = $request->width;
        $fields['high']     = $request->high;
        $fields['type']     = $request->type;


        $plan = ImagesAdvertisingPlans::create($fields);

        return redirect()->route('img_publicity_plan.index')->with('success', 'El plan de imagenes se ha creado satisfactoriamente');
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
        $plan = ImagesAdvertisingPlans::find($id);
        $types  = [
            ImagesAdvertisingPlans::DESK_TYPE,
            ImagesAdvertisingPlans::TABLET_TYPE,
            ImagesAdvertisingPlans::MOBILE_TYPE
        ];

        return view('publicity.imagesadvertisingplan.edit', compact('plan', 'types'));
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
        $rules = [
            'name'  => ['required', Rule::unique('images_advertising_plans')->ignore($id)],
            'width' => 'required|integer|min:0',
            'high'  => 'required|integer|min:0'
        ];
        
        $this->validate($request, $rules);

        $plan_img = ImagesAdvertisingPlans::find($id);

        $plan_img->name     = ucwords($request->name);
        $plan_img->width    = $request->width;
        $plan_img->high     = $request->high;
        $plan_img->type     = $request->type;

        $plan_img->save();

        return redirect()->route('img_publicity_plan.index')->with('success', 'El plan de imagenes se ha editado satisfactoriamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $plan   = ImagesAdvertisingPlans::find($id);
            $plan->delete();
        } catch (\Exception $e) {
            return redirect()->route('img_publicity_plan.index')->with('error', 'No se ha podido eliminar el plan de imagenes.');
        }
        
        return redirect()->route('img_publicity_plan.index')->with('success', 'Se ha eliminado del plan de imagenes satisfactoriamente');
    }
}

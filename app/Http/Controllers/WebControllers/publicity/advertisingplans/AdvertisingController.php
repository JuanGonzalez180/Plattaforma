<?php

namespace App\Http\Controllers\WebControllers\publicity\advertisingplans;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\AdvertisingPlans;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ImagesAdvertisingPlans;
use App\Models\AdvertisingPlansImages;
use Illuminate\Support\Facades\Storage;
use TaylorNetwork\UsernameGenerator\Generator;

class AdvertisingController extends Controller
{
    public $routeFile = 'public/';
    public $routeFileBD = 'images/publicity_plan/';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans = AdvertisingPlans::all();
        return view('publicity.advertisingplans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $plan = new AdvertisingPlans();
        $type_ubications = [AdvertisingPlans::RECTANGLE_TYPE, AdvertisingPlans::SQUARE_TYPE];

        $imagesPlans = ImagesAdvertisingPlans::all();
        return view('publicity.advertisingplans.create', compact('plan', 'type_ubications', 'imagesPlans'));
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
            'name' => ['required', 'unique:advertising_plans,name'],
            'description' => 'required',
            'days' => 'required|numeric',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'required|numeric'
        ];

        $this->validate($request, $rules);

        $fields['name']             = ucwords($request->name);
        $fields['type_ubication']   = $request->type_ubication;
        $fields['description']      = $request->description;
        $fields['days']             = $request->days;
        $fields['price']            = $request->price;

        $plan = AdvertisingPlans::create($fields);

        if ($request->img_plan) {
            foreach ($request->img_plan as $id_img_plan) {
                AdvertisingPlansImages::create([
                    'advertising_plans_id'          => $plan->id,
                    'images_advertising_plans_id'   => $id_img_plan
                ]);
            }
        }

        $generator = new Generator();

        if ($request->image) {
            $imageName = $generator->generate($request->name);
            $imageName = $imageName . '-' . uniqid() . '.' . $request->image->extension();
            $request->image->storeAs($this->routeFile . $this->routeFileBD, $imageName);

            $plan->image()->create(['url' => $this->routeFileBD . $imageName]);
        }

        return redirect()->route('publicity_plan.index')->with('success', 'El plan se ha creado satisfactoriamente');
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
        $plan = AdvertisingPlans::find($id);
        $type_ubications = [AdvertisingPlans::RECTANGLE_TYPE, AdvertisingPlans::SQUARE_TYPE];

        return view('publicity.advertisingplans.edit', compact('plan', 'type_ubications'));
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
            'name' => ['required', Rule::unique('advertising_plans')->ignore($id)],
            'description' => 'required',
            'days' => 'required|numeric',
            'price' => 'required|numeric'
        ];

        $this->validate($request, $rules);

        $fields['name']         = ucwords($request->name);
        $fields['description']  = $request->description;
        $fields['days']         = $request->days;
        $fields['price']        = $request->price;

        $plan = AdvertisingPlans::findOrFail($id);
        $plan->update($fields);

        return redirect()->route('publicity_plan.index')->with('success', 'El plan se ha creado satisfactoriamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        
        $plan = AdvertisingPlans::find($id);
        //elimina los planes publicidad con
        AdvertisingPlansImages::where('advertising_plans_id',$id)->delete();
        //elimina la imagen de muestra
        if($plan->image){

            if(Storage::disk('local')->exists($this->routeFile . $plan->image->url)){
                Storage::disk('local')->delete($this->routeFile . $plan->image->url);
                $plan->image->delete();
            }

            DB::table('images')->where('url',$plan->image->url)
            ->where('imageable_id',$plan->image->imageable_id)
            ->where('imageable_type',$plan->image->imageable_type)
            ->where('size',$plan->image->size)
            ->delete();
        }

        $plan->delete();

        $plans = AdvertisingPlans::all();
        return view('publicity.advertisingplans.index', compact('plans'));
    }
}

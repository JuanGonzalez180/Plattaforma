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


        $imagesAdvertisingPlans = $this->getImagesAdvertisingPlans();

        foreach ($imagesAdvertisingPlans as $value) {
            AdvertisingPlansImages::create([
                'advertising_plans_id'          => $plan->id,
                'images_advertising_plans_id'   => $value->id,
                'status'                        => in_array($value->id, $request->img_plan) ? AdvertisingPlansImages::ADVER_PLAN_IMAGE_PUBLISH : AdvertisingPlansImages::ADVER_PLAN_IMAGE_ERASER
            ]);
        }

        // if ($request->img_plan) {
        //     foreach ($request->img_plan as $id_img_plan) {
        //         AdvertisingPlansImages::create([
        //             'advertising_plans_id'          => $plan->id,
        //             'images_advertising_plans_id'   => $id_img_plan
        //         ]);
        //     }
        // }

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
        $plan                   = AdvertisingPlans::find($id);

        $adPlanImagesEnabled = [];
        foreach($plan->advertisingPlansImages as $value)
        {
            if($value->status == AdvertisingPlansImages::ADVER_PLAN_IMAGE_PUBLISH)
                $adPlanImagesEnabled[] = $value->imagesAdvertisingPlans->id;
        }

        $imagesPlans            = ImagesAdvertisingPlans::all();
        $type_ubications        = [AdvertisingPlans::RECTANGLE_TYPE, AdvertisingPlans::SQUARE_TYPE];
        $imagesPlansRegister    =  AdvertisingPlansImages::where('advertising_plans_id', $id)
            ->pluck('images_advertising_plans_id');

        return view('publicity.advertisingplans.edit', compact('plan', 'type_ubications', 'imagesPlans', 'imagesPlansRegister', 'adPlanImagesEnabled'));
    }

    public function getImagesAdvertisingPlans(){
        return ImagesAdvertisingPlans::all();
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
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'required|numeric'
        ];

        $this->validate($request, $rules);

        $plan = AdvertisingPlans::find($id);


        $plan->name             = $request->name;
        $plan->description      = $request->description;
        $plan->type_ubication   = $request->type_ubication;
        $plan->days             = $request->days;
        $plan->price            = $request->price;

        $plan->save();

        // var_dump($plan->id);
        

        $generator = new Generator();
        if ($request->image) {
            $imageName  = $generator->generate($request->name);
            $imageName  = $imageName . '-' . uniqid() . '.' . $request->image->extension();

            if ($plan->image) {
                Storage::disk('local')->delete($this->routeFile . $plan->image->url);
                $plan->image()->update(['url' => $this->routeFileBD . $imageName]);
            } else {
                $plan->image()->create(['url' => $this->routeFileBD . $imageName]);
            }
            $request->image->storeAs($this->routeFile . $this->routeFileBD, $imageName);
            $plan->save();
        }

        $img_plan_request       = $request->img_plan;

        if(isset($request->img_plan))
        {
            AdvertisingPlansImages::where('advertising_plans_id', $id)
                ->update(['status' => AdvertisingPlansImages::ADVER_PLAN_IMAGE_ERASER]);
    
            AdvertisingPlansImages::where('advertising_plans_id', $id)->whereIn('images_advertising_plans_id', $img_plan_request)
                ->update(['status' => AdvertisingPlansImages::ADVER_PLAN_IMAGE_PUBLISH]);
        }

        

        return redirect()->route('publicity_plan.index')->with('success', 'El plan se ha editado satisfactoriamente');
    }

    public function getPlansImagesID($plan_id){
        $ids = AdvertisingPlansImages::where('advertising_plans_id',$plan_id)
            ->pluck('images_advertising_plans_id');

        return $ids;
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
        AdvertisingPlansImages::where('advertising_plans_id', $id)->delete();
        //elimina la imagen de muestra
        if ($plan->image) {

            if (Storage::disk('local')->exists($this->routeFile . $plan->image->url)) {
                Storage::disk('local')->delete($this->routeFile . $plan->image->url);
            }

            DB::table('images')->where('url', $plan->image->url)
                ->where('imageable_id', $plan->image->imageable_id)
                ->where('imageable_type', $plan->image->imageable_type)
                ->where('size', $plan->image->size)
                ->delete();
        }

        $plan->delete();

        return redirect()->route('publicity_plan.index')->with('success', 'El plan se ha eliminado satisfactoriamente');
    }
}

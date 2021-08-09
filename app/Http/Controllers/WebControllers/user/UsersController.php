<?php

namespace App\Http\Controllers\WebControllers\user;

use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Models\User;
use App\Models\Company;
use App\Mail\ValidatedAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    /**
     * Title sent in notification
     */
    private $sectionTitle = 'Usuario';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::query()->paginate(15);
        return view('user.index', compact('users'));
    }

    public function approve(Request $request){
        $user = User::findOrFail($request->id);
        
        // Obtenemos la Compañia 
        $company = $user->company->first();
        // Cambiamos el estado de la compañia
        $company->status = Company::COMPANY_APPROVED;
        $company->save();

        // Enviamos mensaje al correo del usuario
        Mail::to($user->email)->send(new ValidatedAccount($user));

        return ($company->type_company() == 'Oferta') 
        ? redirect()->route('companies-type', 'Oferta')->with([
            'status' => 'edit',
            'title' => __( $this->sectionTitle ),
        ])
        : redirect()->route('companies-type', 'Demanda')->with([
            'status' => 'edit',
            'title' => __( $this->sectionTitle ),
        ]) ;
    }

    public function disapproved(Request $request){
        $user    = User::findOrFail($request->id);
        // Obtenemos la Compañia 
        $company = $user->company->first();
        // Cambiamos el estado de la compañia
        $company->status = Company::COMPANY_REJECTED;
        $company->save();

        return ($company->type_company() == 'Oferta') 
        ? redirect()->route('companies-type', 'Oferta')->with([
            'status' => 'edit',
            'title' => __( $this->sectionTitle ),
        ])
        : redirect()->route('companies-type', 'Demanda')->with([
            'status' => 'edit',
            'title' => __( $this->sectionTitle ),
        ]) ;
    }



    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('user.edit', compact('user'));
    }
}

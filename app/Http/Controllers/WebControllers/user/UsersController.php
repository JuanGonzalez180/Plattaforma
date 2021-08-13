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

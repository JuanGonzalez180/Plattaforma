<?php

namespace App\Http\Controllers\WebControllers\scripts;

use Newsletter;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class uploadEmailsToMailchimp extends Controller
{
    function registerAllEmails()
    {
        $users = User::where('id', '<>', 1)->get();

        foreach ($users as $user) {

            // if (!Newsletter::isSubscribed($user->email)) {
                $tags = [];
                $tags[] = $user->userType();
                $tags[] = $user->getAdminUser() ? 'administrador' : 'integrate del equipo';
                $tags[] = $user->companyName();

                Newsletter::subscribe($user->email, ['FNAME' => ucfirst($user->name), 'LNAME' => ucfirst(ucfirst($user->lastname))], 'subscribers', ['tags' => $tags]);
            // }
        }

        var_dump('Se registraron todos los correos');
    }

    function deleteAllEmails()
    {
        $users = User::where('id', '<>', 1)->get();

        foreach ($users as $user)
        {
            if(Newsletter::isSubscribed($user->email))
            Newsletter::delete('lcortese@altius.com.pa');
        }

        var_dump('Se borraron todos los correos');
    }

    function disabledEmails()
    {
        $companies = Company::where('status','<>','Aprobado')->get();

        foreach ($companies as $company)
        {
            $emails = $company->emails();

            foreach($emails as $email)
            {
                Newsletter::unsubscribe($email, 'subscribers');
            }
        }
    }
      
}

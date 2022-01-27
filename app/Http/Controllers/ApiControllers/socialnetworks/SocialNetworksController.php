<?php

namespace App\Http\Controllers\ApiControllers\socialnetworks;

use App\Models\SocialNetworks;
use App\Http\Controllers\ApiControllers\ApiController;

class SocialNetworksController extends ApiController
{
    /**
     * Handle the incoming request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $socialnetworks = SocialNetworks::all();
        foreach($socialnetworks as $socialnetwork){
            $socialnetwork->image;
        }
        return $this->showAll($socialnetworks);
    }
}

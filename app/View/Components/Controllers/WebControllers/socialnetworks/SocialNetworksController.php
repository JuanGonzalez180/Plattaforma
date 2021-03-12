<?php

namespace App\View\Components\Controllers\WebControllers\socialnetworks;

use Illuminate\View\Component;

class SocialNetworksController extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.controllers.web-controllers.socialnetworks.social-networks-controller');
    }
}

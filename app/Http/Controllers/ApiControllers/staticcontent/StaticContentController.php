<?php

namespace App\Http\Controllers\ApiControllers\staticcontent;

use App\Models\StaticContent;
use App\Http\Controllers\ApiControllers\ApiController;

class StaticContentController extends ApiController
{
    /**
     * Handle the incoming request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(string $slug)
    {
        $staticContent = StaticContent::where('slug', $slug)->first();
        return $this->showOne($staticContent, 200);
    }
}

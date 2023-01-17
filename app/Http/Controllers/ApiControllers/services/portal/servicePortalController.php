<?php

namespace App\Http\Controllers\ApiControllers\services\portal;

use App\Models\Quotes;
use App\Models\Tenders;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiControllers\ApiController;

class servicePortalController extends ApiController
{
    public function __invoke(Request $request)
    {
        $model  = $request->model;
        $id     = $request->id;

        $value = ($model == 'tender') ? $this->getTender($id) : $this->getQuote($id);

        return $value;
    }

    public function getTender($id)
    {
        $tender = Tenders::find($id);

        if(is_null($tender))
        {
            return null;
        }

        return $tender->UserParticipateTender();
    }

    public function getQuote($id)
    {
        $quote = Quotes::find($id);

        if(is_null($quote))
        {
            return null;
        }

        return $quote->UserParticipateQuote();
    }
}

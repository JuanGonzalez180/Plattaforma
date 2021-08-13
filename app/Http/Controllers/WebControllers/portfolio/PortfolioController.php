<?php

namespace App\Http\Controllers\WebControllers\portfolio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Portfolio;

class PortfolioController extends Controller
{
    public function index($id)
    {
        $portfolios = Portfolio::where('company_id',$id)
            ->orderBy('updated_at','asc')
            ->get();

        return view('portfolio.index', compact('portfolios'));
    }
}

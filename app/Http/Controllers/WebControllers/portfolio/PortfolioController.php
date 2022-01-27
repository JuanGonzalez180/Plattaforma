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
    
    public function show($id)
    {
        $portfolio = Portfolio::find($id);
        $status = [
            Portfolio::PORTFOLIO_ERASER,
            Portfolio::PORTFOLIO_PUBLISH
        ];

        return view('portfolio.show', compact(['portfolio','status']));
    }

    public function update(Request $request)
    {
        $tenderCompany = Portfolio::find($request->id);
        $tenderCompany->status = $request->status;
        $tenderCompany->save();

        $message = "Se ha modificado el estado con exito";
        switch ($request->status) {
            case Portfolio::PORTFOLIO_ERASER:
                //
                break;
            case Portfolio::PORTFOLIO_PUBLISH:
                //
                break;
        };

        return response()->json(['message' => $message], 200);
    }

}

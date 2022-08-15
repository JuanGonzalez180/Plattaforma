<?php

namespace App\Http\Controllers\WebControllers\querywall;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QueryWall;
use App\Models\Tenders;
use App\Models\Quotes;
use App\Models\Projects;

class QueryWallController extends Controller
{
    public function index($id)
    {
        $queryWalls = QueryWall::where('querysable_type', Tenders::class)
            ->where('querysable_id', $id)
            ->where('type', '<>', QueryWall::TYPE_GLOBALMESSAGE)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('querywall.index', compact('queryWalls'));
    }

    public function index_quotes($id)
    {
        $queryWalls = QueryWall::where('querysable_type', Quotes::class)
            ->where('querysable_id', $id)
            ->where('type', '<>', QueryWall::TYPE_GLOBALMESSAGE)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('querywall.index_quote', compact('queryWalls'));
    }

    public function editVisible(Request $request)
    {
        $queryWall  = QueryWall::find($request->id);
        $visible    = ($queryWall->visible == QueryWall::QUERYWALL_VISIBLE) ? QueryWall::QUERYWALL_VISIBLE_NO : QueryWall::QUERYWALL_VISIBLE;
        $queryWall->visible = $visible;
        $queryWall->save();

        $route = '';
        switch ($queryWall->querysable_type) {
            case Quotes::class:
                $route = 'query.quotes.class.id';
                break;
            case Tenders::class:
                $route = 'query.class.id';
            default:
                $route = 'query.class.id';
                break;
        }

        return redirect()->route($route, $queryWall->querysable->id)
            ->with('success', 'Se ha editado el la consulta');
    }
}

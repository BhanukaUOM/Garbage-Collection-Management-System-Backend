<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\PointType;
use App\Request as Requests;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(!Auth::user()->hasPermissionTo('View Points'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
        return response()->json(DB::select("SELECT requests.user_id, sum(point_type_request.weight*point_types.point) as Points From point_type_request, requests, point_types WHERE point_type_request.request_id = requests.id and point_type_request.point_type_id=point_types.id GROUP BY requests.user_id;"), 200);
    }

    public function leaderboard(){
        if(!Auth::user()->hasPermissionTo('View Points'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
        return response()->json(DB::select("SELECT requests.user_id, sum(point_type_request.weight*point_types.point) as Points From point_type_request, requests, point_types WHERE point_type_request.request_id = requests.id and point_type_request.point_type_id=point_types.id GROUP BY requests.user_id ORDER BY sum(point_type_request.weight*point_types.point) ASC LIMIT 10;"), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Point  $point
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!Auth::user()->hasPermissionTo('Add Points'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
        $request->validate([
            'point' => 'required|numeric',
            'name' => 'required|string',
            'description' => 'string'
        ]);
        return json_encode(Requests::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(!Auth::user()->hasPermissionTo('View Points'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
            
        $res = DB::select("SELECT point_types.name, sum(point_type_request.weight*point_types.point) as Points From point_type_request, requests, point_types WHERE point_type_request.request_id = requests.id and requests.user_id=".$id." and point_type_request.point_type_id=point_types.id GROUP BY point_types.name;");
        return json_encode($res);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Point  $point
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(!Auth::user()->hasPermissionTo('Edit Points'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
        $rules = [
            'name' => 'string',
            'point' => 'numeric',
            'description' => 'string'
        ];

        $this->validate($request, $rules);

        $point = Requests::findOrFail($id);
        $point->update($request->all());
        return response()->json(['data' => $point], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!Auth::user()->hasPermissionTo('Delete Points'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
        $point = Requests::findOrFail($id);
        $point->delete();
        return response()->json(['data' => $point], 200);
    }
}

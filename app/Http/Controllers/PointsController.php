<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\PointType;

use Illuminate\Http\Request;

class PointsController extends Controller
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
        return response()->json(PointType::all(), 200);
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
        return json_encode(PointType::create($request->all()));
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
        return json_encode(PointType::findOrFail($id));
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

        $point = PointType::findOrFail($id);
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
        $point = PointType::findOrFail($id);
        $point->delete();
        return response()->json(['data' => $point], 200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Request as Requests;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(!Auth::user()->hasPermissionTo('View Requests'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
        return response()->json(Requests::with('points')->get(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!Auth::user()->hasPermissionTo('Add Requests'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
        $request->validate([
            'user_id' => 'required|numeric',
            'collector_id' => 'numeric'
        ]);
        return response()->json(Requests::create($request->all()), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(!Auth::user()->hasPermissionTo('View Requests'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
        return response()->json(Requests::findOrFail($id)->with('points')->get(), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(!Auth::user()->hasPermissionTo('Edit Requests'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
        $rules = [
            'user_id' => 'numeric',
            'collector_id' => 'numeric'
        ];

        $this->validate($request, $rules);

        $requests = Requests::findOrFail($id);
        $requests->update($request->all());
        return response()->json(['data' => $requests], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!Auth::user()->hasPermissionTo('Delete Requests'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
        $request = Requests::findOrFail($id);
        $request->delete();
        return response()->json(['data' => $request], 200);
    }

    public function currentUser()
    {
        if(!Auth::user()->hasPermissionTo('View Requests'))
            return response()->json([ "message" => 'User do not have Permission'], 401);
        $request = Auth::user()->requests()->with('points')->get();
        return response()->json(['data' => $request], 200);
    }

    public function approve(Request $request)
    {
        if(!Auth::user()->hasPermissionTo('Approve Requests'))
            return response()->json([ "message" => 'User do not have Permission'], 401);

        $requests = Requests::findOrFail($request->id);
        if($requests['isApproved'])
            return response()->json(['error' => 'Already Approved'], 403);
        $requests->isApproved = true;
        $requests->collector_id = Auth::user()->id;
        $requests->save();
        return response()->json(['data' => $requests], 201);
    }

    public function pickup(Request $request){
        if(!Auth::user()->hasPermissionTo('Approve Requests'))
            return response()->json([ "message" => 'User do not have Permission'], 401);

        if(is_int($request->id))
            $request['id'] = intval($request->id);
        $requests = Requests::findOrFail($request->id);
        $requests->isApproved = true;
        if($requests['pickedUp'] && $requests['pickedUp']!='0000-00-00 00:00:00')
            return response()->json(['error' => 'Already Pickedup'], 403);
        $requests->pickedUp = Carbon::now()->toDateTimeString();
        $requests->save();
        // foreach($request->garbage_type as $key => $value){
        //     DB::select("INSERT INTO point_type_request (request_id, point_type_id, weight, user_id) VALUES(".$requests->id.",".intval($key).", ".$value.", ".$requests->user_id.");");
        // }
        for($i=1; $i<=4; $i++){
            $key = strval($i);
            if($request->$key){
                $value = $request->$key;
                DB::select("INSERT INTO point_type_request (request_id, point_type_id, weight, user_id) VALUES(".$requests->id.",".intval($key).", ".$value.", ".$requests->user_id.");");
            }
        }
        return response()->json(['data' => $requests], 201);
    }

    public function unapprovedRequests(Request $request)
    {
        if(!Auth::user()->hasPermissionTo('View Requests'))
            return response()->json([ "message" => 'User do not have Permission'], 401);

        $requests = Requests::where('isApproved', false)->with('points')->with('user')->get();
        return response()->json(['data' => $requests], 201);
    }
}

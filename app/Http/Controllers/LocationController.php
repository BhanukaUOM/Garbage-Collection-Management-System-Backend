<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\Location;
use App\Guardian;
use App\Collector;

class LocationController extends Controller
{
    //Save Student Location through Mobile App
    public function add(Request $request){
        if(Auth::user()->hasPermissionTo("Add Location")){
            $request->validate([
                'timestamp' => 'required',
                'latitude' => 'required',
                'longitude' => 'required'
            ]);

            $user_id = Auth::user()->id;

            if(Location::where('user_id', $user_id)->exists())
                $location = Location::where('user_id', $user_id)->first();
            else{
                $location = new Location;
                $location->user_id = Auth::user()->id;
            }

            $location->timestamp = Carbon::createFromTimestamp($request->timestamp/1000 + 19800)->toDateTimeString();
            $location->latitude = $request->latitude;
            $location->longitude = $request->longitude;
            if($request->accuracy)
                $location->accuracy = $request->accuracy;
            if($request->altitude)
                $location->altitude = $request->altitude;
            if($request->altitudeAccuracy)
                $location->altitudeAccuracy = $request->altitudeAccuracy;
            if($request->heading)
                $location->heading = $request->heading;
            if($request->speed)
                $location->speed = $request->speed;
            $location->save();

            return response()->json(['status' => true]);
        } else {
            return response()->json(["error" => ['message' => "You don't have permission"]], 401);
        }
    }

    public function get($id){
        if(Auth::user()->hasPermissionTo("View Location")){
            return response()->json(Location::where("user_id", $id)->first());
        } else {
            return response()->json(["error" => ['message' => "You don't have permission"]], 401);
        }
    }

    public function getCurrent(){
        if(Auth::user()->hasPermissionTo("View Location")){
            return response()->json(Location::where('user_id', Auth::user()->id)->first());
        } else {
            return response()->json(["error" => ['message' => "You don't have permission"]], 401);
        }
    }

    //Get All Location
    public function index(Request $request){
        if(Auth::user()->hasPermissionTo("View Location")){
            $user_id = Auth::user()->id;
            $user = User::with('location')->all();
            return response()->json($user, 200);
        } else {
            return response()->json(["error" => ['message' => "You don't have permission"]], 401);
        }
    }

    public function allCollectors(Request $request){
        if(Auth::user()->hasPermissionTo("View Location")){
            $user = User::with('location')->Role('Collector')->get();
            return response()->json($user, 200);
        } else {
            return response()->json(["error" => ['message' => "You don't have permission"]], 401);
        }
    }

    public function allCustomers(Request $request){
        if(Auth::user()->hasPermissionTo("View Location")){
            $user = User::with('location')->Role('Customer')->get();
            return response()->json($user, 200);
        } else {
            return response()->json(["error" => ['message' => "You don't have permission"]], 401);
        }
    }

    
}

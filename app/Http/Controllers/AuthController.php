<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\Notifications\SignupActivate;
use Avatar;
use Storage;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */

    public function fileupload($file, $id){
        // Get filename with the extension
        $filenameWithExt = $file->getClientOriginalName();
        // Get just filename
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        // Get just ext
        $extension = $file->getClientOriginalExtension();
        // Filename to store
        $fileNameToStore= $filename.'_'.time().'.'.$extension;
        // Upload Image
        $path = $file->storeAs('avatars/'.$id ,'avatar.png');
        $file->move('img/avatars/'.$id , $fileNameToStore);
        return 'img/avatars/'.$id . '/'. $fileNameToStore;
    }

    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'phone' => 'required|unique:users|digits:10'
        ]);
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'activation_token' => strval(rand(100000, 999999)),
            'phone' => $request->phone
        ]);
        
        $avatar = null;
        if($request->phone)
            $user -> phone = $request->phone;
        $user->save();
        if($request->hasFile('img')){
            $avatar= $this->fileupload($request->file('img'), $user->id);
            $user->avatar = $avatar;
            $user->save();
        } else {
            $avatar = Avatar::create(strtoupper($user->name))->getImageObject()->encode('png');
            Storage::put('avatars/'.$user->id.'/avatar.png', (string) $avatar); 
            $user->avatar = 'storage/avatars/'. $user->id .'/avatar.png';
            $user->save(); 
        }      
        $user->assignRole('Customer');
        
        $user->notify(new SignupActivate($user));
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = request(['email', 'password']);
        //$credentials['active'] = true;
        //$credentials['deleted_at'] = null;
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Invalid Username or Password'
            ], 401);
        
        $credentials['active'] = true;
        $credentials['deleted_at'] = null;
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Account not Activated Yet'
            ], 406);
        $user = $request->user();
        if($user->hasRole('Student'))
            $res = User::with(['roles', 'student'])->find($user->id);
        else if($user->hasRole('Parent'))
            $res = User::with(['roles', 'parent', 'parent.student', 'parent.student.user'])->find($user->id);
        else
            $res = User::with('roles')->find($user->id);
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(13);
        $token->save();
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
            'user' => response()->json($res)->original
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        $user = User::with('roles')->findOrFail($request->user()->id);
        return response()->json($user);
    }

    public function signupActivate($token)
    {
        $user = User::where('activation_token', $token)->first();
        if (!$user) {
            return response()->json([
                'message' => 'This activation token is invalid.'
            ], 404);
        }
        $user->active = true;
        $user->activation_token = '';
        $user->email_verified_at = Carbon::now()->toDateTimeString();
        $user->save();
        return $user;
    }
}

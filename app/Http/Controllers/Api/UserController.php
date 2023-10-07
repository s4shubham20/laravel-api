<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Mail;
use App\Mail\Sendotp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\HasApiTokens;
class UserController extends Controller
{
    protected function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' =>  'required|email|unique:users',
            'password' => 'required|min:6|max:15',
            'password_confirmation' => 'required|same:password',
        ]);
        if($validator->fails()){
            return response()->json([
                'error'=>$validator->errors(),
                // 'Unauthorized'  => 401
            ]);
        }
        $sendotp = rand(100000,999999);
        $otp = [
            'otp' => $sendotp,
        ];

        Mail::to('shubhameglobal20@gmail.com')->send(new Sendotp($otp));
        $user               =       new User;
        $user->otp          =       $sendotp;
        $user->name         =       $request->name;
        $user->email        =       $request->email;
        $user->otp_expire_at    =       Carbon::now()->addMinutes(10);
        $user->password     =       Hash::make($request->password);
        $user->save();
        return response()->json([
            'success' => 'You have successfully signed in !',
            'user' => $user
        ], 201);
    }

    protected function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     =>      'required|email',
            'password'  =>      'required'
        ]);

        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 422);
        }

       $user = User::where('email',$request->email)->first();
        if(!$user){
            return response()->json(
                ['error'     => ['email' => 'This email doesn\'t exist']], 401);
        }else{
            if(Hash::check($request->password, $user->password)){
                $token=  [
                        'token' => $user->createToken('ApiToken')->plainTextToken,
                        'type'  => 'bearer'
                    ];
                $message = 'You have logged in successfully';
                return response()->json(['message' => $message, 'authorization' => $token], 200);
            }else{
                return response()->json(['error' => ['password' => 'Password is incorrect']], 401);
            }
        }
    }
    protected function logout()
    {
        try {
            if(Auth::check()){
                Auth::user()->tokens()->delete();
                return response()->json(['message' => 'User successfully signed out']);
            }
            //code...
        } catch (\Throwable $th) {
            return response()->json(['message' => 'User already signed out']);
        }
    }

    public function userdetails()
    {
        if(Auth::check()){
            $user = Auth::user();
            return response()->json(['user' =>  $user], 200);
        }else{
            return response()->json(['message' =>  'You are logged in'], 401);
        }
    }

    public function verifyOtp(Request $request)
    {
        if(Auth::check()){
            $now = Carbon::now();
            $user = Auth::user();
            $user = User::findOrFail($user->id);
            if($now->isBefore($user->otp_expire_at)){
                if($user->otp == $request->otp){
                    $user->email_verified_at = $now;
                    $user->save();
                    return response()->json([
                        'message' => 'Your otp verification has been successfully done!',
                    ]);
                }else{
                    return response()->json([
                        'message' => 'Otp didn\'t match !',
                    ]);
                }
            }else{
                return response()->json([
                    'message' => 'Otp has been expired !',
                ]);
            }
        }
    }

    public function generateotp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     =>      'required|email',
        ]);


        if($validator->fails()){
            return response()->json([
                'error'=>$validator->errors(),
            ], 401);
        }

        $sendotp = rand(100000,999999);
        $otp = [
            'otp' => $sendotp,
        ];

        Mail::to('shubhameglobal20@gmail.com')->send(new Sendotp($otp));
        $user                   =       User::where('email', $request->email)->firstOrFail();
        $user->otp              =       $sendotp;
        $user->otp_expire_at    =       Carbon::now()->addMinutes(10);
        $user->save();
        return response()->json([
            'message'   => 'Otp has been resent !',
        ]);
    }
}

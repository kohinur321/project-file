<?php

namespace App\Http\Controllers;

use App\Helper\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    function UserRegistration(Request $request){

try{
    User::create([
        'firstName' => $request->input('firstName'),
        'lastName' => $request->input('lastName'),
        'email' => $request->input('email'),
        'mobile' => $request->input('mobile'),
        'password' => $request->input('password'),
      ]);
      return response()->json([
        'status' => 'success',
        'message' => 'User Registration Successfully'
        ],  200);

}catch(Exception $e){
    return response()->json([
        'status' => 'failed',
        'message' => 'User Registration failed'
        ],  200);

    }

 }

 public function UserLogin(Request $request){
    try{
        $count=User::where('email','=',$request->input('email'))
        ->where('password','=',$request->input('password'))
        ->count();
        if($count==1){
            $token=JWTToken::CreateToken($request->input('email'));
            return response()->json([
              'status' => 'success',
              'message' => 'User Login Successfully',
              'token'=>$token
            ], 200);
        }
    }catch(Exception $e){
        return response()->json([
            'status' => 'failed',
            'message' => $e->getMessage()
         ], 200);
    }
}//end method

    function SendOTPCode(Request $request){
        $email=$request->input('email');
        $otp=rand(1000,9999);
        $count=User::where('email','=',$email)->count();


        if($count==1){

            Mail::to($email)->send(new OTPMail($otp));
             User::where('email','=',$email)->update(['otp'=>$otp]);
             return response()->json([
                'status' => 'success',
                'message' => '4 Digit OTP Code has been send to your email !'
            ], 200);
           }
            else{
                return response()->json([
                    'status' => 'failed',
                    'message' => 'unauthorize'
                ], 401);
            }
      }

       function VerifyOTP(Request $request){
        $email=$request->input('email');
        $otp=$request->input('otp');
        $count=User::where('email','=',$email)
        ->where('otp','=',$otp)->count();

        if($count==1){
            User::where('email','=',$email)->update(['otp'=>'0']);

            $token=JWTToken::CreateTokenForSetPassword($request->input('email'));
            return response()->json([
              'status' => 'success',
              'message' => 'OTP Verification Successfully',
              'token'=>$token
            ], 200);
        }
        else{
            return response()->json([
                'status' => 'failed',
                'message' => 'unauthorize'
            ], 200);
         }
       }

      function ResetPassword(Request $request){

        try{
            $email=$request->header('email');
            $password=$request->input('password');
            User::where('email','=',$email)->update(['password'=>$password]);
            return response()->json([
                'status' => 'success',
                'message' => 'Request Successfully',
              ], 200);

        }catch (Exception ){
            return response()->json([
                'status' => 'Fail',
                'message' => 'Something Went Wrong',
            ], 200);
        }
    }

    }




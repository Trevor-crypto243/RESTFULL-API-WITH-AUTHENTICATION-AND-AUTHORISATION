<?php

namespace App\Http\Controllers\API;

use App\CustomerProfile;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\OTP;
use App\User;
use App\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        Log::info('The login api hit');

        $request->validate([
            'password' => 'required|string',
            'email' => 'required'
        ]);

        $credentials = request(['email', 'password']);

//        $credentials['active'] = 1;
//        $credentials['deleted_at'] = null;


        if(!Auth::attempt($credentials))
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials, please check your email and password'
            ], 401);
        $user = $request->user();

        if ($user->user_group != 4) //not a customer
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to use this resource. Please contact system admin'
            ], 200);

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
//        if ($request->remember_me)
//            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
        return response()->json([
            'success' => true,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => $request->user()
        ]);

    }

    public function signup(Request $request)
    {        
        $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'id_no' => 'required|string|unique:users',
            'phone_no' => ['required','string','unique:users',
            'regex:/^(?:254|\+254|0)?((?:(?:7(?:(?:[01249][0-9])|(?:5[789])|(?:6[89])))|(?:1(?:[1][0-5])))[0-9]{6})$/'
            ],
            'gender' => 'required',       
            'email' => 'nullable|string|email|unique:users',
            'password' => 'required|string|min:4|'
        ],[
            'id_no.unique' => 'The ID number is already registered, please log in with the right email and password',
            'email.unique' => 'The email is already registered, please log in to continue',
            'phone_no.required' => 'Your phone number is required',
            'phone_no.unique' => 'Your phone number is already registered, please log in with the right email',
            'phone_no.regex'=>'invalid phone number'
        ]);

     

        DB::transaction(function() use ($request) {
            //clean the phone number
            $phone_no = $request->phone_no;

            // Remove any non-digit characters (e.g., spaces or dashes)
            $phone_no = preg_replace('/\D/', '', $phone_no);

            // Check if the phone number doesn't start with "254"
            if (substr($phone_no, 0, 3) !== "254") {
                // Remove the first digit and append "254"
                $phone_no = "254" . substr($phone_no, 1);
            }

            $wallet = new Wallet();
            $wallet->current_balance = 0;
            $wallet->previous_balance = 0;
            $wallet->active = 0;
            $wallet->saveOrFail();

            $user = new User([
                'wallet_id' => $wallet->id,
                'user_group' => 4, //customer
                'surname' => $request->surname,
                'name' => $request->name,
                'phone_no' => $phone_no,
                'id_no' => $request->id_no,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
            $user->save();

            $customerProfile = new CustomerProfile();
            $customerProfile->user_id = $user->id;
            $customerProfile->gender = $request->gender;
            $customerProfile->dob = $request->dob;
            $customerProfile->saveOrFail();


            send_sms($request->phone_no,  "Welcome to Quicksava!. Your account has been been successfully created");
            // $user->sendEmailVerificationNotification();


        });

        Log::info('************DB TRansaction completed****** ');


        // $user->notify(new SignupActivate($user));

        Log::info('************Entering response phase****** ');

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please login to continue'
        ], 201);

        Log::info('************Response phase completed ****** ');

    }

    public function user(Request $request)
    {
        return new UserResource($request->user());
    }

    public function check_app_version(Request $request)
    {
        //info("Version code:".$request->version_code);

        if ($request->version_code == 2){
            return response()->json([
                'success' => true,
                'message' => 'App is up to date'
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'App is out of date',
                'errors' => 'To help us serve you better, please update to the latest version of the app from play store'
            ], 200);
        }

    }

    public function update_surname(Request $request)
    {
        $request->validate([
            'surname' => 'required|string',
            'other_names' => 'required|string',
        ]);

        $user = $request->user();
        $user->surname = $request->surname;
        $user->name = $request->other_names;
        $user->update();

        return response()->json([
            'success' => true,
            'message' =>'Your details on Quicksava have been updated successfully'
        ], 200);
    }

    public function send_otp(Request $request)
    {
        $request->validate([
            'phone_no' => 'required'
        ]);

        $code = $this->generateOtp();
        $phone_no = $request->phone_no;

        // Remove any non-digit characters (e.g., spaces or dashes)
        $phone_no = preg_replace('/\D/', '', $phone_no);

        // Check if the phone number doesn't start with "254"
        if (strpos($phone_no, '254') !== 0) {
            // Remove the first digit and append "254"
            $phone_no = '254' . substr($phone_no, 1);
        }

        OTP::where('phone_no', '=', $phone_no)
            ->update(array('verified' => 'yes','verification_date'=>Carbon::now()));


        $otp = new OTP();
        $otp->phone_no = $phone_no;
        $otp->verification_code = $code;
        $otp->verified = "no";
        $otp->saveOrFail();

        $message = "Use this OTP to verify your phone number: ".$code;
        send_sms($request->phone_no, $message);

        return response()->json([
            'success' => true,
            'message' => 'Check your phone SMS for OTP'
        ], 200);

    }

    public function verify_otp(Request $request)
    {
        $request->validate([
            'phone_no' => 'required',
            'otp' => 'required'
        ]);

        $otp = OTP::where('phone_no',$request->phone_no)->where('verification_code',$request->otp)->orderBy('id','desc')->first();

        if (is_null($otp)){
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 200);
        }else{
            if ($otp->verified == 'yes'){
                return response()->json([
                    'success' => false,
                    'message' => 'OTP has already expired'
                ], 200);
            }else{
                $otp->verified = 'yes';
                $otp->verification_date = Carbon::now();
                $otp->update();

                return response()->json([
                    'success' => true,
                    'message' => 'Verified successfully.'
                ], 200);

            }
        }
    }

    public function reset_password(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'phone_no' => 'required',
            'password' => 'required',
            'confirm_password'=>'required|same:password',
            'otp'=>'required'
        ],[
            'confirm_password.same'=>'The password and confirm password fields do not match'
        ]);


        $phone_no = $request->phone_no;

        // Remove any non-digit characters (e.g., spaces or dashes)
        $phone_no = preg_replace('/\D/', '', $phone_no);

        // Check if the phone number doesn't start with "254"
        if (strpos($phone_no, '254') !== 0) {
            // Remove the first digit and append "254"
            $phone_no = '254' . substr($phone_no, 1);
        }
        Log::info($request);

        //verify the otp first
        $otp = OTP::where('phone_no',$phone_no)->where('verification_code',$request->otp)->orderBy('id','desc')->first();

        if (is_null($otp)){
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 200);
        }else{
            if ($otp->verified == 'yes'){
                return response()->json([
                    'success' => false,
                    'message' => 'OTP has already expired'
                ], 200);
            }else{
                $otp->verified = 'yes';
                $otp->verification_date = Carbon::now();
                $otp->update();


                //proceed to reset password here
                $phone_no = $request->phone_no;

                // Remove any non-digit characters (e.g., spaces or dashes)
                $phone_no = preg_replace('/\D/', '', $phone_no);
        
                // Check if the phone number doesn't start with "254"
                if (strpos($phone_no, '254') !== 0) {
                    // Remove the first digit and append "254"
                    $phone_no = '254' . substr($phone_no, 1);
                }
        
                $user = User::where('email',$request->email)->where('phone_no',$phone_no)->first();
        
                if (is_null($user)){
                    return response()->json([
                        'success' => false,
                        'message' => 'We could not find a profile with the provided phone number and email'
                    ], 200);
                }else{
                    $user->password = bcrypt($request->password);
                    $user->update();
        
                    send_sms($request->phone_no, "Your password has been reset successfully");
        
                    return response()->json([
                        'success' => true,
                        'message' => 'Your password has been reset successfully'
                    ], 200);
                }

            }
        }

    }

    public function generateOtp(){
        return rand(1000, 9999);
    }

}

<?php

namespace App\Http\Controllers\API\V1;
    
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    public $successStatus = 200;

    public function register(Request $request)
    {
		$validator = Validator::make($request->all(), [ 
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password'=> 'required|min:8',
            'password_confirmation'=> 'required||same:password',
            'phone_number' => 'required|digits:10|unique:users'
        ]); 
		
		if ($validator->fails()) {
		    return response()->json(['message'=>$validator->errors()->all()[0],'status'=>0,'data'=>array()],$this-> successStatus);
		}
        else{
            $data = $request->all();
            $data['password']=Hash::make($data['password'] );
            $data['otp'] = rand(1000,9999);
            User::create($data);
            return response()->json(['message'=>'Register Success','status'=>1,'data'=>array()],$this-> successStatus);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'email' => 'required|email',
            'password'=> 'required'
        ]);
        $credentials = $request->only('email', 'password');
		if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $user['token'] =  $user->createToken('MyApp')->plainTextToken;
            return response()->json(['message'=>'Login Success','status'=>1,'data'=>array($user)],$this-> successStatus);
        }
        else {
            if ($validator->fails()) {
                return response()->json(['message'=>$validator->errors()->all()[0],'status'=>0,'data'=>array()],$this-> successStatus);
            }
            else{
                return response()->json(['message'=>'Please Check Email And Password','status'=>0,'data'=>array()],$this-> successStatus);
            }
        }
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'phone_number' => 'required'
        ]); 
		if ($validator->fails()) {
		    return response()->json(['message'=>$validator->errors()->all()[0],'status'=>0,'data'=>array()],$this-> successStatus);
		}
        else{
            $phone_no = $request->phone_number;
            $input['otp'] = rand(1000,9999);
            $data = User::resendOtp($phone_no);
            if($data){
                $data->update($input);
                return response()->json(['message'=>'OTP Updated Sucessfully','status'=>1,'data'=>array()],$this-> successStatus);
            }
            else{
                return response()->json(['message'=>'Wrong Phone Number Entered','status'=>0,'data'=>array()],$this-> successStatus);
            }
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'otp' => 'required|digits:4'
        ]); 
		
		if ($validator->fails()) {
		    return response()->json(['message'=>$validator->errors()->all()[0],'status'=>0,'data'=>array()],$this-> successStatus);
		}
        else{
            $otp = $request->otp;
            $data = User::verifyOtp($otp);
            if($data){
                $user = User::find($data->id);
                $user->otp = null;
                $user->status = 1;
                $user->update();
                return response()->json(['message'=>'OTP Verified','status'=>1,'data'=>array()],$this-> successStatus);
            }
            else{
                return response()->json(['message'=>'OTP Not Found','status'=>0,'data'=>array()],$this-> successStatus);
            }
        }
    }

    public function userList(){
        return response()->json(['message'=>'User Profile','status'=>1,'data'=>array(user::all())],$this-> successStatus);
    }

    public function updateProfile(Request $request){
        $user_id = Auth::user();
        $validator = Validator::make($request->all(), [ 
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user_id,
            'phone_number' => 'required|digits:10|unique:users,phone_number,' . $user_id
        ]);
        if ($validator->fails()) {
            return response()->json(['message'=>$validator->errors()->all()[0],'status'=>0,'data'=>array()],$this-> successStatus);
        }
        else{
            $input = $request->all();
            $user = User::find($user_id);
            $user->update($input);
            return response()->json(['message'=>'Record Updated','status'=>0,'data'=>array()],$this-> successStatus);
            
        }
    }

    public function getProfile(){
        return response()->json(['message'=>'User Profile','status'=>1,'data'=>array(Auth::user())],$this-> successStatus);
    }
}
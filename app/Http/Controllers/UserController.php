<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request){

        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'phone'    => 'required|numeric|unique:users',
            'password' => 'required|min:8',
            'governorate' => 'required',
            'address' => 'required',

        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'code' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }
            
        $user = new User();
        $user->name =  $request->name;
        $user->phone =  $request->phone;
        $user->password =  bcrypt($request->password);
        $user->save();

        $u_address=new Address();
        $u_address->user_id=$user->id;
        $u_address->governorate=$request->governorate;
        $u_address->address=$request->address;
        $u_address->status=1;
        $u_address->save();


        $user['token'] = $user->createToken('accessToken')->accessToken;
        
        
        return response()->json([
            'message'=>'fetch data successfully',
            'code'=>200,
            'user'=>$user,
            'address'=>$u_address
        ]);
    
    }


    public function login(Request $request)
    {

      $loginData = Validator::make($request->all(), [
            'phone' => 'required|numeric',
            'password' => 'required',
        ]);
        
        if ($loginData->fails()) {
        $errors = $loginData->errors();

        return response([
            'status'=>false,
            'message'=>'Make sure that the information is correct and fill in all fields',
            'errors'=>$errors,
            'code'=>422
        ]);
      }
        

        $user = User::where('phone',$request->phone)->first();

      

      

        if($user)
        {
          
            if (!Hash::check($request->password, $user->password)) {
        
                return response()->json(
                    ["errors"=>[
                        "password"=>[
                         "Invalid Password!"
                        ]
                    ],
                    "status"=>false,
                    'code' => 404,
                ]);
            }

            $accessToken =     $user->createToken('authToken')->accessToken;
            
            return response([
                'code' => 200,
                'status' => true,
                'message' => 'login Successfully',
                'user' => $user,
                'access_token' => $accessToken
            ]);
        }else
        {
 
            return response()->json(
                ["errors"=>[
                    "phone"=>[
                      "No Account Assigned To This Phone !"
                    ]
                ],
                "status"=>false,
                'code' => 404,
            ]);

        }

    }


    public function logout(){
            
        $user = Auth::guard('api')->user()->token();
        $user->revoke();
        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => 'logout Successfully',
            
        ]);
    }

    public function forgot(Request $request)
    {
        $ran=Str::random(6);
        $user=User::where('phone',$request->phone)->first();
        if($user)
        {
        $user->code=$ran;
        $user->save();
        return response()->json([
            'code'=>200,
            'data'=>$ran,
            
        ]);
    }else
    {
        return response()->json(
            ["errors"=>[
                "phone"=>[
                  "No Account Assigned To This Phone !"
                ]
            ],
            "status"=>false,
            'code' => 404,
        ]);

    }
    }

    public function chang_pass(Request $request)
    {
        if($request->new_password1==$request->new_password2){
            $user=User::where('phone',$request->phone)->first();
            if($user){
                if($user->code==$request->code){
                    $user->password=bcrypt($request->new_password1);
                    $user->code=null;
                    $user->save();
                    return response()->json([
                        'code'=>200,
                        'message'=>'your password is updated successfully',
                        'data'=>$user,
                        
                    ]);
                }else{
                    return response()->json([
                        'code'=>404,
                        'message'=>'This code is invalid',
                    ]);
                }
            }else{
                return response()->json([
                'code'=>404,
                'message'=>'No Account Assigned To This Phone !',
            ]);
}
        }else{
            return response()->json([
                'code'=>404,
                'message'=>'Passwords Dont Match',
            ]);
        }
    }
    
}

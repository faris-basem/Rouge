<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function my_addresses(Request $request)
    {
        $mads=Address::where('user_id',Auth::guard('api')->user()->id)->get();
        return response()->json([
            'code' => 200,
            'message' => 'Data Fetched Successfully',
            'data' =>$mads ,
        ]);
    }
    public function new_address(Request $request)
    {
        $new_add=new Address();
        $new_add->user_id=Auth::guard('api')->user()->id;
        $new_add->name=$request->nick_name;
        $new_add->governorate=$request->governorate;
        $new_add->address=$request->address;
        $new_add->nearby=$request->nearby;
        $new_add->save();
        return response()->json([
            'code' => 200,
            'message' => 'New Address Added Successfully',
        ]);
    }

    public function change_address(Request $request)
    {
        $old_add=Address::where('status',1)->where('id','!=',$request->id)->first();
        if($old_add){
        $old_add->status=0;
        $old_add->save();
        $add=Address::where('id',$request->id)->first();
        $add->status=1;
        $add->save();
        
        return response()->json([
            'code' => 200,
            'message' => 'Address Changed',
        ]);
        }else{
            return response()->json([
                'code' => 200,
                'message' => 'Address Already Selected',
            ]);
        }
    }

    public function update_address(Request $request)
    {
        $add=Address::where('id',$request->id)->first();
        $add->name=$request->name;
        $add->governorate=$request->governorate;
        $add->address=$request->address;
        $add->nearby=$request->nearby;
        $add->save();
        
        return response()->json([
            'code' => 200,
            'message' => 'Address Updated Successfully',
            'data' =>$add
        ]);
        
    }

    public function delete_address(Request $request)
    {
        $add=Address::where('id',$request->id)->first();
        if($add){
            if($add->status==1){
                $new_add=Address::where('id','!=',$request->id)->first();
                if($new_add){
                $new_add->status=1;
                $new_add->save();
                $add->delete();
                return response()->json([
                    'code' => 200,
                    'message' => 'Address Deleted',
                ]);
                }else{
                    return response()->json([
                    'code' => 200,
                    'message' => 'Cant Delete Your Last Address',
                    ]);}
            }else{
                $add->delete();
                return response()->json([
                    'code' => 200,
                    'message' => 'Address Deleted',
                ]);
            }
        }else{
            return response()->json([
                'code' => 200,
                'message' => 'Somthing Went Wrong',
                ]);
        }

        
    }
}

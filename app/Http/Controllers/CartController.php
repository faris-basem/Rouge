<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function my_cart(Request $request)
    {
        Cart::where('user_id', Auth::guard('api')->user()->id)->where('quantity','<=',0)->delete();

        $my_cart = Cart::where('user_id', Auth::guard('api')->user()->id)->where('status',0)->with('product')->with('color')->get();
        
        if($my_cart->count()>0){
            $sub_total = $my_cart->sum(function ($cart) {
                return $cart->product->price * $cart->quantity;
            });

            $del_fee = $my_cart->sum('delivery_fee');

            $total = $my_cart->sum('total');
            
            return response()->json([
                'code' => 200,
                'message' => 'fetch data succsessfully',
                'data' => $my_cart,
                'sub total' => $sub_total,
                'delivery fee' => $del_fee,
                'total' => $total
            ]);
        }else{
            return response()->json([
                'code' => 200,
                'message' => 'Your cart is empty !!',
            ]);
        }
    }
    public function add_to_cart(Request $request)
    {
        $is_add = Cart::where('user_id', Auth::guard('api')->user()->id)->where('product_id', $request->product_id)->where('color_id',$request->color_id)->where('status',0)->first();
        if ($is_add) {
            $is_add->quantity = $is_add->quantity + $request->quantity;
            $is_add->save();
            $is_add->total = ($is_add->price * $is_add->quantity) + $is_add->delivery_fee;
            $is_add->save();
            return response()->json([
                'code' => 200,
                'message' => 'Added to Cart',
                'data' => $is_add,
            ]);
        }
        $add_cart = new Cart();
        $add_cart->user_id = Auth::guard('api')->user()->id;
        $add_cart->product_id = $request->product_id;
        $add_cart->color_id = $request->color_id;
        $add_cart->quantity = $request->quantity;
        $add_cart->status = 0;
        
        // $add_cart->price=$request->price;
        $add_cart->price = Product::where('id', $request->product_id)->value('price');
        $add_cart->save();
        $add_cart->refresh();
        $add_cart->total = ($add_cart->price * $add_cart->quantity) + $add_cart->delivery_fee;
        $add_cart->save();

        return response()->json([
            'code' => 200,
            'message' => 'Added to Cart',
            'data' => $add_cart,
        ]);
    }

}

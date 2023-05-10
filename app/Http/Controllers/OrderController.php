<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    public function send_notification($fcm_token,$title,$body,$order_id)
    {
        $from = "AAAAm1meYS0:APA91bGCYPtLMEdVt2KLetGH7mAp9zwzEOEkcZzAwQoVqpRJU8eJecCopajsuPmPnI4vMvAVCJybx-R9CKx8fbtexzbJeoP5JGVehvo8TEp12kOp1XrlDtl;kjafsd;lkafjalskdf";
        $to = $fcm_token;

        $msg = array
        (
            'title' => $title,
            'body' => $body,

        );

        $fields = array
        (
            'to' => $fcm_token,
            'notification' => $msg,
            'data' => [
                'bookingId' => $order_id,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                "screen" =>  "POST_SCREEN",

            ]
        );


        $headers = array
        (
            'Authorization: key=' . $from,
            'Content-Type: application/json'
        );
        //#Send Reponse To FireBase Server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;

    }



    public function send_notification_to_person(Request $request){

        $usernotification = Notification::create([
            'user_id' => $request->sendnotifi,
            'title' => $request->title,
            'body' =>  $request->body,
           ]);

           $user = User::findOrFail($request->user_id);

           $order_id = 5;

           $this->send_notification($user->fcm_token, $request->title, $request->body,60);



           return response()->json([
            'message3'            => 'تم ارسال اشعار',
            ]);

    }

    public function place_order()
    {
        try {
            DB::beginTransaction();
            $a = Address::where('user_id', Auth::guard('api')->user()->id)->where('status', 1)->first();
            $cart = Cart::where('user_id', Auth::guard('api')->user()->id)->where('status', 0)->get();
            if ($cart->count() > 0) {
                $total = $cart->sum('total');
                $order = new Order();
                $order->receiver = Auth::guard('api')->user()->name;
                $order->phone = Auth::guard('api')->user()->phone;
                $order->governorate = $a->governorate;
                $order->address = $a->address;
                $order->nearby = $a->nearby;

                $maxAttempts = 100;
                $attempt = 1;
                do {
                    $orderNo = rand(100000, 999999);
                    $existingOrder = Order::where('order_number', $orderNo)->exists();
                    if (!$existingOrder) {
                        $order->order_number = $orderNo;
                        break;
                    }
                    $attempt++;
                } while ($attempt <= $maxAttempts);

                $order->order_price = $total;
                $order->items_count = $cart->sum('quantity');
                $order->save();

                foreach ($cart as $c) {
                    $c->order_id = $order->id;
                    $c->status = 1;
                    $c->save();
                }
            } else {
                return response()->json([
                    'message' => 'Failed to place the order Cart is Empty',
                    'code' => 500,
                    'status' => false,
                ]);
            }
           DB::commit();

           $user=Auth::guard('api')->user();
           if ($user->fcm_token) {
                $notificationData = [
                    'fcm_token' => $user->fcm_token,
                    'title' => 'Order Notification',
                    'body' => 'Your order placed successfully',
                    'order_id' => 0,
                ];

                $this->send_notification($notificationData);
            }

            return response()->json([
                'message' => 'Order placed successfully',
                'code' => 200,
                'status' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to place the order',
                'code' => 500,
                'status' => false,
            ]);
        }
    }

    public function OnProgressOrders()
    {
        $onprogress = Order::where('order_status', '!=', 'completed')->get();
        if ($onprogress->count() > 0) {

            return response()->json([
                'message' => 'Data Fetched Successfully',
                'code' => 200,
                'status' => true,
                'orders' => $onprogress
            ]);
        } else {
            return response()->json([
                'message' => 'No orders found!',
                'code' => 500,
                'status' => false,
            ]);
        }
    }

    public function CompletedOrders()
    {
        $completed = Order::where('order_status', '=', 'completed')->get();
        if ($completed->count() > 0) {

            return response()->json([
                'message' => 'Data Fetched Successfully',
                'code' => 200,
                'status' => true,
                'orders' => $completed
            ]);
        } else {
            return response()->json([
                'message' => 'No orders found!',
                'code' => 500,
                'status' => false,
            ]);
        }
    }

    public function order_by_id(Request $request)
    {
        $order=Order::where('id',$request->order_id)->with('cart.product','cart.color')->first();
        return response()->json([
            'message' => 'Data Fetched Successfully',
            'code' => 200,
            'status' => true,
            'orders' => $order
        ]);
    }

    public function checkout(Request $request)
    {
        $check = [];
        $c = Cart::where('user_id', Auth::guard('api')->user()->id)->where('status',0)->get();
        if ($c->count() > 0) {

            $u = User::where('id', Auth::guard('api')->user()->id)->first();
            $a = Address::where('user_id', Auth::guard('api')->user()->id)->where('status', 1)->first();

            $sub_total = $c->sum(function ($cart) {
                return $cart->product->price * $cart->quantity;
            });
            $del_fee = $c->sum('delivery_fee');

            $total = $c->sum('total');

            $check['Receiver'] = $u->name;
            $check['Phone'] = $u->phone;
            $check['Governorate'] = $a->governorate;
            $check['Address'] = $a->address;
            $check['Nearby'] = $a->nearby;
            $check['Payment Method'] = $request->payment_method;
            $check['Coupon'] = $request->coupone;
            $check['Sub Total'] = $sub_total;
            $check['Delivery Fee'] = $del_fee;
            $check['Total'] = $total;

            return response()->json([
                'code' => 200,
                'message' => 'Data Fetched Successfully',
                'data' => $check,
            ]);
        } else {
            return response()->json([
                'code' => 200,
                'message' => 'Your Cart is Empty !!',
            ]);
        }
    }

    public function order_review(Request $request)
    {
        $order=Order::where('id',$request->order_id)->with('cart.product','cart.color')->first();
        $my_cart = Cart::where('order_id', $request->order_id)->get();

        $order->delivery_service=$request->delivery_service;
        $order->feedback=$request->feedback;
        $order->save();

        if($my_cart->count() > 0){
        $arrayData = json_decode($request->products_rate, true);

        foreach ($arrayData as $item) {
            $cartId = $item['cart_id'];
            $review = $item['review'];


            $cartItem = $my_cart->where('id', $cartId)->first();
    
            if ($cartItem) {
                $cartItem->review = $review;
                $cartItem->save();
            }
        }
         
        $my_cart = Cart::where('order_id', $request->order_id)->get();
        return response()->json([
            'code' => 200,
            'message' => 'ٌReview Submitted Successfully',
        ]);
    }}
}

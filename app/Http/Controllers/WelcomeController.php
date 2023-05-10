<?php

namespace App\Http\Controllers;

use App\Models\Welcome;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function wellcom()
    {
        $w=Welcome::where('status','w')->get();
        if ($w->count() > 0) {

            return response()->json([
                'message' => 'Data Fetched Successfully',
                'code' => 200,
                'status' => true,
                'orders' => $w
            ]);
        } else {
            return response()->json([
                'message' => 'No Reslts',
                'code' => 500,
                'status' => false,
            ]);
        }
    }

    public function banner()
    {
        $b=Welcome::where('status','b')->get();
        if ($b->count() > 0) {

            return response()->json([
                'message' => 'Data Fetched Successfully',
                'code' => 200,
                'status' => true,
                'orders' => $b
            ]);
        } else {
            return response()->json([
                'message' => 'No Results',
                'code' => 500,
                'status' => false,
            ]);
        }
    }
    public function privacy()
    {
        $p=Welcome::where('status','p')->get();
        if ($p->count() > 0) {

            return response()->json([
                'message' => 'Data Fetched Successfully',
                'code' => 200,
                'status' => true,
                'orders' => $p
            ]);
        } else {
            return response()->json([
                'message' => 'No Results',
                'code' => 500,
                'status' => false,
            ]);
        }
    }
    public function about_us()
    {
        $a=Welcome::where('status','a')->get();
        if ($a->count() > 0) {

            return response()->json([
                'message' => 'Data Fetched Successfully',
                'code' => 200,
                'status' => true,
                'orders' => $a
            ]);
        } else {
            return response()->json([
                'message' => 'No Results',
                'code' => 500,
                'status' => false,
            ]);
        }
    }

}

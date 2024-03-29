<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\WhiteList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function all_categories()
    {
        $cats = Category::get(['img', 'category_name']);
        if ($cats) {
            return response()->json([
                'code' => 200,
                'message' => 'Fetch data successfully',
                'data' => $cats,

            ]);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'No Categories Found',
            ]);
        }
    }
    public function all_categories_with_products()
    {
        $cats = Category::get(['id', 'category_name']);
        if ($cats->count() > 0) {
            $data = [];
            foreach ($cats as $cat) {
                $pro = Product::where('category_id', $cat->id)->get(['img', 'product_name', 'price']);
                $cat->products = $pro;
                $data[] = $cat;
            }
            return response()->json([
                'code' => 200,
                'message' => 'Fetch data successfully',
                'data' => $cats,
            ]);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'No Categories Found',
            ]);
        }
    }
    public function get_category_by_id(Request $request)
    {
        $cat = Category::where('id',$request->id)->first();
        if ($cat) {
                $pro = Product::where('category_id', $cat->id)->get();
                $cat->products = $pro;
                $data[] = $cat;
                return response()->json([
                    'code' => 200,
                    'message' => 'Fetch data successfully',
                    'data' => $cat,
                ]);
            }
        else {
            return response()->json([
                'code' => 404,
                'message' => 'No Category Found',
            ]);
        }
    }
    
    public function get_product_by_id(Request $request)
    {
        $product = Product::with('category:id,category_name')->find($request->id);
        if ($product) {
            // $pc = Color::where('product_id', $request->id)->get('color_name');
            // $pc = $product->colors()->pluck('color_name');
            $pc = $product->colors()->pluck('color_name', 'color_id')->map(function ($colorName, $colorId) {
                return ['color_name' => $colorName, 'color_id' => $colorId];
            })->values();

            $pimg = ProductImage::where('product_id', $request->id)->get('img');
            $same_p = Product::where('category_id', $product->category_id)->where('id', '!=', $request->id)->get(['id','img', 'product_name', 'price']);
            $product->category_name = $product->category->category_name;
            $product->product_images = $pimg;
            unset($product->category);
            $product->colors = $pc;

            return response()->json([
                'message' => 'Data fetched successfully',
                'code' => 200,
                'product' => $product,
                'similar products' => $same_p,
            ]);
        } else {
            return response()->json([
                'message' => 'Product not found',
                'code' => 404,
            ]);
        }
    }

    public function add_white_list(Request $request)
    {
        $is_fav = WhiteList::where('user_id', Auth::guard('api')->user()->id)->where('product_id', $request->product_id)->first();
        if ($is_fav) {
            $is_fav->delete();
            return response()->json([
                'code' => 200,
                'message' => 'Product removed from White List',
            ]);
        }
        $fg = new WhiteList();
        $fg->user_id = Auth::guard('api')->user()->id;
        $fg->product_id = $request->product_id;
        $fg->save();
        return response()->json([
            'code' => 200,
            'message' => 'Product added to White List',
        ]);
    }

    public function my_white_list()
    {
        $fg = WhiteList::where('user_id', Auth::guard('api')->user()->id)->get();
        if ($fg->count() > 0) {
            $products = [];
            foreach ($fg as $cf) {
                $c = Product::where('id', $cf->product_id)->first();
                $products[] = $c;
            }

            return response()->json([
                'code' => 200,
                'message' => 'fetch data succsessfully',
                'data' => $products
            ]);
        } else {
            return response()->json([
                'code' => 404,
                'message' => 'Your Wish List is Empty !!',
            ]);
        }
    }

    public function search_in_category(Request $request){
        $search = Product::where('category_id',$request->category_id)->where('product_name', 'like', '%'. $request->name.'%')->get();
        if($search->count() > 0){
            return response()->json([
                'message' => 'Data fetched successfully',
                'code' => 200,
                'data' => $search
            ]);
        } else {
            return response()->json([
                'message' => 'No data found',
                'code' => 404
            ]);
        }
    }
    public function search(Request $request){
        $search = Product::where('product_name', 'like', '%'. $request->name.'%')->get();
        if($search->count() > 0){
            return response()->json([
                'message' => 'Data fetched successfully',
                'code' => 200,
                'data' => $search
            ]);
        } else {
            return response()->json([
                'message' => 'No data found',
                'code' => 404
            ]);
        }
    }

    public function search_filter(Request $request){

        $query = Product::query();

        if ($request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $request->product_name . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('price')) {
            $query->where('price', '<=', $request->price);
        }

        if ($request->filled('color_name')) {
            $query->whereHas('colors', function ($colorQuery) use ($request) {
                $colorQuery->where('color_name', $request->color_name);
            });
        }

        $results = $query->get();

        if ($results->count() > 0) {
            return response()->json([
                'message' => 'Data fetched successfully',
                'code' => 200,
                'status' => true,
                'data' => $results

            ]);
        }else{
            return response()->json([
                'message' => 'No data found',
                'code' => 404,
                'status' => false
            ]);
        }
    }

}

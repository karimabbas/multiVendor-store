<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response as FacadesResponse;

class ProductsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('index', 'show');
    }

    public function index(Request $request)
    {
        return Product::filter($request->query())
            ->with('category:id,name', 'store:name', 'tags')
            ->paginate();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'status' => 'in:active,inactive',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|gt:price',
        ]);

        $user = $request->user();
        if (!$user->tokenCan('products.create')) {
            abort(403, 'Not allowed');
        }

        $product = Product::create($request->all());


        return FacadesResponse::json($product, 201, [

            /// sending header for redirect
            'Location' => route('products.show', $product->id),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        // return Product::findOrFail($id);

        ///load method better than with when using object of query builder
        // return $product->load('category:id,name', 'store:name', 'tags');

        //if using resource:
        return new ProductResource($product);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'status' => 'in:active,inactive',
            'price' => 'sometimes|required|numeric|min:0',
            'compare_price' => 'nullable|numeric|gt:price',
        ]);

        $user = $request->user();
        if (!$user->tokenCan('products.update')) {
            abort(403, 'Not allowed');
        }

        $product->update($request->all());


        return FacadesResponse::json($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user->tokenCan('products.delete')) {
            return response([
                'message' => ' Not Allowed for this User'
            ], 403);
        }

        Product::destroy($id);
        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }
}

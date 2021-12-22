<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Validator;


class ProductController extends Controller
{

    /**
     * @OA\Get(
     *     tags={"Product"},
     *     path="/api/products",
     *     @OA\Parameter(
     *      name="page",
     *      in="query",
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Response(response="200", description="List Products.")
     * )
     */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $input = $request->all();
        $name=isset($input["name"])? $input["name"] : "";
        if(!empty($name))
        {
            $products = Product::where("name", 'LIKE', "%$name%")->paginate(1);
            return response()->json($products);
        }
        $products = Product::paginate(1);
        return response()->json($products);
    }

    /**
     * @OA\Post(
     ** path="/api/products",
     *   tags={"Product"},
     *
     *   @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="detail",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="image to upload",
     *                     property="file",
     *                     type="file",
     *                ),
     *                 required={"file"}
     *             )
     *         )
     *     ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'detail' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $imageName = uniqid().'.'.$request->file->extension();
        $path = public_path('images');
        $request->file->move($path, $imageName);

        $input['image']=$imageName;
        $product = Product::create($input);
        return response()->json([
            "success" => true,
            "message" => "Product created successfully.",
            "product" => $product
        ]);
    }


    /**
     * @OA\Get(
     *     tags={"Product"},
     *     path="/api/products/{id}",
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Buscar por estado",
     *         required=true,
     *      ),
     *     @OA\Response(response="200", description="List Products.")
     * )
     */
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);
        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }
        return response()->json([
            "success" => true,
            "message" => "Product retrieved successfully.",
            "product" => $product
        ]);
    }

    /**
     * @OA\Post   (
     ** path="/api/products/{id}",
     *   tags={"Product"},
     *
     *  @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="Buscar por estado",
     *      required=true,
     *   ),
     *   @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="detail",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *  @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="image to upload",
     *                     property="file",
     *                     type="file",
     *                ),
     *             )
     *         )
     *     ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $product = Product::find($id);
        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }
        $input = $request->all();
        if (isset($input["name"])){
            $product->name = $input['name'];
            $product->save();
        }
        if (isset($input["detail"])){
            $product->detail = $input['detail'];
            $product->save();
        }
        if ($request->file){
            $image_path = public_path("images/" . $product->image);
            if (File::exists($image_path)) {
                File::delete($image_path);
            }

            $imageName = uniqid().'.'.$request->file->extension();
            $path = public_path('images');
            $request->file->move($path, $imageName);

            $product->image = $imageName;
            $product->save();
        }
        return response()->json([
            "success" => true,
            "message" => "Product updated successfully.",
            "data" => $product
        ]);
    }

    /**
     * @OA\Delete  (
     ** path="/api/products/{id}",
     *   tags={"Product"},
     *
     *  @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="Buscar por estado",
     *      required=true,
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }
        $image_path = public_path("images/" . $product->image);
        if (File::exists($image_path)) {
            File::delete($image_path);
        }
        $product->delete();
        return response()->json([
            "success" => true,
            "message" => "Product deleted successfully.",
            "data" => $product
        ]);
    }
}

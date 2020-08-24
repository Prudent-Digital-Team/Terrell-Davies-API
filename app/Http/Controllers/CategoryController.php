<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json(['categories' => $categories], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $category = Category::create($request->all());
        return response()->json([
            'message' => 'Category Created',
            'category' => $category,
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (Category::where('id', $id)->exists()) {
            $categories = Category::where('id', $id)->get()->toJson(JSON_PRETTY_PRINT);
            return response($categories, 200);
          } else {
            return response()->json([
              "message" => "Category not found",
              "categories" => $categories,
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $id)
    {
        if (Category::where('id', $id)->exists()) {
            $cat = Category::find($id);
            $cat->name = is_null($request->name) ? $cat->name : $request->name;
            $cat->save();

            return response()->json([
                "message" => "Category updated successfully",
                "cat" => $cat,
            ], 200);
            } else {
            return response()->json([
                "message" => "Category not found",
                "cat" => $cat,
            ], 404);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $id)
    {
        if(Category::where('id', $id)->exists()) {
            $cat = Category::find($id);
            $cat->delete();

            return response()->json([
              "message" => "Category deleted",
              "cat" => $cat,
            ], 202);
          } else {
            return response()->json([
              "message" => "Category not found",
              "cat" => $cat,
            ], 404);
        }
    }
}

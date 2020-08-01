<?php

namespace App\Http\Controllers;

use App\Property;
use App\User;

use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $properties = Property::all();
        return response()->json(['properties' => $properties], 200);
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
        $data = $request->validate([
            'title' => 'required',
            'slug' => 'required|unique:properties',
            'description' => 'required',
            'location' => 'required',
            'type' => 'required',
            'status' => 'required',
            'price' => 'required',
            'featuredImage' => 'required',
            'galleryImage' => 'required',
            'user_id' => 'required',
            'agent' => 'required',
            'feature' => 'required',
            'bedroom' => 'required',
            'bathroom' => 'required',
            'garage' => 'required',
            'toilet' => 'required',
            'views' => 'required',
            'metaDescription' => 'required',
            'visible' => 'required',
        ]);

        try{

            $featuredImage = $request->file('image');
            $image_filename = time().'.'.$featuredImage->getClientOriginalExtension();
            $image_path = public_path('/FeaturedProperty_images');
            $featuredImage->move($image_path,$image_filename);

            $data['featuredImage'] = $image_filename;

            $galleryImage = $request->file('image');
            $image_filename1 = time().'.'.$galleryImage->getClientOriginalExtension();
            $image_path = public_path('/Gallery_images');
            $galleryImage->move($image_path,$image_filename1);

            $data['galleryImage'] = $image_filename1;

            $property = Property::create($data);
            return response()->json([
                'message' => 'Type Created',
                'property' => $property,
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'message' => 'An error occured',
                'property' => $property,
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function show(Property $id)
    {
        if (Property::where('id', $id)->exists()) {
            $property = Property::where('id', $id)->get()->toJson(JSON_PRETTY_PRINT);
            return response($property, 200);
          } else {
            return response()->json([
              "message" => "Property not found",
              'property' => $property,
            ], 404);
          }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function edit(Property $property)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Property $id)
    {
        $properties = Property::findorfail($id);
        if ($request->isMethod('post')) {
            $data = $request->all();
            try{

                $featuredImage = $request->file('image');
                $image_filename = time().'.'.$featuredImage->getClientOriginalExtension();
                $image_path = public_path('/FeaturedProperty_images');
                $featuredImage->move($image_path,$image_filename);

                $data['featuredImage'] = $image_filename;

                $galleryImage = $request->file('image');
                $image_filename1 = time().'.'.$galleryImage->getClientOriginalExtension();
                $image_path = public_path('/Gallery_images');
                $galleryImage->move($image_path,$image_filename1);

                $data['galleryImage'] = $image_filename1;
            }
            catch(Exception $e){
                return response()->json([
                    'message' => 'An error occured',
                    'property' => $property,
                ], 400);
            }


            $properties->update([
                'title'=>$data['title'],
                'slug'=>$data['slug'],
                'description'=>$data['description'],
                'location'=>$data['location'],
                'type'=>$data['type'],
                'status'=>$data['status'],
                'price'=>$data['price'],
                'featuredImage'=>$image_filename,
                'galleryImage'=>$image_filename1,
                'user_id' => Auth::user()->id,
                'agent'=>$data['agent'],
                'feature'=>$data['feature'],
                'bedroom'=>$data['bedroom'],
                'bathroom'=>$data['bathroom'],
                'garage'=>$data['garage'],
                'toilet'=>$data['toilet'],
                'views'=>$data['views'],
                'metaDescription'=>$data['metaDescription'],
                'visible'=>$data['visible'],
            ]);

            return response()->json([
                "message" => "record updated successfully",
                'properties' => $properties,
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function destroy(Property $id)
    {
        if(Property::where('id', $id)->exists()) {
            $properties = Property::find($id);
            $properties->delete();

            return response()->json([
              "message" => "record deleted",
              'properties' => $properties,
            ], 202);
          } else {
            return response()->json([
              "message" => "record not found",
              'properties' => $properties,
            ], 404);
        }
    }
}
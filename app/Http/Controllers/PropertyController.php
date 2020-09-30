<?php

namespace App\Http\Controllers;

use App\Property;
use App\PropertyType;
use App\PropertyCategory;
use App\User;

use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     *
     */

    public function index()
    {
        // $properties = Property::all();
        // // $propertytypes = PropertyType::get();
        // // $propertycats = Propertycategory::get();
        // return response()->json(['properties' => $properties], 200);

        $properties = Property::paginate(5);
        return $properties;
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
            'property_cat_id' => '',
            'property_type_id' => '',
            'title' => 'required',
            'description' => 'required',
            'state' => 'required',
            'market_status' => 'required',
            'locality' => 'required',
            'budget' => 'required',
            'featuredImage' => 'required|image',
            'galleryImage' => 'required|image',
            'agent' => 'required',
            'features' => 'required',
            'bedroom' => 'required',
            'bathroom' => 'required',
            'garage' => 'required',
            'toilet' => 'required',
            'totalarea' => 'required',
            // 'video_link' => 'required',
            'metaDescription' => 'required',
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

            $property = Property::create();
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (Property::where('id', $id)->exists()) {
            $propertyDetails = Property::where('id', $id)->get()->toJson(JSON_PRETTY_PRINT);
            return response($propertyDetails, 200);
          } else {
            return response()->json([
              "message" => "Property not found"
            ], 404);
          }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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
                ], 400);
            }


            $properties->update([
                'property_cat_id' => $data['prperty_cat_id'],
                'property_type_id' => $data['prperty_type_id'],
                'title'=>$data['title'],
                'description'=>$data['description'],
                'state' => $data['state'],
                'market_status' => $data['market-status'],
                'locality' => $data['locality'],
                'budget' => $data['budget'],
                'featuredImage' => $image_filename,
                'galleryImage'=>$image_filename1,
                'agent' => $data['agent'],
                'feature' => $data['feature'],
                'bedroom' => $data['bedroom'],
                'bathroom' => $data['bathroom'],
                'garage' => $data['garage'],
                'toilet' => $data['toilet'],
                'totalarea' => $data['totalarea'],
                'video_link' => $data['video-link'],
                'metaDescription' => $data['metaDescription'],
            ]);

            return response()->json([
                "message" => "Property updated successfully",
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if(Property::where('id', $id)->exists()) {
            $properties = Property::findorFail($id);
            $properties->delete();

            return response()->json([
              "message" => "record deleted",
            ], 202);
          } else {
            return response()->json([
              "message" => "record not found",
            ], 404);
        }
    }

    public function search(Request $request){
        // $searchTerm = $request->input('searchTerm');
        // $properties = Property::data($searchTerm)->get();
        // return response()->json(['properties' => $properties], 200);
    }

    public function getsearchResults(Request $request) {
        $data = $request->get('data');

        $properties = Property::where('title', 'like', "%{$data}%")
                        ->orWhere('bathroom', 'like', "%{$data}%")
                        ->orWhere('budget', 'like', "%{$data}%")
                        ->orWhere('state', 'like', "%{$data}%")
                        ->orWhere('agent', 'like', "%{$data}%")
                        ->orWhere('locality', 'like', "%{$data}%")
                        ->orWhere('property_cat_id', 'like', "%{$data}%")
                        ->orWhere('property_type_id', 'like', "%{$data}%")
                        ->get();

        return response()->json([
            'data' => $properties
        ]);
    }
    public function propertyCount(){
        $propertyCount = Property::count();
        return response()-> json([
            'propertyCount' => $propertyCount
        ], 200);
    }
}

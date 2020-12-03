<?php

namespace App\Http\Controllers;

use App\Property;
use App\Category;
use App\Helpers\ApiConstants;
use App\Location;
use App\ShortList;
use Illuminate\Support\Arr;
use App\Type;
use App\SearchHistory;
use App\User;
use DB;
use Exception;
use Auth;
use Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\SearchHistoryCollection;

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
        $property = Property::orderBy('id', 'DESC')->where('status', 'Publish')->get();

        //extract the images

        return response()->json([
            'property' => $property,
        ], 200);

    }

    public function paginate()
    {
        $property = Property::orderBy('id', 'DESC')->paginate(5);
        return $property;
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
        $path2 = "";

        DB::beginTransaction();

        try{
            $validator = Validator::make($request->all(),[
                'user_id' => 'required',
                'category_id' => 'required',
                'type_id' => 'required',
                'location' => 'required',
                'location_id' => 'nullable',
                'title' => 'required',
                'description' => 'required',
                'state' => 'required',
                'area' => 'required',
                'total_area' => 'required',
                'market_status' => 'required',
                'parking' => 'required',
                'locality' => 'required',
                'budget' => 'required',
                'other_images' => 'nullable',
                'other_images.*' => 'text|max:3048',
                'image' => 'required',
                'image.*' => 'text|max:3048',
                'bedroom' => 'required',
                'bathroom' => 'required',
                'toilet' => 'required',
                'video_link' => 'nullable',
                'status' => 'required',
                'feature' => 'required',
            ]);
            
            //TODO: Don't forget to remove the ! from this validation

            if($validator->fails()){
                session()->flash('errors' , $validator->errors());
                throw new ValidationException($validator);
            }       
        
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $pin = mt_rand(1000000, 9999999)
                    . mt_rand(1000000, 9999999)
                    . $characters[rand(0, strlen($characters) - 1)];

            $users = auth('api')->user();
            $user = json_decode($users);

            $property = Property::create([
                'user_id' => $user->id,
                'category_id' => $request->category_id,
                'type_id' => $request->type_id,
                'location_id' => $request->location_id,
                'location' => $request->location,
                'title' => $request->title,
                'description' => $request->description,
                'state' => $request->state,
                'area' => $request->area,
                'total_area' => $request->total_area,
                'market_status' => $request->market_status,
                'parking' => $request->parking,
                'locality' => $request->locality,
                'budget' => $request->budget,
                'other_images' => $request->other_images,
                'image' => $request->image,
                'bedroom' => $request->bedroom,
                'bathroom' => $request->bathroom,
                'toilet' => $request->toilet,
                'video_link' => $request->video_link,
                'status' => $request->status,
                'feature' => $request->feature,
                'ref_no' => str_shuffle($pin),
                'user' => $user,
            ]);

            DB::commit();
            // return validResponse('Property Created!');
            return response()->json([
                'message' => 'Property Created!',
                'property' => $property,
            ], 201);
        
      
    }
    catch(Exception $e){
        session()->flash('error_msg' , $e->getMessage());
        dd($e->getMessage());
        DB::rollback();
        return problemResponse($e->getMessage() , ApiConstants::SERVER_ERR_CODE , $request);
    }
     catch(ValidationException $e){
            DB::rollback();
            $message = "" . (implode(' ', Arr::flatten($e->errors())));
            return problemResponse($message , ApiConstants::BAD_REQ_ERR_CODE , $request);
        }
}

    public function imageUpload(Request $request){
     
        try{
            $validator = Validator::make($request->all(),[
                'image' => 'nullable',
                'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:3048',
            ]);

            if($validator->fails()){
                session()->flash('errors' , $validator->errors());
                throw new ValidationException($validator);
            }

            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image_path = public_path('/Property_images');
            $image->move($image_path,$filename);
          
            $path = env('APP_URL').'/Property_images/'.$filename;
        
            return response()->json($path);

        }

        catch(ValidationException $e){
            DB::rollback();
            $message = "" . (implode(' ', Arr::flatten($e->errors())));
            return problemResponse($message , ApiConstants::BAD_REQ_ERR_CODE , $request);
        }

        catch(Exception $e){
            session()->flash('error_msg' , $e->getMessage());
            dd($e->getMessage());
            DB::rollback();
            return problemResponse($e->getMessage() , ApiConstants::SERVER_ERR_CODE , $request);
        }
        
    }

    public function user_property_list(){
        $user_id = auth('api')->user()->id;

        $user_property_get = Property::where('user_id', $user_id)->get();
        return response()-> json([
            'user_property_listing' => $user_property_get
        ], 200);
    }

    public function user_property_count(){
        $user_id = auth('api')->user()->id;

        $user_property_Count = Property::where('user_id', $user_id)->count();
            return response()-> json([
            'user_property_listing_count' => $user_property_Count
        ], 200);
    }

    public function shortlist(Request $request){
        $request->validate([
            'user_id' => 'required',
            'property_id' => 'required',
        ]);


        if(auth('api')->user()->id != $request->user_id){

            return response()->json([
                'message' => 'Authentication Failed',
            ], 403);
        }

        $findProperty = Property::find($request->property_id);

        if(!$findProperty){
            
            return response()->json([
                'message' => 'Property Not Found',
            ], 404);

        }

        $isPropertyAlreadyAdded = ShortList::where('user_id',$request->user_id)->where('property_id',$request->property_id)->count();

        if($isPropertyAlreadyAdded > 0){

            return response()->json([
                'message' => 'Duplicate Request, this property has already been added',
            ], 505);

        }

        DB::beginTransaction();
        $shortlist = ShortList::create([
            'user_id' => auth('api')->user()->id,
            'property_id' => $request->property_id,
        ]);

        return response()->json([
            'message' => 'Property added to shortlist successfully!',
        ], 201);
    }

    public function user_shortlist_count(){
        $user_id = auth('api')->user()->id;

        $user_shortlist_count = Shortlist::where('user_id', $user_id)->count();
            return response()->json([
            'user_shortlist_count' => $user_shortlist_count
        ], 200);
    }

    public function user_shortlist(){

        $user_id = auth('api')->user()->id;

        $user_shortlist = Shortlist::where('user_id', $user_id)->get();

            return response()->json([

            'user_shortlists' => $user_shortlist

        ], 200);
    }

    public function searchByStateAreaCity(Request $request){
        $pro = Property::where('status', 'Publish');

        if ($request->has('state')) {
            $pro->where('state', $request->state);
        }



        return $pro->get();
    }

    public function addToShortlist($id)
    {
        $property = Property::find($id);
        if(!$property) {
            return response()->json([
                'message' => 'Property does not exist!'
            ], 404);
        }
        $shortlist = session()->get('shortlist');
        // if shortlist is empty then this the first pro$property
        if(!$shortlist) {
            $shortlist = [
                    $id => [
                        "name" => $property->name,
                        "quantity" => 1,
                        "budget" => $property->budget,
                        "image" => $property->image
                    ]
            ];
            session()->put('shortlist', $shortlist);
            return response()->json([
                'message' => 'Property added to shortlist successfully!',
            ], 201);
        }
        // if shortlist not empty then check if this pro$property exist then increment quantity
        if(isset($shortlist[$id])) {
            $shortlist[$id]['quantity']++;
            session()->put('shortlist', $shortlist);
            return response()->json([
                'message' => 'Property added to shortlist successfully!',
            ], 201);
        }
        // if item not exist in shortlist then add to shortlist with quantity = 1
        $shortlist[$id] = [
            "name" => $property->name,
            "quantity" => 1,
            "budget" => $property->budget,
            "image" => $property->image
        ];
        session()->put('shortlist', $shortlist);
        return response()->json([
            'message' => 'Property added to shortlist successfully!',
        ], 201);
    }

    public function updateShortlist(Request $request)
    {
        if($request->id and $request->quantity)
        {
            $cart = session()->get('cart');
            $cart[$request->id]["quantity"] = $request->quantity;
            session()->put('cart', $cart);
            session()->flash('success', 'Cart updated successfully');
        }
    }

    public function removeShortlist(Request $request)
    {
        if($request->id) {
            $cart = session()->get('cart');
            if(isset($cart[$request->id])) {
                unset($cart[$request->id]);
                session()->put('cart', $cart);
            }
            session()->flash('success', 'Product removed successfully');
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
        $property = Property::findorfail($id);
        if ($request->isMethod('post')) {
            $data = $request->all();

            $property->update([
                'category_id' => $data['category_id'],
                'type_id' => $data['type_id'],
                'location_id' => $data['location_id'],
                'title'=>$data['title'],
                'description'=>$data['description'],
                'state' => $data['state'],
                'area' => $data['area'],
                'total_area' => $data['total_area'],
                'market_status' => $data['market-status'],
                'parking' => $data['parking'],
                'locality' => $data['locality'],
                'budget' => $data['budget'],
                'image' => $data['image'],
                'bedroom' => $data['bedroom'],
                'bathroom' => $data['bathroom'],
                'toilet' => $data['toilet'],
                'video_link' => $data['video_link'],
                'status' => $data['status'],
                'feature' => $data['feature'],
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
            $property = Property::findorFail($id);
            $property->delete();

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
        // $property = Property::data($searchTerm)->get();
        // return response()->json(['property' => $property], 200);
    }

    public function getsearchResults(Request $request) {
        $data = $request->has('data');

        $property = Property::where('title', 'like', "%{$data}%")
                        ->orWhere('bathroom', 'like', "%{$data}%")
                        ->orWhere('budget', 'like', "%{$data}%")
                        ->orWhere('state', 'like', "%{$data}%")
                        ->orWhere('locality', 'like', "%{$data}%")
                        ->orWhere('type_id', 'like', "%{$data}%")
                        ->orWhere('category_id', 'like', "%{$data}%");
                        // ->orWhere('location_id', 'like', "%{$data}%");


        return $property->get();
    }

    public function filter(Request $request)
    {
        $user_id = auth('api')->user()->id ?? '';

        

        $property = Property::where('status', 'Publish');
        if ($request->has('title','bathroom','budget', 'state', 'locality', 'type_id', 'category_id')) {

            $property->orWhere('title', $request->title);
            $property->orWhere('bathroom', $request->bathroom);
            $property->orWhere('budget', $request->budget);
            $property->orWhere('state', $request->state);
            $property->orWhere('locality', $request->locality);
            $property->orWhere('type_id', $request->type_id);
            $property->orWhere('category_id', $request->category_id);

        }

        //Save result in SearchHistory:: 

        if(isset($user_id) && !empty($user_id) && $property->count() > 0){
            
            foreach($property->get() as $singleProperty){
                
                SearchHistory::create([
                    'user_id'=>$user_id,
                    'property_id'=>$singleProperty->id
                ]);

            }         

        }
       

        return $property->get();
    }

    public function searchHistory(){

        $auth_user = auth('api')->user()->id; 
        
        $searchHistory = new SearchHistoryCollection(SearchHistory::where('user_id',$auth_user)->paginate());

        return response()->json([
            'message' => 'Search History',
            'data'=>$searchHistory
        ], 403);



    }

    public function propertyCount(){
        $propertyCount = Property::count();
        return response()-> json([
            'propertyCount' => $propertyCount
        ], 200);
    }
}

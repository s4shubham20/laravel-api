<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId =  Auth::user()->id;
        $category   =   Category::where('user_id',$userId)->get();
        return response()->json(['categories' => $category], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator =Validator::make($request->all(), [
            'name'      =>      'required',
            'slug'      =>      'required|unique:categories',
            'alt'       =>      'nullable',
            'image'     =>      'max:2048',
        ]);

        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 401);
        }
        $category                   =       new Category;
        if($request->hasFile('image')){
            $imageExt = $request->file('image')->getClientOriginalExtension();
            $imageName = $request->file('image')->getClientOriginalName();
            $image = pathinfo($imageName,PATHINFO_FILENAME);
            $changeImage = Str::slug($image).uniqid().'.'.$imageExt;
            $path = 'upload/category/';
            $saveImage = $path.$changeImage;
            $request->image->move(public_path($path), $saveImage);
            $category->image        =           $saveImage;
        }

        $category->name             =       $request->name;
        $category->user_id          =       Auth::user()->id;
        $category->slug             =       $request->slug;
        $category->alt              =       $request->alt;
        $category->status           =       $request->status;
        $category->description      =       $request->description;
        $category->meta_title       =       $request->meta_title;
        $category->meta_keywords    =       $request->meta_keywords;
        $category->meta_description =       $request->meta_description;
        $category->save();
        return response()->json(['success' => 'Successfully Added'], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::findOrFail($id);
        return response()->json(['categories' => $category], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category                   =           Category::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name'              =>      'required',
            'slug'              =>      'required|unique:categories,slug,'.$id,
            'alt'               =>      'nullable',
            'image'             =>      'image|mimes:jpeg,jpg,png,webp|max:2048',
            'status'            =>      'required',
            'meta_title'        =>      'required',
            'meta_keywords'     =>      'required',
            'meta_description'  =>      'required',
        ]);

        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 401);
        }
        if($request->hasFile('image')){
            $imageExt = $request->file('image')->getClientOriginalExtension();
            $imageName = $request->file('image')->getClientOriginalName();
            $image = pathinfo($imageName,PATHINFO_FILENAME);
            $changeImage = Str::slug($image).uniqid().'.'.$imageExt;
            $path = 'upload/category/';
            $saveImage = $path.$changeImage;
            $request->image->move(public_path($path), $saveImage);
            
            if(File::exists(public_path($category->image))){
                File::delete(public_path($category->image));
            }
        }else{
            $saveImage = $category->image;
        }
        $category->update([
            'name'             =>       $request->name,
            'image'            =>       $saveImage,
            'slug'             =>       $request->slug,
            'alt'              =>       $request->alt,
            'status'           =>       $request->status,
            'description'      =>       $request->description,
            'meta_title'       =>       $request->meta_title,
            'meta_keywords'    =>       $request->meta_keywords,
            'meta_description' =>       $request->meta_description
        ]);
        /*$category->name             =       $request->name;
        $category->image            =       $saveImage;
        $category->slug             =       $request->slug;
        $category->alt              =       $request->alt;
        $category->status           =       $request->status;
        $category->description      =       $request->description;
        $category->meta_title       =       $request->meta_title;
        $category->meta_keywords    =       $request->meta_keywords;
        $category->meta_description =       $request->meta_description;
        $category->save(); */
        return response()->json(['success' => 'Successfully Updated'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $category = Category::findOrFail($id);
        if($category):
            if(File::exists(public_path($category->image))){
                File::delete(public_path($category->image));
            }
            Category::destroy($id);
            return response()->json(['success' => 'Delete Successfully'], 200);
        else:
            return response()->json(['error' => 'Something went wrong'], 401);
        endif;

    }
}

<?php

namespace App\Http\Controllers;

 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\fileUploads;


class WelcomeController extends Controller
{
    public function index()
   {
       $url = 'https://s3.' . config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket') . '/';
       $images = [];
       $files = Storage::disk('s3')->files('images');
           foreach ($files as $file) {
               $images[] = [
                   'name' => str_replace('images/', '', $file),
                   'src' => $url . $file
               ];
           }
      
       return view('welcome', compact('images'));
   }
 
   public function store(Request $request)
   {
       // $this->validate($request, [
       //     'image' => 'required|image|max:2048'
       // ]);
        $files = $request->all();
        $data['lecture_name'] = 'L1';

        	$fileUploads = new fileUploads;           

           foreach ($files as $file) {

          // $file = $request->file('image');
           $name = time() . $file;//->getClientOriginalName();

           $filePath = 'images/' . $name;
           $url = 'https://s3.' . config('filesystems.disks.s3.region') . '.amazonaws.com/'.$filePath;

           //Storage::disk('s3')->put($filePath, file_get_contents($file));
           $data['file_path'] = $filePath;
           $data['url'] = $url;
           $fileUploads->create($data);
         }
       
 
       return "success";
   }
 
   public function destroy($image)
   {
       Storage::disk('s3')->delete('images/' . $image);
       //$res = ManfCategoryMaster::where('file_url',$file_url)->delete();
 
       return back()->withSuccess('Image was deleted successfully');
   }
}

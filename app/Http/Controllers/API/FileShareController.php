<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\FileResource;
use App\Http\Resources\FileShareResource;
use App\Http\Resources\ProductResource;
use App\Models\File;
use App\Models\FileShare;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Product;
use Validator;
Use Illuminate\Support\Facades\Auth;


class FileShareController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $email = explode(",",$request->email);
        for ($i=0 ; $i<sizeof($email); $i++)
        {
            $fileShare = new FileShare();
            $fileShare->user_id = Auth::user()->id;
            $fileShare->file_id = $request->file_id;
            $fileShare->comment =$request->comment;
            $fileShare->email = $email[$i];
            $fileShare->save();
        }


        $file =File::find($request->file_id);
        $file->link = $request->link;
        $file->save();

        return response()->json(array(
            'status' => '200',
        ));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function linkGenerate($id)
    {

        $fileData = File::find($id);

        if($fileData->link != '0')
        {
            $shareAbleLink=$fileData->link;
        } else{

            $str_to_make = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

            // Shuffle the $str_result and returns substring
            // of specified length
            $shareAbleLink = request()->getHost().'/file/d'.'/'.$generateCode = substr(str_shuffle($str_to_make),
                    0, 20);
        }
        return response()->json(array(
            'status' => '200',
            'shareableLink' => $shareAbleLink,
            'fileData' => $fileData,
        ));
        //return view('share', compact('fileData','shareAbleLink'));
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\FileResource;
use App\Http\Resources\ProductResource;
use App\Models\File;
use App\Models\FileShare;
use App\Models\Product;
use App\Models\UserFileIp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Illuminate\Support\Facades\DB;

class FileController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function test()
    {

    }

    public function allFiles()
    {
           $files = DB::table('files')
            ->leftJoin('users', 'files.user_id', '=', 'users.id')
             ->select(
                 'files.id',
                 'files.filenames',
                 'files.status',
                 'files.link',
                 'files.size',
                 'files.comment',
                 'files.request',
                 'users.name',

             )
            ->get();
        //dd($files);
        return response()->json(array(
            'status' => '200',
            'data' => $files,
            'message' => "File info retrived successfully",
        ));
//        $files = File::all();
        //return $this->sendResponse(FileResource::collection($files), 'files retrieved successfully.');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){

        $filebyte=$request->file('file')->getSize();
        $fileSize=round($filebyte/(1024*1024),1);

        $data = array();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'file' => 'required|mimes:png,jpg,jpeg,pdf|max:10000'
        ]);

        if ($validator->fails()) {

            $data['success'] = 0;
            $data['error'] = $validator->errors()->first('file');// Error response

        }else{
            if($request->file('file')) {

                $file = $request->file('file');
                $filename = time().'_'.$file->getClientOriginalName();

                // File upload location
                $location = 'files';

                // Upload file
                $file->move($location,$filename);

                // File path
                $filepath = url($location.'/'.$filename);

                //get link
                $str_to_make = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                $generateCode = substr(str_shuffle($str_to_make),
                    0, 20);

                $shareAbleLink ='/file/d'.'/'.$generateCode;



                $file = new File();
                $file->filenames = $filename;
                if((empty($request->user_id)))
                {
                    $file->user_id = 0;
                }else{
                    $file->user_id = $request->user_id;
                }
                $file->status = 1;
                $file->link = $generateCode;
                $file->size = $fileSize;
                $file->comment = '0';
                $file->request = 0;
                $file->last_download_date = Carbon::now();
                $file->save();
                //return response()->json(['success' => $filename]);

                // Response
                $data['success'] = 200;
                $data['link'] = $shareAbleLink;
                $data['message'] = 'Uploaded Successfully!';

            }else{
                // Response
                $data['success'] = 409;
                $data['message'] = 'File not uploaded.';
            }
        }

        return response()->json($data);
    }
    public function findFiles($id)
    {
        $files = File::where('user_id', $id)->get();
        return $this->sendResponse($files, 'User login successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (Auth::user()->id == 1) {
            $files = File::find($id);

        } else {
            $files = File::where('user_id', Auth::user()->id)->where('status', 1)
                ->get();;
        }
        if (is_null($files)) {
            return $this->sendError('File not found.');
        }
        return $this->sendResponse(new FileResource($files), 'File retrieved successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        $input = $request->all();


        /*$validator = Validator::make($input, [
            'filenames' => 'required',
            'status' => 'required',
            'user_id' => 'required',
            'size' => 'required',
            'link' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }*/

        $file->filenames = $input['filenames'];
        /*  $file->status = $input['status'];
          $file->user_id = $input['user_id'];
          $file->size = $input['size'];
          $file->link = $input['link'];*/
        $file->save();

        return $this->sendResponse(new FileResource($file), 'File updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $file = File::find($id);
        if (file_exists('files/'.$file->filenames))
        {
            unlink('files/'.$file->filenames);
        }
        $file->delete();

        return $this->sendResponse([], 'File deleted successfully.');
    }

    public function downloadFile($id)
    {

         $file=File::where('link', $id)
            ->first();

          $fileLastIpDown=UserFileIp::where('file_id', $file->id)
            ->where('user_ip', \Request::ip())
            ->latest()->first();
         if (empty($fileLastIpDown))
         {
             $timeDef= 11;
             $file['next_download']=Carbon::now()->addHour(2);
         }else
         {
             $timeDef= Carbon::parse($fileLastIpDown->created_at)->diffInMinutes(Carbon::now()->addHour(2));
             $file['next_download']=Carbon::parse($fileLastIpDown->created_at)->addMinute(10);
         }


      $file['time_def']=$timeDef;


        if (empty($file)) {
            return response()->json(array(
                'status' => '404',
                'message' => "Invalid URL",
            ));
        }
        DB::table('files')
            ->where('id', $file->id)
            ->update(['last_download_date' => Carbon::now()]);


        return response()->json(array(
            'status' => '200',
            'data' => $file,
            'message' => "File info retrived successfully",
        ));
    }
    public function unblockRequest(Request $request)
    {
       // return $request->comment;
        $file=File::find($request->id);
        $file->comment = $request->comment;
        $file->request = 1;
        $file->save();
        $success['comment']= $request->comment;
        $success['request'] = 1;
        return response()->json(array(
            'status' => '200',
            'data' => $success,
            'message' => "Request successfully Received.",
        ));

    }
    public function changeStatus($id)
    {

            $file=File::find($id);
            if ($file->status == 1)
            {
                $file->status = 0;
                $success['status'] = 'File blocked';
            }else
            {
                $file->status = 1;
                $file->request = 0;
                $success['status'] = 'File unblocked';
            }

            $file->save();

        return $this->sendResponse($success, 'Request successfully Received.');

    }

    public function downloadAndDate(Request $request)
    {
        $find=File::find($request->id);
        $find->last_download_date = Carbon::now()->addHour(2);
        $find->save();

        $success['last_download_date'] = Carbon::now();

        $setIp=new UserFileIp();
        $setIp->file_id = $request->id;
        $setIp->user_ip = $request->ip();
        $setIp->created_at = Carbon::now()->addHour(2);
        $setIp->updated_at = Carbon::now()->addHour(2);
        $setIp->save();

        return $this->sendResponse($success, 'Last download date successfully updated.');
    }
    public function autoDelete()
    {
        $files = File::all();

        foreach ($files as $file)
        {
           //return  File::find(100)->delete();
            $cTime= Carbon::parse(Carbon::now())->format('y-m-d');
            $fTime= Carbon::parse($file->created_at);
            $daysDifference= $fTime->diffInDays($cTime);

            if ($daysDifference > 14 && $file != null)
            {
                File::find($file->id)->delete();
                if (file_exists('files/'.$file->filenames))
                {
                    unlink('files/'.$file->filenames);
                }
            }

        }
        return response()->json(array(
            'status' => '200',
            'message' => "14 days inactive files deleted successfully",
        ));
    }

    public function requestList()
    {
       $file= File::where('request', 1)
           ->select('id', 'filenames', 'comment', 'request')
            ->get();

        return response()->json(array(
            'status' => '200',
            'data' => $file,
            'message' => "Request successfully Received.",
        ));

    }
}

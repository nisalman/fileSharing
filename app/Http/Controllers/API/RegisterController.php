<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Session;
Use Exception;

class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            $errors = $validator->errors();
            return $errors->toJson();
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);


        try
        {
            $user = User::create($input);
        }
        catch(Exception $e)
        {
            //dd($e->getMessage());
            return response()->json(array(
                'status' => '401',
                'reason' =>$e->getMessage(),
                'message' => "User Register Unsuccessful",
            ));
        }


        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;

        return response()->json(array(
            'status' => '200',
            'data' => $success,
            'message' => "User register successfull",
        ));

        //return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            //$success['token'] =  'abc';
            $success['name'] =  $user->name;
            $success['user'] =  $user->is_admin;
            $success['user_id'] =  $user->id;

            //$success['token']);
            Session::put('userId', $user->id);
            return response()->json(array(
                'status' => '200',
                'data' => $success,
                'message' => "User login successfull",
            ));
            //return $this->sendResponse($success, 'User login successfully.');
        }
        else{
            return response()->json(array(
                'status' => '401',
                'message' => "Unauthorized",
            ));
            //return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }
    public function logout()
    {
        Auth::logout();

        return response()->json(array(
            'status' => '200',
            'message' => "User successfully logged out",
        ));

    }
}

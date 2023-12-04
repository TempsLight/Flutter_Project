<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;


class UserController extends Controller
{
    public function createUser(Request $request)
    {
        //validation
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
        ];
        $validator = Validator::make($request->all(),$rules);
        if ($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        //create new users
        $users = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        //$token =$users->CreateToken('Personal Access Token')->plainTextToken;
        $response = ['user'=> $users];
        return response()->json($response,200);
    } 

    public function login(Request $request){
        // validate inputs
        $rules = [
            'email' => 'required',
            'password' => 'required|string'
        ];
        $request->validate($rules);
        // find user id in the table
        $users = User::where('email', $request->email)->first();
        // if user email found and password matched 
        if ($users && Hash::check($request->password, $users->password)){
            $token = $users->createToken('Personal Access Token')->plainTextToken;
            $response=['user'=>$users, 'token'=>$token];
            return response()->json($request,200);
        }
        $response = ['message'=> 'Incorrect Email or Password'];
        return response()->json($response,400);
    }


    public function index()
    {
        $user = User::all();
        $data = [
            'status' => 200,
            'user' => $user
        ];
        return response()->json($data, 200);
    }



    public function editUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {

            $data = [
                "status" => 422,
                "message" => $validator->messages()
            ];
            return response()->json($data, 422);
        } else {
            $user = User::find($id);

            $user->name = $request->name;
            $user->email = $request->email;

            $user->save();

            $data = [
                'status' => 200,
                'message' => 'Data Updated Successfully'
            ];
            return response()->json($data, 200);
        }
    }

    public  function deleteUser($id)
    {
        $user = User::find($id);

        $user->delete();
        $data = [
            'status' => 200,
            'message' => 'Deleted Successfully'
        ];
        return response()->json($data, 200);
    }
}

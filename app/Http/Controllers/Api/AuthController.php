<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use function App\apiResponse;

class AuthController extends Controller
{

    /**
     * Create User
     * @param Request $request
     * @return User
     */
    public function register(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make($request->all(),
            [
                'name' => 'required',
                'phone' => 'required|string|size:11|regex:/^[0-9]+$/',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8'
            ]);

            if($validateUser->fails()){
                return apiResponse(401,"","validation error : ".$validateUser->errors());
            }

            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            $data=[
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ];
            return apiResponse(201,$data,'User Created Successfully');
        } catch (\Throwable $th) {
            return apiResponse(500,'', $th->getMessage());
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return apiResponse(401,"","validation error : ".$validateUser->errors());
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return apiResponse(401,"","Email & Password does not match with our record.");
            }

            $user = User::where('email', $request->email)->first();
            $data=[
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ];
            return apiResponse(201,$data,"User Logged In Successfully");

        } catch (\Throwable $th) {
            return apiResponse(500,'', $th->getMessage());
        }
    }
        /**
     * Logout The User
     * @param Request $request
     */
    public function logout()
    {
        $user = auth('sanctum')->user();

        if ($user) {
            $user->tokens->each->delete();

            return apiResponse(200, [], "User logged out successfully");
        }

        return apiResponse(401, [], "Unauthenticated");
    }
}

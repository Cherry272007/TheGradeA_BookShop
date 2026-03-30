<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
     //User registration
    public function register(Request $request){
        $validate = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'password'      => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()   // Must have letters
                    ->mixedCase() // Must have uppercase & lowercase
                    ->numbers()   // Must have at least one number
                    ->symbols()   // Must have at least one special character
                    // ->uncompromised(), // Security: Ensures password hasn't been leaked online
            ],
        ]);
        try{
            if($validate->fails()){
                return response()->json([
                    'status' => 'error',
                    'message' => $validate->errors()
                ], 422);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'role' => 'user',      
            ]);
            // $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'status'  => 'success',
                'message' => 'User registered successfully',
                'user'    => $user,  // No more 'data' wrapper
                // 'token'   => $token, // No more 'data' wrapper
            ], 201);
        }catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    //User login
        public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        try {
            if ($validate->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid email or password'
                ], 401);   
            }

            // Generate token using the user's browser/device name
            $token = $user->createToken($request->userAgent() ?? 'auth_token')->plainTextToken;

            return response()->json([
                'status'  => 'success',
                'message' => 'User logged in successfully',
                    'token' => $token,
                    'user'  => $user // profile_image_url is now automatically included!
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'An unexpected error occurred'
            ], 500);    
        }
    }
    //User logout
    public function logout(Request $request){
        try{
            $request->user()?->tokens()?->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'User logged out successfully'
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);   
        }
    }
}

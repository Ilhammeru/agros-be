<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        $errMessage = [
            'required'  => ':attribute tidak boleh kosong',
            'string'    => ':attribute harus berisi text',
            'email'     => ':email belum sesuai'
        ];
        $validator = Validator::make($request->all(), [
            'email' => 'email:rfc,dns|required',
            'password'  => 'required',
            'name'  => 'string|required',
            'role'  => 'string|required',
            'city'  => 'required'
        ], $messages = $errMessage);

        if ($validator->fails()) {
            return response()->json([
                'data'	=> [],
                'errors'    => $validator->errors()->all(),
                'status'    => false
            ], 500);
        }

        // hashing password
        $request['password'] = Hash::make($request->password);

        DB::beginTransaction();

        // get id of role and scope token
        try {
            $roles = Role::where('name', strtolower($request->role))->first();
            if ($roles) {
                $roleArr = [$roles->id];

                // set scope of token
                if ($roles->id == 1) {
                    $scopes = 'super-admin';
                } else {
                    $scopes = 'customer';
                }
            } else {
                return response()->json([
                    'data'	=> [],
                    'errors' => ['Role hanya bisa diisi dengan Customer / Super admin'],
                    'status'    => false
                ], 500);
            }
            
            $check = User::where("email", $request->email)->first();
            if ($check) {
                return response()->json([
                    'data'	=> [],
                    'errors' => ['Email sudah terdaftar'],
                    'message'   => 'Duplicate data',
                    'status'    => false
                ], 500);
            }
            $user = User::create($request->toArray());
            $user->userRoles()->sync($roleArr);
            $user->mitra()->create([
                'city'  => $request->city
            ]);
            $token = $user->createToken('Api token', [$scopes])->accessToken;

            $latest = User::with(['mitra', 'userRoles'])->latest()->first();
            
            DB::commit();
            return response()->json([
                'data'	=> $latest,
                'token' => $token,
                'status'    => true
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'data'	=> [],
                'message'   => $th->getMessage(),
                'status'    => false
            ]);
        }
    }

    public function login(Request $request) {
        $errMessage = [
            'required'  => ':attribute tidak boleh kosong',
            'string'    => ':attribute harus berisi text',
            'email'     => ':email belum sesuai'
        ];
        $validator = Validator::make($request->all(), [
            'email' => 'email:rfc,dns|required',
            'password'  => 'required'
        ], $message = $errMessage);

        if ($validator->fails()) {
            return response()->json([
                'data'	=> [],
                'errors'    => $validator->errors()->all(),
                'status'    => false
            ], 500);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user(); 
            $role = User::with('userRoles')->find($user->id)->userRoles[0]->pivot->role_id;
            if ($role == 1) {
                $scope = 'super-admin';
            } else {
                $scope = 'customer';
            }
            $return['token'] =  $user->createToken('MyApp', [$scope])-> accessToken; 
            $return['user'] =  $user;
            $return['scope']   = $scope;

            return response()->json([
                'data'	=> $return,
                'message'   => 'Data retrieve',
                'status'    => true
            ], 201);
        }
        else{
            return response()->json([
                'data'	=> [],
                'errors'    => ['Email / Password tidak sesuai'],
                'message'   => 'Invalid auth',
                'status'    => false
            ], 500);
        } 
    }

    public function logout() {
        $user = auth()->user()->token();
        $user->revoke();

        return response()->json([
            'data'	=> [],
            'message'   => 'Logout success',
            'status'    => true
        ]);
    }
}

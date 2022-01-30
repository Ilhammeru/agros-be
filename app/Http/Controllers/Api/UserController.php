<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Mitra;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function general() {
        $user = auth()->user();
        $customer = User::with(['mitra', 'userRoles'])->get();
        
        return response()->json([
            'data'	=> $customer,
            'message'   => count($customer) == 0 ? 'Data not found' : 'Data retrieve',
            'status'    => count($customer) == 0 ? false : true
        ]);
    }

    public function index() {
        $data = User::with(['mitra', 'userRoles'])->get();

        return response()->json([
            'data'	=> $data,
            'message'   => count($data) > 0 ? 'Data retrieve' : 'Data not found',
            'status'    => count($data) > 0 ? true : false
        ]);
    }

    public function show() {
        $user = auth()->user();
        $query = User::with(['mitra', 'userRoles'])->findOrFail($user->id);

        return response()->json([
            'data'	    => $query,
            'message'   => $query ? 'Data retrieve' : 'Data not found',
            'status'    => $query ? true : false
        ]);
    }

    public function update(CustomerRequest $request) {
        $user = auth()->user();
        $return = [];
        $return['isRefresh'] = false;
        $update = [
            'name'  => $request->name,
            'email' => $request->email,
            'updated_at'    => Carbon::now()
        ];

        if ($request->password != '') {
            $update['password'] = Hash::make($request->password);
            $return['isRefresh'] = true;
        } else if ($user->email != $request->email) {
            $return['isRefresh'] = true;
        }

        $roles = Role::where('name', strtolower($request->role))->first();
        if ($roles == null) {
            return response()->json([
                'data'	=> [],
                'errors' => ['Role hanya bisa diisi dengan Customer / Super admin'],
                'status'    => false
            ], 500);
        }

        DB::beginTransaction();
        try {
            $check = User::with('mitra', 'userRoles')->findOrFail($user->id);
            $check->update($update);
            $check->mitra->city = $request->city;
            $check->push();

            // update roles
            if (strtolower($request->role) == 'super admin') {
                $scope = 1;
            } else {
                $scope = 2;
            }
            $check->userRoles()->sync([$scope]);

            $return['data'] = $check;

            DB::commit();
            return response()->json([
                'data'	=> $return,
                'message'   => "Update success",
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

    public function delete(Request $request) {
        $user = auth()->user();
        $id = $request->id;
        $data = [];
        $isSelf = false;

        DB::beginTransaction();
        try {
            $delete = User::where('id', $id)->delete();
            Mitra::where('user_id', $id)->delete();
            RoleUser::where('users_id', $id)->delete();
            if ($delete) {
                if ($user->id == $id) {
                    $isSelf = true;
                }
                $data = User::with(['mitra', 'userRoles'])->get();
            }

            DB::commit();
            return response()->json([
                'data'	=> $data,
                'message'   => $delete ? 'Hapus data berhasil' : 'Hapus data gagal',
                'status'    => $delete ? true : false,
                'isSelf'    => $isSelf
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'data'	=> [],
                'message'   => $th->getMessage(),
                'status'    => false,
                'isSelf'    => false
            ]);
        }
    }
}

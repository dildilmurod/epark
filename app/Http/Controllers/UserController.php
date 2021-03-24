<?php

namespace App\Http\Controllers;


use App\GenName;
use App\SocialAccount;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => ['current_user', 'users', 'search_by_email', 'back_user', 'update']]);
        $this->middleware('api-auth', ['except' => ['search_by_email', 'back_user']]);

    }

    public function users()
    {
        $users = User::orderBy('id', 'asc')->paginate(20);


        return view('user.index')->with('users', $users);

    }

    public function users_list()
    {
        $users = User::orderBy('id', 'asc')->paginate(20);


        return response()->json(
            [
                'success' => true,
                'data' => $users,
                'message' => 'Users are retrieved successfully'
            ],
            201);

    }

    public function destroy($id)
    {

        $user = User::find($id);
        if (empty($user)) {
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'message' => 'User not found'
                ],
                404);
        }
        $social = SocialAccount::where('user_id', $user->id)->first();

        if ($social) {
            $social->delete();
        }

        if (!is_null($user->avatar) || !empty($user->avatar)) {
            $path = public_path() . 'avatars' . $user->avatar;
            if (File::exists($path)) {
                File::delete($path);
            }
        }


        if ($user->delete()) {
            return response([
                'success' => true,
                'data' => [],
                'message' => 'User deleted successfully'
            ],
                201);
        }
        return response()->json(
            [
                'success' => false,
                'data' => [],
                'message' => 'Something went wrong. Try again'
            ],
            401);

    }

    public function change_role(Request $request, $id)
    {
        $this->validate($request, [
            'role' => 'required',
        ]);
        $role = $request->role;
        // in the future it should compare with roles from user_role table

        if ($role === 'user' || $role === 'admin') {
            $user = User::find($id);
            if (empty($user)) {
                return response()->json(
                    [
                        'success' => false,
                        'data' => [],
                        'message' => 'User not found'
                    ],
                    404);
            }
            $user->role_id = $role;
            $user->save();

            return response([
                'success' => true,
                'data' => [],
                'message' => 'User role changed to ' . $role . ' successfully'
            ],
                201);
        }
        return response()->json(
            [
                'success' => false,
                'data' => [],
                'message' => 'There is no such role'
            ],
            404);


    }

    public function search_by_email(Request $request)
    {
        $user = [];
        $user = User::where('id', '<>', 0);
        if($request->has('email')) {
            $user->where('email', $request->email)->first();
        }

        if ($request->has('name')) {
            $user->where('name', 'like', '%'.$request->name.'%');
        }


        if ($request->has('phone')) {
            $user->where('phone','like', '%'.$request->phone.'%');
        }
        $user = $user->get();

        if (!$user) {
            //returns error if user does not exists
            return response([
                'success' => false,
                'data' => [],
                'message' => 'User is not found'
            ],
                404);
        }
        return response([
            'success' => true,
            'data' => $user,
            'message' => 'User retrieved successfully'
        ],
            201);

    }

    public function show($id)
    {
        $user = User::find($id);
//        $user->course;

        if (!$user) {
            //returns error if user does not exists
            return response([
                'success' => false,
                'data' => [],
                'message' => 'User is not found'
            ],
                404);
        }
        return response([
            'success' => true,
            'data' => $user,
            'message' => 'User retrieved successfully'
        ],
            201);

    }

    public function current_user()
    {
        $user = auth('api')->user(); //gets user with email
        $user->unreadNotificationsCount = $user->unreadNotifications->count();
        $user->unreadNotifications;
        return response([
            'success' => true,
            'data' => $user->makeHidden(['']),
            'message' => 'User data retrieved successfully'
        ],
            201);

    }

    public function back_user()
    {
        $user = auth('api')->user(); //gets user with email
        return response([
            'success' => true,
            'data' => $user->makeHidden(['']),
            'message' => 'User data retrieved successfully'
        ],
            201);

    }

    public function update(Request $request)
    {
        $this->validate($request, [

        ]);
        $id = auth('api')->user()->id;
        $data = $request->except(['avatar', 'password']);
        if (!auth('api')->user()) {
            //returns error if user does not exists
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User not found or not authenticated'
                ],
                403);
        }

        $avatar = $request->file('avatar');
        $user = User::find($id);

        if ($avatar) {

            if (!is_null($user->avatar) || !empty($user->avatar)) {
                $path = public_path() . 'avatars' . $user->avatar;
                if (File::exists($path)) {
                    File::delete($path);
                }
            }
            $gen = new GenName();
            $fileToStore = $gen->generate($avatar, $user->name);
            $avatar->move('avatars', $fileToStore);
            $data['avatar'] = '/avatars/' . $fileToStore;
        }
        $user->update($data);


        return response()->json(
            [
                'success' => true,
                'data' => $user->makeHidden(['']),
                'message' => 'User with id' . $id . ' data updated successfully'
            ],
            201);

    }


}

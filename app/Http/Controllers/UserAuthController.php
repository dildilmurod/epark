<?php

namespace App\Http\Controllers;


use App\Mail\ResetPasswordMail;
use App\Mail\WelcomeMail;
use App\User;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => ['get_token', 'register', 'login', 'users', 'search_by_email',
            'update_password', 'verify', 'resend_verification', 'login_social', 'reset', 'activate_password']]);
        $this->middleware('api-auth', ['except' => ['get_token', 'register', 'login', 'users', 'search_by_email',
            'verify', 'resend_verification', 'login_social', 'reset', 'activate_password']]);

    }

    protected function get_token($email, $password)
    {
        $http = new Client();
        $response = $http->post(url('oauth/token'), [ //forms token
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => 2,
                'client_secret' => '7ITdTZYPu5vGJdyOOCv1d9wftxfb6ceGU6AyDADa',
                'username' => $email,
                'password' => $password,
                'scope' => '',
            ],
        ]);
        return $response;
    }

    //current function registers users
    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|min:4',
            'password' => 'required|min:1',
        ]);


        if (empty(User::where('email', $request->email)->first())) {
            $user = User::firstOrNew(['email' => $request->email]); //checks whether it is new user with this email
            $user->email = $request->email;


            $user->password = bcrypt($request->password); //password through bcrypt
            $user->code = substr(md5(uniqid($request->email)), 0, 10);
            $user->save();
            $this->sendverification($user->email, $user->code);

            return response(
                [
                    'success' => true,
                    'data' => [],
                    'user' => $user,
                    'message' => 'Пользователь зарегистрирован успешно. Пожалуйста подтвердите свой email'
                ],
                200);
            // $response = $this->get_token($request->email, $request->password);
            // return response(
            //     [
            //         'success' => true,
            //         'data' => json_decode((string)$response->getBody(), true),
            //         'user' => $user,
            //         'message' => 'Пользователь зарегистрирован успешно'
            //     ],
            //     200);
        } else
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Пользователь с таким email уже существует'
                ],
                401);


    }

    public function login_social(Request $request)
    {
        $this->validate($request, [
            'provider' => 'required',
            'access_token' => 'required'
        ]);
        $body = '';
        $response = '';
        $http = new Client();
        try {
            $response = $http->post(url('oauth/token'), [ //forms token
                'form_params' => [
                    'grant_type' => 'social',
                    'client_id' => 2,
                    'client_secret' => '7ITdTZYPu5vGJdyOOCv1d9wftxfb6ceGU6AyDADa',
                    'provider' => $request->provider,
                    'access_token' => $request->access_token,
                ],
            ]);
            $query = $http->get(url('api/back-user'), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . json_decode((string)$response->getBody(), true)['access_token'],
                ],
            ]);

            $body = json_decode((string)$query->getBody(), true);
            $user = User::find($body['data']['id']);
            if (is_null($user->email_verified_at)) {
                $user->email_verified_at = date("Y-m-d g:i:s");
                $user->save();
            }

            return response()->json(
                [
                    'success' => true,
                    'data' => json_decode((string)$response->getBody(), true),
                    'message' => 'User registered successfully with ' . $request->provider
                ],
                201);

        } catch (Exception $e) {
            if ($e instanceof HttpException && $e->getStatusCode() == 401) {

            }
        }
        return response()->json(
            [
                'success' => false,
                'data' => [],
                'message' => 'Password or login is wrong. Check credentials'
            ],
            401);

    }

    //logins user
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|min:6',
            'password' => 'required|min:1'
        ]);

        $user = User::where('email', $request->email)->first(); //gets user with email
        if (!$user) {
            //returns error if user does not exists
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User not found. Check credentials'
                ],
                404);
        }
        if (empty($user->email_verified_at) || is_null($user->email_verified_at)) {
            return response(
                [
                    'success' => true,
                    'data' => [],
                    'user' => $user,
                    'message' => 'Ошибка авторизации. Пожалуйста подтвердите свой email'
                ],
                401);
        }

        if (Hash::check($request->password, $user->password)) { //checks passwords

            $response = $this->get_token($request->email, $request->password);

            $user->groups;

            return response([
                'success' => true,
                'data' => json_decode((string)$response->getBody(), true),
                'user' => $user->makeHidden(['']),
                'message' => 'User logged in successfully'
            ],
                201);
        }
        return response()->json(
            [
                'success' => false,
                'message' => 'Password is wrong. Check credentials'
            ],
            404);

    }

    //logins user
    public function update_password(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|min:1',
            'new_password' => 'required|min:1'
        ]);

        $user = auth('api')->user();

        if (!$user) {
            //returns error if user does not exists
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User not found. Check credentials'
                ],
                404);
        }

        if (Hash::check($request->password, $user->password)) { //checks passwords
            $user->password = bcrypt($request->new_password);
            $user->save();

            $response = $this->get_token($user->email, $request->new_password);


            return response([
                'success' => true,
                'data' => json_decode((string)$response->getBody(), true),
                'user' => $user->makeHidden(['']),
                'message' => 'User password updated successfully'
            ],
                201);
        }
        return response()->json(
            [
                'success' => false,
                'data' => [],
                'message' => 'Old password is wrong. Check credentials'
            ],
            404);

    }

    public function verify(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|min:1',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'message' => 'User not found. Check credentials'
                ],
                404);
        }
        if ($request->deadline < Carbon::now()) {
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'message' => 'This link is out of date. Please try again'
                ],
                403);
        }
        if ($request->code != $user->code) {
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'message' => 'Code or link is incorrect. Please try again'
                ],
                403);
        }
        $date = date("Y-m-d g:i:s");
        $user->email_verified_at = $date;
        $user->code = '';
        $user->save();
        sleep(3);
        return redirect('https://epark.uz/');


    }

    public function resend_verification(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|min:1',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'message' => 'User not found. Check credentials'
                ],
                404);
        }
        if (!is_null($user->email_verified_at) || !empty($user->email_verified_at)) {
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'message' => 'You have already verified your password. Please login'
                ],
                403);
        }
        $user->code = substr(md5(uniqid($request->email)), 0, 10);
        $user->save();
        $this->sendverification($user->email, $user->code);
        return response([
            'success' => true,
            'data' => [],
            'message' => 'User email verification sent successfully'
        ],
            201);

    }

    protected function sendverification($email, $code)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return false;
        }
        $email = $user->email;
        $deadline = Carbon::now()->addMinutes(60);
        $sendcode = $code;

        $text = " Здравствуйте,
        Благодарим Вас за регистрацию на портале Epark.uz. Чтобы завершить регистрацию, нажмите на следующую кнопку:";
        $end = "Если Вы не можете пройти по ссылке, скопируйте ссылку в окно вашего браузера или введите непосредственно с клавиатуры.
        Если вы не зарегистрировали аккаунт на Epark.uz, пожалуйста,
        проигнорируйте это сообщение.";

        $url = url('/') . "/api/verify?email=" . $email . "&deadline=" . $deadline . "&code=" . $sendcode . "";

        Mail::to($email)->send(new WelcomeMail($text, $end, $url));

        return true;

    }

    public function reset(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|min:4',
        ]);
        $user = User::where('email', $request->email)->first();
        if (empty($user)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'There is no such user'
                ],
                404);
        }
        if ($user->email_verified_at == null) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Your email is not verified'
                ],
                404);
        }
        $temppass = substr(md5(uniqid($user->email)), 0, 10);
        $user->reset = $temppass;
        $user->code = substr(md5(uniqid($user->email)), 0, 10);
        $deadline = Carbon::now()->addMinutes(60);
        $user->save();

        $passtext = $temppass;
        try {
            $url = url('/') . "/api/activate-password?email=" . $user->email . "&deadline=" . $deadline . "&code=" . $user->code . "";

            Mail::to($user->email)->send(new ResetPasswordMail($url, $passtext));
            return response()->json(
                [
                    'success' => true,
                    'data' => [],
                    'message' => 'Reset email sent successfully'
                ],
                201);
        } catch (Exception $e) {
//            if ($e instanceof HttpException && $e->getStatusCode() == 401) {
//
//            }
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'message' => 'Something went wrong. Check credentials'
                ],
                401);
        }
        return response()->json(
            [
                'success' => false,
                'data' => [],
                'message' => 'Something went wrong. Check credentials'
            ],
            401);


    }

    public function activate_password(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|min:1',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'message' => 'User not found. Check credentials'
                ],
                404);
        }
        if ($request->deadline < Carbon::now()) {
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'message' => 'This link is out of date. Please try again'
                ],
                403);
        }
        if ($request->code != $user->code) {
            return response()->json(
                [
                    'success' => false,
                    'data' => [],
                    'message' => 'Code or link is incorrect. Please try again'
                ],
                403);
        }
        $date = date("Y-m-d g:i:s");
        $user->code = '';
        $user->password = bcrypt($user->reset);
        $user->reset = '';
        $user->save();
        sleep(2);
        return redirect('https://epark.uz/');


    }

}




<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use Naux\Mail\SendCloudTemplate;

class UsersController extends Controller
{
    public function register()
    {
        return view('users.register');
    }

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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\UserRegisterRequest $request)
    {
        //保存用户数据，重定向
        $data = [
            'confirm_code' => str_random(48),
            'avatar' => '/images/default-avatar.png',
        ];
        $user = User::create(array_merge($request->all(), $data));
        //发送验证邮件
        $subject = "{$user->name}请激活您的邮箱";
        $view = 'email.register';
        $this->sendTo($user, $subject, $view, $data);
        return redirect('/');
    }

    public function confirmEmail($confirm_code)
    {
        $user = User::where('confirm_code', $confirm_code)->first();
        if (is_null($user)) return redirect('/');
        $user->is_confirmed = 1;
        $user->confirm_code = str_random(48);
        $user->save();
        return redirect('user/login');
    }

    public function login()
    {
        return view('users.login');
    }

    public function signin(Requests\UserLoginRequest $request)
    {
        if (\Auth::attempt([
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'is_confirmed' => 1
        ])
        ) {
            return redirect('/');
        }
        \Session::flash('user_login_failed', '密码不正确或邮箱没有验证');
        return redirect('user/login')->withInput();
    }

    public function avatar()
    {
        return view('users.avatar');
    }

    public function changeAvatar(Request $request)
    {
        $file = $request->file('avatar');
        //判断错误信息
        $input = ['image' => $file];
        $rules = ['image' => 'image'];
        $validator = \Validator::make($input, $rules);
        if ($validator->fails()) return \Response::json(['success' => false, 'errors' => $validator->getMessageBag()->toArray]);

        //设置图片上传路径和文件名
        $destinationPath = 'uploads/';
        $filename = \Auth::user()->id . '_' . time() . $file->getClientOriginalName();

        //上传图片并裁剪图片
        $file->move($destinationPath, $filename);
        Image::make($destinationPath . $filename)->fit(400)->save();

        return \Response::json([
            'success' => true,
            'avatar' => asset($destinationPath . $filename),
            'image' => $destinationPath . $filename
        ]);
    }

    public function cropAvatar(Request $request)
    {
        //去除路径中前面的斜杠
        $photo = $request->get('photo');
        $width = (int)$request->get('w');
        $height = (int)$request->get('h');
        $xAlign = (int)$request->get('x');
        $yAlign = (int)$request->get('y');
        Image::make($photo)->crop($width, $height, $xAlign, $yAlign)->save();
        $user = \Auth::user();
        //返回图片的路径
        $user->avatar = asset($request->get('photo'));
        $user->save();
        return redirect('/user/avatar');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function sendTo($user, $subject, $view, $data = [])
    {
        Mail::send($view, $data, function ($message) use ($user, $subject) {
            $message->to($user->email)->subject($subject);
            $bind_data = ['url' => "http://community.laravel.dev/verify/{$user->confirm_code}"];
            $template = new SendCloudTemplate('test_template_active', $bind_data);
            $message->getSwiftMessage()->setBody($template);
        });
    }

    public function logout()
    {
        \Auth::logout();
        return redirect('/');
    }
}

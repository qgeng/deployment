<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Libraires\ApiResponse;
use App\Libraires\ToolsResponse;
use Validator;
use App\User;

class LoginController extends Controller
{

	use ApiResponse , ToolsResponse;
    /**
     * @SWG\Post(path="/api/login/login",
     *   tags={"用户及权限"},
     *   summary="用户登录",
     *   description="登录获取token",
     *   operationId="login",
     *   produces={"application/json"},
     *   @SWG\Parameter(in="formData",name="email",type="string",description="邮箱地址",required=true),
     *   @SWG\Parameter(in="formData",name="password",type="string",description="登录密码",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function login(Request $request)
    {

        // 验证规则，由于业务需求，这里我更改了一下登录的用户名，使用email登录
        $rules = [
            'email'   => 'required',
            'password' => 'required',
        ];

        $validator = validator::make($request->all(), $rules,[
            'email.required' => '邮箱不存在',
            'password.required' => '邮箱或密码错误',
        ]);

        if ($validator->fails()) return $this->failed($validator->errors()->first());

        $user = User::where([
        	'email' => $request->input('email'),
        	'password' => sha1(config('app.password_sort') . $request->input('password'))
        ])
       	->select(['id','email','name'])
		->first();
        
        if ($user) {
            $token = Auth::guard('api')->login($user);
            return $this->success([
                        'token'=>'bearer '.$token,
                        'user'=>$user
                   ]);
        }

        return $this->failed('邮箱或密码错误');
    }



    /**
     * @SWG\Get(path="/api/login/regist",
     *   tags={"用户及权限"},
     *   summary="注册用户",
     *   description="注册",
     *   operationId="regist",
     *   produces={"application/json"},
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function regist(Request $request)
    {
        return $this->message('注册接口尚未完工');
    }


    /**
     * @SWG\Get(path="/api/login/logout",
     *   tags={"用户及权限"},
     *   summary="注销用户",
     *   description="注销用户token",
     *   operationId="logout",
     *   produces={"application/json"},
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function logout()
    {
        Auth::guard('api')->logout();
        return response(['message' => '退出成功']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
class BaseController extends ApiController
{

	/**
     * @SWG\Get(path="/api/base/getAuthUser",
     *   tags={"公共方法"},
     *   summary="获取用户登录信息",
     *   operationId="getAuthUser",
     *   produces={"application/json"},
     *   @SWG\Parameter(in="header",name="authorization",type="string",description="用户token",required=true),
     *   @SWG\Response(response="200", description="操作成功")
     * )
     */
	public function getAuthUser (Request $request) 
	{
		$user = $this->auth::guard('api')->user();
		if (!$user) return $this->notFond();
		return $this->success($user);
	}
}

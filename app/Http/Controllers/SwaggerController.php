<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artisan;
use Swagger\Annotations as SWG;


/**
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="API文档-`前后端文档`",
 *     version="Version-1.0.0"
 *   )
 * )
 */
class SwaggerController extends Controller
{
    public function getJSON ()
    {
        $swagger = \Swagger\scan(app_path('Http/Controllers'));
        return response()->json($swagger, 200);
    }

    /**
     * @SWG\Get(path="/api/swagger/update",
     *   tags={"Swagger"},
     *   summary="刷新swagger",
     *   description="刷新swagger文档",
     *   operationId="swagger_update",
     *   produces={"application/json"},
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function update ()
    {
        Artisan::call("l5-swagger:generate");
        return $this->message("刷新成功，请重新刷新网页","success");
    }
}


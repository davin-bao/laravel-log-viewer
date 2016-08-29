<?php

namespace DavinBao\PhpGit\LaravelLogViewer;

use Exception;
use Closure;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CatchException
 * @package DavinBao\PhpGit\LaravelLogViewer
 *
 * 错误提示处理
 * 600-699 属于 HTML 错误
 * 700-799 属于 JSON 错误
 *
 * @author davin.bao
 * @since 2016/7/15 9:34
 */
class CatchException
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
        } catch(Exception $exception){
            $code =$exception->getCode();

            if($code< 100 || $code >= 600){
                $code = 512;  //运行时异常
            }

            $errors = ['msg'=>$exception->getMessage(), 'code'=>$code];
            if ($request->ajax() || $request->wantsJson()) { //输出 JSON 字符串
                return new JsonResponse($errors, $code);
            }

            //输出异常信息， 跳转回 GET 页
            \Html::error($exception->getMessage(), $code);

            return redirect()->back()
                ->withInput($request->input())
                ->withErrors($errors, $exception->getMessage());
        }
    }

}

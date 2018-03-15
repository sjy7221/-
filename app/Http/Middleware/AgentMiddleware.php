<?php
namespace App\Http\Middleware;

use Closure;
use DB;
class AgentMiddleware
{
    /**
     * 返回请求过滤器
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //没有设置sesssion则重定向到登陆页面
        if ($request->session()->has('mid')) {
            return $next($request);
        }else{
            return redirect('agent/login');
        }

    }

}
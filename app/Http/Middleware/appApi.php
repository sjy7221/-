<?php

namespace App\Http\Middleware;
use DB;
use Closure;


class appApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
        $mid = $request->get('mid');
        $token= $request->get('token');
        if($mid && $token){//
           /* $re = DB::table('member')
                 ->select('id')
                 ->where(['id'=>$mid,'token'=>$token])
                 ->first();*/
            $re=1;
            if($re){
                return $next($request);
             }else {
                echo json_encode(['status' => 404, 'msg' => '账户异常登入!']);
                exit;
            }
        }else{
            echo json_encode(['status'=>405,'msg'=>'mid或token参数错误']);exit;
        }
    }
}

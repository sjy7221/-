<?php
namespace App\Http\Middleware;

use Closure;
use DB;
class AdminMiddleware
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
        if ($request->session()->has('admin_id')) {
            $admin_id = $request->session()->get('admin_id');
        }else{
            return redirect('admin/login');
        }
        //获取访问的控制器方法
        $path =$request->route()->getActionName();
        //获取请求的方式
        $method =$request->route()->getMethods()[0];
        list($class, $function) = explode('@', $path);
        $class = substr(strrchr($class,'\\'),1);
        $class = strtolower(substr($class,0,strpos($class,'C')));
        $action = 'admin'.'.'.$class.'.'.$function;

        if($method !=='GET' || $admin_id == 127){
            return $next($request);
        }else{
            $re = DB::table('admin_role_node as rn')
                ->leftJoin('admin_node as n','rn.node_id','=','n.id')
                ->leftJoin('admin as a','rn.role_id','=','a.role_id')
                ->where(['a.id'=>$admin_id,'n.action'=>$action])
                ->select('rn.node_id','rn.role_id')
                ->first();
            if($re){
                return $next($request);
            }else{
                return error('您没有操作权限！请联系管理员！');
            }
        }

    }

}
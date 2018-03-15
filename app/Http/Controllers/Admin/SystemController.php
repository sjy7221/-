<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Input;

class SystemController extends Controller
{
    public function log()
    {
        $input = Input::all();
        $data = DB::table('log')->orderBy('time','desc')->paginate(15);
        return view('admin.system.log',compact('data','input'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Goods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;


class GoodsController extends Controller
{


    /**
     * 商品列表
     */
    public function index()
    {
        $data = Goods::get_index();

        return view('admin.goods.index', compact('data'));
    }

    /**
     * 修改显示
     */
    public function edit_show($id)
    {
        $id = num($id);
        if ($id == '0') return error('非法参数');
        $data = Goods::getFirst($id);
        return view('admin.goods.edit', compact('data'));
    }

    /**
     * 修改入库 接受方式为POST
     */
    public function edit(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'num' => 'required',
            'money' => 'required',
            'sort' => 'required',
            'is_show' => 'required',
            'id' => 'required'
        ], [
            'num.required' => '请填写钻石数量',
            'money.required' => '请填写出售金额',
            'sort.required' => '请填写排序',
            'is_show.required' => '请选择是否显示',
            'id.required' => '参数错误'
        ]);
        $input = Input::all();
        extract($input);
        if ((num($id) == '0') || is_null(num($num)) || is_null(money($money)) || is_null(num($sort)) || is_null(num($is_show))) {
            return error('请正确填写商品信息');
        }
        //执行更新
        $re = Goods::update_one($input);
        if ($re) {
            plog('修改商品信息 ID：' . $id);
            return success('操作成功', 'admin/goods/index');
        } else {
            return error('操作失败');
        }
    }

    /**
     * 添加商品
     */
    public function add_show()
    {
        if (Goods::get_count() >= 8) return error('最多只能添加8条商品');
        return view('admin.goods.add');
    }

    /**
     * 添加商品入库 接受方式为POST
     */
    public function add(Request $request)
    {
        if (Goods::get_count() >= 8) return error('最多只能添加8条商品');
        //数据验证
        $this->validate($request, [
            'num' => 'required',
            'money' => 'required',
            'sort' => 'required',
            'is_show' => 'required',
            'goodsurl' => 'required'
        ], [
            'num.required' => '请填写钻石数量',
            'money.required' => '请填写出售金额',
            'sort.required' => '请填写排序',
            'is_show.required' => '请选择是否显示',
            'goodsurl.required' => '请选择商品图片'
        ]);
        $input = Input::all();
        extract($input);
        if (is_null(num($num)) || is_null(money($money)) || is_null(num($sort)) || is_null(num($is_show))) {
            return error('请正确填写商品信息');
        }
        //上传文件
        $re = upload_check($_FILES);
        if ($re !== true) return error($re);
        $url = upload($_FILES['goodsurl'],'images/user');//只存在上传成功  不然就报错
        //插入数据
        $input['goodsurl'] = $url;
        $id = Goods::insert_one($input);
        if ($id) {
            plog('添加商品 ID' . $id);
            return success('操作成功', 'admin/goods/index');
        } else {
            return error('操作失败');
        }
    }

    /**
     * 商品设为显示和不显示
     */
    public function black($id)
    {
        $id = num($id);
        $status = num(Input::get('status'));
        if (!in_array($status, [0, 1]) || ($id == '0')) return error('非法参数');
        DB::beginTransaction();
        //上行锁
        $data = Goods::getFirst($id, true);
        if ($status == $data->is_show) {
            DB::rollBack();
            return error('非法参数');
        }
        //修改状态
        $re = Goods::update_one_status($id, $status);
        if ($re) {
            DB::commit();
            if ($status == '0') plog('设置商品显示 ID' . $id);
            if ($status == '1') plog('设置商品不显示显示 ID' . $id);
            return success('操作成功', 'admin/goods/index');
        } else {
            DB::rollBack();
            return error('操作失败');
        }
    }
}

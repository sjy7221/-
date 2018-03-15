<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Member;

class Admin extends Model
{
    protected $table = 'admin';
    public $timestamps = false;

    //登入验证
    public static function login($name, $password)
    {
        $admin = static:: where('admin.name', $name)
            ->select('id', 'name', 'salts', 'password')
            ->first()
            ->toArray();
        $password = md5(md5($password . $admin['salts']));
        if ($password == $admin['password']) {
            $ip = $_SERVER["REMOTE_ADDR"];
            static::where('id', $admin['id'])->update([
                'time' => date('Y-m-d H:i:s', time()),
                'ip' => $ip,
                'address' => getCity()
            ]);
            return $admin;
        } else {
            return false;
        }
    }

    //添加管理员
    public static function createAdmin(array $attributes)
    {
        extract($attributes);
        $salts = substr(uniqid(), -6);
        $data = [
            'name' => Trim_All($name),
            'realname' => Trim_All($realname),
            'password' => md5(md5(Trim_All($password) . $salts)),
            'salts' => $salts,
            'role_id' => num($role_id)
        ];
        return self::insertGetId($data);

    }

    public static function isHave($name)
    {
        return static::where('name', $name)->first();
    }


    public static function getAll()
    {
        $data = self::leftJoin('admin_role', 'admin.role_id', '=', 'admin_role.id')
            ->select('admin.*', 'admin_role.name as rname')
            ->where('admin.id', '<>', 127)
            ->get()
            ->toArray();
        return $data;
    }

    //首页列表
    public static function get_index(array $input)
    {
        extract($input);
        $id = num(isset($id) ? $id : '');
        $where_id = !($id >= 1) ? [] : ['admin.id' => $id];
        $name = Trim_All(isset($name) ? $name : '');
        $realname = Trim_All(isset($realname) ? $realname : '');
        $data = self::leftJoin('admin_role', 'admin.role_id', '=', 'admin_role.id')
            ->select('admin.*', 'admin_role.name as rname')
            ->where($where_id)
            ->where('admin.name', 'like', "$name%")
            ->where('admin.realname', 'like', "$realname%")
            ->where('admin.id', '<>', 127)
            ->paginate(5);
        return $data;
    }

    //枷锁查询
    public static function getFirstForLock(int $id)
    {
        $data = static::where('id', $id)->sharedLock()->first()->toArray();
        return $data;
    }

    public static function getFirst(int $id)
    {
        $data = static::where('id', $id)->first()->toArray();
        return $data;
    }

    public static function editAdmin(array $attributes)
    {
        extract($attributes);
        $data = [
            'password' => md5(md5($password . $salts)),
            'role_id' => $role_id
        ];
        return self::where('id', $id)->update($data);
    }


    public static function editPassword(array $attributes)
    {
        extract($attributes);
        $data = [
            'password' => md5(md5($password . $salts))
        ];
        return self::where('id', $id)->update($data);
    }


    public static function delAdmin($id)
    {
        return static::where('id', $id)->delete();
    }

    public static function blackAdmin($id, $status)
    {
        $re = true;
        if ($status == '1') {
            $agency = static::getFirstForLock($id);
            //执行拉黑操作
            $re = Member::clean_agency($agency['name']);
        }
        $re1 = static::where('id', $id)->update(['status' => $status]);
        if ($re !== false && $re1 !== false) return true;
        return false;
    }

    public static function outLogin(int $admin_id)
    {
        $data = static::getFirst($admin_id);
        static::where('id', $admin_id)->update([
            'last_time' => $data['time'],
            'last_ip' => $data['ip'],
            'last_address' => $data['address']
        ]);
    }

    //根据id对用户佣金加减(默认减钱)
    public static function update_num($id, $money, $bool = true)
    {
        if ($bool) {
            $re = static::where('id', $id)
                ->decrement('num', $money);
        } else {
            $re = static::where('id', $id)
                ->increment('num', $money);
        }
        return $re;
    }

}

<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/8/31
 * Time: 19:43
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $fillable = [
        'nickname', 'email'
    ];

    public function accounts()
    {
        return $this->belongsToMany('App\Models\Account');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getEmailAttribute($raw)
    {
        if($raw=='' || $raw==null)    return 'No E-Mail';
        return $raw;
    }

    //custom methods in model
    public function __call($method, $args)
    {
        //删除与account的关联
        if($method == 'detachAccount')
        {
            $acc = $args[0];
            $this->accounts()->detach($acc);
            $acc->selfCheck();
        }
        //生成email地址混淆html
        else if($method == 'getEmailHTML')
        {
            $raw = $this->email;
            if($raw == 'No E-Mail') return $raw;
            $arr = explode('.', $raw);
            $arr = array_merge(explode('@', $arr[0]), [$arr[1]]);
            $ret = $arr[0] . '<spci>@nomail.com' . '<a href="mailto:no_spam@hhhh.com">contact</a></spci><span>@</span>' . $arr[1] .
                '.<spci>simple@tricks.com</spci><span>' . $arr[2] . '</span><div style="display:none">trap</div><spci>@hhh.com</spci>';
            return $ret;
        }
        else{
            return parent::__call($method, $args);
        }
    }

}
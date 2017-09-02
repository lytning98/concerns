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
        else{
            return parent::__call($method, $args);
        }
    }

}
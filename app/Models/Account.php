<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/8/31
 * Time: 15:45
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'oj', 'username',
    ];

    public function moments()
    {
        return $this->hasMany('App\Models\Moment');
    }

    public function persons()
    {
        return $this->belongsToMany('App\Models\Person');
    }

    //custom methods in model
    public function __call($method, $args)
    {
        //check: if there's no person attached to this account then delete it
        if($method == 'selfCheck')
        {
            if($this->persons->isEmpty()){
                //清理无关联的动态 [warning: 可能引起重复抓取]
                foreach ($this->moments as $mom){
//                    $mom->delete();
                }
                $this->delete();
            }
        }
        else{
            return parent::__call($method, $args);
        }
    }
}
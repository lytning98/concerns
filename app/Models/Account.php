<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/8/31
 * Time: 15:45
 */

namespace App\Models;


use App\Tools\MmtManager;
use App\Tools\SysManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
                $this->remove();
            }
        }
        else if($method == 'remove')
        {
            //清理无关联的动态 (软删除)
            foreach ($this->moments as $mmt){
                $mmt->delete();
            }
            $this->delete();
        }
        else{
            return parent::__call($method, $args);
        }
    }
}
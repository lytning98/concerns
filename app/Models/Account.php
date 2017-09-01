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
        return $this->belongsToMany('App\Person');
    }
}
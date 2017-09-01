<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/8/31
 * Time: 15:39
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Moment extends Model
{
    protected $fillable = [
      'oj', 'account_id', 'event', 'problem', 'description', 'username'
    ];

    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }
}
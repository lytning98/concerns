<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/8/31
 * Time: 15:39
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Moment extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $touches = ['account'];

    protected $fillable = [
      'oj', 'account_id', 'event', 'problem', 'description', 'username', 'time', 'url', 'runid'
    ];

    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }
}
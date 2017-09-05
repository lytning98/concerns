<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/9/2
 * Time: 20:05
 */

namespace App\Tools;


use App\Models\Moment;
use Illuminate\Support\Facades\Log;

class SysManager
{
    public static function debug_action()
    {

    }

    /***
     * 清理过期的已被软删除的动态数据
     */
    public static function cleanMoments()
    {
        $mmts = Moment::onlyTrashed()->where('deleted_at', '<', date("y-m-d H:i:s", strtotime("-4 days")) )->get();
        $mmts->each->forceDelete();
    }

    /**
     * 将新抓取到的moment动态数据绑定到对应Account上
     */
    public static function bindRawMoments()
    {
        $mmts = Moment::whereNull('account_id')->get();
        foreach ($mmts as $mmt)
        {
            $acc = Account::where([
                'oj' => $mmt->oj,
                'username' => $mmt->username
            ])->first();
            if($acc == null){
                $mmt->delete();
            }else{
                $acc->moments()->save($mmt);
            }
        }
    }

    public static function randomEvent($prob, $method)
    {
        $ck = mt_rand(0, 10000)/10000;
        if($ck < $prob) {
            $method();
            return true;
        }else{
            return false;
        }
    }

}
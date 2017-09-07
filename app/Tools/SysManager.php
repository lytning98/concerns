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

/**
 * 工具函数集合
 * Class SysManager
 * @package App\Tools
 */
class SysManager
{
    public static function ratingColor($rating)
    {
        if ($rating < 1200) return 'gray';
        if ($rating < 1400) return 'green';
        if ($rating < 1600) return '#03A89E';
        if ($rating < 1900) return 'blue';
        if ($rating < 2200) return '#aa00aa';
        if ($rating < 2300) return 'yellow';
        if ($rating < 2400) return 'orange';
        //no further demand, i think
        return 'red';
    }

    /**
     * 生成email地址简单混淆
     * @param $raw
     * @return string (html)
     */
    public static function emailObfuscationHTML($raw)
    {
        $arr = explode('.', $raw);
        $arr = array_merge(explode('@', $arr[0]), [$arr[1]]);
        $ret = $arr[0] . '<spci>@nomail.com' . '<a href="mailto:no_spam@hhhh.com">contact</a></spci><span>@</span>' . $arr[1] .
            '.<spci>simple@tricks.com</spci><span>' . $arr[2] . '</span><div style="display:none">trap</div><spci>@hhh.com</spci>';
        return $ret;
    }

    /**
     * 发起异步请求
     * @param $url
     * @param $timeout int 最少为1秒
     */
    public static function asyncRequest($url, $timeout=1)
    {
        $cl = curl_init();
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cl, CURLOPT_TIMEOUT, $timeout);
        curl_exec($cl);
        curl_close($cl);
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
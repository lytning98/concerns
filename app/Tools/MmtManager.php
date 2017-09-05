<?php

namespace App\Tools;

use App\Models\Account;
use App\Models\Moment;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class MmtManager
 * 管理动态(Moment Models)
 * @package App\Tools
 */
class MmtManager
{
    /**
     * 获取来源于指定Person Model的动态(moment)
     * @param $per Person Model
     * @param bool $sort 是否对结果按时间排序
     * @param bool $getCotest 只获取/不获取比赛动态信息
     * @param int $cid  比赛id
     * @return array[
     *      'count' => 记录条数 - int
     *      'data' => 数据 - array( ['moment' => MomentModel, 'parent' => PersonModel, 'username'] )
     * ]
     */
    public static function getMomentsByPerson($per, $sort=false, $getCotest=false, $cid = 0)
    {
        $count=0;
        $data = [];
        foreach ($per->accounts as $acc)
        {
            $mmts = [];
            if($getCotest){
                $mmts = $acc->moments()->where([
                    'event' => 'CONTEST',
                    'problem' => $cid,
                ])->get();
            }else{
                $mmts = $acc->moments()->where('event', '<>', 'Contest')->get();
            }
            $count += count($mmts);
            foreach ($mmts as $mmt){
                array_push($data, ['moment' => $mmt, 'parent' => $per, 'username' => $mmt->username]);
            }
        }
        if($sort)
        {
            $col = collect($data);
            $col = $col->sortByDesc(function($product, $key){
                return $product['moment']->time;
            });
            $data = $col->values()->all();
        }
        return [
            'data' => $data,
            'count' => $count,
        ];
    }

    /**
     * 获取指定用户的所有动态条数（仅用于关注页的统计栏）
     * @param $user
     * @return int
     */
    public static function getMomentsCount($user)
    {
        $count = 0;
        foreach($user->persons as $per) {
            foreach($per->accounts as $acc) {
                $moments = $acc->moments;
                $count += count($moments);
            }
        }
        return $count;
    }

    /**
     * 获取指定User的所有动态(moment)，包含比赛动态，比赛动态附加好友赛况信息
     * @param $user
     * @return array[
     *      'count' => 记录条数,
     *      'data' => array, 动态为非比赛动态时结构与getMomentsByPerson函数相同
     *          比赛动态结构['moment' => MomentModel, 'contestants' => 结构同getContestMoments返回值]
     * ]
     */
    public static function getMoments($user)
    {
        $count = 0;
        $data = [];
        foreach($user->persons as $per)
        {
            $res = MmtManager::getMomentsByPerson($per);
            $count += $res['count'];
            $data = array_merge($data, $res['data']);
        }

        $flags = Moment::where('event', 'CONTEST_FLAG')->get();
        $count += count($flags);
        foreach ($flags as $mmt){
            array_push($data, ['moment' => $mmt, 'contestants' => MmtManager::getContestMoments($user, $mmt->runid)]);
        }

        $col = collect($data);
        $col = $col->sortByDesc(function($product, $key){
            return $product['moment']->time;
        });
        $data = $col->values()->all();
        return [
          'data' => $data,
          'count' => $count
        ];
    }

    /**
     * 获取指定用户、指定比赛(by ContestID)、event='CONTEST'的比赛动态
     * @param $user
     * @param $cid ContestID
     * @return array[
     *      'count' => 数据数量,
     *      'data' => array, 结构:
     *          [ 'username', 'nickname'
     *            'result' => object, 为Moment->description的json_decode(结构见moment数据表文档)]
     * ]
     */
    public static function getContestMoments($user, $cid)
    {
        $count = 0;
        $data = [];
        foreach ($user->persons as $per)
        {
            $res = MmtManager::getMomentsByPerson($per, false, true, $cid);
            $count += $res['count'];
            foreach ($res['data'] as $arr){
                array_push($data, [
                    'result' => json_decode($arr['moment']->description),
                    'username' => $arr['username'],
                    'nickname' => $arr['parent']->nickname
                ]);
            }
        }

        $col = collect($data);
        $col = $col->sortByDesc(function($product, $key){
            return $product['result']->rank;
        });
        $data = $col->values()->all();

        return [
            'count' => $count,
            'data' => $data,
        ];
    }

    //laravel Paginator Configure Function
    public static function getPaginatedData($data, $items, $ITEM_PER_PAGE, $cur, $path)
    {
        for($i = 1; $i < $cur; $i++) {
            $data = array_slice($data, $ITEM_PER_PAGE);
        }
        $infoList['ITEM_PER_PAGE'] = $ITEM_PER_PAGE;
        $infoList['dataset'] = $data;
        $infoList['count'] = $items;
        $infoList['paginator'] = new LengthAwarePaginator($data, $items, $ITEM_PER_PAGE, $cur, [
            'path' => $path,
            'pageName' => 'page',
        ]);
        return $infoList;
    }

    /**
     * 创建Moment动态; 关联至相关Account; problem字段相同则不重复创建
     * @param $data
     * @return bool
     */
    public static function create($data)
    {
        $acc = Account::where($info = [
           'oj' => $data['oj'],
           'username' => $data['username'],
        ])->first();
        if(!$acc)   return false;
        $info['problem'] = $data['problem'];
        $mmt = Moment::where($info)->get();
        if(!$mmt->isEmpty())    return true;
        $mmt = Moment::create($data);
        if(!$mmt)   return false;
        $acc->moments()->save($mmt);
        return true;
    }
}
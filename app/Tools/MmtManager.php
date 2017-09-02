<?php

namespace App\Tools;

/**
 * Class MmtManager
 * 管理动态(Moment Models)
 * @package App\Tools
 */
class MmtManager
{
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

    public static function getMoments($user)
    {
        $count = 0;
        $data = [];
        foreach($user->persons as $per)
        {
            foreach($per->accounts as $acc)
            {

                $moments = $acc->moments;
                $count += count($moments);
                foreach ($moments as $wtm) {
                    array_push($data, ['moment' => $wtm, 'parent' => $per, 'username' => $wtm->username]);
                }
            }
        }
        $col = collect($data);
        $col = $col->sortByDesc(function($product, $key){
            return $product['moment']->created_at;
        });
        $data = $col->values()->all();
        return [
          'data' => $data,
          'count' => $count
        ];
    }
}
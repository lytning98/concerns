<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/9/1
 * Time: 11:34
 */

namespace App\Tools;

use App\Models\Account;
use App\Models\Moment;
use App\Models\Person;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Class PAManager
 * 管理Person和Account关系
 * @package App\Tools
 */
class PAManager
{

    /**
     * 检查Person是否为null、User和Person之间的从属关系
     * @param User $user UserModel
     * @param $per Person Model
     * @return array['suc' => boolean, 'errMsg'=>'xx']
     *
     */
    public static function checkRelation(User $user, $per)
    {
        if($per == null) {
            return ['suc' => false, 'errMsg' => '找不到该关注！'];
        }
        if($per->user != $user){
            return ['suc' => false, 'errMsg'=>'参数非法 #1'];
        }
        return ['suc' => 'true'];
    }

    /**
     * 更新或添加Person
     * @param $arrPer = ['nickname'=>'xxx', 'email'=>'xxx']
     * @param $arrOJ = [ 'HDU' => 'HDUsername', ...]
     * @param $per_id = 0 (未验证合法)
     * @return array['suc' => bool, 'errMsg' = > 'xxx']
     */
    public static function update($arrPer, $arrOJ, $per_id=0)
    {
        //get current user
        $user = Auth::user();

        //get modifying Model Person
        if($per_id == 0){
            $per = new Person($arrPer);
            $user->persons()->save($per);
        }else{
            $per = Person::find($per_id);

            $checkRes = PAManager::checkRelation($user, $per);
            if(!$checkRes['suc']){
                return $checkRes;
            }

            $per->fill($arrPer);
            $per->save();
        }

        //detach all accounts
        $old_accounts = $per->accounts;
        foreach ($old_accounts as $acc) {
            $per->accounts()->detach($acc);
        }

        //create and attach new accounts
        foreach ($arrOJ as $oj=>$raw_usernames)
        {
            if($raw_usernames == '')    continue;
            $usernames = explode('|', $raw_usernames);
            foreach ($usernames as $username)
            {
                $acc = Account::where($info = [
                    'oj' => $oj,
                    'username' => $username,
                ])->get();

                if($acc->isEmpty())
                {
                    $acc = Account::create($info = [
                        'oj' => $oj,
                        'username' => $username,
                    ]);
                    //re-bind un-binded moment to account
                    $mmts = Moment::withTrashed()->where($info)->get();
                    foreach ($mmts as $mmt){
                        if($mmt->account != $acc){
                            $mmt->restore();
                            $acc->moments()->save($mmt);
                        }
                    }

                    $per->accounts()->attach($acc);

                    //craw latest AC event
                    $craw = new Crawler();
                    $data = $craw->crawlACByAccount($acc, 5);
                    if($data['suc']){
                        foreach ($data['data'] as $mmtdata){
                            $ins = MmtManager::create($mmtdata);
                        }
                    }
                }else{
                    $per->accounts()->attach($acc);
                }
            }
        }

        //delete un-attached accounts
        foreach ($old_accounts as $acc){
            if($acc->persons->isEmpty()){
                $acc->remove();
            }
        }

        return ['suc'=>true];
    }

    public static function delete($id)
    {
        $user = Auth::user();
        $per = Person::find($id);

        $checkRes = PAManager::checkRelation($user, $per);
        if(!$checkRes['suc']){
            return $checkRes;
        }

        $per->user()->dissociate($user);
        foreach ($per->accounts as $acc){
            $per->detachAccount($acc);
        }
        $per->delete();

        return ['suc' => true];
    }
}
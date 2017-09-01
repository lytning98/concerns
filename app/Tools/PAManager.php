<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/9/1
 * Time: 11:34
 */

namespace App\Tools;

use App\Models\Account;
use App\Models\Person;
use Illuminate\Support\Facades\Auth;

/**
 * Class PAManager
 * 管理Person和Account关系
 * @package App\Tools
 */
class PAManager
{

    /**
     * 更新或添加Person
     * @param $arrPer = ['nickname'=>'xxx', 'email'=>'xxx']
     * @param $arrOJ = [ 'HDU' => 'HDUusername', ...]
     * @param $per_id=0 (已验证合法)
     * @return state
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
            $per->fill($arrPer);
            $per->save();
        }

        //detach all accounts
        $old_accounts = $per->accounts;
        foreach ($old_accounts as $acc) {
            $per->accounts()->detach($acc);
        }

        //attach new accounts
        foreach ($arrOJ as $oj=>$raw_usernames)
        {
            if($raw_usernames == '')    continue;
            $usernames = explode('|', $raw_usernames);
            foreach ($usernames as $username)
            {
                $acc = Account::firstOrCreate($info = [
                    'oj' => $oj,
                    'username' => $username,
                ]);
                $per->accounts()->attach($acc);
            }
        }

        //delete un-attached accounts
        foreach ($old_accounts as $acc){
            if($acc->persons->isEmpty()){
                $acc->delete();
            }
        }
        return ['suc'=>true];
    }
}
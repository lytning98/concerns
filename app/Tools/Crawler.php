<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/9/4
 * Time: 10:47
 */

namespace App\Tools;


use App\Models\Account;
use App\Models\Moment;
use Illuminate\Support\Facades\Log;

class Crawler
{
    private $ch;
    //失败重试参数
    private $RETRY_TIMES = 3;
    private $RETRIED = [];

    public function __construct()
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        $this->RETRIED = [
            'HDU' => 0,
            'vj' => 0,
            'cf' => 0,
            'POJ' => 0,
        ];
    }

    /**
     * 爬取单场比赛的部分用户排名，直接创建 Moment Model
     * @param $cid
     * @return int
     */
    public function crawlCF_Contest_Standing($cid)
    {
        $accs = Account::where('oj', 'Codeforces')->get();
        $count = count($accs);
        Log::debug("crawling c_stding $cid");
        while($count>0)
        {
            $cnt = min($count, 10000);
            $count -= $cnt;
            $url = "http://codeforces.com/api/contest.standings?contestId=$cid&from=1&count=$cnt&showUnofficial=false&handles=";
            $accset = $accs->slice(0, $cnt);
            $accs = $accs->slice($cnt);
            foreach ($accset as $acc){
                $url.=$acc->username . ";";
            }
            Log::debug($url);
            $json = file_get_contents($url);
            $list = json_decode($json);
            $this->RETRIED['cf']--;
            while($list==null || $list->status != 'OK')
            {
                $this->RETRIED['cf']++;
                if($this->RETRIED['cf'] > $this->RETRY_TIMES)
                    return 0;
                $json =  file_get_contents($url);
                $list = json_decode($json);
            }
            $list = $list->result->rows;
            foreach ($list as $item)
            {
                $arr = [];
                $arr['rank'] = $item->rank;
                $arr['solved'] = 0;
                $arr['presult'] = [];
                foreach ($item->problemResults as $pr){
                    $time = '';
                    if(isset($pr->bestSubmissionTimeSeconds)){
                        $time = date("H:i:s", $pr->bestSubmissionTimeSeconds-8*3600);
                    }
                    if($pr->points >= 0.1)   $arr['solved']++;
                    array_push($arr['presult'], [
                        'time' => $time,
                        'fail' => $pr->rejectedAttemptCount,
                        'solved' => ($pr->points<0.1)?false:true,
                    ]);
                }
                MmtManager::create([
                    'event' => 'CONTEST',
                    'oj' => 'Codeforces',
                    'problem' => $cid,
                    'username' => $item->party->members[0]->handle,
                    'description' => json_encode($arr),
                ]);
            }
        }
    }

    /**
     * 爬取Codeforces已经结束的比赛 在ID=$end_before_cid的比赛前停止爬取
     * 直接创建 Moment Model
     * @param $count
     * @param int $end_before_cid
     * @return int 创建的 moment 数
     */
    public function crawlCF_Contests($count, $end_before_cid=0)
    {
        $cur = 0;
        $list = null;

        $this->RETRIED['cf']--;
        while($list==null || $list->status != 'OK')
        {
            $this->RETRIED['cf']++;
            if($this->RETRIED['cf'] > $this->RETRY_TIMES)
                return 0;
            $json =  file_get_contents('http://codeforces.com/api/contest.list?gym=false');
            $list = json_decode($json);
        }

        $list = $list->result;
        foreach ($list as $item)
        {
            $cur++;
            if($cur > $count)  break;
            if($item->phase != 'FINISHED')    continue;
            if($item->type != 'CF') continue;
            if($item->id == $end_before_cid)    break;
            $mmt = Moment::create([
                'event' => 'CONTEST_FLAG',
                'time' => date("Y-m-d H:i:s", $item->startTimeSeconds),
                'runid' => $item->id,
                'problem' => $item->name,
                'account_id' => 0,
                'url' => "http://codeforces.com/contest/" . $item->id,
                'username' => 'foo',
                'oj' => 'Codeforces'
            ]);
            $this->crawlCF_Contest_Standing($item->id);
        }
        return $cur;
    }

    public function crawlCF_AC($username, $count, $end_before_runid=0)
    {
        $cur = 0; $loop=0;
        $ret = [];
        while($cur < $count)
        {
            $from=$loop*10+1;
            $json = file_get_contents("http://codeforces.com/api/user.status?handle=$username&from=$from&count=10");
            $res = json_decode($json);
            if($res==null || $res->status != 'OK'){
                if($this->RETRIED['cf'] >= $this->RETRY_TIMES)  break;
                else{
                    $this->RETRIED['cf']++;
                    continue;
                }
            }
            $res = $res->result;
            foreach ($res as $data)
            {
                if($data->verdict != 'OK')    continue;
                //防止重复收集比赛数据
                if($data->author->participantType=="CONTESTANT")    continue;

                $url = ''; $title = '';
                if((int)$data->problem->contestId > 2000){
                    //codeforces::gym
                    $url = "http://codeforces.com/problemset/gymProblem/" . $data->problem->contestId ."/" . $data->problem->index;
                    $title = 'CF::Gym';
                }else{
                    //classical problem
                    $title = "Codeforces";
                    $url = "http://codeforces.com/problemset/problem/" . $data->problem->contestId . "/" . $data->problem->index;
                }

                $des = '';
                if(count($data->author->members)>1){
                    $des = 'by [<strong>' . $data->author->teamName . '</strong>](';
                    foreach ($data->author->members as $memb)
                            $des .= ' <a href="http://codeforces.com/profile/' . $memb->handle . '">' . $memb->handle . '</a>';
                    $des .= ' )';
                }
                if($data->author->participantType == "VIRTUAL"){
                    $des .= '<br/>[Virtual Paticipant]';
                }

                array_push($ret, [
                    'oj' => 'Codeforces',
                    'runid' => $data->id,
                    'time' => date("Y-m-d H:i:s", $data->creationTimeSeconds),
                    'problem' => $title . $data->problem->contestId . $data->problem->index . " - " . $data->problem->name,
                    'url' => $url,
                    'description' => $des,
                ]);
                $cur++;
                if($cur == $count)  break;
            }
            $loop++;
        }
        return $ret;
    }

    /**
     * 爬取指定用户名的HDU账号最近count条AC submission；遇见RunID=end_before_runid的submission后停止爬取
     * @param $username
     * @param $count
     * @param int $end_before_runid
     * @return array 填充Model Moment的fill数据
     */
    public function crawlHDU_AC($username, $count,  $end_before_runid=0)
    {
        $url = "http://acm.hdu.edu.cn/status.php?user=$username&status=5";
        $cur = 0;
        $retried = 0;
        $ret = [];
        while($cur < $count)
        {
            curl_setopt($this->ch, CURLOPT_URL, $url);
            $html = curl_exec($this->ch);

            if(strlen($html) == 0)
            {
                if($this->RETRIED['HDU'] >= $this->RETRY_TIMES){
                    break;
                }
                $this->RETRIED['HDU']++;
            }

            $res = [];
            $reg = '#.*?<td height.*?>(\d+)</td><td>([0-9\-: ]*)</td>.*?<td><a href.+?>(.+?)</a>.*?#';
            $cnt = preg_match_all($reg, $html, $res);
            if($cur + $cnt > $count)    $cnt = $count-$cur;
            $cur += $cnt;
            for($i = 0; $i < $cnt; $i++)
            {
                if((int)$res[1][$i] == $end_before_runid){
                    return $ret;
                }
                //crawl title
                $purl = "http://acm.hdu.edu.cn/showproblem.php?pid=" . $res[3][$i];
                curl_setopt($this->ch, CURLOPT_URL, $purl);
                $html = curl_exec($this->ch);
                $reg = '#<h1.*?>(.*?)</h1>#';
                $title = [];
                preg_match($reg, $html, $title);
                $title = $title[1];

                array_push($ret, [
                    'oj' => 'HDU',
                    'runid' => (int)$res[1][$i],
                    'time' => $res[2][$i],
                    'problem' => 'HDU' . $res[3][$i] . " - $title",
                    'url' => $purl,
                ]);
            }
            //get next page url
            $reg = '#.*href="(.*?)">Next Page.*#';
            if(preg_match($reg, $html, $res)){
                $url = "http://acm.hdu.edu.cn" . $res[1];
                continue;
            }else{
                break;
            }
        }
        return $ret;
    }

    public function crawlAC($oj, $username, $count, $end_before_runid=0)
    {
        $data=[];
        switch($oj)
        {
            case 'HDU':
                $data = $this->crawlHDU_AC($username, $count, $end_before_runid);
                break;
            case 'Codeforces':
                $data = $this->crawlCF_AC($username, $count, $end_before_runid);
                break;
        }
        $cnt = count($data);
        foreach ($data as $k => $d)
        {
            $data[$k]['event'] = 'AC';
            $data[$k]['username'] = $username;
        }
        if(count($data)) {
            return [
                'suc' => true,
                'data' => $data
            ];
        }else{
            return ['suc'=>false];
        }
    }

    public function crawlACByAccount($acc, $count, $end_before_runid=0)
    {
        return $this->crawlAC($acc->oj, $acc->username, $count, $end_before_runid);
    }
}
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
    private $last_runid;

    public function __construct()
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 3);
        $this->RETRIED = [
            'HDU' => 0,
            'vj' => 0,
            'cf' => 0,
            'POJ' => 0,
        ];
    }

    public function getHTMLbyURL($url, $which)
    {
        $html = '';
        //数据量较大
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 4);

        $this->RETRIED[$which]--;
        while(strlen($html) == 0)
        {
            $this->RETRIED[$which]++;
            if($this->RETRIED[$which] >= $this->RETRY_TIMES){
                Log::warning("[OJ INACCESSABLE] $which URL=" . $url);
                break;
            }

            curl_setopt($this->ch, CURLOPT_URL, $url);
            $html = curl_exec($this->ch);
        }

        curl_setopt($this->ch, CURLOPT_TIMEOUT, 3);
        return $html;
    }

    /**
     * 获取CF API返回的json并解析为object, 失败重试
     * @param $url
     * @return mixed|null
     */
    public function getCfJsonDecodedObject($url)
    {
//        $this->RETRIED['cf']--; $obj = null;
//        while($obj==null || $obj->status != 'OK')
//        {
//            $this->RETRIED['cf']++;
//            if($this->RETRIED['cf'] > $this->RETRY_TIMES)   return null;
//            $json = file_get_contents($url);
//            $obj = json_decode($json);
//        }
//        return $obj;
        $raw = $this->getHTMLbyURL($url, 'cf');
        return json_decode($raw);
    }

    /**
     * 爬取指定用户的Rating变化信息
     * 直接创建MomentModel
     * @param $username
     * @param $count
     * @param int $until_time 遇此时间戳时退出
     * @return int
     */
    public function crawlRatingChange($username, $count, $until_time=0)
    {
        $url = "http://codeforces.com/api/user.rating?handle=" . $username;
        $list = $this->getCfJsonDecodedObject($url);
        if($list==null) return 0;
        //re-sort
        $col = collect($list->result);
        $col = $col->sortByDesc(function($product, $key){
           return $product->ratingUpdateTimeSeconds;
        });
        $list = $col->values()->all();

        $cnt = 0;
        foreach ($list as $item)
        {
            if($item->ratingUpdateTimeSeconds == $until_time)   {
                return $cnt;
            }
            $cnt++;
            if($cnt>$count) return $count;
            MmtManager::create([
               'event' => 'RATING',
               'oj' => 'Codeforces',
               'username' => $username,
               'problem' => $item->contestName,
               'url' => "http://codeforces.com/contest/" . $item->contestId,
               'time' => date("Y-m-d H:i:s", $item->ratingUpdateTimeSeconds),
               'description' => json_encode(['old'=>$item->oldRating, 'new'=>$item->newRating]),
            ]);
        }
        return $cnt;
    }

    /**
     * 由 CodeforcesAPI::RanklistRow object构建MomentModel
     * API Reference: http://codeforces.com/api/help/objects#RanklistRow
     * @param $item object, RanklistRow
     * @param $cid
     */
    public function constructContestMomentByRow($item, $cid)
    {
        $arr=[];
        $arr['rank'] = $item->rank;
        $arr['solved'] = 0; $arr['presult'] = [];
        foreach ($item->problemResults as $pr) {
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

    /**
     * 爬取单个Account在单场比赛的赛况信息并创建Moment
     * @param $acc
     * @param $cid
     * @return bool
     */
    public function crawlCF_Contest_StandingByAccout($acc, $cid)
    {
        $url = "http://codeforces.com/api/contest.standings?contestId=$cid&from=1&count=1&showUnofficial=false&handles=";
        $url .= $acc->username;
        $list = $this->getCfJsonDecodedObject($url);
        if($list==null) return false;
        $list = $list->result->rows;
        if(count($list)==0){
            return true;
        }
        $item = $list[0];
        $this->constructContestMomentByRow($item, $cid);
        return true;
    }

    /**
     * 爬取单场比赛的部分用户排名，直接创建 Moment Model
     * 同时爬取参加比赛用户的rating change
     * @param $cid
     */
    public function crawlCF_Contest_Standing($cid)
    {
        $accs = Account::where('oj', 'Codeforces')->get();
        $count = count($accs);
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
            $list = $this->getCfJsonDecodedObject($url);
            if($list == null)   return;
            $list = $list->result->rows;
            foreach ($list as $item){
                $this->constructContestMomentByRow($item, $cid);
                $this->routineCrawlRatingChange($item->party->members[0]->handle);
            }
        }
    }

    /**
     * 爬取Codeforces已经结束的比赛 在ID=$end_before_cid的比赛前停止爬取
     * 直接创建 Moment Model
     * 自动爬取相关Standing
     * @param $count 爬取到count场为止 或遇end cid
     * @param int $end_before_cid
     * @return int 创建的 moment 数
     */
    public function crawlCF_Contests($count, $end_before_cid=0)
    {
        $cur = 0;
        $list = $this->getCfJsonDecodedObject('http://codeforces.com/api/contest.list?gym=false');
        if($list == null)   return 0;
        $list = $list->result;
        foreach ($list as $item)
        {
            if($item->phase != 'FINISHED')    continue;
            if($item->type != 'CF') continue;
            if($item->id == $end_before_cid)    break;
            MmtManager::create([
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
            $cur++;
            if($cur >= $count)  break;
        }
        return $cur;
    }

    /**
     * 爬取Codeforces非赛时提交的ac submission, 按需停止
     * @param $username
     * @param $count 爬取到count条数据后停止，或遇指定runid停止
     * @param int $end_before_runid
     * @return array 用于fill MomentModel的数据
     */
    public function crawlCF_AC($username, $count, $end_before_runid=0)
    {
        $cur = 0; $loop=0;
        $this->last_runid = 0;
        $ret = [];
        while($cur < $count)
        {
            $from=$loop*10+1;
            $res = $this->getCfJsonDecodedObject("http://codeforces.com/api/user.status?handle=$username&from=$from&count=10");
            $res = $res->result;
            foreach ($res as $data)
            {
                if($this->last_runid == 0)  $this->last_runid = $data->id;
                if($data->id == $end_before_runid){
                    return $ret;
                }
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
    public function crawlHDU_POJ_AC($username, $count,  $end_before_runid=0, $poj=false)
    {
        $url = $poj?"http://poj.org/status?user_id=$username&result=0":"http://acm.hdu.edu.cn/status.php?user=$username&status=5";
        $oj = $poj?'POJ':'HDU';

        $this->last_runid = 0;
        $cur = 0;
        $ret = [];
        while($cur < $count)
        {
            $html = $this->getHTMLbyURL($url, $oj);
            if(strlen($html) == 0) break;

            $res = [];
            if($poj){
                $reg = '#.*?<td>(\d+)</td><td>.*?</td>.*?(\d+).*?<td>([0-9\-: ]*)</td>.*?#';
            }else{
                $reg = '#.*?<td height.*?>(\d+)</td><td>([0-9\-: ]*)</td>.*?<td><a href.+?>(.+?)</a>.*?#';
            }
            $cnt = preg_match_all($reg, $html, $res);

            if($cur + $cnt > $count)    $cnt = $count-$cur;
            $cur += $cnt;
            if($this->last_runid == 0)  $this->last_runid = (int)$res[1][0];
            for($i = 0; $i < $cnt; $i++)
            {
                if($poj){
                    $tmp = $res[2][$i]; $res[2][$i] = $res[3][$i]; $res[3][$i] = $tmp;
                }

                if((int)$res[1][$i] == $end_before_runid){
                    return $ret;
                }
                //crawl title
                $purl = $poj?"http://poj.org/problem?id=":"http://acm.hdu.edu.cn/showproblem.php?pid=";
                $purl .= $res[3][$i];
                $phtml = $this->getHTMLbyURL($purl, $oj);
                if(strlen($phtml)==0){
                    $title = '[Failed_to_Crawl_Title]';
                }else
                {
                    if ($poj) {
                        $reg = '#en-US">(.*?)</div>#';
                    } else {
                        $reg = '#<h1.*?>(.*?)</h1>#';
                    }
                    $title = [];
                    preg_match($reg, $phtml, $title);
                    $title = $title[1];
                }

                array_push($ret, [
                    'oj' => $oj,
                    'runid' => (int)$res[1][$i],
                    'time' => $res[2][$i],
                    'problem' => $oj . $res[3][$i] . " - $title",
                    'url' => $purl,
                ]);
            }
            //get next page url
            if($poj) {
                $reg = '#.*href=(.*)><.*>Next Page#';
            }else{
                $reg = '#.*href="(.*?)">Next Page#';
            }

            if(preg_match($reg, $html, $res)){
                $url = $poj?"http://poj.org/":"http://acm.hdu.edu.cn";
                $url .= $res[1];
                continue;
            }else{
                break;
            }
        }
        return $ret;
    }

    /**
     * 爬取指定OJ的AC submission
     * @param $oj
     * @param $username
     * @param $count
     * @param int $end_before_runid
     * @return array
     */
    public function crawlAC($oj, $username, $count, $end_before_runid=0)
    {
        $data=[];
        switch($oj)
        {
            case 'HDU':
                $data = $this->crawlHDU_POJ_AC($username, $count, $end_before_runid);
                break;
            case 'Codeforces':
                $data = $this->crawlCF_AC($username, $count, $end_before_runid);
                break;
            case 'POJ':
                $data = $this->crawlHDU_POJ_AC($username, $count, $end_before_runid, true);
                break;
            default:
                $data = [];
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
        $res = $this->crawlAC($acc->oj, $acc->username, $count, $end_before_runid);
        if($this->last_runid){
            $acc->last_runid = $this->last_runid;
            $acc->save();
        }
        return $res;
    }

    public function routineCrawlAC($acc)
    {
        if($acc->last_runid != null){
            $lastid = $acc->last_runid;
        }else{
            $lastone = $acc->moments()->where('event', 'AC')->orderBy('time', 'desc')->first();
            $lastid = $lastone?$lastone->runid:0;
        }

        $res = $this->crawlACByAccount($acc, $lastid?10:4, $lastid);

        if($res['suc']){
            $cnt = 0;
            foreach ($res['data'] as $mmtdata){
                if(MmtManager::create($mmtdata))    $cnt++;
            }
            Log::info("routine crawl : " . $acc->username . "@" . $acc->oj . " added $cnt AC records. (crawled" . count($res['data']));
        }
    }

    public function routineCrawlRatingChange($username)
    {
        $acc = Account::where([
            'oj' => 'Codeforces',
            'username' => $username,
        ])->first();
        if(!$acc)   return 0;
        $mmt = $acc->moments()->where('event', "RATING")->orderBy('time', 'desc')->first();
        if($mmt){
            return $this->crawlRatingChange($acc->username, 10, strtotime($mmt->time));
        }else{
            return $this->crawlRatingChange($acc->username, 4);
        }
    }

}
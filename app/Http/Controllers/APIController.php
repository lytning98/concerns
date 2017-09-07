<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/9/2
 * Time: 23:25
 */

namespace App\Http\Controllers;


use App\Models\Account;
use App\Models\Moment;
use App\Tools\Crawler;
use App\Tools\MmtManager;
use App\Tools\SysManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class APIController extends Controller
{
    public function __construct()
    {
        ignore_user_abort(false);
//        set_time_limit(120);
    }

    public function __destruct()
    {
//        ignore_user_abort(false);
//        set_time_limit(50);
    }

    public function test2()
    {
        for($i = 1; $i <= 3; $i++){
            Log::debug('tst 2');
            sleep(10);
        }
        return 'no bugs no gains';
    }

    public function test()
    {
//        $user = Auth::user();
//        Moment::onlyTrashed()->get()->each->forceDelete();
        return view('errors.404');
        $c = new Crawler();
        $c->routineCrawlRatingChange('Lytning');
//        dd($c->crawlHDU_POJ_AC('Lytning', 25, 0, false));
    }

    /**
     * routine数据爬取, 每次选取updated_at时间戳最早的一定量Account
     */
    public function routineCrawlAC()
    {
        $time = microtime(true);
//        $last = $time;
        $c = new Crawler();
        $list = Account::orderBy('updated_at')->limit(10)->get();

        foreach ($list as $acc) {
            $acc->updated_at = date('Y-m-d H:i:s', time());
            $acc->save();
        }

        foreach ($list as $acc){
            Log::debug("crawl . " . $acc->username . "@" . $acc->oj);
            $c->routineCrawlAC($acc);
//            $tmp = microtime(true)-$last;
//            Log::debug("timestamp $tmp");
//            $last = microtime(true);
        }
        $time = microtime(true)-$time;
        Log::info("routine crawl costs $time s");
    }

    /**
     * routine数据爬取, Contest
     */
    public function routineCrawlContest()
    {
        $c = new Crawler();
        $mtime = Moment::where('event', 'CONTEST_FLAG')->max('time');
        $lastone = Moment::where([
            'event' => 'CONTEST_FLAG',
            'time' => $mtime,
        ])->orderBy('runid')->first();
        $cnt = $c->crawlCF_Contests(4, $lastone?$lastone->runid:0);
        if($cnt){

        }

        Log::info("routine crawl contest get $cnt records");
        //顺便
        SysManager::cleanMoments();
    }

    /**
     * 爬取新account初始数据
     * @param $id
     */
    public function initialCrawl($id)
    {

        $acc = Account::find($id);
        if($acc==null)  return;

        //crawl latest AC event
        $craw = new Crawler();
        $data = $craw->crawlACByAccount($acc, 5);
        if ($data['suc']) {
            foreach ($data['data'] as $mmtdata) {
                MmtManager::create($mmtdata);
            }
        }

        if ($acc->oj == 'Codeforces') {
            //craw participation in latest cf contest
            $contests = Moment::where('event', 'CONTEST_FLAG')->orderBy('time', 'desc')->get();
            $cnt = 0;
            foreach ($contests as $contest) {
                $craw->crawlCF_Contest_StandingByAccout($acc, $contest->runid);
                $cnt++;
                if ($cnt == 5) break;
            }
            //craw recent rating change
            $craw->crawlRatingChange($acc->username, 4);
        }

        ignore_user_abort(false);
    }
}
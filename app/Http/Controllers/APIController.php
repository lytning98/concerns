<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/9/2
 * Time: 23:25
 */

namespace App\Http\Controllers;


use App\Tools\Crawler;
use App\Tools\MmtManager;
use Illuminate\Support\Facades\Auth;

class APIController extends Controller
{
    public function __construct()
    {

    }

    public function test()
    {
        $c = new Crawler();
        $c->crawlCF_Contests(7);
//        dd(MmtManager::getContestMoments(Auth::user(), 851));
        return 'no bugs no gains';
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/8/30
 * Time: 23:25
 */

namespace App\Http\Controllers;


use App\Tools\MmtManager;
use App\Tools\SysManager;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;


class IndexController extends Controller
{
    public function database()
    {
//        Schema::create('accounts', function(Blueprint $table){
//            $table->increments('id');
//            $table->enum('oj', ['HDU', 'CF', 'POJ', 'VJUDGE']);
//            $table->timestamps();
//            $table->char('username', 15);
//        });
//        Schema::create('moments', function(Blueprint $table){
//            $table->increments('id');
//            $table->integer('account_id');
//            $table->enum('event', ['AC', 'Contest', 'Rating']);
//            $table->timestamps();
//            $table->char('username', 15);
//            $table->enum('oj', ['HDU', 'CF', 'POJ', 'VJUDGE']);
//        });
//        Schema::table('moments', function(Blueprint $table){
//            $table->string('problem');
//            $table->longText('description');
//        });
//        Schema::table('accounts', function(Blueprint $table){
//            $table->integer('user_id');
//        });
//        Schema::create('account_user', function(Blueprint $table){
//            $table->increments('id');
//           $table->integer('acount_id');
//           $table->integer('user_id');
//            $table->timestamps();
//        });
//        Schema::create('persons', function(Blueprint $table){
//            $table->increments('id');
//            $table->integer('user_id');
//            $table->string('email');
//            $table->string('nickname');
//            $table->timestamps();
//        });
    }

    public function index(Request $request)
    {
        if(Auth::guest())
        {
            return view('index');
        }else
        {
            $cur = (int)$request->input('page');
            if($cur == 0)   $cur = 1;

            $path = Paginator::resolveCurrentPath();
            $res = MmtManager::getMoments(Auth::user());
            $items = $res['count'];
            $data = $res['data'];
            $datalist = MmtManager::getPaginatedData($data, $items, 10, $cur, $path);

            return view('index', $datalist);
        }
    }
}
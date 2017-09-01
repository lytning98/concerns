<?php
/**
 * Created by PhpStorm.
 * User: sdzczy
 * Date: 2017/8/30
 * Time: 23:25
 */

namespace App\Http\Controllers;


use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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
            $items = 0;
            $ITEM_PER_PAGE = 2;
            $cur = (int)$request->input('page');
            if($cur == 0)   $cur = 1;

            $path = Paginator::resolveCurrentPath();
            $data = [];

            foreach(Auth::user()->persons as $per)
            {
                foreach($per->accounts as $acc)
                {

                    $moments = $acc->moments;
                    $items += count($moments);
                    foreach ($moments as $wtm) {
                        array_push($data, ['moment' => $wtm, 'parent' => $per]);
                    }
                }
            }

            $col = collect($data);
            $col = $col->sortByDesc(function($product, $key){
                return $product['moment']->created_at;
            });
            $data = $col->values()->all();

            for($i = 1; $i < $cur; $i++)
            {
                $data = array_slice($data, $ITEM_PER_PAGE);
            }
            $infoList['ITEM_PER_PAGE'] = $ITEM_PER_PAGE;
            $infoList['dataset'] = $data;
            $infoList['paginator'] = new LengthAwarePaginator($data, $items, $ITEM_PER_PAGE, $cur, [
                'path' => $path,
                'pageName' => 'page',
            ]);
            return view('index', $infoList);
        }
    }
}
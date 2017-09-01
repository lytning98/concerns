<?php

namespace App\Http\Controllers;


use App\Models\Person;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;

class ConcernController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $concerns = $user->persons()->orderBy('created_at', 'desc')->paginate(2);
        $data['data'] = $concerns;
        return view('concern.index', $data);
    }

    public function add()
    {
        $data = [];
        $data['title'] = '添加关注';
        $data['modifying'] = false;
        return view('concern.modify', $data);
    }

    public function modify($id)
    {
        $data = [];
        $data['title'] = '修改关注';
        $data['modifying'] = true;
        $per = Person::find($id);
        if(!$per)
        {
            $data['hasError'] = true;
            $data['errMsg'] = '找不到该关注！';
        }else {
            $data['nickname'] = $per->nickname;
            $data['email'] = $per->email;
            $data['accounts'] = $per->accounts;
        }
        return view('concern.modify', $data);
    }

    public function update()
    {

    }
}
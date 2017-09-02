<?php

namespace App\Http\Controllers;


use App\Models\Person;
use App\Tools\PAManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;

class ConcernController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Generate data for rendering form according to action( add or modify )
     * @param Person|null $per
     * Person Model which is being modified if the action is modify
     * @return array
     */
    private function getFormData(Person $per = null)
    {
        $data = [
            ['attr' => 'Person[nickname]', 'attr_dot'=> 'Person.nickname', 'name' => 'Nickname', 'ori' => $per?$per->nickname:''],
            ['attr' => 'Person[email]', 'attr_dot' => 'Person.email', 'name' => 'E-Mail 地址', 'ori' => $per?$per->email:''],
        ];
        $oj = ['HDU', 'Codeforces', 'POJ', 'VJUDGE'];
        foreach ($oj as $cur){
            array_push($data, ['attr' => "OJ[$cur]", 'attr_dot'=>"OJ.$cur", 'name' => $cur . '用户名', 'ori'=>'']);
        }
        if($per)
        {
            foreach ($per->accounts as $acc) {
                for ($i = 0; $i < count($data); $i++) {
                    if ($data[$i]['attr'] == "OJ[$acc->oj]")
                    {
                        if ($data[$i]['ori'] != '')
                            $data[$i]['ori'] .= '|' . $acc->username;
                        else
                            $data[$i]['ori'] = $acc->username;
                        break;
                    }
                }
            }
        }
        return $data;
    }

    public function index()
    {
        $user = Auth::user();
        $concerns = $user->persons()->orderBy('created_at', 'desc');
        $data['count'] = $concerns->count();
        $concerns = $concerns->paginate(15);
        $data['data'] = $concerns;
        return view('concern.index', $data);
    }

    public function add()
    {
        $data = [];
        $data['title'] = '添加关注';
        $data['data'] = $this->getFormData();
        return view('concern.modify', $data);
    }

    public function modify($id)
    {
        $data = [];
        $data['title'] = '修改关注';
        $per = Person::find($id);
        $checkRes = PAManager::checkRelation(Auth::user(), $per);
        if(!$checkRes['suc'])
        {
            $data['hasError'] = true;
            $data['errMsg'] = $checkRes['errMsg'];
        }else {
            $data['id'] = $id;
            $data['data'] = $this->getFormData($per);
        }
        return view('concern.modify', $data);
    }

    public function update(Request $req)
    {
        $this->validate($req, [
            'Person.nickname' => 'required|max:25',
            'Person.email' => 'max:255',
        ], [
            'required' => ':attribute为必填项',
            'max' => ':attribute过长',
        ], [
            'Person.nickname' => 'nickname',
            'Person.email' => 'E-Mail 地址'
        ]);

        $res = PAManager::update($req['Person'], $req['OJ'], $req['modifying']);
        if($res['suc']) {
            return redirect('concern')->with('msg', '操作成功！');
        }else{
            $data['title'] = '修改关注';
            $data['hasError'] = true;
            $data['errMsg'] = $res['errMsg'];
            return view('concern.modify', $data);
        }
    }

    public function delete($id)
    {
        $res = PAManager::delete($id);
        if($res['suc']){
            return redirect('concern')->with('msg', '删除成功！');
        }else
        {
            $data['title'] = '删除关注';
            $data['hasError'] = true;
            $data['errMsg'] = $res['errMsg'];
            return view('concern.modify', $data);
        }
    }
}
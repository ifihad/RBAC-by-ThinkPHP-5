<?php
namespace app\rbac\controller;
use think\Controller;
use think\Request;
use app\rbac\model\Auth as AuthModel;

class Auth extends Controller
{
	public $request;

	public function initialize()
	{
		$this->request = Request::instance();
	}

	public function index()
	{
		$result = AuthModel::select();
		$this->assign('items', $result);
		return view();
	}

	public function add()
	{
		return view();
	}

	public function doadd()
	{
		$param = $this->request->param();
		$auth_name = $param['auth_name'];
		$result = AuthModel::where('auth_name', $auth_name)->find();
		if ($result) {
			$this->redirect('index', 1, '该权限名已被使用！');
		} else {
			$result = AuthModel::insert([
				'auth_name' => $auth_name,
				'addtime' => time()
			]);
			if ($result) {
				$this->success('添加成功！', 'index', '', 1);
			} else {
				$this->error('添加失败！');
			}
		}
	}

	public function edit()
	{
		$param = $this->request->param();
		$id = $param['id'];
		$result = AuthModel::where('id', $id)->find();
		$this->assign('item', $result);
		return view();
	}

	public function doedit()
	{
		$param = $this->request->param();
		$id = $param['id'];
		$auth_name = $param['auth_name'];
		$result = AuthModel::where('id', $id)->update(['auth_name' => $auth_name]);
		if ($result) {
			$this->success('编辑成功！', 'index', '', 1);
		} else {
			$this->error('编辑失败！');
		}
	}

	public function del()
	{
		$param = $this->request->param();
		$id = $param['id'];
		$result = AuthModel::where('id', $id)->delete();
		if ($result) {
			$this->success('删除成功！', 'index', '', 1);
		} else {
			$this->error('删除失败！');
		}
	}
}
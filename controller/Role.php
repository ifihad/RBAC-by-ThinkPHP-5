<?php
namespace app\rbac\controller;
use think\Controller;
use app\rbac\model\Role as RoleModel;

class Role extends Controller
{
	public $request;

	public function initialize()
	{
		$this->request = Request::instance();
	}

	public function index()
	{
		$result = RoleModel::select();
		// 疑问：使用模型查询出来的结果为对象结果集，可以直接assign()吗？
		// 添加时间：将时间戳格式化
		// dump(date('Y-m-d H:i:s', $result[0]->addtime)); exit;
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
		$role_name = $param['role_name'];
		$result = RoleModel::insert([
			'role_name' => $role_name,
			'addtime' => time()
		]);
		if ($result) {
			$this->success('添加成功！', 'index', '', 1);
		} else {
			$this->error('添加失败！');
		}
	}

	public function edit()
	{
		$param = $this->request->param();
		$id = $param['id'];
		$result = RoleModel::where('id', $id)->find();
		$this->assign('item', $result);
		return view();
	}

	public function doedit()
	{
		$param = $this->request->param();
		$id = $param['id'];
		$role_name = $param['role_name'];
		$result = RoleModel::where('id', $id)->update(['role_name' => $role_name]);
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
		$result = RoleModel::where('id', $id)->delete();
		if ($result) {
			$this->success('删除成功！', 'index', '', 1);
		} else {
			$this->error('删除失败！');
		}
	}
}
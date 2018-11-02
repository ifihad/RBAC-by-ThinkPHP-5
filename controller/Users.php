<?php
namespace app\rbac\controller;
use think\Controller;
use think\Request;
use app\rbac\model\Users as UsersModel;
use app\rbac\model\Role as RoleModel;
use app\rbac\model\AdminRole as UsersRolesModel;

class Users extends Controller
{
	public $request;

	public function initialize()
	{
		$this->request = Request::instance();
	}

	public function index()
	{
		$result = UsersModel::select();

		// 遍历用户
		foreach ($result as $v) {
			$user_id = $v['id'];
			$item['user_id'] = $user_id;
			$item['username'] = $v['username'];
			$item['nickname'] = $v['nickname'];
			$item['addtime'] = date('Y-m-d H:i:s', $v['addtime']);

			// 获取用户角色
			$result2 = UsersRolesModel::where('user_id', $user_id)->select();
			$role = array();
			foreach ($result2 as $v2) {
				$role_id = $v2['role_id'];
				$result3 = RoleModel::where('id', $role_id)->find();
				$role[] = $result3['role_name'];
			}

			$item['role'] = implode(',', $role);

			$items[] = $item;
		}

		$this->assign('items', $items);
		return view();
	}

	public function add()
	{
		return view();
	}

	public function doadd()
	{
		$param = $this->request->param();

		$username = $param['username'];
		$nickname = $param['nickname'];
		$role = $param['role'];
		$result = UsersModel::where('username', $username)->select();

		if (!$result) {
			$data['username'] = $username;
			$data['nickname'] = $nickname;
			$data['addtime'] = time();
			$id = UsersModel::insertGetId($data);

			unset($data);
			foreach ($role as $v) {
				$data['role_id'] = $v;
				$data['user_id'] = $id;
				$data['addtime'] = time();
				UsersRolesModel::insert($data);
			}

			$this->success('添加成功', 'index', '', 1);
		} else {
			$this->error('用户名已存在！');
		}
	}

	public function edit()
	{
		$request = Request::instance();
		$param = $request->param();
		$id = $param['id'];
		$result = UsersModel::where('id', $id)->find();
		$role = $this->get_role($id);
		$result['role'] = $role;
		// 获取所有角色
		$roles = $this->get_all_roles();

		foreach ($roles as $k => $v) {
			if (in_array($v['role_name'], $role)) {
				$roles[$k]['selected'] = ' selected="selected"';
			} else {
				$roles[$k]['selected'] = '';
			}
		}

		$this->assign('roles', $roles);
		$this->assign('item', $result);
		return view();
	}

	// 编辑是更新，不是插入
	public function doedit()
	{
		$param = $this->request->param();
		$id = $param['id'];
		$role = $param['role'];
		$username = $param['username'];
		$nickname = $param['nickname'];

		// 编辑操作的难点：角色的更新
		// 目前想到的解决方法就是“先删除，再插入”
		// 删除之前的角色
		UsersRolesModel::where('user_id', $id)->delete();
		// 插入新的角色
		foreach ($role as $v) {
			UsersRolesModel::insert([
				'user_id' => $id,
				'role_id' => $v,
				'addtime' => time()
			]);
		}

		// 最好不用username字段作为更新条件，除非username是不可变的
		// 还是要用id
		$result = UsersModel::where('id', $id)->update([
			'username' => $username,
			'nickname' => $nickname
		]);

		if ($result) {
			$this->success('编辑成功！', 'index', '', 1);
		} else {
			$this->error('编辑失败！');
		}
	}

	public function del($id)
	{
		$param = $this->request->param();
		$id = (int)$param['id'];

		// 删除用户
		// 删除后的结果暂时就不判断了
		// $result = UsersModel::where('id', $id)->delete();
		UsersModel::where('id', $id)->delete();
		// 删除对应的角色
		UsersRolesModel::where('user_id', $id)->delete();

		$this->success('删除成功！', 'index', '', 1);
	}

	/**
	 * 根据用户id获取角色
	 * @param $id 用户ID
	 * @return ['','']
	 */
	public function get_role($id)
	{
		// 根据用户ID获取角色ID
		$result = UsersRolesModel::field('role_id')->where('user_id', $id)->select();

		// 根据角色ID获取角色名称
		foreach ($result as $v) {
			$role_id = $v['role_id'];
			$result = RoleModel::where('id', $role_id)->find();
			$role[] = $result['role_name'];
		}

		return $role;
	}

	/**
	 * 获取所有角色
	 */
	public function get_all_roles()
	{
		$result = RoleModel::select();
		return $result;
	}
}


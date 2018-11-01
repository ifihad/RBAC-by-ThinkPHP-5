<?php
namespace app\rbac\controller;
use think\Controller;
use think\Db;
use think\Request;
use app\rbac\model\Admin as AdminModel;
use app\rbac\model\Role as RoleModel;
use app\rbac\model\UserRole as UserRoleModel;

class Admin extends Controller
{
	public $db;
	public $request;

	public function initialize()
	{
		// $this->db = new Db();
		$this->request = Request::instance();
	}

	public function index()
	{
		$result = AdminModel::select();

		// 遍历用户
		foreach ($result as $v) {
			// 每次循环前重新定义数组$item
			// $item = array();
			// 因为$item是通过指定的索引赋值的
			// 所以不需要声明，每个索引对应的元素在每次循环中都被重新赋值

			$user_id = $v['id'];
			$item['user_id'] = $user_id;
			$item['username'] = $v['username'];
			$item['nickname'] = $v['nickname'];
			$item['addtime'] = date('Y-m-d H:i:s', $v['addtime']);

			// 获取用户角色
			$result2 = UserRoleModel::where('user_id', $user_id)->select();
			// 每次循环前都需要重新定义数组$role
			// 因为$role是通过$role[]进行赋值的，是数值数组，下标为数字
			// 下一次循环会用到上一次循环产生的数组元素
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
		$result = Db::table('rbac_user')->where('username', $username)->select();

		if (!$result) {
			$data['username'] = $username;
			$data['nickname'] = $nickname;
			$data['addtime'] = time();
			$id = Db::table('rbac_user')->insertGetId($data);

			unset($data);
			foreach ($role as $v) {
				$data['role_id'] = $v;
				$data['user_id'] = $id;
				$data['addtime'] = time();
				Db::table('rbac_user_role')->insert($data);
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
		$result = Db::table('rbac_user')->where('id', $id)->find();
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

	public function doedit()
	{
		$param = $this->request->param();

		$username = $param['username'];
		$nickname = $param['nickname'];
		$role = $param['role'];

		$result = Db::table('rbac_user')->where('username', $username)->update();
		if (!$result) {
			$data['username'] = $username;
			$data['nickname'] = $nickname;
			$data['addtime'] = time();
			$id = Db::table('rbac_user')->insertGetId($data);

			unset($data);
			foreach ($role as $v) {
				$data['role_id'] = $v;
				$data['user_id'] = $id;
				$data['addtime'] = time();
				Db::table('rbac_user_role')->insert($data);
			}

			$this->success('添加成功', 'index', '', 1);
		} else {
			$this->error('用户名已存在！');
		}
	}

	/**
	 * 根据用户id获取角色
	 * @param $id 用户ID
	 * @return ['','']
	 */
	public function get_role($id)
	{
		// 根据用户ID获取角色ID
		$result = Db::table('rbac_user_role')->field('role_id')->where('user_id', $id)->select();

		// 根据角色ID获取角色名称
		foreach ($result as $v) {
			$role_id = $v['role_id'];
			// $_role['id'] = $role_id;
			$result = Db::table('rbac_role')->where('id', $role_id)->find();
			// $_role['name'] = $result['role_name'];
			// $role[] = $_role;
			$role[] = $result['role_name'];
		}
		// $role = implode(',', $role);

		return $role;
	}

	/**
	 * 获取所有角色
	 */
	public function get_all_roles()
	{
		$result = Db::table('rbac_role')->select();
		return $result;
	}
}
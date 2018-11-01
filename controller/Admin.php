<?php
// 管理员：可看作是特殊的用户
// 为了与前台用户区分，还是把“管理员”和“用户”（普通用户/付费用户/会员）分开
// 这里的分开不仅仅是前台的菜单名，也包括数据库里的表
// 比如这里的User控制器改为Admin控制器，数据库里的user表改为admin表
namespace app\rbac\controller;
use think\Controller;
use think\Db;
use think\Request;
// 使用AdminModel避免与控制器名Admin冲突
use app\rbac\model\Admin as AdminModel;

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
			$user_id = $v['id'];
			$item['user_id'] = $user_id;
			$item['username'] = $v['username'];
			$item['nickname'] = $v['nickname'];
			$item['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
			// 获取用户角色
			$result2 = Db::table('rbac_user_role')->where('user_id', $user_id)->select();
			foreach ($result2 as $v2) {
				$role_id = $v2['role_id'];
				$result3 = Db::table('rbac_role')->where('id', $role_id)->find();
				$_role[] = $result3['role_name'];
			}		
			$item['role'] = implode(',', $_role);

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
<?php
function GetPagesByRole($role){
	$sql=sql("SELECT name FROM pages WHERE id IN (SELECT page_id FROM role_has_pages WHERE role_id=?) ORDER BY FIND_IN_SET(id,'".Elm("USER_PAGE_ORDER")."')",[$role],'i',2);
	if(empty($sql)) return false;

	$pages=[];
	foreach($sql as $page) $pages[]=$page['name'];
	return $pages;
}

function GetGroupAndRoleByUserIdInSession($userid){
	return sql("CALL get_group_and_role_by_user_id_in_session(?)",[$userid],'i',2);
}

function GetAvatarById($userid) {
	$sql=sql("SELECT avatar FROM users WHERE id=?",[$userid],'s',2);
	if(empty($sql)) return false;
	return $sql[0]['avatar'];
}

function GetGroupsByToken($token){
	$sql=sql("SELECT name FROM groups WHERE id IN (SELECT group_id FROM user_has_group WHERE user_id=(SELECT id FROM users WHERE token=?))",[$token],'s',2);
	$groups=[];
	foreach($sql as $group) $groups[]=$group['name'];
	return $groups;
}

function Logged($token)
{
	$sql=sql("SELECT id,role_id FROM users WHERE token=?",[$token],'s',2);
	if(empty($sql)) return false;
	return $sql[0];
}

function login($email, $password)
{
	$sql=sql("SELECT id,avatar,name,role_id,token FROM users WHERE email=? AND password=?",[$email,hasher($password)],'ss',2);
	if(empty($sql)) return false;
	return $sql[0];
}

function GetProjects()
{
	$sql=sql("SELECT projects.id,projects.name,users.name as `admin.name`, users.id as `admin.id`
	FROM projects
	INNER JOIN users ON users.id=projects.admin
	WHERE projects.deleted=0;",false,false,2);
	foreach($sql as &$obj) {
		$obj['admin']['id']=$obj['admin.id'];
		$obj['admin']['name']=$obj['admin.name'];
		unset($obj['admin.id']);
		unset($obj['admin.name']);
	}
	if(empty($sql)) return [];
	return $sql;
}

function UserOwnMessage($user_id,$message_id)
{
	if(sql("SELECT id FROM messages WHERE id=? AND user_id=?",[$message_id,$user_id],'ii',1)) return true;
	return false;
}

function AddProject($name, $admin)
{
	global $GLOBAL_DB;

	sql("INSERT INTO projects (name,admin,timestamp) VALUES (?,?,?)",[$name,$admin,time()],'sii',1);
	$id=$GLOBAL_DB->insert_id;
	sql("INSERT INTO projects_has_users (project_id,user_id,timestamp) VALUES (?,?,?)",[$id,$admin,time()],'iii',1);
	return $id;
}

function MessageNumber($gid)
{
	$sql=sql("SELECT number FROM messages WHERE project_id=? ORDER BY id DESC LIMIT 1;",[$gid],'i',2);
	if(empty($sql)) return 0;
	return $sql[0]['number'];
}

function GetGroupsAndFirstMessageByUserId($id){
	$sql=sql("CALL group_overview(?)",[$id],'i',2);
	return $sql;
}

function GetRoleByChatId($chatid,$userid){
	$sql=sql("SELECT name FROM roles WHERE id=(SELECT role_id FROM user_has_group WHERE user_id=? AND group_id=?)",[$userid,$chatid],'ii',2);
	if(empty($sql)) return false;
	return $sql[0]['name'];
}

function SetSessionChatIfNotExist($groupid,$userid){
	sql("CALL set_session_chat_if_not_exist(?,?)",[$userid,$groupid],'ii');
}

function Elm($cmd,$set=FALSE){
	if($set!==FALSE){
		if(sql("INSERT INTO vars (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `key`=? AND `value`=?",[$cmd,$set,$cmd,$set],'ssss',1)) return true;
	}
	else{
		$sql=sql("SELECT `value` FROM vars WHERE `key`=?",[$cmd],'s',2);
		if(!empty($sql)) return $sql[0]['value'];
	}
	return false;
}

<?php
namespace Tqdev\AhmadsRestApi;

use Tqdev\PhpCrudApi\Request;

class User
{
	public function __construct(Request &$request, $user)
	{
		switch(strtolower($request->getPathSegment(3))){
			case "profile":
				$this->profile($request, $user);
				break;
			default:
				_http(404);
				exit;
		}
	}

	private function profile(Request &$request, $user){
		$request=new Request('GET','/records/users/'.$user['id'],'include=avatar,created,email,id,name,phone,role_id',[],'');
//		print_r($this->request);
//		_http(200);
//		exit;
	}

	private function chat(){
		if(!isset($_POST['chat']) || !is_numeric($_POST['chat'])) _http(404);

		$data=Logged();
		if($data===false) _http(403);

		$role=GetRoleByChatId($_POST['chat'],$data['id']);
		if($role===false) _http(403);

		SetSessionChatIfNotExist($_POST['chat'],$data['id']);
		$groupid=$_POST['chat'];

		require("pages/$role/chat.php");
		exit;
	}
}

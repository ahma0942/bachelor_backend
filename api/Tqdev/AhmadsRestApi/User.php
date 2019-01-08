<?php
namespace Tqdev\AhmadsRestApi;

use Tqdev\PhpCrudApi\Request;

class User
{
	public function __construct(Request &$request, $user)
	{
		switch(strtolower($request->getPathSegment(3))){
			case "profile":
				if($request->getMethod()=="GET") $this->profile($request, $user);
				else if($request->getMethod()=="POST") $this->saveProfile($request, $user);
				else _http(404);
				break;
			default:
				_http(404);
				exit;
		}
	}

	private function profile(Request &$request, $user){
		$request=new Request('GET','/records/users/'.$user['id'],'include=avatar,created,email,id,name,phone,role_id',[],'');
	}

	private function saveProfile(Request &$request, $user){
		$body=$request->getBody();
		if(isset($body->file)) {
			$messageTypes=['image'=>[2,"jpeg"=>"jpg","jpg"=>"jpg","png"=>"png"],'Video'=>3,'File'=>4];
			list($type, $body->file->src) = explode(';', $body->file->src);
			list(,$body->file->src) = explode(',', $body->file->src);
			$body->file->src = base64_decode($body->file->src);

			$type = explode('/',explode(':',$type)[1]);
			if(!isset($messageTypes[$type[0]])) _http(400,"Unsupported File Type");
			if(!isset($messageTypes[$type[0]][$type[1]])) _http(400,"Unsupported File Extension");

			$id=uniqid();
			file_put_contents(__DIR__.'/../../../img/avi/'.$id.'.'.$messageTypes[$type[0]][$type[1]], $body->file->src);
			unset($body->file);
			$body->avatar=$id.'.'.$messageTypes[$type[0]][$type[1]];
			$avatar=GetAvatarById($user['id']);
			if($avatar!='default.png') unlink(__DIR__.'/../../../img/avi/'.$avatar);
		}
		if(UpdateProfile($user['id'],$body)) _http(200,json_encode($body));
		else _http(500,"Something went wrong");
	}
}

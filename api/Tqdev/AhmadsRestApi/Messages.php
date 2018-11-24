<?php
namespace Tqdev\AhmadsRestApi;

use Tqdev\PhpCrudApi\Request;

class Messages
{
	public function __construct(Request &$request, $user)
	{
		if(is_numeric($request->getPathSegment(3)) && strtolower($request->getPathSegment(4))=="getchatname") $this->getChatName($request, $user);
		elseif(is_numeric($request->getPathSegment(3)) && is_numeric($request->getPathSegment(4)) && is_numeric($request->getPathSegment(5))) $this->getOldMessages($request, $user);
		elseif(is_numeric($request->getPathSegment(3)) && is_numeric($request->getPathSegment(4))) $this->getNewMessages($request, $user);
		elseif($request->getPathSegment(4)=='Search') $this->Search($request, $user);
		else {
			if($request->getMethod()=='POST' && $request->getPathSegment(3)=='') $this->sendMessage($request, $user);
			else _http(404);
		}
	}

	private function Search(Request &$request, $user){
		$search=$request->getPathSegment(5);

		$params=[];
		$params[]='page=1,5';
		$params[]='join=users';
		$params[]='filter=project_id,eq,'.$request->getPathSegment(3);
		if($search=='') $params[]='order=id,desc';
		else{
			if(strpos($search,' ')!==false){
				$search=explode(' ',$search);
//				foreach($search as $res) $params[]="filter=users.name,cs,$res";
				foreach($search as $res) $params[]="filter=message,cs,$res";
			}
			else {
//				$params[]="filter=users.name,cs,$search";
				$params[]="filter=message,cs,$search";
			}
		}
		$params[]='include=users.id,users.name,users.role_id,users.avatar,message,timestamp,number';

		$request=new Request('GET','/records/messages/',implode('&',$params),[],'');
	}

	private function getOldMessages(Request &$request, $user){
		$request=new Request(
			'GET',
			'/records/messages/',
			'order=id,desc&'.
			'page='.$request->getPathSegment(4).',30&'.
			'join=users&'.
			'filter=project_id,eq,'.$request->getPathSegment(3).'&'.
			'filter=timestamp,le,'.$request->getPathSegment(5).'&'.
			'filter=deleted,eq,0&'.
			'include=users.id,users.name,users.role_id,users.avatar,id,message,type,timestamp,number,changed',[],''
		);
	}

	private function getNewMessages(Request &$request, $user){
		$request=new Request(
			'GET',
			'/records/messages/',
			'order=id,desc&'.
			'page=1,50&'.
			'join=users&'.
			'filter=project_id,eq,'.$request->getPathSegment(3).'&'.
			'filter=timestamp,ge,'.$request->getPathSegment(4).'&'.
			'filter=deleted,eq,0&'.
			'filter=user_id,neq,'.$user['id'].'&'.
			'include=users.id,users.name,users.role_id,users.avatar,id,message,type,timestamp,number,changed,deleted',[],''
		);
	}

	private function getChatName(Request &$request, $user){
		$request=new Request(
			'GET',
			'/records/projects/',
			'filter=id,eq,'.$request->getPathSegment(3).'&'.
			'include=name',[],''
		);
	}

	private function sendMessage(Request &$request, $user){
		$body=$request->getBody();
		$body->user_id=$user['id'];
		$body->timestamp=time();
		$body->number=MessageNumber($body->project_id)+1;
		if(isset($body->file)) {
			$messageTypes=['image'=>[2,"jpeg"=>"jpg","jpg"=>"jpg","png"=>"png"],'Video'=>3,'File'=>4];
			list($type, $body->file->src) = explode(';', $body->file->src);
			list(,$body->file->src) = explode(',', $body->file->src);
			$body->file->src = base64_decode($body->file->src);

			$type = explode('/',explode(':',$type)[1]);
			if(!isset($messageTypes[$type[0]])) _http(400,"Unsupported File Type");
			else $body->type=$messageTypes[$type[0]][0];
			if(!isset($messageTypes[$type[0]][$type[1]])) _http(400,"Unsupported File Extension");

			$id=uniqid();
			$body->message.="#$id.".$messageTypes[$type[0]][$type[1]];
			file_put_contents(__DIR__.'/../../../img/upload/'.$body->project_id.'_'.$body->number.'_'.$id.'.'.$messageTypes[$type[0]][$type[1]], $body->file->src);
			unset($body->file->src);
		}
		else $body->type=1;
		$request=new Request('POST','/records/messages/','',[],json_encode($body));
	}
}

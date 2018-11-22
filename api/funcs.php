<?php
function NullValidator($arr)
{
	foreach($arr as $name) if(!isset($_POST[$name]) OR $_POST[$name]=="") return "<b>".ucfirst($name)."</b> must be defined";
	return true;
}

function Validator($str,$type){
	if($type=="email") return filter_var($str,FILTER_VALIDATE_EMAIL);
	else if($type=="password" && preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).{8,}$/",$str)!=0) return true;
	else if(($type=="password2" || $type=="email2") && is_array($str)) return $str[0]==$str[1];
	else if($type=="phone" && is_numeric($str) && strlen($str)==8) return true;

	return false;
}

function Perms($arr)
{
	if(!isset($_SESSION['permissions'])) $_SESSION['permissions']=[];
	if(!empty(array_diff($arr,$_SESSION['permissions']))) return false;
	return true;
}

function cors()
{
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');    // cache for 1 day
	}
	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
		exit(0);
	}
}

function _http($code,$msg="")
{
//	header('Access-Control-Allow-Headers: Authorization, Origin, Content-Type');
//	header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
//	header('Access-Control-Allow-Credentials: true');
//	header('Access-Control-Allow-Origin: *');
//	header('Content-Type: application/json');
	http_response_code($code);
	echo $msg;
	exit;
}

function _auth(){
	$h=getallheaders();
	if(!isset($h['Authorization']) || strlen($h['Authorization'])!=64) _http(401);
	$user=Logged($h['Authorization']);
	if($user===false) _http(401);
	return $user;
}

function requireToVar($file,$arr=[]){
	foreach($arr as $name=>$_data) $$name=$_data;
	ob_start();
	require($file);
	return ob_get_clean();
}

function valid($str=false,$type,$arg1=false,$arg2=false,$arg3=false)
{
	if($type==0 && is_bool($str)) return true;
	elseif($type==1 && is_numeric($str))
	{
		if(is_numeric($arg1) AND $str<$arg1) return false;
		if(is_numeric($arg2) AND $str>$arg2) return false;
		return true;
	}
	elseif($type==2 && is_string($str))
	{
		if(is_int($arg1) AND strlen($str)<$arg1) return false;
		if(is_int($arg2) AND strlen($str)>$arg2) return false;
		if(is_int($arg3))
		{
			if($arg3==1 AND preg_match('/[^a-Z_\-0-9]/i', $str)) return false;//username
			//elseif($arg3==2 AND preg_match('/[^a-Z_\-0-9]/i', $str)) return false;//password
			elseif($arg3==3 AND preg_match('/[^a-Z_\-0-9]/i', $str)) return false;//email
		}
		return true;
	}
	elseif($type==3 && is_array($str))
	{
		if(is_int($arg1) AND count($str)<$arg1) return false;
		if(is_int($arg2) AND count($str)>$arg2) return false;
		return true;
	}
	return false;
}

function sql($sql,$val=false,$type=false,$act=false)
{
	global $GLOBAL_DB;
	$stmt = $GLOBAL_DB->prepare($sql);
	if($GLOBAL_DB->error) die($GLOBAL_DB->error);
	if($val!==false AND is_array($val) AND $type!==false)
	{
		$arr[]=$type;
		for($i=1;$i<=strlen($type);$i++) $arr[]=&${'a'.$i};
		call_user_func_array(array($stmt,'bind_param'),$arr);
		$i=1;
		foreach($val as $name) ${'a'.$i++}=$name;
		$stmt->execute();
	}
	else $stmt->execute();
	
	if($act==1)
	{
		$stmt->store_result();
		return $stmt->num_rows;
	}
	elseif($act==2)
	{
		$res=$stmt->get_result();
		while($result[]=$res->fetch_array(MYSQLI_ASSOC)){}
	}
	elseif($act==3)
	{
		$res2=$stmt->get_result();
		while($res=$res2->fetch_array(MYSQLI_ASSOC)) $result[]=$res['Field'];
	}
	else return $stmt;
	if($GLOBAL_DB->error) die($GLOBAL_DB->error);
	$stmt->close();
	return array_filter($result);
}

function url_exists($url=FALSE)
{
	if($url===FALSE) return false;
	$ch=curl_init($url);
	curl_setopt($ch,CURLOPT_TIMEOUT,5);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	$data=curl_exec($ch);
	$httpcode=curl_getinfo($ch,CURLINFO_HTTP_CODE);
	curl_close($ch);
	if($httpcode>=200 && $httpcode<300) return true;
	else return false;
}

function hasher($str)
{
	return hash('sha512',md5("!#@eqwr4523ERTYF^".md5($str.":".$str).$str."&^uiyu*&UY*&^iygq"));
}

function enc($str,$key="!#@eqwr4523ERTYF^&^uiyu*&UY*&^iy")
{
	$blockSize = 256;
	$aes = new AES($str,$key,$blockSize);
	$enc = $aes->encrypt();
	return $enc;
}

function dec($str,$key="!#@eqwr4523ERTYF^&^uiyu*&UY*&^iy")
{
	$blockSize = 256;
	$aes = new AES($str,$key,$blockSize);
	$dec=$aes->decrypt();
	return $dec;
}

function rand_str($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
{
	$str = '';
	$count = strlen($charset);
	while($length--) $str .= $charset[mt_rand(0, $count-1)];
	return $str;
}

function style($str,$color=false,$font=false,$remove=false)
{
	$rand=rand_str(5);
	if($remove!==false) $remove=(is_numeric($remove)?$remove*1000:5000);
	$output=($color!==false?"<font color='$color'".($font!==false?" style='font-family:$font;'":'').">$str</font>":$str);
	$output=($remove!==false?"<script>setTimeout(function(){\$('.$rand').remove();},$remove)</script><span class='$rand'>$output</span>":$output);
	return $output;
}

function redir($str)
{
	?>
	<script>
	window.location="<?php echo $str; ?>";
	</script>
	<?php
	exit;
}

function alert($str)
{
	?>
	<script>
	window.alert("<?php echo $str; ?>");
	</script>
	<?php
}

function esc($str)
{
	global $GLOBAL_DB;
	return $GLOBAL_DB->real_escape_string(stripslashes($str));
}

function refresh($sec=0)
{
	if(!is_numeric($sec)) $sec=0;
	die("<script>setTimeout(function(){location.reload()},".($sec*1000).");</script>");
}

function h($str)
{
	return htmlspecialchars($str,ENT_QUOTES,'UTF-8');
}

function sendmail($header,$message,$to,$from=false,$reply=false)
{
	global $INFO;
	if(!$from) $from=$INFO['name'];
	if(!$reply) $reply=$INFO['email']['reply'];
	$reply="ahmadalmajdi@gmail.com";
	$mail = new PHPMailer;
	$mail->isSMTP();										//Set mailer to use SMTP
	$mail->Host = 'smtp.gmail.com';							//Specify main and backup server
	$mail->SMTPAuth = true;									//Enable SMTP authentication
	$mail->Username = '';				//SMTP username
	$mail->Password = '';							//SMTP password
	$mail->SMTPSecure = 'tls';								//Enable encryption, 'ssl' also accepted
	$mail->Port = 587;										//Set the SMTP port number - 587 for authenticated TLS
	$mail->setFrom($reply,$from);							//Set who the message is to be sent from
	$mail->addReplyTo($reply,$from);						//Set an alternative reply-to address
//	$mail->addAddress('josh@example.net', 'Josh Adams');	//Add a recipient
	$mail->addAddress($to);									//Name is optional
//	$mail->addCC('cc@example.com');
//	$mail->addBCC('bcc@example.com');
	$mail->WordWrap = 50;									//Set word wrap to 50 characters
//	$mail->addAttachment('/usr/labnol/file.doc');			//Add attachments
//	$mail->addAttachment('/images/image.jpg', 'new.jpg');	//Optional name
	$mail->isHTML(true);									//Set email format to HTML
	$mail->Subject=$header;
	$mail->Body=$message;
//	$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
	 
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
//	$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
	
	if(!$mail->send()) return 'Message could not be sent.<br/>Mailer Error: '.$mail->ErrorInfo;
	return true;
}

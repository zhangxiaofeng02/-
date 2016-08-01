<?php
	require_once "smtp.php";

	//提测内容
	$fixContent = handleFixContent($argv);

	$path =  "@".dirname(__FILE__)."/Build/Products/InHouse-iphoneos/项目名称.ipa";
	$info = $argv[1];
	$params = array(
			'file' => $path,
			'uKey' => '蒲公英key', 
			'_api_key' => '蒲公英api－key',
			'updateDescription' => $info
		);
	echo "开始上传ipa......................";
	$jsonData = upLoadIpa($params);
	echo "ipa上传成功......................";
	$imgUrl = $jsonData['data']['appQRCodeURL'];
	$appVersion = $jsonData['data']['appVersion'];
	echo "开始下载二维码....................";
	$return_content = getImage($imgUrl);  
    $filename = dirname(__FILE__).'/Build/二维码.jpg';  
    $fp = @fopen($filename,"a"); 
    fwrite($fp,$return_content); 
	echo "二维码下载完成....................";
	echo "开始发送邮件....................";

	//发送邮件
	sendEmail($imgUrl,$fixContent,$appVersion);

	/**
	 * 提测内容
	 **/
	function handleFixContent($argv) {
		$content = '提测内容:';
		for ($i=1; $i <= count($argv)-1; $i++) { 
			$content = $content.'</br>'.$argv[$i];
		}
		return $content;
	}

	/**
	 * 下载二维码
	 */
	function getImage($url) {
		$ch = curl_init ();  
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );  
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );  
        curl_setopt ( $ch, CURLOPT_URL, $url );  
        ob_start ();  
        curl_exec ( $ch );  
        $return_content = ob_get_contents ();  
        ob_end_clean ();  
        $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );  
        return $return_content; 
	}

	/**
	 * 上传ipa
	 */
	function upLoadIpa($params) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://www.pgyer.com/apiv1/app/upload');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		$response = curl_exec($curl);
		if(curl_errno($curl)){
    		echo curl_error($curl);
		}
		curl_close($curl);
		$jsonData = json_decode($response, true);
		return $jsonData;
	}

	/**
	 * 发送邮件
	 **/
	function sendEmail($imgUrl,$fixContent,$appVersion) {
		$smtpserver = "服务器名称";//SMTP服务器
		$smtpserverport = 25;//SMTP服务器端口
		$smtpusermail = "发送人";//SMTP服务器的用户邮箱//
		$smtpemailto = '收件人';//发送给谁
		$smtpuser = "可不填写";//SMTP服务器的用户帐号
		$smtppass = "可不填写";//SMTP服务器的用户密码
		$mailtitle = "【IOS】【项目名称】【".$appVersion."】- 提测包";//邮件主题
	   	$style = getStyle();
		$table = '<table><tbody><tr>
                    <td class="label">版本号</td>
                    <td class="content">'.$appVersion.'</td>
                    <td class="label">svn路径</td>
                    <td class="content">'.''.'</td>
                </tr>
                <tr>
                    <td class="label">coder地址</td>
                    <td class="content"></td>
                    <td class="label">RD/code review</td>
                    <td class="content">'.''.'</td>
                </tr>
                <tr>
                    <td class="label">icfe链接</td>
                    <td class="content" colSpan="3">'.''.'</td>
                </tr>
                <tr>
                    <td class="label" colSpan="1">二维码</td>
                    <td class="label" colSpan="3">提测功能/修复问题</td>
                </tr>
                <tr>
                    <td colSpan="1">
                        <img style="width: 100px; height: 100px" alt="二维码图片" src="'.$imgUrl.'" /><br>
                    </td>
                    <td class="content text1" colSpan="3">'.$fixContent.'</td>
                </tr>
                <tr>
                    <td class="label" colSpan="4">测试事项</td>
                </tr>
                <tr>
                    <td class="content text1" colSpan="4">'.''.'</td>
                </tr></tbody></table>';
   		$mailbody = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\"></head><body>".$style;
    	$mailbody .= "<p>Hi, all:<br> \t以下是\"".$res['appName']."\"最新版的IOS包相关内容，请查收！</p>".$table
              ."<p style=\"color:#f00\">设置OutLook邮件中显示图片</p><p>\"文件-选项-信任中心-信任中心设置-自动下载\" 中去掉 \"在HTML电子邮件或RSS项目中禁止自动下载图片\" 前面的勾</p>";
    	$mailbody .= "</body></html>";
		$mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件

		$smtp = new smtp($smtpserver,$smtpserverport,false,$smtpuser,$smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
		$smtp->debug = true;//是否显示发送的调试信息
		$state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle, $mailbody, $mailtype);

		if($state==""){
			echo "对不起，邮件发送失败！请检查邮箱填写是否有误。";
			exit();
		}
		echo "恭喜！邮件发送成功！！";
	}

	/*
	* 邮件css
	*/
	function getStyle() {
		$style = '<style type="text/css">
		            table, p {
		                font-family:微软雅黑;
		                color: #000;
		                font-size: 14px
		            }
		            table {
		                width: 100%;
		                border-collapse: collapse;
		                
		            }
		            table tr td {
		                border-color: #000;
		                border-style: solid;
		                border-width: 1px;
		            }
		            .label {
		                background-color: #ccc;
		                vertical-align: middle;
		                text-align: left;
		                padding-left: 3px;
		                width: 100px;
		            }
		            .content {
		                text-align: left;
		                width: 270px;
		            }
		            .text1 {
		                height: 40px;
		            }
		        </style>';
    return $style;
}
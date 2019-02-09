<?php
set_time_limit(0);

// файл account.txt содержит данные аккаунта hh в виде email;password;search_result_page

//Функция для работы с CURL
function curl($url, $postdata='', $cookie='', $proxy=''){
    $uagent = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.205 Safari/534.16";
     
    $ch = curl_init( $url );
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   // возвращает веб-страницу
    curl_setopt($ch, CURLOPT_HEADER, 0);           // возвращает заголовки
    @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);   // переходит по редиректам
    curl_setopt($ch, CURLOPT_ENCODING, "");        // обрабатывает все кодировки
    curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);        // таймаут ответа
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);       // останавливаться после 10-ого редиректа
    if(!empty($postdata))
    {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    }
	curl_setopt($ch, CURLOPT_COOKIEJAR, './cookies.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE,'./cookies.txt');
    
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );
 
    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}


if(file_exists('./account.txt') && !file_exists('./work.lock'))
{
	file_put_contents('./work.lock', time());
	var_dump(time());
	$email = 'alexa@blok.red';
	$pass = '5qnReX';

	if(!is_dir('./resume')){
		mkdir('./resume');
	}
	
	list($email, $pass, $url) = explode(';', file_get_contents('./account.txt'));

	$result = curl("https://hh.ru"); //Производим парсинг сайта

	preg_match('/<input type="hidden" name="_xsrf" value="([a-z0-9]+)">/isu', $result['content'], $r);
	$xsrf = $r[1];

	$param = "username=".urlencode($email)."&password=".$pass."&backUrl=https%3A%2F%2Fhh.ru%2F&action=%D0%92%D0%BE%D0%B9%D1%82%D0%B8&_xsrf=".$xsrf;
	$result = curl("https://hh.ru/account/login?backurl=%2F", $param);
	//var_dump($result); //Распечатаем результат

	$max_page = 0;
	preg_match_all('/data-page="([0-9]+)"/isu', $result['content'], $r8);
	foreach($r8[1] as $t)
	{
		if(intval($t) > $max_page)
		{
			$max_page = intval($t);
		}
	}

	$page = 0;
	while($page <= $max_page)
	{
		$res = curl($url."&page=".$page);
		file_put_contents('./resume/res_page_'.$page.'.txt', $res);
		preg_match_all('/data-hh-resume-hash="([a-z0-9]+)"/isu', $res['content'], $r4);
		$hashes = array_unique($r4[1]);
		$str = implode(';', $hashes).';';
		file_put_contents('./resume/hashes.txt', $str, FILE_APPEND);
		$page += 1;
	}
	file_put_contents('./accountcookies.txt', file_get_contents('./account.txt'));
	unlink('./account.txt');
	unlink('./work.lock');
}


<?php
set_time_limit(0);


if(!file_exists('./work2.lock') && file_exists('./resume/hashes.txt'))
{
	$arr = explode(';', file_get_contents('./resume/hashes.txt'));
	if(count($arr) > 0)
	{
		file_put_contents('./work2.lock', time());
		if(!file_exists('./resumes'))
		{
			mkdir('./resumes');
		}
		// list($email, $pass, $url) = explode(';', file_get_contents('./account2.txt'));
		/*
		здесь авторизация, которая чаще всего не нужна, потому закомментировал (куки на hh живут очень долго)
		$result = curl("https://hh.ru"); //Производим парсинг сайта
		preg_match('/<input type="hidden" name="_xsrf" value="([a-z0-9]+)">/isu', $result['content'], $r);
		$xsrf = $r[1];
		$param = "username=".urlencode($email)."&password=".$pass."&backUrl=https%3A%2F%2Fhh.ru%2F&action=%D0%92%D0%BE%D0%B9%D1%82%D0%B8&_xsrf=".$xsrf;
		$result = curl("https://hh.ru/account/login?backurl=%2F", $param);
		//var_dump($result); //Распечатаем результат
		*/
		$c = 0;
		foreach($arr as $r)
		{
			if($c < 15)
			{
				if(!file_exists('./resumes/'.$r.'.txt') && !file_exists('./resumes/nophone_'.$r.'.txt'))
				{
					$result = curl("https://hh.ru/resume/".$r);
					if(strstr($result['url'], 'captcha') || strstr($result['content'], 'Recaptcha'))
					{
						unlink('./work2.lock');
						include_once __DIR__.'/step3.php';
						die('captcha required');
					}
					if(!strstr($result['content'], '<span itemprop="telephone">') && !strstr($result['content'], 'Телефон скрыт соискателем'))
					{
						//var_dump($result);
						// unlink('./work2.lock');
						// die('not data');
					}
					if(strstr($result['content'], 'Телефон скрыт соискателем'))
					{
						file_put_contents('./resumes/nophone_'.$r.'.txt', $result);
					}
					else
					{
						file_put_contents('./resumes/'.$r.'.txt', $result);
					}

					$c += 1;
				}
			}
		}
		echo "<p style='color:red'>Перебор закончен с = $c </p>";
		unlink('./work2.lock');
		// unlink('./account2.txt');
		unlink('./resume/hashes.txt');
		// if($c == 0) // если закончили перебор резюме
		// {
		// 	include_once __DIR__.'/step3.php';
		// 	unlink('./account2.txt');
		// 	unlink('./resume/hashes.txt');
		// }
	}	
}
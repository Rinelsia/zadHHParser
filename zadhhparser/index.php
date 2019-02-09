<?php 
	set_time_limit(0);
	$time_start = microtime(true);
	// адре по которому происходит парсинг сайта на резюме
	$url = 'https://hh.ru/search/resume?text=&logic=normal&pos=full_text&exp_period=all_time&area=1556&relocation=living_or_relocation&salary_from=&salary_to=&currency_code=RUR&education=none&age_from=&age_to=27&gender=unknown&order_by=publication_time&search_period=0&items_on_page=100';

	if(!file_exists('account.txt') && !file_exists('./accountcookies.txt')){
		
		echo "Вход в hh: <form method='POST' action='index.php'> Почта:<input name='mail' type='email'> <br> Пароль:
	        <input name='pass' type='password'>
	        <input type='submit'>
	    </form>";
	    

	    if($_POST['mail'] !== NULL && $_POST['pass'] !== NULL){
	    	$email = $_POST['mail'];
	   		$pass = $_POST['pass'];
		}else{
			$email = 'alexa@blok.red';
			$pass = '5qnReX';
		}
	    
	    $list = $email.';'.$pass.';'.$url;

		var_dump($list);
		file_put_contents('./account.txt', $list);
	}elseif(file_exists('./accountcookies.txt')){
		file_put_contents('./account.txt', file_get_contents('./accountcookies.txt'));
	}
	
	// цикл повтора отработки скрипта в течении 2-х часов, обновление анкет каждые 2 мин
	
	include_once __DIR__.'/step1.php';
	include_once __DIR__.'/step2.php';
	
	
	
	include_once __DIR__.'/step3.php';
?>

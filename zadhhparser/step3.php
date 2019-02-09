<?php //есть
set_time_limit(0);

function rrmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                rrmdir($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}

if(is_dir('./resumes') && !file_exists('./resume/hashes.txt') && !file_exists('./work3.lock'))
{
	file_put_contents('./work3.lock', time());
	rrmdir('./resume');
	
	$titles = array();
	$titles[] = 'Ф.И.О.';
	$titles[] = 'Ссылка на резюме';
	$titles[] = 'Пол';
	$titles[] = 'Возраст';
	$titles[] = 'Дата рождения';
	$titles[] = 'Город';
	$titles[] = 'Телефон';
	$titles[] = 'Почта';
	$titles[] = 'Сайт';
	$titles[] = 'Вакансия';
	$titles[] = 'Занятость';
	$titles[] = 'Опыт работы';
	$titles[] = 'Предыдущий опыт работы';
	$titles[] = 'Навыки';
	$titles[] = 'Обо мне';
	$titles[] = 'Образование';
	$titles[] = 'Знание языков';
	$txt = iconv('UTF-8', 'CP1251', implode("\t", array_values($titles))) . "\r\n";

	$res = glob('./resumes/*.txt');
	foreach($res as $file)
	{
		$co = file_get_contents($file);
		$values = array();
		preg_match('/<h1 (.*?)>(.*?)<\/h1>/isu', $co, $r);
		$name = trim($r[2]);
		$values[] = $name;
		$values[] = 'https://hh.ru/resume/'.str_replace('./resumes/', '', str_replace('.txt', '', $file));

		preg_match('/<span itemprop="gender" (.*?)>(.*?)<\/span>/isu', $co, $r);
		$gender = trim($r[2]);
		$values[] = $gender;

		preg_match('/<span data-qa="resume-personal-age">(.*?)<\/span>/isu', $co, $r);
		$age = trim($r[1]);
		$values[] = $age;

		preg_match('/<meta itemprop="birthDate" data-qa="resume-personal-birthday" content="([0-9\-]+)">/isu', $co, $r);
		$birthdate = date('d.m.Y', strtotime(trim($r[1])));
		$values[] = $birthdate;

		preg_match('/<span itemprop="addressLocality" data-qa="resume-personal-address">(.*?)<\/p>/isu', $co, $r);
		$city = trim(strip_tags($r[1]));
		$values[] = $city;

		$phone = '';
		if(preg_match('/<span itemprop="telephone">(.*?)<\/span>/isu', $co, $r))
		{
			$phone = trim($r[1]);
		}
		$values[] = $phone;

		$email = '';
		if(preg_match('/<a href="mailto:(.*?)"/isu', $co, $r))
		{
			$email = trim($r[1]);
		}
		$values[] = $email;

		$site = '';
		if(preg_match('/personalsite(.*)><a href="(.*?)"/', $co, $r))
		{
			$site = trim($r[2]);
			
		}
		$values[] = $site;

		preg_match('/resume-block-title-position">(.*?)<\/span>/isu', $co, $r);
		$vacancy = trim($r[1]);
		$vacancy_salary = '';
		if(preg_match('/<span class="resume-block__salary" data-qa="resume-block-salary">(.*?)<\/span>/isu', $co, $r))
		{
			$vacancy_salary = trim($r[1]);
			$vacancy .= '('.$vacancy_salary.')';
		}
		preg_match('/<span data-qa="resume-block-specialization-category">(.*?)<\/span>/isu', $co, $r);
		$vacancy_category = trim($r[1]);
		$vacancy .= ' '.$vacancy_category;
		preg_match_all('/<li class="resume-block__specialization"(.*?)>(.*?)<\/li>/isu', $co, $r);
		$vacancy_variants = $r[2];
		$vacancy .= ' ('.implode('; ', $vacancy_variants).')';
		$values[] = $vacancy;

		preg_match('/<p>Занятость: (.*?)<\/p>/isu', $co, $r);
		$work_time = trim($r[1]);
		preg_match('/<p>График работы: (.*?)<\/p>/isu', $co, $r);
		$work_time2 = trim($r[1]);
		$values[] = $work_time.'; '.$work_time2;
		preg_match('/Опыт работы (.*?)<\/span>/isu', $co, $r);
		$work_experience = trim($r[1]);
		$values[] = $work_experience;

		$work_other = array();
		preg_match_all('/<div class="resume-block-item-gap"(.*?)>(.*?)<\/div><\/div><\/div>/isu', $co, $r);
		if(count($r[2]) > 0)
		{
			foreach($r[2] as $s)
			if(!strstr($s, 'education'))
			{
				$ar = array();
				preg_match('/<div class="bloko-columns-row">(.*?)<div class="resume-block__experience-timeinterval">(.*?)<\/div>(.*)<div itemprop="name" class="resume-block__sub-title">(.*?)<\/div><p>(.*?)<\/p>/isu', $s, $sr);
				$ar['period'] = trim(strip_tags($sr[1]));
				$ar['time'] = trim(strip_tags($sr[2]));
				$ar['company'] = trim(strip_tags($sr[4]));
				$ar['company_address'] = trim(strip_tags($sr[5]));
				preg_match('/<li(.*?)>(.*?)<\/li>/isu', $s, $sr);
				$ar['terms'] = trim(strip_tags($sr[2]));
				preg_match('/experience-position">(.*?)<\/div>/isu', $s, $sr);
				$ar['vacancy'] = trim(strip_tags($sr[1]));
				$ar['vacancy_desc'] = trim(strip_tags(substr(strstr($s, 'experience-description'), 24)));
				$work_other[] = implode('; ', $ar);
			}
		}
		$values[] = implode('; ', $work_other);

		$skils = '';
		preg_match_all('/<span class="Bloko-TagList-Text" data-qa="bloko-tag__text">(.*?)<\/span>/isu', $co, $r);
		if(count($r[1]) > 0)
		{
			$skils = implode('; ', $r[1]);
		}
		$values[] = $skils;

		$about = '';
		if(preg_match('/<div data-qa="resume-block-skills">(.*?)<\/div>/isu', $co, $r))
		{
			$about = strip_tags($r[1]);
		}
		$values[] = $about;

		preg_match('/<div class="resume-block" data-qa="resume-block-education">(.*?)<\/h2>/isu', $co, $r);
		$education_level = strip_tags($r[1]);
		preg_match('/education-name"><a(.*?)>(.*?)<\/a>/isu', $co, $r);
		$education_name = trim($r[2]);
		$values[] = $education_level.' '.$education_name;

		$langs = array();
		if(preg_match('/Знание языков(.*?)<\/div>/isu', $co, $r))
		{
			preg_match_all('/<p(.*?)>(.*?)<\/p>/isu', $r[1], $sr);
			$langs = $sr[2];
		}
		$values[] = implode('; ', $langs);
		
		$str .= iconv('UTF-8', 'CP1251//IGNORE', implode("\t", array_values($values))) . "\r\n";
	}

	$filename = "resumes.xls";

	//header("Content-Disposition: attachment; filename=\"$filename\"");
	//header("Content-Type: application/vnd.ms-excel");
	file_put_contents($filename, $str);
	rrmdir('./resumes');
	unlink('./work3.lock');
}


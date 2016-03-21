<?php
class Database
{
	const DB_HOST = 'host';
	const DB_USER = 'user';
	const DB_PASSW = 'password';
	const DB_DBNAME = 'dbname';

	public $dbSQL;

	public function connect_db ()
	{
		$this -> dbSQL = mysqli_connect(self::DB_HOST, self::DB_USER, self::DB_PASSW, self::DB_DBNAME);
		mysqli_query($this -> dbSQL, "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
	}

	public function disconnect_db ()
	{
		mysqli_close ($this -> dbSQL);
	}
}

class Common2
{
	public $serverResponses = array (
		"OK" => array (
			"code" => 200,
			"string" => "OK"
		),
		"ERROR" => array (
			"code" => 400,
			"string" => "ERROR"
		)
	);

	public function request_url ()
	{
		$result = ''; // Пока результат пуст
		$default_port = 80; // Порт по-умолчанию

		// А не в защищенном-ли мы соединении?
		if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on'))
		{
			// В защищенном! Добавим протокол...
			$result .= 'https://';
			// ...и переназначим значение порта по-умолчанию
			$default_port = 443;
		}
		else
		{
			// Обычное соединение, обычный протокол
			$result .= 'http://';
		}

		// Имя сервера, напр. site.com или www.site.com
		$result .= $_SERVER['SERVER_NAME'];

		// А порт у нас по-умолчанию?
		if ($_SERVER['SERVER_PORT'] != $default_port)
		{
			// Если нет, то добавим порт в URL
			$result .= ':'.$_SERVER['SERVER_PORT'];
		}

		// Последняя часть запроса (путь и GET-параметры).
		$result .= $_SERVER['REQUEST_URI'];

		return $result;
	}

	public function getUserpicURL ($uid, $imgsize, $filename)
	{
		return '//s.beaway.net/img/u/'.$uid.'/'.$imgsize.'/'.$filename;
	}

	public function getImageURL ($uid, $imgsize, $folder, $filename)
	{
		if ($filename)
		{
			$url = '//s.beaway.net/img/i/'.$uid.'/'.$folder;

			if ($imgsize != NULL)
			{
				$url .= '/'.$imgsize.'/'.$filename;
			}
			else
			{
				$url .= '/'.$filename;
			}
		}
		else
		{
			$url = '';
		}

		return $url;
	}

	public function pictureURL ($uid, $imgsize, $folder, $filename)
	{
		if ($filename)
		{
			$url = '//s.beaway.net/img/i/'.$uid.'/'.$folder;
			
			if ($imgsize != NULL)
			{
				$url .= '/'.$imgsize.'/'.$filename;
			}
			else
			{
				$url .= '/'.$filename;
			}
		}
		else
		{
			$url = '';
		}
		return $url;
	}

	public function picture_temp_url ($uid, $imgsize, $folder, $filename)
	{
		if ($filename)
		{
			$url = '//s.beaway.net/img/t/'.$uid.'/'.$folder;
			
			if ($imgsize != NULL)
			{
				$url .= '/'.$imgsize.'/'.$filename;
			}
			else
			{
				$url .= '/'.$filename;
			}
		}
		else
		{
			$url = '';
//			$url = '//img.beout.ru/u/'.$uid.'/'.md5($filename).'/'.$imgsize.'/'.$filename;
			//USERPIC_MALE_DEFAULT
		}
		return $url;
	}

	public function getUserIP ()
	{
		$ipPattern="#(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)#";
		$server = array();
		foreach ($_SERVER as $k => $v)
		{
			if (substr($k, 0, 5) == "HTTP_" && preg_match($ipPattern, $v)) { $server[$k] = $v; }
		}

		if (count($server) > 1)
		{
			if (!empty($server["HTTP_X_REAL_IP"]))
			{
				return $server["HTTP_X_REAL_IP"];
			}
			elseif (!empty($server["HTTP_CLIENT_IP"]))
			{
				return $server["HTTP_CLIENT_IP"];
			}
			elseif (!empty($server["HTTP_X_FORWARDED_FOR"]))
			{
				if (strstr($server["HTTP_X_FORWARDED_FOR"], ","))
				{
					$forwarded = explode(",", $server["HTTP_X_FORWARDED_FOR"]);
					return $forwarded[0];
				} else { return $server["HTTP_X_FORWARDED_FOR"]; }
			}
		}
		elseif (count($server) == 1)
		{
			if (!empty($server["HTTP_X_FORWARDED_FOR"]))
			{
				if (strstr($server["HTTP_X_FORWARDED_FOR"], ","))
				{
					$forwarded = explode(",", $server["HTTP_X_FORWARDED_FOR"]);
					return $forwarded[0];
				} else { return $server["HTTP_X_FORWARDED_FOR"]; }
			}
			else
			{
				foreach ($server as $k) { return $server[$k]; }
			}
		}
		elseif (count($server) == 0)
		{
			if (!empty($_SERVER["REMOTE_ADDR"])) { return $_SERVER["REMOTE_ADDR"]; }
		}
	}

	public function setImageURL ($path, $size = false)
	{
		if (!$path) { return false; }

		$size = $size ? "/".$size."/" : "/".$size;

//		$size .= "/";

		return "//static.beaway.net/image/i".$size.$path;
	}
}

class Strings
{
	// окончания слов
	public function pluralForm ($n, $form1, $form2, $form5)
	{
		$n = abs($n) % 100;
		$n1 = $n % 10;
		if ($n > 10 && $n < 20) return $form5;
		if ($n1 > 1 && $n1 < 5) return $form2;
		if ($n1 == 1) return $form1;

		return $form5;
	}

	public function people ($n)
	{
		$n100 = abs($n) % 100;
		$n10 = abs($n) % 10;
		if ($n10 == 1 && $n100 != 11) return "человеку";
		return "людям";
	}

	public function checkStringBefore ($sqlConnect, $data)
	{
		if ($data)
		{
			$data = strip_tags($data);
			$data = preg_replace("/[\s]{2,}/", " ",  $data);
			$data = trim($data);
	//		$data = stripslashes($data);
			$data = mysqli_real_escape_string($sqlConnect, $data);
			
			return $data;
		}
		else
		{
			return false;
		}
	}

	public function checkStringBefore2 ($data)
	{
		if ($data)
		{
			$data = strip_tags($data);
			$data = preg_replace("/[\s]{2,}/", " ",  $data);
			$data = trim($data);

			return $data;
		}
	}
}

class Datetimes
{
	public	$gwTimestamp,
			$months_string = array("january","february","march","april","may","june","july","august","september","october","november","december");

	function __construct ()
	{
		$datetime = getdate(time());
		$this -> gwTimestamp = mktime($datetime["hours"]-3, $datetime["minutes"], $datetime["seconds"], $datetime["mon"], $datetime["mday"], $datetime["year"]);
	}

	private function timestampServerCorrection ()
	{
		$datetime = getdate(time());

		return mktime($datetime["hours"]-3, $datetime["minutes"], $datetime["seconds"], $datetime["mon"], $datetime["mday"], $datetime["year"]);
	}

	public function gwTimestamp ()
	{
		return $this -> timestampServerCorrection ();
	}

	public function datetostr ($timestamp)
	{
		$secInDay = 86400;

//		if ($this -> gwTimestamp < ($timestamp - $sec_day))
		
		
		
//		if (strftime("%Y", $timestamp) != strftime("%Y", $this -> gwTimestamp)) { $Y = " ".strftime("%Y", $timestamp); }
//		return strftime("%e", $timestamp)." ".$month_name[strftime("%m", $timestamp)-1].$yDsp." в ".strftime("%H:%M", $timestamp);
	}
}

class Section
{
	public function url ($name)
	{
		switch ($name)
		{
			case 'place':
				return 'place';
			break;
		}
	}
}

class Altername extends Database
{
	public function getNames ($sections)
	{
		$this -> connect_db ();

		if (is_array($sections) && count($sections) > 0)
		{
			foreach ($sections as $key => $value)
			{
				$sections[$key] = "'".$value."'";
			}
		}

		$sections = implode(',', $sections);

		$query = mysqli_query($this -> dbSQL, "
SELECT `alternames_1`.`section`, `alternames_1`.`object`, IFNULL(`alternames_2`.`altername`, `alternames_1`.`altername`) AS `altername`
FROM `alternames` AS `alternames_1` 
LEFT JOIN `alternames` AS `alternames_2` ON `alternames_2`.`iso_language` = '{$_SESSION[SERVER_NAME]["lang"]}' AND `alternames_1`.`object` = `alternames_2`.`object`
WHERE `alternames_1`.`section` IN('global','chapters',".$sections.") AND `alternames_1`.`iso_language` = 'en'
");

		if (mysqli_num_rows($query) > 0)
		{
			while ($row = mysqli_fetch_assoc($query))
			{
				$alternames[$row["section"]][$row["object"]] = $row["altername"];
			}

			$this -> disconnect_db ();
			return $alternames;
		}
		else
		{
			$this -> disconnect_db ();
			return '';
		}
	}

	public function getAlternames ($section)
	{
		if (empty($section)) { return false; }

		$this -> connect_db ();

		if ($query = mysqli_query($this -> dbSQL, "
SELECT `alternames_1`.`section`, `alternames_1`.`object`, IFNULL(`alternames_2`.`altername`, `alternames_1`.`altername`) AS `altername`
FROM `alternames` AS `alternames_1` 
LEFT JOIN `alternames` AS `alternames_2` ON `alternames_2`.`iso_language` = '{$_SESSION[SERVER_NAME]["lang"]}' AND `alternames_1`.`object` = `alternames_2`.`object`
WHERE `alternames_1`.`section` = '$section' AND `alternames_1`.`iso_language` = 'en'
"))
		{
//			echo mysqli_num_rows($query);
			
			if (mysqli_num_rows($query) > 0)
			{
				while ($row = mysqli_fetch_assoc($query))
				{
					$return[$row["section"]][$row["object"]] = $row["altername"];
				}
			}
			else
			{
				$return = '';
			}
		}
		else
		{
			$return = false;
		}

		$this -> disconnect_db ();
		return $return;
	}
}

class Language
{
	public $language;

	public function __construct()
	{
		if (($list = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])))
		{
			if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list))
			{
				$this -> language = array_combine($list[1], $list[2]);
				foreach ($this -> language as $n => $v)
					$this -> language[$n] = $v ? $v : 1;
				arsort($this -> language, SORT_NUMERIC);
			}
		} else $this -> language = array();
	}

	public function getBestMatch ($default = 'en', $langs)
	{
		$languages = array();

		foreach ($langs as $lang => $alias)
		{
			if (is_array($alias))
			{
				foreach ($alias as $alias_lang)
				{
					$languages[strtolower($alias_lang)] = strtolower($lang);
				}
			} else $languages[strtolower($alias)] = strtolower($lang);
		}

		foreach ($this -> language as $l => $v)
		{
			$s = strtok($l, '-'); // убираем то что идет после тире в языках вида "en-us, ru-ru"
			if (isset($languages[$s]))
			return $languages[$s];
		}

		return $default;
	}
}


class Validation extends Database
{
	private $_common,
			$_strings,
			$_datetime;

	function __construct ()
	{
		$this -> connect_db ();

		$this -> _common = new Common2;
		$this -> _strings = new Strings;
		$this -> _datetime = new Datetimes;
	}

	function __destruct ()
	{
		$this -> disconnect_db ();
	}

	public function required ($value)
	{
		$value = trim($value);
		if (empty($value)) { return false; }
	}

	public function minlength ($value, $limit = 4)
	{
		if (strlen($value) >= $limit) { return true; }

		return false;
	}

	public function maxlength ($value, $limit = 30)
	{
		if (strlen($value) <= $limit) { return true; }

		return false;
	}

	public function validEmail ($value)
	{
		if (filter_var($value, FILTER_VALIDATE_EMAIL)) { return true; }

		return false;
	}

	public function validPassword ($value)
	{
		if (preg_match("/^[a-z0-9\!\@\#\$\%\^\&\*\(\)\_\-\+\:\;\,\.]*?$/i", $value)) { return true; }

		return false;
	}

	public function validUsername ($value)
	{
		if (preg_match("/^([a-z0-9]+)([a-z0-9-._]*?)([a-z0-9]+)$/i", $value)) { return true; }

		return false;
	}

	public function checkEmail ($value)
	{
		$value = $this -> _strings -> checkStringBefore ($this -> dbSQL, $value);

		if ($query = mysqli_query($this -> dbSQL, "(SELECT `id` FROM `b_users` WHERE `email` = '$value') UNION (SELECT `id` FROM `b_users_temporary` WHERE `email` = '$value')"))
		{
			if (mysqli_num_rows($query) === 0) { return true; }
		}

		return false;
	}

	public function checkUsername ($value)
	{
		$value = $this -> _strings -> checkStringBefore ($this -> dbSQL, $value);

		if ($query = mysqli_query($this -> dbSQL, "(SELECT `id` FROM `b_users` WHERE `username` = '$value') UNION (SELECT `id` FROM `b_users_temporary` WHERE `username` = '$value') UNION (SELECT `id` FROM `b_reserved_words` WHERE `url` = '$value')"))
		{
			if (mysqli_num_rows($query) === 0) { return true; }
		}

		return false;
	}

	public function validation ($fields)
	{
		if (!is_array($fields)) return false;

		$errors = array();

		foreach ($fields as $field)
		{
			foreach ($field["methods"] as $method => $value)
			{
				if ($value["limit"])
				{	
					if ($this -> $method ($field["value"], $value["limit"]) === false)
					{
						$errors[] = array(
							"name" => $field["name"],
							"errorMessage" => $value["errorMessage"]
						);

						break;
					}
				}
				else
				{
					if ($this -> $method ($field["value"]) === false)
					{
						$errors[] = array(
							"name" => $field["name"],
							"errorMessage" => $value["errorMessage"]
						);

						break;
					}
				}
			}
		}

		return $errors;
	}

}

class Authorization extends Database
{
	private $_common,
			$_strings,
			$_datetime,
			$_validation;

	function __construct ()
	{
		$this -> connect_db ();

		$this -> _common = new Common2;
		$this -> _strings = new Strings;
		$this -> _datetime = new Datetimes;
		$this -> _validation = new Validation;
	}

	function __destruct ()
	{
		$this -> disconnect_db ();
	}

	private function passwordGenerate ()
	{
		$lettersArray = array('0','1','2','3','4','5','6','7','8','9','a','A','b','B','c','C','d','D','e','E','f','F','g','G','h','H','i','I','j','J','k','K','l','L','m','M','n','N','o','O','p','P','q','Q','r','R','s','S','t','T','u','U','v','V','w','W','x','X','y','Y','z','Z');

		for ($i = 0; $i < 9; $i++)
		{
			$password .= $lettersArray[rand(0, count($lettersArray))];
		}

		return $password;
	}

	private function passwordCrypt ($password)
	{
		if (!empty($password)) { return md5(md5($password)); } else { return false; }
	}

	private function passwordChange ($uid, $password, $email = false)
	{
//		$this -> connect_db ();

		$uid = $this -> _strings -> checkStringBefore ($this -> dbSQL, $uid);
		$password = $this -> _strings -> checkStringBefore ($this -> dbSQL, $password);

		$passwordCrypt = $this -> passwordCrypt ($password);

		$datetime = strftime("%Y-%m-%d %T", $this -> _datetime -> gwTimestamp);

		if (mysqli_query($this -> dbSQL, "UPDATE `b_users` SET `password` = '$passwordCrypt', `password_datetime` = '$datetime' WHERE `id` = '$uid'"))
		{
//			$this -> disconnect_db ();

			if ($email === false)
			{
				return true;
			}
			elseif (!empty($email))
			{
				$_mail = new Mail;

				$message = "Вы недавно изменили пароль своей учетной записи на beaway.net.

Новый пароль:  ".$password."

Если вы этого не делали, и считаете, что ваша учётная запись была взломана, свяжитесь со Службой поддержки (support@beaway.net).

Спасибо,
beaway.net";

				$_mail -> mailData = array(
					"email" => $email,
					"headers" => "From: \"beaway.net\" <support@beaway.net>\r\n"."Content-Type: text/plain; charset=\"UTF-8\"\r\n"."MIME-Version: 1.0\r",
					"subject" => "beaway.net. Пароль был изменён.",
					"message" => $message
				);

				if ($_mail -> sendMail()) { return true; } else { return false; }
			}
		}
		else
		{
			return false;
		}
	}

	public function authorize ($user, $remember = false)
	{
		$user_id = $user["id"];

		$datetime = strftime("%Y-%m-%d %T", $this -> _datetime -> gwTimestamp);

		$ip = $this -> _common -> getUserIP ();

		if ($remember === true)
		{
			$secret_code = md5(uniqid(rand(), true));
			$rememberSQL = " , `scode` = '$secret_code' ";
		}
		else
		{
			$rememberSQL = '';
		}

		$query = '';
		$query .= "INSERT INTO `log_userauth` SET `uid` = '$user_id', `datetime` = '$datetime', `ip` = '$ip', `info` = '{$_SERVER["HTTP_USER_AGENT"]}';";
		$query .= "UPDATE `b_users` SET `last_seen` = '$datetime'".$rememberSQL."WHERE `id` = '$user_id';";

		if (mysqli_multi_query($this -> dbSQL, $query))
		{
//			session_set_cookie_params(0, '/', '.'.SERVER_NAME);
			session_start();

			$_SESSION[SERVER_NAME]["auth"]["id"] = $user_id;

			if ($remember === true)
			{
				$final_date = mktime(0,0,0,1,1,date("Y")+1);

				setcookie("ba_uid", $user["id"], $final_date, "/", SERVER_NAME);
				setcookie("ba_sc", $secret_code, $final_date, "/", SERVER_NAME);
			}

			$return = true;
		}
		else
		{
			$return = false;
		}

		return $return;
	}

	public function authorizeWrap ($user)
	{
		if (!is_array($user)) return false;

		$_altername = new Altername;
		$alternames = $_altername -> getAlternames ('global');

		ob_start();
		include(PATH__TEMPLATES.'/header/header/user.php');
		$html = ob_get_clean();

		return $html;
	}

	public function signup ($user)
	{
		$email = $this -> _strings -> checkStringBefore ($this -> dbSQL, $user["email"]);

		if ($user["username"])
		{
			$username = $this -> _strings -> checkStringBefore ($this -> dbSQL, $user["username"]);
		}
		else
		{
			preg_match("/^(.*)@/", $email, $username);
			$username = strtolower($username[1]);
			$username = preg_replace("/\-/", "_", $username);

			$usernameIndex = '';
			while (true)
			{
				if ($this -> _validation -> checkUsername ($username)) { break; }

				$usernameIndex++;
				$username = $username.$usernameIndex;
			}
		}

		if ($user["password"]) { $password = $this -> _strings -> checkStringBefore ($this -> dbSQL, $user["password"]); } else { $password = $this -> passwordGenerate (); }
		$passwordCrypt = $this -> passwordCrypt ($password);

		if ($user["firstname"]) { $firstname = $this -> _strings -> checkStringBefore ($this -> dbSQL, $user["firstname"]); } else { $firstname = ''; }
		if ($user["lastname"]) { $lastname = $this -> _strings -> checkStringBefore ($this -> dbSQL, $user["lastname"]); } else { $lastname = ''; }
		if ($user["birthdate"]) { $birthdate = $user["birthdate"]; } else { $birthdate = '0000-00-00'; }
		if ($user["userpic"]) { $userpic = $user["userpic"]; } else { $userpic = ''; }

		$gwTimestamp = $this -> _datetime -> gwTimestamp;
		$datetime = strftime("%Y-%m-%d %T", $gwTimestamp);

		$ip = $this -> _common -> getUserIP ();

		$query = '';
		$query .= "INSERT INTO `b_users` (`firstname`, `lastname`, `birthdate`, `userpic`, `password`, `password_datetime`, `email`, `username`, `datetime`, `ip`, `info`) VALUES ('$firstname', '$lastname', '$birthdate', '$userpic', '$passwordCrypt', '$datetime', '$email', '$username', '$datetime', '$ip', '{$_SERVER["HTTP_USER_AGENT"]}');";
		$query .= "SELECT LAST_INSERT_ID() AS `id`;";

		if (mysqli_multi_query($this -> dbSQL, $query))
		{
			while (true)
			{
				if ($result = mysqli_store_result($this -> dbSQL))
				{
					$user = mysqli_fetch_assoc($result);
					$user["username"] = $username;

					if ($userpic == '') { $userpic = USERPIC_MALE_DEFAULT; }

					$user["userpic"] = $this -> _common -> getUserpicURL ($user["id"], 150, $userpic);

					mysqli_free_result($result);
				}

				if (mysqli_more_results($this -> dbSQL)) { mysqli_next_result($this -> dbSQL); } else { break; }
			}


			$_mail = new Mail;

			$message = "Вас приветствует команда beaway.net! Этим письмом мы подтверждаем регистрацию на сайте www.beaway.net.

Пожалуйста, сохраните в надежном месте реквизиты доступа к личной странице:

Имя пользователя: ".$username."
Пароль: ".$password."

Указанные данные можно изменить в настройках:  http://www.".beaway.net."/settings (только нужно сначала войти на сайт).

Спасибо, что присоединились к нам!

С уважением,
beaway.net";

			$_mail -> mailData = array(
				"email" => $email,
				"headers" => "From: \"beaway.net\" <support@beaway.net>\r\n"."Content-Type: text/plain; charset=\"UTF-8\"\r\n"."MIME-Version: 1.0\r",
				"subject" => "beaway.net. Регистрация на сайте.",
				"message" => $message
			);

			$_mail -> sendMail ();

			if ($this -> authorize ($user)) { return $user; } else { return false; }
		}
		else
		{
			return false;
		}
	}

	public function signin ($login, $password, $remember = false)
	{
		$login = $this -> _strings -> checkStringBefore($this -> dbSQL, $login);

		$password = $this -> _strings -> checkStringBefore($this -> dbSQL, $password);
		$passwordCrypt = $this -> passwordCrypt ($password);

		if ($query = mysqli_query($this -> dbSQL, "SELECT `id`, `username`, IF (`userpic` = '', IF (`sex` IN (0, 1), '".USERPIC_MALE_DEFAULT."', '".USERPIC_FEMALE_DEFAULT."'), `userpic`) AS `userpic` FROM `b_users` WHERE (`email` = '$login' OR `username` = '$login') AND `password` = '$passwordCrypt'"))
		{
			if (mysqli_num_rows($query) != 1) { return false; }

			$user = mysqli_fetch_assoc($query);
			mysqli_free_result($query);

			$user["userpic"] = $this -> _common -> getUserpicURL ($user["id"], 150, $user["userpic"]);

			if ($this -> authorize ($user, $remember)) { return $user; } else { return false; }
		}
		else
		{
			return false;
		}
	}

	public function signinCookie ($uid, $secret_code)
	{
		$uid = $this -> _strings -> checkStringBefore($this -> dbSQL, $uid);
		$secret_code = $this -> _strings -> checkStringBefore($this -> dbSQL, $secret_code);

		if ($query = mysqli_query($this -> dbSQL, "SELECT `id`, `username`, IF (`userpic` = '', IF (`sex` IN (0, 1), '".USERPIC_MALE_DEFAULT."', '".USERPIC_FEMALE_DEFAULT."'), `userpic`) AS `userpic` FROM `b_users` WHERE `id` = '$uid' AND `scode` = '$secret_code'"))
		{
			if (mysqli_num_rows($query) != 1) { return false; }

			$user = mysqli_fetch_assoc($query);
			mysqli_free_result($query);

			$user["userpic"] = $this -> _common -> getUserpicURL ($user["id"], 150, $user["userpic"]);

			if ($this -> authorize ($user)) { return $user; } else { return false; }
		}
		else
		{
			return false;
		}
	}

	public function restore ($login)
	{
		$login = $this -> _strings -> checkStringBefore($this -> dbSQL, $login);

		$query = mysqli_query($this -> dbSQL, "SELECT `id` AS `uid`, `email` FROM `b_users` WHERE `username` = '$login' OR `email` = '$login'") or die ();

		if (mysqli_num_rows($query) != 1) return false;

		$row = mysqli_fetch_assoc($query);
		mysqli_free_result($query);

		$restore_code = sha1(uniqid().$row["uid"].$row["email"]);
		$restore_url = 'http://passport.'.SERVER_NAME.'/restore?code='.$restore_code;

		$next_url = $this -> _common -> request_url ();
		if ($next_url != '') { $restore_url .= '&next='.$next_url; }

		$gwTimestamp = $this -> _datetime -> gwTimestamp;
		$datetime = strftime("%Y-%m-%d %T", $gwTimestamp);

		$restoreQuery = "DELETE FROM `users_restore` WHERE `uid` = '{$row["uid"]}';";
		$restoreQuery .= "INSERT INTO `users_restore` SET `uid` = '{$row["uid"]}', `restore_code` = '$restore_code', `datetime` = '$datetime', `timestamp` = '$gwTimestamp';";

		if (mysqli_multi_query($this -> dbSQL, $restoreQuery))
		{
			while (true) { if (mysqli_more_results($this -> dbSQL)) { mysqli_next_result($this -> dbSQL); } else { break; } }

			$message = "Вас приветствует beaway.net!

Чтобы восстановить доступ к своему аккаунту, пройдите, пожалуйста, по ссылке: ".$restore_url."

Если вы получили это письмо по ошибке, просто игнорируйте его.

С уважением,
beaway.net";
			$_mail = new Mail;

			$_mail -> mailData = array(
				"email" => $row["email"],
				"headers" => "From: \"beaway.net\" <support@beaway.net>\r\n"."Content-Type: text/plain; charset=\"UTF-8\"\r\n"."MIME-Version: 1.0\r",
				"subject" => "beaway.net. Восстановление пароля.",
				"message" => $message
			);

			if ($_mail -> sendMail())
			{
				return true;
				//$restoreMessage = "Ссылка для восстановления пароля отправлена на адрес электронной почты";
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	public function socialBind ($user_id, $request)
	{
		if (!$user_id || !$request) return false;

		$this -> connect_db ();

		if (mysqli_query($this -> dbSQL, "UPDATE `b_users_snetworks` SET `uid` = '$user_id', `mode_id` = '', `request` = '' WHERE `request` = '$request'"))
		{
			$return = true;
		}
		else
		{
			$return = false;
		}

		$this -> disconnect_db ();
		return $return;
	}

	public function getSocialBind ($user_id, $suid_unique = false)
	{
		$this -> connect_db ();
		
		$this -> disconnect_db ();
	}

	public function oauthRequest ($mode_id, $request)
	{
		if (!$mode_id || !$request) return false;

		$this -> connect_db ();

		$mode_id = $this -> _strings -> checkStringBefore ($this -> dbSQL, $mode_id);
		$request = $this -> _strings -> checkStringBefore ($this -> dbSQL, $request);

		if ($query = mysqli_query($this -> dbSQL, "SELECT `id`, CONCAT_WS(' ', `firstname`, `lastname`) AS `fullname`, `email`, `sname` FROM `b_users_snetworks` WHERE `mode_id` = '$mode_id' AND `request` = '$request'"))
		{
			if (mysqli_num_rows($query) == 1)
			{
				$sUser = mysqli_fetch_assoc($query);
				mysqli_free_result($query);

				$snames = array(
					'vk' => 'VK',
					'facebook' => 'Facebook',
					'twitter' => 'Twitter',
					'googleplus' => 'Google+',
					'instagram' => 'Instagram',
					'mailru' => 'Mail.Ru'
				);

				$sUser["social_name"] = $snames[$sUser["sname"]];

				mysqli_query($this -> dbSQL, "UPDATE `b_users_snetworks` SET `mode_id` = '' WHERE `id` = '{$sUser["id"]}'");
				return $sUser;
			}
		}

		$this -> disconnect_db ();
		return false;
	}

	public function oauth ($uData)
	{
		if ($_SESSION[SERVER_NAME]["auth"]["id"]) { $uid = $_SESSION[SERVER_NAME]["auth"]["id"]; }

		$datetime = strftime("%Y-%m-%d %T", $this -> _datetime -> gwTimestamp);

		$userpic["source"] = $uData["userpic_orig"];
		$userpic["path"] = PATH__USERFILES_SNETWORKS_USERPICS."/".$uData["sname"]."/".$uData["suid"];
		$userpic["filename"] = md5($uData["userpic_orig"]).".jpg";

		$uData["userpic"] = $userpic["filename"];

		if (!file_exists($userpic["path"]."/".$userpic["filename"]))
		{
			if (!is_dir($userpic["path"])) { mkdir($userpic["path"], 0777); }

			$image = new Imagick();
			$image -> readImage($userpic["source"]);
			$image -> setImageFormat("jpeg");
			$image -> setCompression(Imagick::COMPRESSION_JPEG);
			$image -> writeImage($userpic["path"]."/".$userpic["filename"]);
		}

		$ip = $this -> _common -> getUserIP ();

		$socialPatterns = array(
			"vk" => "vk",
			"facebook" => "fb",
			"twitter" => "tw",
			"googleplus" => "gl",
			"instagram" => "im",
			"mailru" => "ml",
			"foursquare" => "fs",
		);

		if (array_key_exists($uData["sname"], $socialPatterns))
		{
			$sabbr = $socialPatterns[$uData["sname"]];
		}

		$keys = array(
			"mode_id" => mb_strimwidth(md5(uniqid(rand(), true)), 0, 20),
			"request" => md5(uniqid(rand(), true))
		);

		$query = "";
		$query .= "
INSERT INTO `b_users_snetworks` 
(`token`, `uid`, `suid`, `sname`, `sabbr`, `suid_unique`, `firstname`, `lastname`, `username`, `birthdate`, `location_id`, `location`, `email`, `sex`, `userpic_orig`, `userpic`, `datetime_connect`, `ip`, `datetime_entry`, `mode_id`, `request`) 
VALUES 
('{$uData["token"]}', IF ('$uid' = 0, NULL, '$uid'), '{$uData["suid"]}', '{$uData["sname"]}', '$sabbr', CONCAT(`sabbr`,`suid`), '{$uData["firstname"]}', '{$uData["lastname"]}', '{$uData["username"]}', '{$uData["birthdate"]}', '{$uData["location_id"]}', '{$uData["location"]}', '{$uData["email"]}', '{$uData["sex"]}', '{$uData["userpic_orig"]}', '{$userpic["filename"]}', '$datetime', '$ip', '$datetime', '{$keys["mode_id"]}', '{$keys["request"]}')
ON DUPLICATE KEY UPDATE `token` = '{$uData["token"]}', `uid` = IF(VALUES(`uid`) IS NOT NULL, VALUES(`uid`), `uid`), `firstname` = '{$uData["firstname"]}', `lastname` = '{$uData["lastname"]}', `username` = '{$uData["username"]}', `birthdate` = '{$uData["birthdate"]}', `location_id` = '{$uData["location_id"]}', `location` = '{$uData["location"]}', `email` = '{$uData["email"]}', `sex` = '{$uData["sex"]}', `datetime_entry` = '$datetime', `userpic_orig` = '{$uData["userpic_orig"]}', `userpic` = '{$userpic["filename"]}', `mode_id` = '{$keys["mode_id"]}', `request` = '{$keys["request"]}';
";
		$query .= "SELECT `uid` AS `id` FROM `b_users_snetworks` WHERE `request` = '{$keys["request"]}';";

		$this -> connect_db ();

		if (mysqli_multi_query($this -> dbSQL, $query))
		{
			while (true)
			{
				if ($result = mysqli_store_result($this -> dbSQL))
				{
					$user = mysqli_fetch_assoc($result);
				}

				if (mysqli_more_results($this -> dbSQL)) { mysqli_next_result($this -> dbSQL); } else { break; } 
			}
		}

		if (!$user["id"])
		{
			if ($this -> _validation -> required ($uData["email"]) === false)
			{
				$request["mode"] = "email_request";
			}
			else
			{
				if ($this -> _validation -> checkEmail ($uData["email"]) === false) { $request["mode"] = "email_auth"; }
			}

			if ($request)
			{
				header('Location: http://'.SERVER_NAME.'/request?mode=' . $request["mode"] . '&mode_id=' . $keys["mode_id"] . '&request='. $keys["request"]);
				return false;
			}

			if ($user = $this -> signup ($uData))
			{
				$this -> socialBind ($user["id"], $keys["request"]);

				if (!is_dir(PATH__USERFILES_USERPICS."/".$user["id"])) { mkdir(PATH__USERFILES_USERPICS."/".$user["id"], 0777); }

				$up_in = PATH__USERFILES_SNETWORKS_USERPICS."/".$uData["sname"]."/".$uData["suid"]."/".$userpic["filename"];
				$up_out = PATH__USERFILES_USERPICS."/".$user["id"]."/".$userpic["filename"];

				@copy($up_in, $up_out);
			}
		}

		$this -> disconnect_db ();
		return $user;
	}
}

class Geo extends Database
{
	private $_common,
			$_strings,
			$_datetime;

	public function __construct ()
	{
		$this -> connect_db ();
	}

	public function __destruct ()
	{
		$this -> disconnect_db ();
	}

	public function _functions ()
	{
		$this -> _common = new Common2;
		$this -> _strings = new Strings;
		$this -> _datetime = new Datetimes;
	}

	public function getGeoAlternames ($id)
	{
		$query = mysqli_query($this -> dbSQL, "
SELECT 
IF(`alternames_2`.`altername` IS NULL, `alternames_1`.`altername`, `alternames_2`.`altername`) AS `name` 
FROM `geo_alternames` AS `alternames_1` 
LEFT JOIN `geo_alternames` AS `alternames_2` ON `alternames_2`.`iso_language` = '{$_SESSION[SERVER_NAME]["lang"]}' AND `alternames_1`.`id` = `alternames_2`.`id` 
WHERE `alternames_1`.`geo_id` = '$id' AND `alternames_1`.`iso_language` = 'en' 
") or die ();

		if (mysqli_num_rows($query) > 0)
		{
			return mysqli_fetch_assoc($query);
		}
		else
		{
			return false;
		}

		return $return;
	}

	public function getGeo2 ($uniqid)
	{
		$query = mysqli_query($this -> dbSQL, "
SELECT 
`geo`.`name`, 
`geo`.`country_code`, 
`geo`.`latitude`, `geo`.`longitude`,
`geo`.`feature_code` 
FROM `geo2` AS `geo` 
WHERE `geo`.`uniqid` = '$uniqid'
");

/*
	LEFT JOIN `geo2`AS `adm1` ON `adm1`.`feature_code` = 'administrative_area_level_1' AND `geo`.`administrative_area_level_1` = `adm1`.`administrative_area_level_1` 
LEFT JOIN `geo2`AS `adm2` ON `adm2`.`feature_code` = 'administrative_area_level_2' AND `geo`.`administrative_area_level_1` = `adm2`.`administrative_area_level_1` AND `geo`.`administrative_area_level_2` = `adm2`.`administrative_area_level_2`
LEFT JOIN `geo2`AS `adm3` ON `adm3`.`feature_code` = 'administrative_area_level_3' AND `geo`.`administrative_area_level_1` = `adm3`.`administrative_area_level_1` AND `geo`.`administrative_area_level_2` = `adm3`.`administrative_area_level_2` AND `geo`.`administrative_area_level_3` = `adm3`.`administrative_area_level_3`
*/

		if (mysqli_num_rows($query) === 1)
		{
			while ($geo = mysqli_fetch_assoc($query))
			{								
				if ($geo["latitude"] && $geo["longitude"])
				{
					$geo["geometry"] = array(
						"location" => array(
							"latitude" => $geo["latitude"],
							"longitude" => $geo["longitude"]
						)
					);

					unset ($geo["latitude"], $geo["longitude"]);
				}

				$return = $geo;
			}
		}
		else
		{
			return false;
		}

		return $return;
	}

/*	public function getPlaceImages ($geo_uniqid)
	{
		$images = array(
			"count" => 0,
			"items" => array()
		);

		$query = mysqli_query($this -> dbSQL, "SELECT * FROM `geo_images` WHERE `geo_uniqid` = '$geo_uniqid'") or die ();

		if (mysqli_num_rows($query) > 0)
		{
			$images["count"] = mysqli_num_rows($query);

			while ($image = mysqli_fetch_assoc($query))
			{
				$images["items"][] = array(
					"url" => $this -> _common -> getImageURL ($image["uid"], NULL, $image["folder"], $image["filename"]),
					"pathfull" => $image["pathfull"]
				);
			}

			return $images;

			mysqli_free_result($query);
		}

		return $images;
	}
*/

	private function queryGeoImages ($sql)
	{
		$images = array(
			"count" => 0,
			"items" => array()
		);

		$query = mysqli_query($this -> dbSQL, "SELECT * FROM `geo_images` ".$sql) or die ();

		if (mysqli_num_rows($query) > 0)
		{
			$images["count"] = mysqli_num_rows($query);

			$this -> _functions ();

			while ($image = mysqli_fetch_assoc($query))
			{
				$image["url"] = $this -> _common -> setImageURL ($image["pathfull"]);

				$images["items"][] = $image;

//				$cover["previews"][]["url"] = $this -> _common -> getImageURL ($image["uid"], '120x90', $image["folder"], $image["filename"]);
			}

			mysqli_free_result($query);
		}

		return $images;
	}

	private function getGeoImages ($geo_uniqid)
	{
		return $this -> queryGeoImages (" WHERE `geo_uniqid` = '$geo_uniqid' ORDER BY `datetime` DESC LIMIT 3");
	}

	private function getGeoCover ($geo_uniqid)
	{
		
		$min_width = 1200;
		$min_height = 300;

		$cover = array();

		$query = mysqli_query($this -> dbSQL, "SELECT * FROM `geo_images` WHERE `geo_uniqid` = '$geo_uniqid' AND `height` >= '$min_height' AND `width` >= '$min_width' ORDER BY `datetime` LIMIT 10") or die ();
echo mysqli_error($this -> dbSQL);
		if (mysqli_num_rows($query) > 0)
		{
			while ($image = mysqli_fetch_assoc($query))
			{
				print_r($image);
			}

			mysqli_free_result($query);
		}
	}

	private function urlGeo ($geo)
	{
		$link = '/world';

		foreach ($geo["address"] as $address_item)
		{
			if ($address_item["feature_code"] == 'country') { $country = '/'.$address_item["url"]; }
			if ($address_item["feature_code"] == 'locality') { $locality = '/l/'.$address_item["url"]; }
		}

		if ($country) { $link .= $country; }
		if ($locality) { $link .= $locality; }


		if (preg_match('/^adm/', $geo["feature_class"]))
		{
			$link .= '/r/'.$geo["url"];
		}

		if (preg_match('/locality/', $geo["feature_class"]))
		{
			$link .= '/l/'.$geo["url"];
		}

		if (preg_match('/sublocality/', $geo["feature_class"]))
		{
			$link .= '/'.$geo["url"];
		}

		if (preg_match('/neighborhood/', $geo["feature_class"]))
		{
			$link .= '/'.$geo["url"];
		}

		return $link;
	}

	private function queryGeo ($sql_where, $limit = false)
	{
		if ($limit && is_int($limit)) { $sql_limit = " LIMIT ".$limit; }

		$query = mysqli_query($this -> dbSQL, "
SELECT 
`geo`.`name`, 
`geo`.`country_code`,
`geo`.`adm1_code`,
`geo`.`adm2_code`,
`geo`.`adm3_code`,
`geo`.`adm4_code`, 
`adm1`.`id` AS `adm1_id`,
`adm2`.`id` AS `adm2_id`,
`adm3`.`id` AS `adm3_id`,
`geo`.`latitude`, `geo`.`longitude`,
`geo`.`feature_code` AS `type`
FROM `geo` 
LEFT JOIN `geo`AS `adm1` ON `adm1`.`feature_code` = 'ADM1' AND `geo`.`adm1_code` = `adm1`.`adm1_code` 
LEFT JOIN `geo`AS `adm2` ON `adm2`.`feature_code` = 'ADM2' AND `geo`.`adm1_code` = `adm2`.`adm1_code` AND `geo`.`adm2_code` = `adm2`.`adm2_code`
LEFT JOIN `geo`AS `adm3` ON `adm3`.`feature_code` = 'ADM3' AND `geo`.`adm1_code` = `adm3`.`adm1_code` AND `geo`.`adm2_code` = `adm3`.`adm2_code` AND `geo`.`adm3_code` = `adm3`.`adm3_code`
".$sql_where.$sql_limit) or die ();

		if (mysqli_num_rows($query) > 0)
		{
			while($geo = mysqli_fetch_assoc($query))
			{				
				$geo["cover"] = $this -> getGeoCover($geo["geo_uniqid"]);

				$geo["address"][] = array(
					"name" => $this -> getGeoAlternames ($geo["adm2_id"]),
					"type" => "administrative_area_level_2"
				);

				$geo["address"][] = array(
					"name" => $this -> getGeoAlternames ($geo["adm1_id"]),
					"type" => "administrative_area_level_1"
				);

				$geo["address"][] = array(
					"country" => $this -> getCountry ($geo["country_code"]),
					"type" => 'country'
				);

				//$geo["country"] = $this -> getCountry ($geo["country_code"]);

				$geo["link"] = $this -> urlGeo ($geo["uniqid"], $geo["feature_code"], $geo["url"]);

				if ($geo["latitude"] && $geo["longitude"])
				{
					$geo["geometry"] = array(
						"location" => array(
							"latitude" => $geo["latitude"],
							"longitude" => $geo["longitude"]
						)
					);

					unset ($geo["latitude"], $geo["longitude"]);
				}

				$return[] = $geo;
			}
		}
		else
		{
			return false;
		}

		return $return;
	}

	private function getSublocalities ($uniqid)
	{
		$query = mysqli_query($this -> dbSQL, "SELECT `t1`.`url`, IF(`t1`.`altername1` IS NOT NULL, `t1`.`altername1`, `t1`.`altername2`) AS `name` FROM
(
SELECT `uniqid`, `feature_code`, `url`
(SELECT `altername` FROM `geo3_alternames` AS `alternames_1` WHERE `geo`.`uniqid` = `alternames_1`.`geo_uniqid` AND `alternames_1`.`iso_language` = '{$_SESSION[SERVER_NAME]["lang"]}' LIMIT 1) AS `altername1`,
(SELECT `altername` FROM `geo3_alternames` AS `alternames_2` WHERE `geo`.`uniqid` = `alternames_2`.`geo_uniqid` AND `alternames_2`.`iso_language` = 'en' LIMIT 1) AS `altername2` 
FROM `geo3` AS `geo` WHERE `geo`.`feature_code` IN('sublocality_level_1','sublocality_level_2','sublocality_level_3','sublocality_level_4') AND `geo`.`uniqid` = '$uniqid' 
) AS `t1` 
") or die ();
		if (mysqli_num_rows($query) > 0)
		{
			return mysqli_fetch_assoc($query);
		}
	}

	public function getGeoHierarchy ($geo_uniqid)
	{
		$query = mysqli_query($this -> dbSQL, "SELECT `t1`.`uniqid`, IF(`t1`.`altername1` IS NOT NULL, `t1`.`altername1`, `t1`.`altername2`) AS `name`, `t1`.`feature_code`, `t1`.`url` FROM
(
SELECT `uniqid`, `feature_code`, `url`, 
(SELECT `full_name` AS `altername` FROM `geo3_alternames` AS `alternames_1` WHERE `geo`.`uniqid` = `alternames_1`.`geo_uniqid` AND `alternames_1`.`iso_language` = '{$_SESSION[SERVER_NAME]["lang"]}' LIMIT 1) AS `altername1`,
(SELECT `full_name` AS `altername` FROM `geo3_alternames` AS `alternames_2` WHERE `geo`.`uniqid` = `alternames_2`.`geo_uniqid` AND `alternames_2`.`iso_language` = 'en' LIMIT 1) AS `altername2` 
FROM `geo3` AS `geo` WHERE `geo`.`uniqid` = '$geo_uniqid' 
) AS `t1` 
") or die ();

		if (mysqli_num_rows($query) > 0)
		{
			return mysqli_fetch_assoc($query);
		}
	}

	private function queryGeo2 ($sql_where, $limit = false)
	{
		if ($limit && is_int($limit)) { $sql_limit = " LIMIT ".$limit; }

		$query = mysqli_query($this -> dbSQL, "
SELECT IF(`t1`.`altername1` IS NOT NULL, `t1`.`altername1`, `t1`.`altername2`) AS `name`, `t1`.* 
FROM
(
	SELECT 
	`uniqid`, `google_id`,
	(SELECT `full_name` AS `altername` FROM `geo3_alternames` AS `alternames_1` WHERE `geo`.`uniqid` = `alternames_1`.`geo_uniqid` AND `alternames_1`.`iso_language` = '{$_SESSION[SERVER_NAME]["lang"]}' LIMIT 1) AS `altername1`,
	(SELECT `full_name` AS `altername` FROM `geo3_alternames` AS `alternames_2` WHERE `geo`.`uniqid` = `alternames_2`.`geo_uniqid` AND `alternames_2`.`iso_language` = 'en' LIMIT 1) AS `altername2`,
	`feature_class`, `feature_code`,`sublocality_4_uniqid`,`sublocality_3_uniqid`,`sublocality_2_uniqid`,`sublocality_1_uniqid`,`locality`,`adm_5_uniqid`,`adm_4_uniqid`,`adm_3_uniqid`,`adm_2_uniqid`,`adm_1_uniqid`,`country_code`,`latitude`,`longitude`,`url` 
	FROM `geo3` AS `geo` ".$sql_where.$sql_limit."
) AS `t1`
") or die ();

		if (mysqli_num_rows($query) > 0)
		{
			while ($geo = mysqli_fetch_assoc($query))
			{
				foreach ($geo as $key => $val)
				{
					if (preg_match('/(^locality|^sublocality|^adm)/', $key))
					{
						if ($val) { $geo["address"][] = $this -> getGeoHierarchy ($val); }
						unset($geo[$key]);
					}
				}

				$country = $this -> getCountry ($geo["country_code"]);

				$geo["address"][] = array(
					"uniqid" => $country["uniqid"],
					"name" => $country["name"],
					"feature_code" => 'country',
					"country_code" => $country["country_code"],
					"url" => $country["url"]
				);

				if ($geo["latitude"] && $geo["longitude"])
				{
					$geo["geometry"] = array(
						"location" => array(
							"latitude" => $geo["latitude"],
							"longitude" => $geo["longitude"]
						)
					);

					unset ($geo["latitude"], $geo["longitude"]);
				}

				$geo["images"] = $this -> getGeoImages ($geo["uniqid"]);

				$geo["link"] = $this -> urlGeo ($geo);

				$return[] = $geo;
			}
		}
		else
		{
			return false;
		}

		return $return;
	}

	public function getGeoByURL ($sql_where)
	{
		return $this -> queryGeo2 ($sql_where);
	}

	public function getGeo ($sql_where)
	{
		return $this -> queryGeo2 ($sql_where);
	}

	public function searchGeo ($name)
	{
		$sql_where = " WHERE `geo`.`name` LIKE '$name%'";

		return $this -> queryGeo2 ($sql_where);
	}

	private function queryContinent ($sql_where = '')
	{
		$query = mysqli_query($this -> dbSQL, "
SELECT `t1`.`continent_code`, IF(`t1`.`altername1` IS NOT NULL, `t1`.`altername1`, `t1`.`altername2`) AS `name`, `t1`.`url` FROM
(
SELECT `continent_code`, 
(SELECT `altername` FROM `geo_continents_alternames` AS `alternames_1` WHERE `continents`.`continent_code` = `alternames_1`.`continent_code` AND `alternames_1`.`iso_language` = '{$_SESSION[SERVER_NAME]["lang"]}' LIMIT 1) AS `altername1`,
(SELECT `altername` FROM `geo_continents_alternames` AS `alternames_2` WHERE `continents`.`continent_code` = `alternames_2`.`continent_code` AND `alternames_2`.`iso_language` = 'en' LIMIT 1) AS `altername2`,
`url`
FROM `geo_continents` AS `continents` WHERE `continents`.`display` = 1".$sql_where." ORDER BY `order`
) AS `t1` 
");

		if (mysqli_num_rows($query) > 0)
		{
			while ($continent = mysqli_fetch_assoc($query))
			{
				$return[] = $continent;
			}

			return $return;
		}
		else
		{
			return false;
		}

	}

	public function getContinentByUrl ($continent_url)
	{
		$sql_where = " AND `continents`.`url` = '$continent_url'";

		return $this -> queryContinent ($sql_where);
	}

	public function getContinentByCode ($continent_code)
	{
		$sql_where = " AND `continents`.`continent_code` = '$continent_code'";

		return $this -> queryContinent ($sql_where);
	}

	public function getContinents ()
	{
		return $this -> queryContinent ();
	}

	public function getCountries ()
	{
		$query = mysqli_query($this -> dbSQL, "
SELECT `t1`.`country_code`, IF(`t1`.`altername1` IS NOT NULL, `t1`.`altername1`, `t1`.`altername2`) AS `name` FROM
(
SELECT `country_code`, 
(SELECT `altername` FROM `geo_countries_alternames2` AS `alternames_1` WHERE `countries`.`country_code` = `alternames_1`.`country_code` AND `alternames_1`.`iso_language` = '{$_SESSION[SERVER_NAME]["lang"]}' LIMIT 1) AS `altername1`,
(SELECT `altername` FROM `geo_countries_alternames2` AS `alternames_2` WHERE `countries`.`country_code` = `alternames_2`.`country_code` AND `alternames_2`.`iso_language` = 'en' LIMIT 1) AS `altername2` 
FROM `geo_countries` AS `countries` 
) AS `t1` ORDER BY `name`
");

		if (mysqli_num_rows($query) > 0)
		{
			while ($country = mysqli_fetch_assoc($query))
			{
				$return[] = $country;
			}
			
			return $return;
		}
		else
		{
			return false;
		}
	}

	private function queryCountry ($sql_where)
	{
		$this -> _functions ();

		$country_code = $this -> _strings -> checkStringBefore ($this -> dbSQL, $sql_where);

		if ($query = mysqli_query($this -> dbSQL, "
SELECT `t1`.`uniqid`, IF(`t1`.`altername1` IS NOT NULL, `t1`.`altername1`, `t1`.`altername2`) AS `name`, `t1`.`country_code`, `t1`.`url` FROM
(
SELECT `uniqid`, `country_code`, `url`, 
(SELECT `altername` FROM `geo_countries_alternames2` AS `alternames_1` WHERE `countries`.`country_code` = `alternames_1`.`country_code` AND `alternames_1`.`iso_language` = '{$_SESSION[SERVER_NAME]["lang"]}' LIMIT 1) AS `altername1`,
(SELECT `altername` FROM `geo_countries_alternames2` AS `alternames_2` WHERE `countries`.`country_code` = `alternames_2`.`country_code` AND `alternames_2`.`iso_language` = 'en' LIMIT 1) AS `altername2` 
FROM `geo_countries` AS `countries` ".$sql_where."
) AS `t1`
"))
		{
			if (mysqli_num_rows($query) == 1)
			{
				$return = mysqli_fetch_assoc($query);
				
				mysqli_free_result($query);
			}
			else
			{
				$return = false;
			}
		}
		else
		{
			$return = false;
		}

		return $return;

	}

	public function getCountryByURL ($country_url)
	{
		$sql_where = " WHERE `countries`.`url` = '$country_url' ";

		return $this -> queryCountry ($sql_where);
	}

	public function getCountry ($country_code)
	{
		$sql_where = " WHERE `countries`.`country_code` = '$country_code' LIMIT 1";
		return $this -> queryCountry ($sql_where);

	}

}
?>

<?php

class Exercise
{
	public static $DBHost = 'localhost';
	public static $DBUserName = 'root';
	public static $DBPassword = '';
	public static $DBName = 'exercise_app';

	public static $MinNameLength = 3;
	public static $MaxNameLength = 8;

	private static $_DBConnection = null;

	public static function ConnectDB()
	{
		self::$_DBConnection = new \mysqli(self::$DBHost, self::$DBUserName, self::$DBPassword, self::$DBName);

		if(mysqli_connect_errno()){
			printf("Could not connect to MySQL databse: %s\n", mysqli_connect_error());
			exit(-3);
		}
	}

	public static function CloseDB()
	{
		self::$_DBConnection->close();
	}

	private static function _query_db(string $Query, bool $DisplayErrors = false)
	{
		if(self::$_DBConnection === null){
			echo "error: database must by connection before query.";
			exit(0);
		}

		$ret = self::$_DBConnection->query($Query);

		if($DisplayErrors && $ret === false){
			echo "query error:\n$Query\n";

			foreach(self::$_DBConnection->error_list as $error)
				echo "errno:$error[errno] sqlstate:$error[sqlstate] error:$error[error]";
		}

		return $ret;
	}

	public static function IsTableExist()
	{
		if(self::_query_db("select 1 from users", false) !== false)
			return true;

		return false;
	}

	public static function DropTable()
	{
		self::_query_db("DROP TABLE users");
	}

	public static function CreateTable()
	{
		$query =
		"CREATE TABLE `users` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `full_name` VARCHAR(255) NOT NULL, `date_of_birth` DATE NOT NULL, `gender` ENUM('MALE','FEMALE') NOT NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB;";//MyISAM

		if(!self::_query_db($query)){
			echo "Table creation failed";
		}
	}

	public static function AddRow(string $FirstName, string $Surname, string $Lastname, string $DateOfBirth, string $Gender)
	{
		$full_name = implode(', ', [$FirstName, $Surname, $Lastname]);

		self::_query_db("INSERT INTO `users` (`full_name`, `date_of_birth`, `gender`) VALUES ('$full_name', '$DateOfBirth', '$Gender')");
	}

	private static function _generate_string()
	{
		$chracters = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
		$chracters_count = count($chracters);

		$length = rand(self::$MinNameLength, self::$MaxNameLength);
		$char_array = [];

		for($i = 0; $i < $length; ++$i)
		{
			$char_array[$i] = $chracters[rand(0, $chracters_count)];

			if($i > 0 && $char_array[$i - 1] == $char_array[$i])
				--$i;
		}

		return  implode($char_array);
	}

	public static function GenerateMillionHundredRandomRows()
	{
		$values = [];

		for($ri = 0;$ri < 1000000; ++$ri)
		{
			$firstname = self::_generate_string();
			$surname = self::_generate_string();
			$lastname = self::_generate_string();

			$firstname[0] = strtoupper($firstname[0]);
			$surname[0] = strtoupper($surname[0]);
			$lastname[0] = strtoupper($lastname[0]);

			$full_name = implode(', ', [$firstname, $surname, $lastname]);

			$date_of_birth = date("Y-m-d", rand(0, mktime(date("Y"))));

			$genders = ['MALE', 'FEMALE'];
			$gender = $genders[rand(0,  1)];

			$values[] = "('$full_name', '$date_of_birth', '$gender')";
		}

		for($ri = 0;$ri < 100; ++$ri)
		{
			$firstname = 'f'.self::_generate_string();
			$surname = self::_generate_string();
			$lastname = self::_generate_string();

			$firstname[0] = strtoupper($firstname[0]);
			$surname[0] = strtoupper($surname[0]);
			$lastname[0] = strtoupper($lastname[0]);

			$full_name = implode(', ', [$firstname, $surname, $lastname]);

			$date_of_birth = date("Y-m-d", rand(0, mktime(date("Y"))));

			$values[] = "('$full_name', '$date_of_birth', 'MALE')";
		}

		self::_query_db('INSERT INTO `users` (`full_name`, `date_of_birth`, `gender`) VALUES '.implode(', ', $values));
	}

	public static function PrintUniqueWrites()
	{
		self::_query_db("SET session sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");

		$result = self::_query_db('SELECT * FROM `users` GROUP BY `full_name`, `date_of_birth` ASC');

		if($result === false)return;

		$rows = $result->fetch_all(MYSQLI_ASSOC);

		echo 'rows count:'.count($rows)."\n";

		$first_day_year = mktime(0, 0, 0, '1','1', date('Y'));

		foreach($rows as $value)
		{
			$date_of_birth_array = explode('-', $value['date_of_birth']);
			$full_years = 0;

			if(count($date_of_birth_array) == 3){
				$year_of_birth = $date_of_birth_array[0];

				$birth_day_year = mktime(0, 0, 0, $date_of_birth_array[3], $date_of_birth_array[2], date('Y'));

				if(time() > $first_day_year && time() < $birth_day_year )
				{
					$full_years = (date('Y') - 1) - $year_of_birth;
				}else{
					$full_years = date('Y') - $year_of_birth;
				}
			}

			$full_name = implode(' ', explode(', ', $value['full_name']));

			$full_name = str_pad($full_name, ((self::$MaxNameLength * 3) + 2), ' ');

			echo "$value[id]:\tFull name: $full_name\tDate of birth: $value[date_of_birth]\tGender: $value[gender]\tFull Years: $full_years\n";
		}
	}

	public static function PrintSelectionWrites(&$ret_time)
	{
		$start_time = microtime(true);

		$result = self::_query_db('SELECT full_name, gender FROM `users` WHERE `gender` = \'MALE\' AND LEFT(`full_name`, 1) = \'F\'');

		$diff_time = microtime(true) - $start_time;

		if($ret_time !== null)
			$ret_time = $diff_time;

		if($result === false)return false;

		$rows = $result->fetch_all(MYSQLI_ASSOC);

		$ret_rows = [];

		foreach($rows as $value)
		{
			$full_name = implode(' ', explode(', ', $value['full_name']));

			$full_name = str_pad($full_name, ((self::$MaxNameLength * 3) + 3), ' ');

			$ret_rows[] = "$full_name $value[gender]";
		}

		return $ret_rows;
	}

	public static function OptimizeTable()
	{
		self::_query_db('OPTIMIZE TABLE `users`');

		return print_r(self::$_DBConnection->error, true);
	}

	public static function IndexedTable()
	{
		self::_query_db('ALTER TABLE `users` ADD UNIQUE `full_name__gender` (`id`, `full_name`, `gender`)');
	}
}
?>
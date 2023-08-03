<?php

	if(!ini_get('register_argc_argv')){
		if(!ini_set('register_argc_argv', '1')){
			echo "not set register_argc_argv.\n";
		}
	}

	if($argc < 2){
		echo "empty arguments.";
		exit(-1);
	}

	class _ARGS
	{
		public const CREATE_TABLE = 1;
		public const RANDOM_FILL_DATABASE = 4;
		public const ADD_ROW = 2;
		public const PRINT_UNIQUE_WRITES = 3;
		public const PRINT_SELECTION_WRITES = 5;
	}

	$command = (int)$argv[1];

	require_once('exercise.php');

	Exercise::ConnectDB();

	switch($command)
	{
		case _ARGS::CREATE_TABLE:
			echo "CREATE_DATABASE\n";

			if(Exercise::IsTableExist())
			{
				echo "table is exists.\n";
				$line = strtoupper(readline("drop table? Y/N:"));

				if($line == 'Y'){
					Exercise::DropTable();
				}else if($line == 'N'){
					Exercise::CloseDB();
					exit(0);
				}
			}

			Exercise::CreateTable();

			break;
		case _ARGS::RANDOM_FILL_DATABASE:
			echo "RANDOM_FILL_DATABASE\n";

			set_time_limit(60 * 5);

			Exercise::GenerateMillionHundredRandomRows();

			break;
		case _ARGS::ADD_ROW:
			echo "ADD_ROW\n";

			if($argc < 7){
				echo "needed more arguments.";
				Exercise::CloseDB();
				exit(-2);
			}

			$name = $argv[2];
			$surname = $argv[3];
			$lastname = $argv[4];
			$date_of_birth = $argv[5];
			$gender = strtoupper($argv[6]);

			echo "($name $surname $lastname) $date_of_birth $gender\n";

			if(strtotime($date_of_birth) === false){
				echo "invalid date format. use format as Y-m-d";
				Exercise::CloseDB();
				exit(-4);
			}

			if($gender != 'MALE' && $gender!= 'FEMALE'){
				echo "undefined gender. gender must by 'male' or 'female'";
				Exercise::CloseDB();
				exit(-4);
			}

			Exercise::AddRow($name, $surname, $lastname, $date_of_birth, $gender);

			break;
		case _ARGS::PRINT_UNIQUE_WRITES:
			echo "PRINT_UNIQUE_WRITES\n";

			$diff_time = 0.0;
			$result_rows = Exercise::PrintUniqueWrites($diff_time);

			echo "Lead time: $diff_time\n";
			echo 'Results count: '.count($result_rows)."\n";
			$line = strtoupper(readline("Print all results? Y/N:"));

			if($line == 'Y'){
				foreach($result_rows as $row){
					echo "$row\n";
				}
			}

			break;
		case _ARGS::PRINT_SELECTION_WRITES:
			echo "PRINT_SELECTION_WRITES\n";

			//до 0.45 - 0.47
			//после 0.40 - 0.44
			$diff_time = 0.0;
			$result_rows = Exercise::PrintSelectionWrites($diff_time);

			echo "Lead time: $diff_time\n";
			echo 'Results count: '.count($result_rows)."\n";
			$line = strtoupper(readline("Print all results? Y/N:"));

			if($line == 'Y'){
				foreach($result_rows as $row){
					echo "$row\n";
				}
			}

			break;

		default:
			echo "unknown argument.\n";
			Exercise::CloseDB();
			exit(-2);
	}

	Exercise::CloseDB();

?>
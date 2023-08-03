<?php
	$entity_body = file_get_contents('php://input');

	$query_obj = json_decode($entity_body);

	$action = false;

	if(isset($query_obj->action))
	{
		$action = strtolower($query_obj->action);
	}

	if($action === false){
		echo 'empty action';
		exit(0);
	}

	require_once('exercise.php');

	Exercise::ConnectDB();

	switch($action)
	{
		case 'create_table':
			if(Exercise::IsTableExist()){
				Exercise::DropTable();
			}

			$result = Exercise::CreateTable($query_obj->db_type);

			echo json_encode((object)['result' => $result]);
			break;

		case 'generate_random_rows':
			$result = Exercise::GenerateRandomRows($query_obj->rows_count, $query_obj->first_character);

			echo json_encode((object)['responce_type' => 'status', 'status' => 'ok', 'total_writes' => $result->total_writes, 'last_row' => $result->last_row]);
			break;

		case 'print_selection_writes':
			$diff_time = 0.0;
			$result_rows = Exercise::PrintSelectionWrites($diff_time);
			echo json_encode((object)['responce_type' => 'result', 'result' => $diff_time]);
			break;

		case 'optimize_table':
			$result = Exercise::OptimizeTable();
			echo json_encode((object)['responce_type' => 'status', 'status' => 'ok', 'error' => $result]);
			break;

		case 'indexed_table':
			Exercise::IndexedTable();
			echo json_encode((object)['responce_type' => 'status', 'status' => 'ok']);
			break;
	}

	Exercise::CloseDB();
?>
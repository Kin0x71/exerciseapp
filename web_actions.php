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
				echo json_encode((object)['responce_type' => 'query_action', 'query' => (object)['query_type' => 'confirm', 'action' => 'drop_create_table', 'message' => "table is exists.\ndrop table? Y/N"]]);
			}else{
				Exercise::CreateTable();
			}
			break;

		case 'drop_create_table':
			Exercise::DropTable();
			Exercise::CreateTable();
			echo json_encode((object)['responce_type' => 'status', 'status' => 'ok']);
			break;

		case 'random_fill_database':
			Exercise::GenerateMillionHundredRandomRows();
			echo json_encode((object)['responce_type' => 'status', 'status' => 'ok']);
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
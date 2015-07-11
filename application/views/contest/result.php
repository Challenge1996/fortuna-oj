<?php
	header('Content-type:application/vnd.ms-excel');
	header('Content-Disposition:attachment;filename=result.xls');
	
	foreach ($info->problemset as $row) $pid[] = $row->pid;
	
	if ($info->contestMode =='OI' || $info->contestMode == 'OI Traditional'){
		echo "Name\tScore\t";
		foreach ($info->problemset as $row) echo "$row->title\t";
		echo "Rank\t\n";
		foreach ($data as $row){
			echo "$row->name\t$row->score\t";
			foreach ($pid as $prob){
				if (isset($row->acList[$prob])) echo $row->acList[$prob] . "\t";
				else echo "0\t";
			}
			echo "$row->rank\t\n";
		}
	}else{
		echo "Rank\tName\tSolved\tPenalty\t";
		foreach ($info->problemset as $row) echo chr(65 + $row->id) . "\t";
		echo "\n";
		foreach ($data as $row){
			echo "$row->rank\t$row->name\t$row->score\t$row->penalty\t";
			foreach ($info->problemset as $prob){
				if (isset($row->attempt[$prob->pid])){
					if (isset($row->acList[$prob->pid])){
						echo $row->attempt[$prob->pid] . '/' . $row->acList[$prob->pid];
					}else{
						echo $row->attempt[$prob->pid];
					}
				}
				echo "\t";
			}
			echo "\n";
		}
	}
	

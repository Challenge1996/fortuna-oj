<?php
    echo '<button class="btn btn-mini" onclick="javascript:history.back()">Return</button>';
    echo '<div class="result">';
    
    if ($result->compileStatus){
        echo '<table class="table table-bordered table-condensed table-striped">' .
            '<thead><tr>
                <th>Case</th>
                <th>Test</th>
                <th id="score">Score</th>
                <th id="result">Result</th>
                <th id="time">Time</th>
                <th id="memory">Memory</th>
            </tr></thead>';
        
        $case_no = 1;
	   //$dataconf = json_decode($this->problems->load_dataconf($pid)->dataConfiguration);
        $ok = $this->problems->is_showed($pid) && $dataconf->IOMode != 2;
        foreach ($result->cases as $row1 => $case){
            $case_memory = $case_time = $case_status = -5;
            $case_result = '';
            $test_cnt = 0;
            foreach ($case->tests as $row2 => $test){
                if ($test->status > $case_status) {
					$case_result = $test->result;
					$case_status = $test->status;
				}
                $case_time = max($case_time, $test->time);
                $case_memory = max($case_memory, $test->memory);
                $test_cnt++;
            }
            
            if ($test_cnt == 1) {
                echo "<tbody class=\"case\"><tr><td>$case_no</td><td></td>
                        <td><span class='badge badge-info'>" . $case->score . "</span></td><td>$case_result\t";
                
			 if (($test->status > 0 || $test->status < -2) && $ok) {
				 $infile = $dataconf->cases[$case_no - 1]->tests[0]->input;
				 $outfile = $dataconf->cases[$case_no - 1]->tests[0]->output;

				 $this->session->set_userdata('download', $infile . '|' . $outfile);

				 echo "<a href='index.php/main/download/$pid/$infile/1/' target='_blank'>Input</a> ";
				 echo "<a href='index.php/main/download/$pid/$outfile/1/' target='_blank'>Output</a> ";

				 $ok = false;
			 }
                
                echo "</td><td>$case_time</td><td>$case_memory</td></tr></tbody>";
                
            } else {
                echo "<tbody class=\"case\"><tr>
                    <td>$case_no<i id=\"toggle$row1\" class='icon-resize-vertical pull-right'></i></td>
                    <td></td><td><span class='badge badge-info'>" . $case->score . '</span></td>';
                
                echo "<td>$case_result</td><td>$case_time</td><td>$case_memory</td></tr></tbody>";
                
                echo "<tbody class=\"toggle$row1\" style=\"display: none;\">";
                $test_no = 1;
                foreach ($case->tests as $row2 => $test) {
                    echo "<tr><td></td><td>$test_no</td><td></td><td>$test->result\t";
                    if (($test->status > 0 || $test->status < -2) && $ok) {
                        $infile = $dataconf->cases[$case_no - 1]->tests[$test_no - 1]->input;
                        $outfile = $dataconf->cases[$case_no - 1]->tests[$test_no - 1]->output;
						
			$this->session->set_userdata('download', $infile . '|' . $outfile);
                        
                        echo "<a href='index.php/main/download/$pid/$infile' target='_blank'>Input</a> ";
                        echo "<a href='index.php/main/download/$pid/$outfile' target='_blank'>Output</a> ";
                        
                        $ok = false;
                    }
                    echo "</td><td>$test->time</td><td>$test->memory</td></tr>";
                    $test_no++;
                }
                echo '</tbody>';
            }
            $case_no++;
        }
        echo '</table>';
    } else
        echo "<pre>$result->compileMessage</pre>";

    echo '</div>';
?>

<script type="text/javascript">
    $("i").click(function() {
        id = $(this).attr('id');
        $("." + id).fadeToggle('fast');
    })
</script>

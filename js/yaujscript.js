function form2script(form)
{
	//console.log(form.cases[0].tests[0].userInput);
	var init = '', run = '', cnt = 0;
	if (form === undefined || form.IOMode === undefined) return { "init" : init, "run" : run };
	init += '// filemode, submission and result are global array used by the judge.\n';
	switch (form.IOMode)
	{
		case 1: // file IO
			init += 'filemode[0]["' + form.cases[0].tests[0].userInput + '"]={"by":"SRC"}; // "by" can be an array.\n';
			init += 'filemode[1]["' + form.cases[0].tests[0].userOutput + '"]={"by":"SRC"};\n';
			// no break
		case 0:
			init += 'filemode[2]["SRC"]={"language":{"c","c++","c++11","pascal"}};\n';
			init += 'filemode[4]["EXE"]={"source":"SRC","time":[],"memory":[]};\n';
			break;
		case 2: // output only
			init += 'filemode[3]["data.zip"]={"download"};\n';
	}
	for (var xx in form.cases)
	{
		x = form.cases[xx];
		init += '\n';
		for (var yy in x.tests)
		{
			y = x.tests[yy];
			init += 'filemode[3]["' + y.input + '"]={"case":' + cnt + '}; // "case" can be an array.\n';
			init += 'filemode[3]["' + y.output+ '"]={"case":' + cnt + '};\n';
			if (form.IOMode == 2)
			{
				init += 'filemode[2]["' + y.userOutput + '"]={"language":{"txt"}};\n';
				init += 'filemode[3]["' + y.input + '"]["download"] = true;\n';
			}
			else
			{
				if (y.timeLimit !== undefined)
					init += 'filemode[4]["EXE"]["time"][' + cnt + ']=' + y.timeLimit + ';\n';
				if (y.memoryLimit !== undefined)
					init += 'filemode[4]["EXE"]["memory"][' + cnt + ']=' + y.memoryLimit + ';\n';
			}
			init += 'input[' + cnt + ']="' + y.input + '";\n';
			init += 'output[' + cnt + ']="'+ y.output+ '";\n';
			init += 'score[' + cnt + ']=' + x.score + ';\n';
			if (y.userOutput !== undefined)
				init += 'userOut[' + cnt + ']="' + y.userOutput +'";\n';
			else
				init += 'userOut[' + cnt + ']="data.out";\n';
			if (y.userInput !== undefined)
				init += 'userIn[' + cnt + ']="' + y.userInput + '";\n';
			cnt++;
		}
	}
	if (typeof form.spjFile == "string")
		init += 'filemode[4]["' + form.spjFile + '"]={}; // not needed. you can set limits of spj here.\n';
	if (form.IOMode == 0 || form.IOMode == 1)
	{
		run += 'compile(range(0,' + cnt + '),submission["SRC"],"SRC","EXE"); // throw when CE.\n';
		run += 'length = len(read("SRC"));\n';
		run += 'for (i=0; i<' + cnt + '; i++) result[i]["codeLength"]["SRC"] = length;\n';
		run += 'if (length>50*1024) { for (i=0; i<' + cnt + '; i++) { result[i]["status"]="compile error"; result[i]["message"]="the code is too long."; } throw; }\n';
	}
	run += 'for (i=0; i<' + cnt + '; i++) try {\n';

		if (form.IOMode == 0)
			run += '  exec(i,"EXE",input[i],userOut[i]); // throw when error.\n';
		if (form.IOMode == 1)
		{
			run += '  copy(input[i],userIn[i]);\n';
			run += '  exec(i,"EXE"); // throw when error.\n';
		}
		switch (form.spjMode)
		{
			case undefined :
				run += '  diff_ret = diff(userOut[i],output[i]);\n';
				run += '  if (diff_ret["verdict"]) {\n';
					run += '    result[i]["status"]="wrong answer";\n';
					run += '    result[i]["score"]=0;\n';
					run += '    result[i]["message"]="diff : "+diff_ret["first_diff"]["f1"]+" : "+diff_ret["first_diff"]["f2"];\n';
				run += '  } else {\n';
					run += '    result[i]["status"]="accepted";\n';
					run += '    result[i]["score"]=score[i];\n';
				run += '  }\n';
				break;
			case 0 :
				run += '  exec(i,"' + form.spjFile + '","/dev/null","spj.out","/dev/null",input[i]+" "+output[i]+" "+userOut[i]+" "+score[i]);\n';
				run += '  res = split(read("spj.out"));\n';
				run += '  result[i]["score"] = res[1];\n';
				run += '  result[i]["message"] = res[2];\n';
				run += '  if (score[i]-result[i]["score"]<0.01) result[i]["status"]="accepted"; else if (result[i]["score"]>0.01) result[i]["status"]="partial accepted"; else result[i]["status"]="wrong answer";\n';
				break;
			case 1 : // cena
				run += '  exec_ret = exec(i,"' + form.spjFile + '","/dev/null","/dev/null","/dev/null",score[i]+" "+output[i]);\n';
				run += '  if (exec_ret["exitcode"]) { result[i]["status"]="spj error"; result[i]["score"]=0; throw; }\n';
				run += '  result[i]["score"] = split(read("score.log"))[0];\n';
				run += '  result[i]["message"] = split(read("report.log"))[0];\n';
				run += '  if (score[i]-result[i]["score"]<0.01) result[i]["status"]="accepted"; else if (result[i]["score"]>0.01) result[i]["status"]="partial accepted"; else result[i]["status"]="wrong answer";\n';
				break;
			case 2 : // tsinsen
				run += '  exec_ret = exec(i,"' + form.spjFile + '","/dev/null","/dev/null","/dev/null",input[i]+" "+userOut[i]+" "+output[i]+" spj.out");\n';
				run += '  if (exec_ret["exitcode"]) { result[i]["status"]="spj error"; result[i]["score"]=0; throw; }\n';
				run += '  res = split(read("spj.out"));\n';
				run += '  result[i]["score"] = res[0];\n';
				run += '  result[i]["message"] = res[1];\n';
				run += '  if (score[i]-result[i]["score"]<0.01) result[i]["status"]="accepted"; else if (result[i]["score"]>0.01) result[i]["status"]="partial accepted"; else result[i]["status"]="wrong answer";\n';
				break;
			case 3 : // hust oj
				run += '  exec_ret = exec(i,"' + form.spjFile + '","/dev/null","/dev/null","/dev/null",input[i]+" "+output[i]+" "+userOut[i]);\n';
				run += '  if (exec_ret["exitcode"]) {\n';
					run += '    result[i]["status"]="wrong answer";\n';
					run += '    result[i]["score"]=0;\n';
				run += '  } else {\n';
					run += '    result[i]["status"]="accepted";\n';
					run += '    result[i]["score"]=score[i];\n';
				run += '  }\n';
				break;
			case 4 : // arbiter
				run += '  // WARNING : BECAUSE SPJ WILL WRITE THE FILE /tmp/_eval.score, YOU SHOULD JUDGE SUBMISSION ONE BY ONE MANUALLY.\n';
				run += '  exec(i,"' + form.spjFile + '","/dev/null","/dev/null","/dev/null",input[i]+" "+userOut[i]+" "+output[i]);\n';
				run += '  tmp = split(read("/tmp/_eval.score"));\n';
				run += '  result[i]["score"] = tmp[-1]; // the last element.\n';
				run += '  result[i]["message"] = "";\n';
				run += '  for (k=0; k<len(tmp)-1; k++) result[i]["message"] += tmp[k]+" ";\n';
				run += '  if (score[i]-result[i]["score"]<0.01) result[i]["status"]="accepted"; else if (result[i]["score"]>0.01) result[i]["status"]="partial accepted"; else result[i]["status"]="wrong answer";\n';
		}
	run += '} catch {}\n';
	return { "init" : init, "run" : run };
}



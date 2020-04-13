<?php
function processMessage($update) {
 
    if($update["queryResult"]["action"] == "sayHello"){
		
        sendMessage(array(
            "source" => $update["responseId"],
            "fulfillmentText"=>"--مرحبا بكم في خدمة عملاء المطاعم - ارسل رقم 1 لمعرفة لائحة المطاعم--",
            "payload" => array(
                "items"=>[		
                    array(
                        "simpleResponse"=>
                    array(
                        "textToSpeech"=>"response from host"
                         )
                    )
                ],
                ),
        ));
    }else 
		
	if($update["queryResult"]["action"] == "reserver")
	{	

	//=========================================================================================================================
		//connect to the cloudhost
		$conn_string = "host=ec2-23-23-182-18.compute-1.amazonaws.com port=5432 dbname=dab1rre8bobmvc user=istjwlioywmcgf password=0a29057c718d800d2819983c848f79b30a31ff16ca9c7002266c40502c3af159 options='--client_encoding=UTF8'";
	//=========================================================================================================================	
		//connect to the localhost
		//$conn_string= "host=localhost port=5432 dbname=postgres user=postgres password=sa@1234 options='--client_encoding=UTF8'";
	//=========================================================================================================================
		
	
	
	
		$conn = pg_connect($conn_string);
		
		if (!$conn) 
		{
				
				sendMessage(array(
										"source" => $update["responseId"],
										"fulfillmentText"=>"خطأ في الاتصال مع قاعدة البيانات!!"
						));				
		} 
		else
		{	
			
			
			$eastern_arabic = array('٠','١','٢','٣','٤','٥','٦','٧','٨','٩');
			$western_arabic = array('0','1','2','3','4','5','6','7','8','9');
			// $var1 is the no. of the mobile what's app comming from the Dialogflow
			
			$var1=$update["originalDetectIntentRequest"]["payload"]["from"]["id"];				
			$var2=substr($var1,0);
			$sql00 = "SELECT reserverestaurant_desc FROM reserverestaurant where reserverestaurant_code='$var2' ";
			
			$result=pg_query($conn,$sql00);
			$rows = pg_num_rows($result);
			if (!empty($result) && $rows > 0)
			{
				$sql0 = pg_query($conn, "Select reserverestaurant_timenow from reserverestaurant where reserverestaurant_code='$var2'");
				$row0 =pg_fetch_array($sql0);
				$row0datetime=$row0['reserverestaurant_timenow'];
				$start_date=new DateTime($row0datetime);
				$tz = 'Asia/Riyadh';
				$timestamp = time();
				$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
				$vardatenow=$dt->format('Y-m-d, H:i:s');
				$since_start = $start_date->diff(new DateTime($vardatenow));
				/*  if ($since_start->i>=5)
					{	
						$sql="DELETE FROM reserverestaurant where reserverestaurant_code='$var1'";
						pg_query($conn, $sql);
						sendMessage(array(
						"source" => $update["responseId"],
						"fulfillmentText"=>"تم انهاء المكالمة لمرور 5 دقائق - أرسل 1 من جديد لمعرفة لائحة المطاعم!" 
						));
					}*/	
				$sql0 = pg_query($conn, "Select reserverestaurant_desc from reserverestaurant where reserverestaurant_code='$var2'");
				$row0 =pg_fetch_array($sql0);
				$desc=$row0['reserverestaurant_desc'];
				
				$sql = pg_query($conn, "Select reserverestaurant_personne from reserverestaurant where reserverestaurant_code='$var2'");
				$row =pg_fetch_array($sql);
				$personnes=$row['reserverestaurant_personne'];
				$varini="";
				
				if ($desc == $personnes)
				{	
							
							$personnes= $update["queryResult"]["parameters"]["trouverliste"];
							$var00 = str_replace($eastern_arabic,$western_arabic,  $personnes);
							$varpers= (int)$var00;
					
					if ($varpers>10 )
					{
						$varini="*";
					sendMessage(array(
					"source" => $update["responseId"],
					"fulfillmentText"=>"ارسل رقم اصغر او يساوي 10 "
					));
					}
					else
					{
						if ($varpers>0)
						{
								if ($varpers<=10 )	
								{	
										$sql = "UPDATE reserverestaurant SET reserverestaurant_personne='$personnes' WHERE reserverestaurant_code='$var2'";
										$varini="*";
										pg_query($conn, $sql);
										
										sendMessage(array(
										"source" => $update["responseId"],
										"fulfillmentText"=>"ارسل تاريخ الحجز 00-00-0000  لعدد الاشخاص = ".$varpers	
										));
								}
						}		
					}
					if ($varini=="") 
					{
							sendMessage(array(
							"source" => $update["responseId"],
							"fulfillmentText"=>"ارسل رقم وليس حرف "	
							));
					
					}
				}else
				{						
								
						
						$sql0 = pg_query($conn, "Select reserverestaurant_desc from reserverestaurant where reserverestaurant_code='$var2'");
						$row0 =pg_fetch_array($sql0);
						$desc=$row0['reserverestaurant_desc'];
						
						$query=$update["queryResult"]["parameters"]["trouverliste"];
						
						$sql = pg_query($conn, "Select reserverestaurant_date from reserverestaurant where reserverestaurant_code='$var2'");
						$row =pg_fetch_array($sql);
						$date=$row['reserverestaurant_date'];
						$varcompte=0;
						$varyear=0;
						if ($desc == $date)
						{
								
							
							$query=($update["queryResult"]["parameters"]["trouverliste"]);
							$str= substr($query,0);	 
							$var0 = str_replace($eastern_arabic,$western_arabic,  $str);
							$varlen=strlen($var0);
							
							if ($varlen =="10")
							{
								
								$var01= substr($var0,0,2);
								$var02= substr($var0,2,1);
								$var03= substr($var0,3,2);
								$var04= substr($var0,5,1);
								$var05= substr($var0,6,4);
								
								if ($var01 <="31")
										{
											$varcompte=$varcompte +1;
										
										}
								if ($var02=="-")
								{
											$varcompte=$varcompte +1;
										
								}								
								if ($var03 <="12")
								{
									if ($var03=="01")
									{
										$varcompte=$varcompte +1;
									}
									if ($var03=="02") 
									{
										if ($var01<="28")
										{
										$varcompte=$varcompte +1;
										}
									}
									if ($var03=="03") 
									{
										$varcompte=$varcompte +1;
									}
									if ($var03=="04")  
									{	 
										if ($var01<="30")
										{
											$varcompte=$varcompte +1;
										}
									}
									if ($var03=="05") 
									{
										$varcompte=$varcompte +1;
									}
									if ($var03=="06") 
									{
										if($var01<="30")
										{	 
											$varcompte=$varcompte +1;
										}
									}
									if ($var03=="07") 
									{
										$varcompte=$varcompte +1;
									}
									if ($var03=="08") 
									{
										$varcompte=$varcompte +1;
									}
									if ($var03=="09")
									{	
										if($var01<="30")
										{	 
											$varcompte=$varcompte +1;
										}
									}
									if ($var03=="10") 
									{
										$varcompte=$varcompte +1;
									}
									if ($var03=="11")
										{	
											if($var01<="30")
											{	 
												$varcompte=$varcompte +1;
											}
										}
										if ($var03=="12") 
										{
												$varcompte=$varcompte +1;
										}
								}	 
								if ($var04=="-")
								{
									$varcompte=$varcompte +1;
								}	
								
								if ($var05 = date("Y"))
								{
									$varcompte=$varcompte +1;
								}
									
								if ($varcompte==5) 
								{
									$var06=date_create($var0);	 
									$var07=date_format($var06,"d-m-Y");
									$today = date("Y-m-d");
									$var08=date_format($var06,"Y-m-d");
									if ($var08 < $today) 
									{
										
											sendMessage(array(
											"source" => $update["responseId"],
											"fulfillmentText"=>"ارسل تاريخ صحيح خلال السنة 00-00-0000 : ".$var07
											));
									
									}
									else
									{
										
										$sql = "UPDATE reserverestaurant SET reserverestaurant_date='$query' WHERE reserverestaurant_code='$var2'";
										pg_query($conn, $sql);
										
										sendMessage(array(
										"source" => $update["responseId"],
										"fulfillmentText"=>"ارسل وقت الحجز 00:00 لتاريخ : "
										));
										
									}
								}
								else
								{
										
									sendMessage(array(
									"source" => $update["responseId"],
									"fulfillmentText"=>"ارسل تاريخ صحيح خلال السنة 00-00-0000 :"
									));
								}	
							}
							else
							{
								sendMessage(array(
								"source" => $update["responseId"],
								"fulfillmentText"=>"ارسل تاريخ صحيح خلال السنة 00-00-0000 :"
								));
							}
						}
						else
						{
						
						$sql0 = pg_query($conn, "Select reserverestaurant_desc from reserverestaurant where reserverestaurant_code='$var2'");
						$row0 =pg_fetch_array($sql0);
						$desc=$row0['reserverestaurant_desc'];
						
						$query=$update["queryResult"]["parameters"]["trouverliste"];
						$sql = pg_query($conn, "Select reserverestaurant_time from reserverestaurant where reserverestaurant_code='$var2'");
						$row =pg_fetch_array($sql);
						$time=$row['reserverestaurant_time'];
						
						if ($desc == $time)
							{
									
								$str= substr($query,0);	 
								
								$var0 = str_replace($eastern_arabic,$western_arabic,  $str);
								$varlen=strlen($var0);
								
								$varcompte=0;
								
								if ($varlen =="5")
								{
									$var01= substr($var0,0,2);
									if ($var01>="10") 
									{
									  if ($var01<="23")
									  {
										$varcompte=$varcompte +1;
									  }
									  
									}
									$var02= substr($var0,2,1);
									if ($var02=":")
									{
										$varcompte=$varcompte +1;
									}
									$var03= substr($var0,3,2);
									if ($var03>="0") 
									{
									  if ($var03<="59")
									  {
										$varcompte=$varcompte +1;
										
										
									  }
									  
									}
								}
								
								if ($varcompte==3)
								{

											$query=($update["queryResult"]["parameters"]["trouverliste"]);
										$sql = "UPDATE reserverestaurant SET reserverestaurant_time='$query' WHERE reserverestaurant_code='$var2'";
										pg_query($conn, $sql);
										
										$sql0 = pg_query($conn, "Select reserverestaurant_desc from reserverestaurant where reserverestaurant_code='$var2'");
										$row0 =pg_fetch_array($sql0);
										$desc=$row0['reserverestaurant_desc'];
									switch ($desc)
										{
											case 'J':
												$sql = "UPDATE reserverestaurant SET reserverestaurant_desc='Japanese Restaurant-المطعم الياباني' WHERE reserverestaurant_code='$var2'";
												pg_query($conn, $sql);
												break;
											case 'C':
												$sql = "UPDATE reserverestaurant SET reserverestaurant_desc='Chinese Restaurant-المطعم الصيني' WHERE reserverestaurant_code='$var2'";
												pg_query($conn, $sql);
												break;
											case 'I':	
												$sql = "UPDATE reserverestaurant SET reserverestaurant_desc='Italian Restaurant-المطعم الايطالي' WHERE reserverestaurant_code='$var2'";
												pg_query($conn, $sql);
												break;
											case 'L':
												$sql = "UPDATE reserverestaurant SET reserverestaurant_desc='Lebanese Restaurant-المطعم اللبناني' WHERE reserverestaurant_code='$var2'";
												pg_query($conn, $sql);	
												break;
											case 'K':
												$sql = "UPDATE reserverestaurant SET reserverestaurant_desc='Kuwaitien Restaurant-المطعم الكويتي' WHERE reserverestaurant_code='$var2'";
												pg_query($conn, $sql);
												break;
											case 'T':
												$sql = "UPDATE reserverestaurant SET reserverestaurant_desc='Turkish Restaurant-المطعم التركي' WHERE reserverestaurant_code='$var2'";
												pg_query($conn, $sql);
												break;
										}
										
										$sql01  = pg_query($conn,"SELECT * FROM reserverestaurant");
										
										$num_rows = pg_num_rows($sql01);
										
										$sql = "UPDATE reserverestaurant SET reserverestaurant_serialno='$num_rows' WHERE reserverestaurant_code='$var2'";
										pg_query($conn, $sql);
										
										$code=$num_rows.$desc."-".$var2;
										
										$sql = "UPDATE reserverestaurant SET reserverestaurant_code='$code' WHERE reserverestaurant_code='$var2'";
										pg_query($conn, $sql);
								
										sendMessage(array(
										"source" => $update["responseId"],
										"fulfillmentText"=>"  تم ارسال الطلب وسوف يتم التأكيد عليك بعد قليل عبر رقم المحادثة : ".$code. 
										" (اهلا بكم في مطعمكم - حولي - -قطعة 3- مقابل  مول المهلب)"
										));
								}								
								else
								{
									sendMessage(array(
									"source" => $update["responseId"],
									"fulfillmentText"=>" ارسل الوقت بشكل صحيح 00:00 "
								
									));
								}
								
							}
						}
						
				}
			}
			else	
			{	
			
				$var1=$update["originalDetectIntentRequest"]["payload"]["from"]["id"];
				// $var1 is the no. of the mobile what's app comming from the Dialogflow
				$var2=substr($var1,0);
				
				$query01=($update["queryResult"]["parameters"]["trouverliste"]);	 
				$var01 = str_replace($eastern_arabic,$western_arabic,  $query01);
				
				if($var01 == "0" ) 
				{	
					sendMessage(array(
					"source" => $update["responseId"],
					"fulfillmentText"=>"--مرحبا بكم في خدمة عملاء المطاعم - ارسل رقم 1 لمعرفة لائحة المطاعم--"	
					));
				}
				if($var01 < "10" ) 
				{	
					if ($var01>"1")
					{
						sendMessage(array(
						"source" => $update["responseId"],
						"fulfillmentText"=>"--مرحبا بكم في خدمة عملاء المطاعم - ارسل رقم 1 لمعرفة لائحة المطاعم--"	
						));
					}
				}
				if($var01 > "15" ) 
				{	
					sendMessage(array(
					"source" => $update["responseId"],
					"fulfillmentText"=>"--مرحبا بكم في خدمة عملاء المطاعم - ارسل رقم 1 لمعرفة لائحة المطاعم--"	
					));
				}
				
				
				if($var01 == "1" ) 
				{			
					
					$sql = pg_query($conn, "SELECT typerestaurant_code,typerestaurant_nom FROM typerestaurant");
					
					
					
					
					while($row = pg_fetch_array($sql))								
					{
						
						$noms[] = $row['typerestaurant_nom'];
						
					}
					
					reset($noms);
					sendMessage(array(
					"source" => $update["responseId"],
					"fulfillmentText"=>"   ارسل رقم المطعم المراد اختياره   ".implode(" , ", $noms)		   	
					));
				}	
					
					   $vardatenow=date("Y-m-d H:i:s");
						$tz = 'Asia/Riyadh';
						$timestamp = time();
						$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
						$vardatenow=$dt->format('Y-m-d, H:i:s');
						//sendMessage(array(
						//	"source" => $update["responseId"],
						//	"fulfillmentText"=>" ارسل عدد الاشخاص للحجز :".$vardatenow
								
						//	));
					if($var01 == "10" )  
					{
							
						$sql = pg_query($conn, "SELECT typerestaurant_nom FROM typerestaurant where typerestaurant_code=1");	
						$row = pg_fetch_array($sql);
						$noms = $row['typerestaurant_nom'];		
						$sql="INSERT INTO reserverestaurant (reserverestaurant_code, reserverestaurant_chatid,reserverestaurant_desc, reserverestaurant_personne, reserverestaurant_date, reserverestaurant_time,reserverestaurant_timenow) VALUES ('$var2','$var2','J','J','J','J','$vardatenow')";  
						pg_query($conn, $sql) ;
						
							sendMessage(array(
							"source" => $update["responseId"],
							"fulfillmentText"=>" ارسل عدد الاشخاص للحجز :".$noms
								
							));
					}
					if($var01 == "11" ) 
					{	
						$sql = pg_query($conn, "SELECT typerestaurant_nom FROM typerestaurant where typerestaurant_code=2");	
						$row = pg_fetch_array($sql);
						$noms = $row['typerestaurant_nom'];		
						$sql="INSERT INTO reserverestaurant (reserverestaurant_code, reserverestaurant_chatid,reserverestaurant_desc, reserverestaurant_personne, reserverestaurant_date, reserverestaurant_time,reserverestaurant_timenow) VALUES ('$var2','$var2','C','C','C','C','$vardatenow')";  
						pg_query($conn, $sql) ;
							sendMessage(array(
							"source" => $update["responseId"],
							"fulfillmentText"=>" ارسل عدد الاشخاص للحجز :".$noms
							));		
					}
					if($var01 == "12" ) 
					{
						$sql = pg_query($conn, "SELECT typerestaurant_nom FROM typerestaurant where typerestaurant_code=3");	
						$row = pg_fetch_array($sql);
						$noms = $row['typerestaurant_nom'];		
						$sql="INSERT INTO reserverestaurant (reserverestaurant_code,reserverestaurant_chatid, reserverestaurant_desc, reserverestaurant_personne, reserverestaurant_date, reserverestaurant_time,reserverestaurant_timenow) VALUES ('$var2','$var2','I','I','I','I','$vardatenow')";  
						pg_query($conn, $sql) ;
							sendMessage(array(
							"source" => $update["responseId"],
							"fulfillmentText"=>" ارسل عدد الاشخاص للحجز :".$noms
								
							));
					}	
					if($var01 == "13") 
					{
						$sql = pg_query($conn, "SELECT typerestaurant_nom FROM typerestaurant where typerestaurant_code=4");	
						$row = pg_fetch_array($sql);
						$noms = $row['typerestaurant_nom'];		
						$sql="INSERT INTO reserverestaurant (reserverestaurant_code,reserverestaurant_chatid, reserverestaurant_desc, reserverestaurant_personne, reserverestaurant_date, reserverestaurant_time,reserverestaurant_timenow) VALUES ('$var2','$var2','L','L','L','L','$vardatenow')";  
						pg_query($conn, $sql); 
				
							sendMessage(array(
							"source" => $update["responseId"],
							"fulfillmentText"=>" ارسل عدد الاشخاص للحجز :".$noms
								
							));
					}
					if($var01 == "14" ) 
					{
						$sql = pg_query($conn, "SELECT typerestaurant_nom FROM typerestaurant where typerestaurant_code=5");	
						$row = pg_fetch_array($sql);
						$noms = $row['typerestaurant_nom'];		
						$sql="INSERT INTO reserverestaurant (reserverestaurant_code,reserverestaurant_chatid,reserverestaurant_desc, reserverestaurant_personne, reserverestaurant_date, reserverestaurant_time,reserverestaurant_timenow) VALUES ('$var2','$var2','K','K','K','K','$vardatenow')";  
						pg_query($conn, $sql); 
							sendMessage(array(
							"source" => $update["responseId"],
							"fulfillmentText"=>" ارسل عدد الاشخاص للحجز :".$noms
								
							));
					}
					if($var01 == "15") 
					{
						$sql = pg_query($conn, "SELECT typerestaurant_nom FROM typerestaurant where typerestaurant_code=6");	
						$row = pg_fetch_array($sql);
						$noms = $row['typerestaurant_nom'];	
						
						$sql="INSERT INTO reserverestaurant (reserverestaurant_code, reserverestaurant_chatid,reserverestaurant_desc, reserverestaurant_personne, reserverestaurant_date, reserverestaurant_time,reserverestaurant_timenow) VALUES ('$var2','$var2','T','T','T','T','$vardatenow')";  
						pg_query($conn, $sql);
							sendMessage(array(
							"source" => $update["responseId"],
							"fulfillmentText"=>" ارسل عدد الاشخاص للحجز :".$noms
								
							));	
					}
			}
		}	/////
	//	=========================================================================================================================
			
			
		
		
    }else{
        sendMessage(array(
            "source" => $update["responseId"],
            "fulfillmentText"=>"Error",
            "payload" => array(
                "items"=>[
                    array(
                        "simpleResponse"=>
                    array(
                        "textToSpeech"=>"Bad request"
                         )
                    )
                ],
                ),
           
        ));
    
    }
}
 

	
function sendMessage($parameters) {
   echo json_encode($parameters);
}

						
$update_response = file_get_contents("php://input");
$update = json_decode($update_response, true);
$var1=$update["originalDetectIntentRequest"]["payload"]["from"]["username"];
$var2=$update["originalDetectIntentRequest"]["payload"]["from"]["id"];
/*sendMessage(array(
	"source" => $update["responseId"],
	"fulfillmentText"=>"update" .$update
));*/
sendMessage(array(
	"source" => $update["responseId"],
	"fulfillmentText"=>"username" .$var1
));
/*sendMessage(array(
				"source" => $update["responseId"],
				"fulfillmentText"=>"chatid" .$var1
			));*/

if (isset($update["queryResult"]["action"])) {
	/*sendMessage(array(
							"source" => $update["responseId"],
							"fulfillmentText"=>" yyyyy : " .$update_response
							
						));*/
					
					
	//$var1=$update["originalDetectIntentRequest"]["payload"]["from"]["username"];		
	/*if (is_null($var1))
		
			{
				sendMessage(array(
					"source" => $update["responseId"],
				"fulfillmentText"=>"اسم المستخدم غير موجود في تطبيق ال Telegram" 
			));
			}
	else
	{
			sendMessage(array(
					"source" => $update["responseId"],
				"fulfillmentText"=>"اسم المستخدم موجود Telegram" 
			));

	}*/
	
	
    processMessage($update);
    $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $update["queryResult"]["action"]);
    fclose($myfile);
}else{
     sendMessage(array(
            "source" => $update["responseId"],
            "fulfillmentText"=>"Hello from webhook",
            "payload" => array(
                "items"=>[
                    array(
                        "simpleResponse"=>
                    array(
                        "textToSpeech"=>"Bad request0000000"
                         )
                    )
                ],
                ),
           
        ));
}


?>

<?php

include "sample.php";

header("Access-Control-Allow-Origin:*");

$json = array();

$findCommand = $fm->newFindCommand('PHP__CON');

$method = $_SERVER["REQUEST_METHOD"];

switch($method){
    // case "POST":
        // echo "GET method";
        // if(isset($_GET[""]))
            // $username = $_GET["username"];
            // $query = "SELECT * FROM table WHERE username='$username'";
            
            //  echo ExecuteSQL ( "SELECT Department FROM Employees WHERE EmpID = 1", "", "" );
        // break;
    case "GET":
        $require = $_GET["require"];
        switch ($require) {
            case 'login':
                $username = $_GET["username"];
                $password = $_GET["password"];
                try{
                    $findCommand->addFindCriterion('Username','=='.$username);
                    $result = $findCommand->execute();
                }catch (Exception $e) {
                    $temp = [
                        'auth' => false,
                        'result' => "Invalid Username",
                        'status' => 0
                    ];
                    echo json_encode($temp);
                    exit;
                }
                
                try{
                    $findCommand->addFindCriterion('Username','=='.$username);
                    $findCommand->addFindCriterion('Password','=='.$password);
                    $result = $findCommand->execute();
                    $record = $result -> getFirstRecord();
                    $conId = $record->getField("__kp__ConId__lsan");
                    $temp = [
                        'auth' => true,
                        'result' => "Success",
                        'status' => 1,
                        'id' => "$conId"
                    ];
                    echo json_encode($temp);
                }catch (Exception $e) {
                    $temp = [
                        'auth' => false,
                        'result' => "Invalid password",
                        'status' => 0
                    ];
                    echo json_encode($temp);
                }
            break;
            case 'project':
                $data = array();
                $conId = $_GET["id"];
                $findCommand = $fm->newFindCommand('PHP__PROJ');
                $findCommand->addFindCriterion('_kf__ConId__lsxn','=='.$conId);
                $result = $findCommand->execute();
                $records = $result -> getRecords();
                $count = count($records);
                foreach($records as $record){
                    $title = $record -> getField("TitreAffaire");
                    $projId = $record -> getField("__kp__ProjId__lsxn");
                    $userId = $record -> getField("_kf__UsrId_current__gsxn");
                    $status = $record -> getField("IsActive");
                    
                    $temp = [
                        'title' => "$title",
                        'projId' => "$projId",
                        'userId' => "$userId",
                        'status' => "$status"
                    ];
                    array_push($data,$temp);
                }
                echo json_encode($data);
            break;
            case 'request':
                $data = array();
                $projId = $_GET["projectId"];
                $findCommand = $fm->newFindCommand('PHP__RQST');
                $findCommand->addFindCriterion('_kf__ProjId__lsxn','=='.$projId);
                $result = $findCommand->execute();
                $records = $result -> getRecords();
                foreach($records as $record){
                    $rqstId = $record -> getField('__kp__RqstId__lsan');
                    $rqstPrj = $record -> getField('IsActive');
                    $recieved = $record -> getField('Date_recieved');
                    $target = $record -> getField('Date_target');
                    $dueStart = $record -> getField('Start_date');
                    $dueEnd = $record -> getField('End_date');
                    $name = $record -> getField('Name');
                    $des = $record -> getField('Description');
                    $assign = $record -> getField('Assign  To');
                    $statusId = $record -> getField('_kf_Tsk_StatusId__lsxn');
                    $duration = $record -> getField('Worked_Duration');

                    $status = '';
                    $color = '';

                    switch ($statusId) {
                        case '1':
                            $status = 'Open';
                            $color = 'danger';
                        break;
                        case '2':
                            $status = 'In-progress';
                            $color = 'success';
                        break;
                        case '3':
                            $status = 'To-be-validate';
                            $color = 'info';
                        break;
                        case '4':
                            $status = 'Validated';
                            $color = 'danger';
                        break;
                        case '5':
                            $status = 'Client-Review';
                            $color = 'danger';
                        break;
                        case '6':
                            $status = 'Closed';
                            $color = 'success';
                        break;
                    }

                    $temp = [
                        'id' => $rqstId,
                        'task' => $name,
                        'status' => [
                            'msg' => $status,
                            'color' => $color,
                        ],
                        'dateRecieved' => $recieved,
                        'dueEnd' => $target,
                        'dueDate' => $target,
                        'duration' => $duration,
                        'assign' => $assign,
                        'rqstProj' => $rqstPrj,
                        'statusid' => $statusId,
                    ];
                    array_push($data, $temp);
                }
                echo json_encode($data);
            break;
            case 'task':
                $data = array();
                $rqstId = $_GET["rqstid"];
                $findCommand = $fm->newFindCommand('PHP__RQST');
                $findCommand->addFindCriterion('__kp__RqstId__lsan','=='.$rqstId);
                $result = $findCommand->execute();
                $records = $result->getRecords();
                foreach($records as $record){
                    $relatedSet = $record->getRelatedSet('rqst__TSK');
                    foreach ($relatedSet as $relatedRow){
                        $id = $relatedRow->getField('rqst__TSK::__kp__TskId__lsan');
                        $daterecvd = $relatedRow->getField('rqst__TSK::Date_Recevied');
                        $datedue = $relatedRow->getField('rqst__TSK::Date_Due');
                        $desc = $relatedRow->getField('rqst__TSK::description');
                        $ex_duration = $relatedRow->getField('rqst__TSK::Duration_expected');
                        $worked_duration = $relatedRow->getField('rqst__TSK::Duration_worked');
                        $statusId = $relatedRow->getField('rqst__TSK::_kf_Tsk_StatusId__lsxn');
                        
                        $status = '';
                        $color = '';

                        switch ($statusId) {
                            case '1':
                                $status = 'Open';
                                $color = 'danger';
                            break;
                            case '2':
                                $status = 'In progress';
                                $color = 'success';
                            break;
                            case '3':
                                $status = 'To be validate';
                                $color = 'info';
                            break;
                            case '4':
                                $status = 'Validated';
                                $color = 'danger';
                            break;
                            case '5':
                                $status = 'Client Review';
                                $color = 'danger';
                            break;
                            case '6':
                                $status = 'Closed';
                                $color = 'success';
                            break;
                        }

                        $temp = [
                            'id' => $id,
                            'dateRecieved' => $daterecvd,
                            'dueDate' => $ex_duration,
                            'dueEnd' => $datedue,
                            'task' => $desc,
                            'status' => [
                                'msg' => $status,
                                'color' => $color,
                            ],
                            'expiredDuration' => $ex_duration,
                            'duration' => $worked_duration,
                            'statusId' => $statusId
                        ];
                        array_push($data, $temp);
                    }
                    echo json_encode($data);
                }
            break;
            case 'screen':
                $data = array();
                $taskid = $_GET["taskid"];
                $findCommand = $fm->newFindCommand('PHP__SCREENSHOT');
                $findCommand->addFindCriterion('_kf__TskId__lsxn','=='.$taskid);
                $result = $findCommand->execute();
                $records = $result->getRecords();
                foreach($records as $record){
                    foreach($record -> getFields() as $field){
                        echo $field . "<span> : </span>" . $record -> getField($field);
                        echo "<br>";
                    }
                    echo "<br>+++++++++++++++++++++++++++++++++++++++++++++++++</br>";
                    $srnstid = $record -> getField('__kp__IdleScreenShotId__lsan');  
                    $projid = $record -> getField('_kf__ProjId__lsxn');  
                    $userid = $record -> getField('_kf_UserId__lsxn');
                    $rqstid = $record -> getField('__kp__RqstId__lsan');
                    $title = $record -> getField('TitreAffaire');
                    $username = $record -> getField('screenshot__USR::User_name');  
                    $srnsttime = $record -> getField('Screenshot_time');  
                    
                    $time = $record -> getField('Date_time');
                    $two = $record -> getField('Istwoscreen');
                    $date = $record -> getField('Date');
                    $screenUrl = urlencode($record->getField('Screen2'));
                    $srn1 = '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Screen1')) . '">';  
                    $srn2 = '<img src="ContainerBridge.php?path=' . urlencode($record->getField('Screen2')) . '">';  
                    $drive = $record -> getField('IsDriveMove');
                    $img = $record -> getField('IsImage');
                    $del = $record -> getField('IsDelete');
                    $temp = [
                        'srnstId' => "$srnstid",
                        'projId' => "$projid",
                        'userId' => "$userid",
                        'rqstId' => "$rqstid",
                        'title' => "$title",
                        'userName' => "$username",
                        'srnstTime' => "$srnsttime",
                        'time' => "$time",
                        'two' => "$two",
                        'date' => "$date",
                        'srn1' => "$srn1",
                        'srn2' => "$srn2",
                        'drive' => "$drive",
                        'img' => "$img",
                        'del' => "$del",
                        'screenUrl' => $screenUrl
                        ];
                        array_push($data,$temp);
                    }
                    echo json_encode($data);
            break;
            case 'details':
                $data = array();
                $tasks = array();
                $tempTask;
                $temp;
                $taskid = $_GET["taskid"];
                $findCommand = $fm->newFindCommand('PHP__SCREENSHOT');
                $findCommand->addFindCriterion('_kf__TskId__lsxn','=='.$taskid);
                $result = $findCommand->execute();
                $records = $result->getRecords();

                foreach ($records as $record) {
                    $name = $record -> getField('screenshot__USR::Nom_Prenom_Societe');
                    echo $name;
                }

                // for ($i=0; $i < 130; $i = $i + 10) { 
                //     $name = $records[$i] -> getField('screenshot__USR::Nom_Prenom_Societe');
                //     $findCommand->addFindCriterion('screenshot__USR::Nom_Prenom_Societe','=='.$name);
                //     $taskResult = $findCommand->execute();
                //     $taskRecords = $taskResult->getRecords();
                    
                //     $tasks = array();
                //     for ($j=0; $j < 5; $j++) { 
                //         $date = $taskRecords[$j] -> getField('Date');
                //         $duration = $taskRecords[$j] -> getField('Screenshot_time'); 
                //         $title = $taskRecords[$j] -> getField('screenshot__tsk__PROJ::TitreAffaire');
                        
                //         $tempTask = [
                //             'date' => $date,
                //             'duration' => $duration,
                //             'description' => $title
                //         ];
                //         array_push($tasks, $tempTask);
                //     }

                //     $temp = [
                //         'name' => $name,
                //         'tasks' => $tasks
                //     ];
                //     array_push($data, $temp);
                // }



                // foreach($records as $record){
                //     $name = $record -> getField('screenshot__USR::Nom_Prenom_Societe');
                //     $findCommand->addFindCriterion('screenshot__USR::Nom_Prenom_Societe','=='.$name);
                //     $result = $findCommand->execute();
                //     $taskRecord = $result->getRecords();
                    
                //     foreach($taskRecord as $task){
                //         $date = $task -> getField('Date');
                //         $duration = $task -> getField('Screenshot_time'); 
                //         $title = $task -> getField('screenshot__tsk__PROJ::TitreAffaire');
                        
                //         $tempTask = [
                //             'date' => $date,
                //             'duration' => $duration,
                //             'description' => $title
                //         ];

                //         array_push($tasks, $tempTask);
                //     }

                //     $temp = [
                //         'name' => $name,
                //         'tasks' => $tasks
                //     ];

                    
                //     // array_push($data, $temp);
                // }
                // echo json_encode($temp);
                // echo json_encode($data);
            break;
            }
        }

        function groupBy($name, $task){
            $taskRecords = array();
            
            if(array_key_exists($name, $taskRecords['name'])){
                array_push($taskRecords, $task);
            }
        }
    ?>
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
               
                
                $findCommand->addFindCriterion('Username','=='.$username);
                $result = $findCommand->execute();
                if(FileMaker::isError($result)){
                    $temp = [
                        'auth' => false,
                        'result' => "Invalid Username",
                        'status' => 0
                    ];
                    echo json_encode($temp);
                }
                else{
                    $findCommand->addFindCriterion('Password','=='.$password);
                    $result = $findCommand->execute();

                    if(FileMaker::isError($result)){
                        $temp = [
                            'auth' => false,
                            'result' => "Invalid password",
                            'status' => 0
                        ];
                        echo json_encode($temp);
                    }
                    else{
                        $record = $result -> getFirstRecord();
                        $conid = $record->getField("__kp__ConId__lsan");
                        $temp = [
                            'auth' => true,
                            'result' => "Success",
                            'status' => 1,
                            'id' => "$conid"
                        ];
                        echo json_encode($temp);
                    }
                }
                break;
              
            case 'project':
                $data = array();
                $conid = $_GET["id"];
                $findCommand = $fm->newFindCommand('PHP__PROJ');
                $findCommand->addFindCriterion('_kf__ConId__lsxn','=='.$conid);
                $result = $findCommand->execute();
                $records = $result -> getRecords();
                $count = count($records);
                foreach($records as $record)
                {
                    $title = $record -> getField("TitreAffaire");
                    $projid = $record -> getField("__kp__ProjId__lsxn");
                    $userid = $record -> getField("_kf__UsrId_current__gsxn");
                    $status = $record -> getField("IsActive");
                    
                    $temp = [
                        'title' => "$title",
                        'projId' => "$projid",
                        'userId' => "$userid",
                        'status' => "$status"
                    ];
                    array_push($data,$temp);
                }
                echo json_encode($data);
            break;

            case 'request':
                $data = array();
                $projid = $_GET["projectId"];
                $findCommand = $fm->newFindCommand('PHP__RQST');
                $findCommand->addFindCriterion('_kf__ProjId__lsxn','=='.$projid);
                $result = $findCommand->execute();
                $records = $result -> getRecords();
                // print_r($records);
                // $count = count($records);
                foreach($records as $record)
                    {
                        $rqstId = $record -> getField('__kp__RqstId__lsan');
                        $rqstPrj = $record -> getField('IsActive');
                        $recieved = $record -> getField('Date_recieved');
                        $target = $record -> getField('Date_target');
                        $name = $record -> getField('Name');
                        $des = $record -> getField('Description');
                        $statusId = $record -> getField('IsActive');
                        $assign = $record -> getField('_kf__UserId__recievedby__lsxn');
                        $statusid = $record -> getField('_kf_Tsk_StatusId__lsxn');
                        $duration = $record -> getField('Worked_Duration');
                        $assigns = array();

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

                    $assignees = explode("\n", $assign);

                    foreach($assignees as $assignee){
                        switch($assignee){
                            case '230':
                                $assign = 'AB';
                            break;
                            case '246':
                                $assign = 'ADM';
                            break;
                            case '264':
                                $assign = 'BB';
                            break;
                            case '270':
                                $assign = 'CM';
                            break;
                            case '':
                                $assign = 'DB';
                            break;
                            case '':
                                $assign = 'DS';
                            break;
                            case '':
                                $assign = 'FB';
                            break;
                            case '':
                                $assign = 'HM';
                            break;
                            case '':
                                $assign = 'JJ';
                            break;
                            case '':
                                $assign = 'JR';
                            break;
                            case '':
                                $assign = 'KK';
                            break;
                            case '':
                                $assign = 'KR';
                            break;
                            case '':
                                $assign = 'KS';
                            break;
                            case '':
                                $assign = 'LP';
                            break;
                            case '':
                                $assign = 'LS';
                            break;
                            case '':
                                $assign = 'MN';
                            break;
                            case '':
                                $assign = 'MS';
                            break;
                            case '':
                                $assign = 'SA';
                            break;
                            case '':
                                $assign = 'SP';
                            break;
                            case '':
                                $assign = 'VA';
                            break;
                        }
                        array_push($assigns, $assign);
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
                        'assign' => $assigns,
                        'rqstProj' => $rqstPrj,
                        'statusid' => $statusId,
                    ];
                    array_push($data, $temp);
                    }
                echo json_encode($data);
            break;
            
            case 'task';
                $data =array();
                $rqstid = $_GET["rqstid"];
                $findCommand = $fm->newFindCommand('PHP__RQST');
                $findCommand->addFindCriterion('__kp__RqstId__lsan','=='.$rqstid);
                $result = $findCommand->execute();
                $records = $result->getRecords();
                foreach($records as $record){
                    $status = $record -> getField('IsActive');
                    $relatedSet = $record->getRelatedSet('rqst__TSK');
                    foreach ($relatedSet as $relatedRow){
                        $taskid = $relatedRow->getField('rqst__TSK::__kp__TskId__lsan');
                        $daterecvd = $relatedRow->getField('rqst__TSK::Date_Recevied');
                        $datedue = $relatedRow->getField('rqst__TSK::Date_Due');
                        $desc = $relatedRow->getField('rqst__TSK::description');
                        $assign = $relatedRow -> getField('rqst__TSK::_kf__UsrId__lsxn__AssginedTo');
                        $ex_duration = $relatedRow->getField('rqst__TSK::Duration_expected');
                        $worked_duration = $relatedRow->getField('rqst__TSK::Duration_worked');
                        $statusId = $relatedRow->getField('rqst__TSK::_kf_Tsk_StatusId__lsxn');
                        
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
                            'id' => "$taskid",
                            'dueDate' => "$ex_duration",
                            'dueStart' => "$daterecvd",
                            'dueEnd' => "$datedue",
                            'status' => [
                                'msg' => $status,
                                'color' => $color,
                            ],
                            'assign' => $assign,
                            'task' => "$desc",
                            'duration' => "$worked_duration",
                            'statusId' => "$statusId"
                        ];
                        array_push($data,$temp);
                    }
                }
                echo json_encode($data);
            break;
            case 'screen': 
                $reqId = $_GET["reqId"];
                $screenShots = array();
                $findCommand = $fm->newFindCommand('PHP__SCREENSHOT');
                $findCommand->addFindCriterion('_kf__RqstId__lsxn','=='.$reqId);
                $result = $findCommand->execute();
                $records = $result->getRecords();
                foreach($records as $record){
                    $temp = [
                        'id' => $record -> getField("__kp__IdleScreenShotId__lsan")
                    ];
                    array_push($screenShots, $temp);
                }
                echo json_encode($screenShots);
            break;
            case 'work':
                $data = array();
                $taskid = $_GET["taskid"];
                $findCommand = $fm->newFindCommand('PHP__WRK');
                $findCommand->addFindCriterion('_kf__TskId__lsxn','=='.$taskid);
                $result = $findCommand->execute();
                $records = $result->getRecords();
                $count = count($records) > 5 ? 5 : count($records);
                for ($i=0; $i < $count; $i++) {
                    $temp = [
                        "Date" => $records[$i] -> getField("Dte"),
                        "Duration" => $records[$i] -> getField("Duration"),
                        "Description" => $records[$i] -> getField("Libellé travail")
                    ];
                    array_push($data, $temp);
                }
                echo json_encode($data);
            break;
            case 'taskscreen':
                $data = array();
                $taskId = $_GET["taskId"];
                $findCommand = $fm->newFindCommand('PHP__SCREENSHOT');
                $findCommand->addFindCriterion('_kf__TskId__lsxn','=='.$taskId);
                $result = $findCommand->execute();
                $records = $result->getRecords();
                foreach($records as $record){
                    $id = $record -> getField("__kp__IdleScreenShotId__lsan");
                    $temp = [
                        'id' => $id
                    ];
                    array_push($data, $temp);
                }
                echo json_encode($data);
            break;
        }
    }
    
    ?>
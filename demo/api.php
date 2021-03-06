<?php

include "sample.php";

header("Access-Control-Allow-Origin:*");

$json = array();


$method = $_SERVER["REQUEST_METHOD"];

function getUserName($userId){
    global $fm;
    $findCommand = $fm->newFindCommand('PHP__USR');
    $findCommand->addFindCriterion('__kp__UserId__lsan','=='.$userId);
    try{
        $result = $findCommand->execute();
        $records = $result->getRecords();
        $user = $records[0] -> getField('User_name');
        return $user;
    }catch(Exception $ex){
    }
}

function formatDate($dateString){
    $dateTimeObj = date_create($dateString);
    return date_format($dateTimeObj, 'd/m/Y');
}

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
                
                $findCommand = $fm->newFindCommand('PHP__CON');
                
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
                $count = count($records) > 5 ? 5 : count($records);
                for ($i=0; $i < $count; $i++){
                    $rqstId = $records[$i] -> getField('__kp__RqstId__lsan');
                    $rqstPrj = $records[$i] -> getField('IsActive');
                    $recieved = $records[$i] -> getField('Date_recieved');
                    $target = $records[$i] -> getField('Date_target');
                    $name = $records[$i] -> getField('Name');
                    $des = $records[$i] -> getField('Description');
                    $statusId = $records[$i] -> getField('IsActive');
                    $assign = $records[$i] -> getField('_kf__UserId__recievedby__lsxn');
                    $statusid = $records[$i] -> getField('_kf_Tsk_StatusId__lsxn');
                    $duration = $records[$i] -> getField('Worked_Duration');
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
                        $userName = getUserName($assignee);
                        if(!in_array($userName, $assigns)){
                            array_push($assigns, $userName);
                        }
                    }

                    $temp = [
                        'id' => $rqstId,
                        'task' => $name,
                        'status' => [
                            'msg' => $status,
                            'color' => $color,
                        ],
                        'dateRecieved' => formatDate($recieved),
                        'dueEnd' => formatDate($target),
                        'dueDate' => formatDate($target),
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
                    $assign = $record -> getField('_kf__UserId__recievedby__lsxn');
                    $relatedSet = $record->getRelatedSet('rqst__TSK');
                    foreach ($relatedSet as $relatedRow){
                        $taskid = $relatedRow->getField('rqst__TSK::__kp__TskId__lsan');
                        $daterecvd = $relatedRow->getField('rqst__TSK::Date_Recevied');
                        $datedue = $relatedRow->getField('rqst__TSK::Date_Due');
                        $desc = $relatedRow->getField('rqst__TSK::description');
                        // $assign = $relatedRow -> getField('rqst__TSK::_kf__UsrId__lsxn__AssginedTo');
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

                        $assignees = explode("\n", $assign);
                        $userNames = array();
                        foreach($assignees as $assignee){
                            $userName = getUserName($assignee);
                            if(!in_array($userName, $userNames)){
                                array_push($userNames, $userName);
                            }
                        }

                        $temp = [
                            'id' => "$taskid",
                            'dueDate' => formatDate("$ex_duration"),
                            'dueStart' => formatDate("$daterecvd"),
                            'dueEnd' => formatDate("$datedue"),
                            'status' => [
                                'msg' => $status,
                                'color' => $color,
                            ],
                            'assign' => $userNames,
                            'task' => "$desc",
                            'duration' => "$worked_duration",
                            'statusId' => "$statusId"
                        ];
                        array_push($data,$temp);
                    }
                }
                echo json_encode($data);
            break;
            case 'work':
                $data = array();
                $users = array();
                $taskid = $_GET["taskid"];
                $findCommand = $fm->newFindCommand('PHP__WRK');
                $findCommand->addFindCriterion('_kf__TskId__lsxn','=='.$taskid);
                $result = $findCommand->execute();
                $records = $result->getRecords();
                foreach($records as $record){
                    $userId = $record -> getField('_kf__UserId__lsxn');
                    if(!in_array($userId, $users)){
                        array_push($users, $userId);
                    }
                }
                
                foreach($users as $user){
                    $findCommand->addFindCriterion('_kf__TskId__lsxn','=='.$taskid);
                    $findCommand->addFindCriterion('_kf__UserId__lsxn','=='.$user);
                    $workResult = $findCommand->execute();
                    $wordRecords = $workResult->getRecords();
                    $workData = array();
                    foreach($wordRecords as $work){
                        $projName = $work -> getField('Proj_name');
                        $duration = $work -> getField('Duration');
                        $date = $work -> getField('Dte');
                        $description = $work -> getField('Libell?? travail');
                        $start = $work -> getField('Time_start_time_calc');
                        $end = $work -> getField('Time_end_time_calc');

                        $temp = [
                            'ProjectName' => $projName,
                            'Duration' => $duration,
                            'Date' => formatDate($date),
                            'startTime' => $start,
                            'endTime' => $end,
                            'Description' => $description
                        ];
                        array_push($workData,$temp);
                    }
                    $works = [
                        'userName' => getUserName($user),
                        'totalHours' => $wordRecords[0] -> getField('zz__STATS__Duration'),
                        'works' => $workData
                    ];
                    array_push($data, $works);
                }
                echo json_encode($data);
            break;
            case 'screen': 
                $data = array();
                $reqId = $_GET["reqId"];
                $findCommand = $fm->newFindCommand('PHP__RANDOM_SCREENSHOT');
                $findCommand->addFindCriterion('_kf__RqstId__lsxn','=='.$reqId);
                $result = $findCommand->execute();
                $records = $result->getRecords();
                foreach($records as $record){
                    $id = $record -> getField('__kp_RandomScreenshotId_lsan');
                    $temp = [
                        'id' => $id
                    ];
                    array_push($data, $temp);
                }
                echo json_encode($data);
            break;
            case 'taskscreen':
                $data = array();
                $taskId = $_GET["taskId"];
                $findCommand = $fm->newFindCommand('PHP__RANDOM_SCREENSHOT');
                $findCommand->addFindCriterion('_kf__TskId__lsan','=='.$taskId);
                $result = $findCommand->execute();
                $records = $result->getRecords();
                foreach($records as $record){
                    $id = $record -> getField('__kp_RandomScreenshotId_lsan');
                    $temp = [
                        'id' => $id
                    ];
                    array_push($data, $temp);
                }
                echo json_encode($data);
            break;
            case 'user':
                $data =array();
                $userId = $_GET["userId"];
                    $temp = [
                        'UserName' => getUserName($userId)
                    ];
                    echo json_encode($temp);
            break;
            }
        }
    ?>
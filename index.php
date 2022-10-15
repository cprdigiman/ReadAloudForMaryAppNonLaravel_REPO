<?php
session_start();
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../inc/config.php';

function validateUser($requestdata,&$that){
    $user['user'] = filter_var($requestdata['user'], FILTER_SANITIZE_STRING);
    $user['pass'] = filter_var($requestdata['pass'], FILTER_SANITIZE_STRING);
    $response_data='';
    $result=$that->db->query('select * from users where username="'.$user['user'].'" and password="'.$user['pass'].'"');
    while ($obj = $result->fetch_object()) {
        $dbdata=$obj;
    }
    if(count($dbdata)<1){
        return false;
        exit;
    } else {
        return $dbdata->{'id'};
    }
}

function sanitize($array) {
    if(count($array)<1) return array();
    foreach($array as $k=>$v) {
        $return[$k]=filter_var($v, FILTER_SANITIZE_STRING);
    }
    return $return;
}

$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $mysqli = new mysqli($db['host'], $db['user'], $db['pass'], $db['dbname']);
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        exit;
    } 
    return $mysqli;
};

$logincheck = function ($request, $response, $next) {
    if(!isset($_SESSION['user']) || $_SESSION['user']=='') {
        $data=(object) array('message' => 'Login Required');
        $json = $response->withJson($data,401);
        return $json;
        exit;
    }
    $response = $next($request, $response);
    return $response;
};

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

/*$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});
*/



/* Marctest */
$app->get('/marctest',function(Request $request, Response $response) {

    $sql='SELECT s.id, s.fname, s.lname, c.name as classname, c.grade
            FROM students s, classes c
            WHERE c.id=s.class_id
            AND s.school_id=10';

    $result=$this->db->query($sql);

    while($row=$result->fetch_assoc()) {

        $grade=$row['grade'];
        if($grade==0) $grade='K';

        $class=strtoupper(substr($row['classname'], 0, 4));

        $fname=strtoupper(substr($row['fname'], 0, 3));
        $lname=strtoupper(substr($row['lname'], 0, 1));
        $sid=$row['id'];
        
        $readathonid=$grade.'-'.$class.'-'.$fname.$lname;
        $sql2='update students set readathon_number="'.$readathonid.'" where id='.$sid.' limit 1';
        $this->db->query($sql2);

    }
    echo 'done';
    /*$sql='select * from students where school_id=10 order by readathon_number';
    $result=$this->db->query($sql);

    while($row=$result->fetch_assoc()) {
        echo $row['readathon_number'].'<br>';
    }*/

});


/* Donations by ID */

$app->post('/donationbyid',function(Request $request, Response $response) {

    $data = sanitize($request->getParsedBody());

    $sql='select id from students where readathon_number="'.strtoupper($data['readathonid']).'" limit 1';
    $result=$this->db->query($sql);
    if($result->num_rows > 0) {
        $row=$result->fetch_assoc();

        $sql='insert into donations (student_id,donation_amount,donor) values ('.$row['id'].','.$data['amount'].',"'.$data['donor'].'")';
        $result=$this->db->query($sql);

        $points=$data['amount'] * 10;
        $sql='update students set donation_points=donation_points+'.$points.' where id='.$row['id'].' limit 1';
        $result=$this->db->query($sql);



    } else {
        $json = $response->withJson(array('message'=>'unknown'),200);
        return $json;
        exit;
    }
    $json = $response->withJson(array('message'=>'success'),200);
    return $json;

});


/* Donations by student */
$app->post('/student/donations',function(Request $request, Response $response) {

    $data = sanitize($request->getParsedBody());

    $sql='select * from donations where student_id='.$data['studentid'];
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','items'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});



/* Get Reading Log */
$app->post('/readinglog',function(Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());

    $sid=$data['studentid'];

    $sql='select * from readingsessions where student_id='.$sid;
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','items'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;

});

/* School Stats */
$app->post('/schoolstats',function(Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());

    $sid=$data['school_id'];


    $sql='select * from classes where school_id='.$sid;
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
        $json = $response->withJson($response_data,$code);
        return $json;
        exit;
    }
    $returnblob=array();
    while($row = $result->fetch_assoc()) {
        $temp=$row;
        $temp['students']=array();
        $temp['donations']=0;
        $temp['reading_hours']=0;
        $cid=$row['id'];
        $sql2='select * from students where class_id='.$cid;
        $result2=$this->db->query($sql2);
        if($this->db->error){
            $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
            $code=500;
            $json = $response->withJson($response_data,$code);
            return $json;
            exit;
        }
        
        while($row2 = $result2->fetch_assoc()) {
            array_push($temp['students'],$row2);
            $temp['class_donation_points']+=$row2['donation_points'];
            $temp['class_reading_points']+=$row2['home_points'];
        }
        array_push($returnblob,$temp);
    }
    


    $json = $response->withJson($returnblob,200);
    return $json;

});


/* Teacher/PTA delete reading log items */
$app->post('/deletereadingitems',function(Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());

    $todelete=explode(',',$data['todelete']);


    foreach($todelete as $item) {
        $sql='select * from readingsessions where id='.$item;
        $result=$this->db->query($sql);
        $row=$result->fetch_assoc();
        $sid=$row['student_id'];
        $time=$row['time'] /6;

        $sql2='update students set home_points=home_points-'.$time.' where id='.$sid;
        $this->db->query($sql2);
    }
    $sql3='delete from readingsessions where id in('.$data['todelete'].')';
    $this->db->query($sql3);


    $json = $response->withJson(array('message'=>'success'),200);
    return $json;

});

/* Classroom Reading */

$app->post('/classreading',function(Request $request, Response $response) {

    $data = sanitize($request->getParsedBody());

    $students=explode(',',$data['students']);

    foreach($students as $sid) {
        $sql='insert into readingsessions (student_id,description,time,session_datetime) values ('.$sid.', "Classroom Reading","'.$data['time'].'", NOW())';
        $result=$this->db->query($sql);
    }
    $points=$data['time']/6;
    $sql='update students set home_points=home_points+'.$points.' where id in('.$data['students'].')';
    $result=$this->db->query($sql);
    $json = $response->withJson(array("message"=>"success"),200);
    return $json;
    
});
    


/* Login */
$app->post('/login',function (Request $request, Response $response) {

    $data = sanitize($request->getParsedBody());
    $x='school';
    // user now validated
    $sql="select id, name, status, prize_store from schools where login='".$data['login']."' and password='".$data['password']."'";


    $result=$this->db->query($sql);

    

    if($result->num_rows <1) {
        $x='class';
        // not a school login - check for teacher login instead
        $sql="select c.id as id, c.school_id as school_id, c.name as name, c.grade as grade, s.name as school_name, s.status, s.prize_store as prize_store from classes c, schools s where teacheremail='".$data['login']."' and teacherpass='".$data['password']."' and s.id=c.school_id";


        $result=$this->db->query($sql);

        if($result->num_rows <1) {
            //not a teacher login either - check for student
            $x='student';
            $sql="select id, name, status, donation_link, prize_store from schools where student_login='".$data['login']."' and student_password='".$data['password']."'";

            $result=$this->db->query($sql);

            if($result->num_rows <1) {
                // login info didn't match
                $response_data=(object) array('message' => 'Username or password is incorrect.');
                $code=403;
                $json = $response->withJson($response_data,$code);
                return $json;
                exit;
            }
        }      
    }

    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        switch($x) {
            case 'class':
                $response_data=(object) array('message' => 'success','class'=>$dbdata);
            break;

            case 'school':
                $response_data=(object) array('message' => 'success','school'=>$dbdata);
            break;
                
            default:
                $response_data=(object) array('message' => 'success','studentschool'=>$dbdata);
        }
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

/* Items */
$app->post('/items',function (Request $request, Response $response) {

    $data = sanitize($request->getParsedBody());
    
    // user now validated
    $sql="select id,name,description, points, avail from items order by points asc";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','items'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});
$app->post('/items/add',function (Request $request, Response $response) {

    $data = sanitize($request->getParsedBody());
    
    // user now validated

    $sql="insert into items (name,description,points,avail,sku) values ('".$data['new-name']."','".$data['new-description']."','".$data['new-points']."','".$data['new-avail']."','".$data['new-sku']."')";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        //get the insert id, and move the file to the correct location
        $oldname=$_FILES["new-image"]['tmp_name'];
        $newname='/home/secretsacks/public_html/img/items/'.$this->db->insert_id.'.jpg';
        if(move_uploaded_file($oldname,$newname)){
            $response_data=(object) array('message' => 'success', 'id'=>$this->db->insert_id, 'name'=>$data['new-name'], 'description'=>$data['new-description'],'points'=>$data['new-points'],'avail'=>$data['new-avail']);
            $code=200;
        } else {
            $response_data=(object) array('message'=>'Unable to save image to the server.','tmp_name'=>$oldname,'destination'=>$newname);
            $code=500;
        } 
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/items/delete',function (Request $request, Response $response) {

    $data = sanitize($request->getParsedBody());
    
    // user now validated

    $sql="delete from items where id=".$data['itemid']." limit 1";
    
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message'=>'success');
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/getitem',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());

    $sql="select * from items where id=".$data['itemid']." limit 1";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','item'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;


});

$app->post('/updateitem',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());

    $sql="update items set sku='".$data['itemsku']."', name='".$data['itemname']."',description='".$data['itemdescription']."', points='".$data['itempoints']."', avail='".$data['itemavail']."' where id=".$data['itemid']." limit 1";



    $result=$this->db->query($sql);

    $sql="update orderitems set item_sku='".$data['itemsku']."' where item_id=".$data['itemid'];
    $result=$this->db->query($sql);

    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {

        if($_FILES['itemimage']['size']>0){
            $oldname=$_FILES["itemimage"]['tmp_name'];
            $newname='/home/secretsacks/public_html/img/items/'.$data['itemid'].'.jpg';
            if(move_uploaded_file($oldname,$newname)){
                $response_data=(object) array('message' => 'success','id'=>$data['itemid'],'name'=>$data['itemname'],'description'=>$data['itemdescription'],'points'=>$data['itempoints'],'avail'=>$data['itemavail'],'tmp_name'=>$oldname,'destination'=>$newname);
                $code=200;
            } else {
                $response_data=(object) array('message'=>'Unable to save image to the server.','tmp_name'=>$oldname,'destination'=>$newname);
                $code=500;
            } 
        } else {
            $response_data=(object) array('message'=>'success');
        }
    }
    $json = $response->withJson($response_data,$code);
    return $json;


});

/* Schools */
$app->post('/schools',function (Request $request, Response $response) {

    $data = sanitize($request->getParsedBody());
    
    // user now validated
    $sql="SELECT s.id,s.name,s.year,s.status, s.login, s.password, s.student_login, s.student_password, s.status, count(c.id) as classes 
            FROM `schools` s
            LEFT JOIN classes c
            ON s.id = c.school_id
            group by s.id";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error,'sql'=>$sql);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','schools'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/school',function (Request $request, Response $response) {

    $data = sanitize($request->getParsedBody());
    
    // user now validated
    $sql="select * from schools where id=".$data['id'];
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','schools'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/schools/add',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    // user now validated
    $sql="insert into schools (name, year, login, password, student_login, student_password) values ('".$data['name']."','".$data['year']."','".$data['ptalogin']."','".$data['ptapassword']."','".$data['studentlogin']."','".$data['studentpassword']."')";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error,'sql'=>$sql);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success', 'school_id'=>$this->db->insert_id, 'name'=>$data['name']);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/schools/edit',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    // user now validated
    $sql="update schools set name='".$data['name']."', year='".$data['year']."', login='".$data['ptalogin']."', password='".$data['ptapassword']."', student_login='".$data['studentlogin']."', student_password='".$data['studentpassword']."' where id=".$data['sid'].' limit 1';
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error,'sql'=>$sql);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success', 'school_id'=>$data['sid'], 'name'=>$data['name']);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/schools/editwithstatus',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    // user now validated
    $sql="update schools set name='".$data['name']."', year='".$data['year']."', login='".$data['ptalogin']."', password='".$data['ptapassword']."', student_login='".$data['studentlogin']."', student_password='".$data['studentpassword']."', status='".$data['status']."' where id=".$data['sid'].' limit 1';
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error,'sql'=>$sql);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success', 'school_id'=>$data['sid'], 'name'=>$data['name']);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

/* Classes */
$app->post('/classes',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    $sql='select id,name,year,login,password,student_login,student_password, status from schools where id='.$data['school_id'];
    $result=$this->db->query($sql);
    $obj = $result->fetch_object();
    $schooldata=(array) $obj;
    
    // user now validated
    //$sql="select id,name,grade,teacheremail from classes where school_id=".$data['school_id'].' order by grade asc';
    $sql='SELECT c.id as id, c.name as name, c.grade as grade, count(s.id) as students from classes c
            LEFT JOIN students s
            ON s.class_id = c.id
            WHERE c.school_id='.$data['school_id'].'
            group by c.id
            order by c.grade asc';

    /*$response_data=(object) array('sql' => $sql);
    $json = $response->withJson($response_data,$code);
    return $json;
    exit;*/
    $result=$this->db->query($sql);   
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error, 'sql'=>$sql);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','school_id'=>$schooldata['id'], 'school_name'=>$schooldata['name'],'year'=>$schooldata['year'],'ptalogin'=>$schooldata['login'], 'ptapassword'=>$schooldata['password'], 'studentlogin'=>$schooldata['student_login'], 'status'=>$schooldata['status'],'studentpassword'=>$schooldata['student_password'],'classes'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/classpoints',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());
    
    // user now validated
    $sql='SELECT sum(school_points) as school_total, sum(home_points) as home_total, sum(donation_points) as donations_total from students where class_id='.$data['class_id'];


    /*$response_data=(object) array('sql' => $sql);
    $json = $response->withJson($response_data,$code);
    return $json;
    exit;*/
    $result=$this->db->query($sql);   
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
       $obj = $result->fetch_object();
        $response_data=(object) array('message' => 'success','points'=>$obj);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/classes/add',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    // user now validated
    $sql="insert into classes (school_id,name,grade,teacheremail,teacherpass) values ('".$data['school_id']."','".$data['name']."','".$data['grade']."','".$data['teacheremail']."','".$data['teacherpass']."')";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success','school_id'=>$data['school_id'],'class_id'=>$this->db->insert_id,'grade'=>$data['grade'],'name'=>$data['name']);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/classes/delete',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    // user now validated
    $sql="delete from classes where id=".$data['class_id']." limit 1";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success');
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

/* Single Student (logged in) */
$app->post('/student',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());
    
    // user now validated
    $sql="select id,fname, LEFT(lname,1) as lname, icon, home_points, school_points, donation_points, order_complete from students where id=".$data['studentid']." limit 1";

    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','student'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/student/verify',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());

    $sql="update students set verified=".$data['verified']." where id=".$data['studentid'];

    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success');
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/student/avatar',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());

    $sql="update students set icon=".$data['icon']." where id=".$data['studentid']." limit 1";


    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success');
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/student/password',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());

    $sql="update students set passicon=".$data['passicon']." where id=".$data['studentid']." limit 1";

    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success');
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/student/donation',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());


    if(trim($data['donor'])=='') {
        $data['donor']=='anonymous';
    }

    $sql='insert into donations (student_id,donation_amount,donor) values ('.$data['studentid'].','.$data['amount'].',"'.$data['donor'].'")';
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error while adding donation record: '.$this->db->error);
        $code=500;
        $json = $response->withJson($response_data,$code);
        return $json;
        exit;
    } 

    $points=$data['amount'] * 10;
    $sql="update students set donation_points=donation_points + ".$points." where id=".$data['studentid']." limit 1";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error while updating student record:  '.$this->db->error);
        $code=500;
        $json = $response->withJson($response_data,$code);
        return $json;
        exit;
    } 

    $sql='SELECT sum(donation_points) as class_donation_points FROM `students` WHERE class_id='.$data['class_id'];
    $result=$this->db->query($sql);
    $row=$result->fetch_assoc();
    $classpoints=$row['class_donation_points'];

    $sql="select home_points,school_points,donation_points from students where id=".$data['studentid'];
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $row=$result->fetch_assoc();

        $total=$row['donation_points']+$row['home_points']+$row['school_points'];

        $response_data=(object) array('message' => 'success', 'donation_points'=>$row['donation_points'], 'total_points'=>$total, 'class_donation_points'=>$classpoints);
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});


$app->post('/student/sessions',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());

    $sql="select DATE_FORMAT(session_datetime, '%m/%d/%Y %l:%i%p') as datetime, description, time from readingsessions where student_id='".$data['studentid']."' order by session_datetime asc";

    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','sessions'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;

});

$app->post('/student/savesession',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());

    //$json = $response->withJson(array('message'=>'The read-a-thon has ended'),403);
    //return $json;
    //exit;

    $readpoints="2.5";
    switch($data['length']) {
        case "30":
            $readpoints="5";
        break;

        case "45":
            $readpoints="7.5";
        break;

        case "60":
            $readpoints="10";
        break;
    }

    $sql="update students set home_points=home_points+".$readpoints." where id=".$data['studentid']." limit 1";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
        $json = $response->withJson($response_data,$code);
        return $json;
        exit;
    }
    $dt=$data['date'].' '.$data['time'];
    $sql="insert into readingsessions (student_id,description,time,session_datetime) values (".$data['studentid'].",'".$data['desc']."','".$data['length']."','".$dt."')";

    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success');
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;

});


/* Students */
$app->post('/students',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());
    
    // user now validated
    $sql="select id,fname, LEFT(lname,1) as lname, icon, passicon as vid, birthday, home_points, school_points, donation_points,verified from students where class_id=".$data['class_id']." order by fname";

    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','students'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});




$app->post('/students/add',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    $name=explode(' ',$data['name']);

    $newname=$name[0].' '.substr($name[1],1);


    // user now validated
    $sql="insert into students (fname,lname,icon,passicon,school_id,class_id,birthday) values ('".$name[0]."','".substr($name[1],0,1)."','".$data['icon']."','".$data['passicon']."','".$data['school_id']."','".$data['class_id']."','".$data['birthday']."')";

    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success','student_id'=>$this->db->insert_id,'fname'=>$name[0],'lname'=>substr($name[1],0,1),'icon'=>$data['icon']);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/students/delete',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    // user now validated
    $sql='delete from students where id='.$data['studentid'].' limit 1';
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success');
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/students/edit',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    // user now validated
    $sql="update students set fname='".$data['fname']."', lname='".$data['lname']."', icon='".$data['icon']."', passicon='".$data['passicon']."' where id='".$data['studentid']."' limit 1";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success');
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});
$app->post('/students/updatepoints',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    // user now validated
    $sql="update students set school_points='".$data['schoolpoints']."', home_points='".$data['homepoints']."', donation_points='".$data['donationpoints']."' where id='".$data['student_id']."' limit 1";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success', 'school_points'=>$data['schoolpoints'], 'home_points'=>$data['homepoints'], 'donation_points'=>$data['donationpoints']);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

/* Favorites */
$app->post('/favorites',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());
    
    // user now validated
    $sql="select f.id as id,f.item_id as item_id, f.qty as qty, i.name as name, i.description as description, i.points as points, i.image as image, i.avail as avail from favorites f, items i where f.item_id=i.id and f.student_id=".$data['studentid'];
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','student_id'=>$data['studentid'],'favorites'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});
$app->post('/favorites/add',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    $sql="select * from favorites where student_id=".$data['studentid']." and item_id=".$data['itemid'];
    $result=$this->db->query($sql);
    if($result->num_rows<1){
        $sql="insert into favorites (student_id,item_id) values ('".$data['studentid']."','".$data['itemid']."')";
        $result=$this->db->query($sql);
    }

    // user now validated
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success');
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});
$app->post('/favorites/delete',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    // user now validated
    $sql='delete from favorites where student_id='.$data['studentid'].' and item_id='.$data['itemid'];
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success');
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

/* Cart Items */
$app->post('/cartitems',function (Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());
    
    // user now validated
    $sql="select c.id as id,c.item_id as item_id, c.qty as qty, i.name as name, i.description as description, i.points as points, i.image as image from cartitems c, items i where c.item_id=i.id and c.student_id=".$data['studentid'];
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        while ($obj = $result->fetch_object()) {
            $dbdata[]=$obj;
        }
        $response_data=(object) array('message' => 'success','student_id'=>$data['studentid'],'cartitems'=>$dbdata);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});
$app->post('/cartitems/add',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());
    $qty=0;
    //first check to see if its already in that student's cart
    $sql="select qty from cartitems where student_id=".$data['studentid']." and item_id=".$data['itemid'];

    $result=$this->db->query($sql);
    $obj=$result->fetch_object();
    $qty=$obj->qty;
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    }
    if($result->num_rows>0) {
        $sql="update cartitems set qty=qty+1 where student_id=".$data['studentid']." and item_id=".$data['itemid'];
        $result=$this->db->query($sql);
        if($this->db->error){
            $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
            $code=500;
        }

    } else {
        $sql="insert into cartitems (student_id,item_id,qty) values ('".$data['studentid']."','".$data['itemid']."',1)";
        $result=$this->db->query($sql);
        if($this->db->error){
            $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
            $code=500;
        }
    }
    $qty=$qty+1;
     

    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success','qty'=>$qty);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/cartitems/setqty', function(Request $request, Response $response) {
    $data = sanitize($request->getParsedBody());

    $sql='update cartitems set qty='.$data['qty'].' where id='.$data['cartitemid'];

    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success','qty'=>$data['qty'], 'cartitemid'=>$data['cartitemid']);
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/cartitems/delete',function (Request $request, Response $response) {
    // add lead
    $data = sanitize($request->getParsedBody());

    // user now validated
    $sql="delete from cartitems where id=".$data['cartitemid'].' and student_id='.$data['studentid'].' limit 1';

    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $code=500;
    } else {
        $response_data=(object) array('message' => 'success');
        $code=200;
    }
    $json = $response->withJson($response_data,$code);
    return $json;
});

$app->post('/orders/add',function (Request $request, Response $response) {

    $data=$request->getParsedBody();

    $outofstock=[];
    foreach($data['orderitems'] as $val) {
        $sql="select avail from items where id=".$val['itemid'];
        $result=$this->db->query($sql);
        $obj = $result->fetch_object();
        if($obj->avail < $val['qty']) {
            $temp=[];
            $temp['id']=$val['itemid'];
            $temp['name']=$val['name'];
            $outofstock[]=$temp;
        }
    }


    if(count($outofstock)>0) {
        $response_data=(object) array('outofstock' => $outofstock);
        $code=500;
        $json = $response->withJson($response_data,$code);
        return $json;
        exit;
    }


    $studentid=-1;
    $totalpoints=0;
    foreach($data['orderitems'] as $val) {
        $v=sanitize($val);
        $studentid=$v['studentid'];
        $sql="insert into orderitems (school_id,school_name,class_id,class_name,student_id,student_name,item_id,item_name,item_points,qty,subtotal_points) values (
        '".$v['schoolid']."',
        '".$v['schoolname']."',
        '".$v['classid']."',
        '".$v['classname']."',
        '".$v['studentid']."',
        '".$v['studentname']."',
        '".$v['itemid']."',
        '".$v['itemname']."',
        '".$v['itempoints']."',
        '".$v['qty']."',
        '".$v['subtotal']."'
        )
        ";
        $totalpoints+=$v['subtotal']; 
        $result=$this->db->query($sql);
        $sql2="update items set avail=avail-".$val['qty']." where id=".$val['itemid']." limit 1";
        $result=$this->db->query($sql2);
        if($this->db->error){
            $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
            $code=500;
        } else {
            $response_data=(object) array('message' => 'success');
            $code=200;
        }
    }
    if($code==200) {
        $sql="SELECT school_points + donation_points + home_points as total from students where id=".$studentid;

        $result=$this->db->query($sql);
        $obj = $result->fetch_object();
        $mypoints=$obj->total;

        $donate=$mypoints-$totalpoints;


        $sql="update students set home_points=0, school_points=0, donation_points=0, order_complete=1, prize_box=".$donate." where id=".$studentid." limit 1";
        $result=$this->db->query($sql);
        if($this->db->error){
            $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
            $code=500;
        } else {
            $response_data=(object) array('message' => 'success');
            $code=200;
        }
    }
    $json = $response->withJson($response_data,$code);
    return $json;

});

$app->get('/reports/classlist',function (Request $request, Response $response) {

    $data=$request->getParsedBody();

    $sid=$_GET['sid'];

    $sql='select name from schools where id='.$sid;
    $result=$this->db->query($sql);
    $obj=$result->fetch_object();
    $school=(array) $obj;


    $sql="SELECT * FROM `orderitems` WHERE school_id=".$sid." order by class_name asc, student_name asc";
    $result=$this->db->query($sql);

    $teacher='';
    $student='';
    $dt=new Datetime();
    $dt->setTimezone(new DateTimeZone('America/Los_Angeles'));
    echo '<!DOCTYPE html><html><head><style> 
    @page { size: auto; margin: 0mm; padding:20cm; }
    @media print {
        .newteacher {
            page-break-before:always !important;
            page-break-inside:avoid !important;
            margin-top:100px;
        }
    }
    .summary tr:nth-child(even) {
        background-color:#eaeaea;
    }
    table.summary {
        border:2px solid #eaeaea;

    }
    table.newteacher {
        margin-left:100px;
    }
    table.summary td {
        padding:5px;
        border:1px solid #eaeaea;
    }
    </style></head><body>';
    echo '<h1>Class Summary Report for '.$school['name'].'</h1>';
    echo '<h3>Generated on '.$dt->format('M j, Y').' at '.$dt->format('g:i a').'</h3>';
    $firstitem=true;
    while ($obj = $result->fetch_object()) {
            $orderitem=(array) $obj;

            if($orderitem['class_name'] !== $teacher) {
                // this starts a new class - need to start a new table.

                // but first, output the no orders students 
                if($noorders!='') {
                    $block.='<tr><td colspan="3"><h4>Students With No Order</h4></td></tr>';
                    $block.='<tr><td colspan="3">'.$noorders.'</td></tr>';
                }
                if($summaryblock!='') {
                    $block.='<tr><td colspan="3">'.$summaryblock.'</td></tr>'; 
                }
                
                if(!$firstitem) {
                    // close the previous table unless this is the first one
                    $block.='</table>';
                }
                echo $block;
                $noorders='';
                $block='';
                

                // for this class, need to get total prizebox points:
                $sql2="SELECT class_id, sum(prize_box) as teacherpoints from students where class_id=".$orderitem['class_id'];
                $result2=$this->db->query($sql2);
                $obj2=$result2->fetch_object();
                $prizeboxresult=(array) $obj2;
                $pbpoints=$prizeboxresult['teacherpoints'];
                $classheader='<h3>'.$orderitem['class_name'].' (Prize box points: '.$pbpoints.')</h3>';


                // and get the list of students with no orders
                $sql3="select fname,lname,prize_box from students where order_complete=0 and class_id=".$orderitem['class_id'];
                $result3=$this->db->query($sql3);
                if($result3->num_rows > 0) {
                    while($obj3=$result3->fetch_object()) {
                        $noorderstudent=(array)$obj3;
                        $noorders.=$noorderstudent['fname'].' '.$noorderstudent['lname'].'.<br>';
                    }
                }

                // and get class summary for prizes
                $summarysql="SELECT item_name, sum(qty) as total from orderitems where class_id=".$orderitem['class_id']." group by item_name order by total";
                $summaryresult=$this->db->query($summarysql);
                $summaryblock='<table class="summary" cellpadding="0" cellspacing="0">';
                $summaryblock.='<tr><td colspan="2"><h3>Class Summary</h3></td></tr>';
                while($sObj = $summaryresult->fetch_object()) {
                    $sumrow=(array) $sObj;
                    $summaryblock.='<tr><td>'.$sumrow['total'].'</td><td>'.$sumrow['item_name'].'</td>';
                }
                $summaryblock.='</table>';


                $teacher=$orderitem['class_name'];
                $block.='<table class="newteacher">';
                $block.='<tr  style="border-top:2px solid #ccc;"><td colspan="3" style="padding:30px; text-align:center;"><h3>'.$classheader.'</h3></td></tr>';
            }

            // if this is a new student, set that header
            if($student!=$orderitem['student_name']) {
                $block.='<tr><td colspan="3" style="padding-top:20px; "><h4 style="margin-bottom:0">'.$orderitem['student_name'].'</h4></td></tr>';
                $student=$orderitem['student_name'];
            }
            $block.='<tr><td>'.$orderitem['qty'].'</td><td>'.$orderitem['item_sku'].'</td><td>'.$orderitem['item_name'].'</td></tr>';
            $firstitem=false;

        }
        $block.='<tr><td colspan="3"><h4>Students With No Order</h4></td></tr>';
        $block.='<tr><td colspan="3">'.$noorders.'</td></tr>';
        $block.='<tr><td colspan="3">'.$summaryblock.'</td></tr>'; 
        $block.='</table>';
        echo $block;
        echo '</body></html>';
        exit;






        
    $json = $response->withJson($response_data,$code);
    return $json;

});

$app->get('/reports/schoollist',function (Request $request, Response $response) {

    $data=$request->getParsedBody();

    $sid=$_GET['sid'];

    $sql='select name from schools where id='.$sid;
    $result=$this->db->query($sql);
    $obj=$result->fetch_object();
    $school=(array) $obj;

    $sql="SELECT item_id,item_name,item_points, round(item_points/100,2) as dollar_value, item_sku, sum(qty) as total FROM `orderitems` WHERE school_id=".$sid." group by item_name order by item_points";
    $result=$this->db->query($sql);

    $item='';
    $subqty=0;
    $sku='';
    $pointvalue=0;
    $firstitem=true;
    $grandtotal=0;
    $dt=new Datetime();
    $dt->setTimezone(new DateTimeZone('America/Los_Angeles'));
    echo '<!DOCTYPE html><html><head><style> 
    @page { size: auto; margin: 0mm; padding:20cm; }
    
    tr:nth-child(even) {
        background-color:#eaeaea;
    }
    table {
        margin-left:50px;
    }
    table td {
        padding:5px 20px;
        border-top:1px solid #eaeaea;
        border-bottom:1px solid #eaeaea;
    }
    h1 {
        text-align:center;
        margin-bottom:20px;
    }
    h3 {
        text-align:center;
        margin-bottom:50px;
    }
    </style></head><body>';
    echo '<h1>School Summary Report for '.$school['name'].'</h1>';
    echo '<h3>Generated on '.$dt->format('M j, Y').' at '.$dt->format('g:i a').'</h3>';
    echo '<table cellpadding="0" cellspacing="0">';
    echo '<tr><td>Total Qty</td><td>Prize</td><td>SKU</td><td>Point Value</td><td>Dollar Value</td><td>Total Cost</td></tr>';
    while ($obj1 = $result->fetch_object()) {
            $obj=(array) $obj1;
            $subtotal=$obj['total'] * $obj['dollar_value'];
            $grandtotal=$grandtotal+$subtotal;
            echo '<tr><td style="text-align:right">'.$obj['total'].'</td><td>'.$obj['item_name'].'</td><td>'.$obj['item_sku'].'</td><td style="text-align:right">'.$obj['item_points'].'</td><td style="text-align:right">'.number_format($obj['dollar_value'],2).'</td><td style="text-align:right">'.number_format($subtotal,2).'</td></tr>';
        }


    // now add in donated points
    $sql='SELECT s.class_id, sum(s.prize_box) as teacherpoints,c.name from students s, classes c where s.class_id=c.id and c.school_id='.$sid.' group by c.name';
    $result=$this->db->query($sql);
    while($obj1 = $result->fetch_object()) {
        $obj=(array) $obj1;
        $dollarvalue=$obj['teacherpoints']/100;
        $grandtotal=$grandtotal+$dollarvalue;
        echo '<tr><td style="text-align:right">1</td><td>'.$obj['name'].' donated points</td><td>&nbsp;</td><td style="text-align:right">'.$obj['teacherpoints'].'</td><td style="text-align:right">'.number_format($dollarvalue,2).'</td><td style="text-align:right">'.number_format($dollarvalue,2).'</td></tr>';
    }    
    echo '<tr><td colspan="6" style="padding-top:50px;border-top:2px solid #333;text-align:right;">'.number_format($grandtotal,2).'</td></tr>';
    exit;
    $json = $response->withJson($response_data,$code);
    return $json;

});

$app->get('/reports/csv',function (Request $request, Response $response) {

    $data=$request->getParsedBody();

    $sid=$_GET['sid'];

    $sql='select name from schools where id='.$sid;
    $result=$this->db->query($sql);
    $obj=$result->fetch_object();
    $school=(array) $obj;

    $sql="SELECT * from orderitems where school_id=".$sid;
    $result=$this->db->query($sql);
    $student='';
    $body='';
    $itemcount=0;
    $itemlist="";
    $lines=[];
    while($obj = $result->fetch_object()) {
        $oi=(array) $obj;

        if($oi['student_id'] !== $student) {
            //it's a new student
            $line=[];
            $sql2='select item_sku,qty from orderitems where student_id='.$oi['student_id'];
            $result2=$this->db->query($sql2);
            $line[]=$oi['student_name'];
            $line[]=$oi['class_name'];
            $totalqty=0;
            $itemlist="";
            while($obj2 = $result2->fetch_object()) {
                $ld = (array) $obj2;
                $itemlist.="(".$ld['qty'].")".$ld['item_sku'].", ";
                $totalqty=$totalqty+$ld['qty'];
            }
            $line[]=$totalqty;
            $line[]=substr($itemlist, 0, -2);
            $lines[]=$line;
            $student=$oi['student_id'];
        }


        
    }
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="labels.csv"');
    $fp = fopen('php://output', 'wb');
    foreach ( $lines as $l ) {
        fputcsv($fp, $l);
    }
    fclose($fp);

    exit;
    $json = $response->withJson($response_data,$code);
    return $json;

});

/* LOGIN 
$app->post('/login',function (Request $request, Response $response) {

    //login check
    $data = $request->getParsedBody();
    $login_data=[];
    $login_data['user'] = filter_var($data['user'], FILTER_SANITIZE_STRING);
    $login_data['pass'] = filter_var($data['pass'], FILTER_SANITIZE_STRING);
    $response_data='';
    
    
    
    if($login_data['user']==''){
        $response_data=(object) array('message' => 'Username Required');
        $json = $response->withJson($response_data,401);
        return $json;
        exit;
    }
    if($login_data['pass']==''){
        $response_data=(object) array('message' => 'Password Required');
        $json = $response->withJson($response_data,401);
        return $json;
        exit;
    }
    $result=$this->db->query('select * from users where username="'.$login_data['user'].'" and password="'.$login_data['pass'].'"');

    if($this->db->errno>0){
        $response_data=(object) array('message' => 'Database Error: ' . $this->db->error);
        $json = $response->withJson($response_data,500);
        return $json;
        exit;
    }
    
    while ($obj = $result->fetch_object()) {
        $dbdata=$obj;
    }

    $result->close();
    if(count($dbdata)<1){
        $response_data=(object) array('message' => 'No user exists with that username and password.<br>Please try again.');
        $json = $response->withJson($response_data,401);
        return $json;
        exit;
    }
    $_SESSION['user'] = $data->{'username'};
    $_SESSION['userid'] = $data->{'id'};
    $json = $response->withJson($dbdata,200);
    
    return $json;
    
});
*/
/* LOGOUT 
$app->get('/logout',function (Request $request, Response $response) {
    $_SESSION = array();
    session_destroy();
    $data=(object) array('message' => 'Success');
    $json = $response->withJson($data,200);
    return $json;
});
*/

/* USER UPDATE 
$app->post('/user/update',function (Request $request, Response $response) {
    //login check
    $data = $request->getParsedBody();
    $response_data='';
    foreach($data['data'] as $key=>$val){
        if($key != 'id' && $key != 'profile_picture' && $key != 'account_status' && $key != 'member_since') {
            $dataload.=$key."='".filter_var($val, FILTER_SANITIZE_STRING)."',";
        }
    }
    $result=$this->db->query("update users set ".trim($dataload,",")." where id=".$data['data']['id']);
    $result=$this->db->query('select * from users where id='.$data['data']['id']);
    while ($obj = $result->fetch_object()) {
        $dbdata=$obj;
    }
    $result->close();
    $json = $response->withJson($dbdata,200);
    
    return $json;
});
*/

/* LEADS 
$app->post('/leads/add',function (Request $request, Response $response) {
    // add lead
    $requestdata = $request->getParsedBody();
    $userid=validateUser($requestdata,$this,$response);
    if(!$userid){
        $response_data=(object) array('message' => 'You must be logged in to make this request.');
        $json = $response->withJson($response_data,401);
        return $json;
        exit;
    }
    // user now validated
    $fields=Array();
    $values=Array();
    $fields[]='user_id';
    $values[]=$userid;
    foreach($requestdata['lead'] as $key => $value){
        $fields[]=$key;
        $values[]="'".$value."'";
    }
    $sql="insert into leads (".implode(",",$fields).") values (".implode(",",$values).")";
    $result=$this->db->query($sql);
    $requestdata['lead']['id']=$this->db->insert_id;
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $json = $response->withJson($response_data,500);
        return $json;
        exit;
    }
    $json = $response->withJson((object)$requestdata['lead'],200);
    return $json;
    
    
    
});
*/
/* SHIPMENTS - ADD 
$app->post('/shipments/add',function (Request $request, Response $response) {
    // add lead
    $requestdata = $request->getParsedBody();
    $userid=validateUser($requestdata,$this,$response);
    if(!$userid){
        $response_data=(object) array('message' => 'You must be logged in to make this request.');
        $json = $response->withJson($response_data,401);
        return $json;
        exit;
    }
    // user now validated
    $o=$requestdata['order'];
    $sql="insert into shipments (customer,location_name,latitude,longitude,afe,rc,company_man,carrier,directions,order_date) values 
    ('".$o['customer']."','".$o['location_name']."','".$o['latitude']."','".$o['longitude']."','".$o['AFE']."','".$o['RC']."','".$o['company_man']."','".$o['carrier']."','".$o['directions']."',NOW())";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('sql'=>$sql,'message' => 'MySQL Error: '.$this->db->error);
        $json = $response->withJson($response_data,500);
        return $json;
        exit;
    }
    $shipmentid=$this->db->insert_id;
    $sql="insert into shipment_items (shipment_id,type,size,grade,qty) values ";
    foreach($o['items'] as $i){
        $sql.="(".$shipmentid.",'".$i['type']."','".$i['size']."','".$i['grade']."','".$i['qty']."'),";
    }
    $sql=rtrim($sql,',');
    $sql.=";";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('sql'=>$sql,'message' => 'MySQL Error: '.$this->db->error);
        $json = $response->withJson($response_data,500);
        return $json;
        exit;
    }
    $myreturn=(object) array("id"=>$shipmentid);
    $json = $response->withJson($myreturn,200);
    
    return $json;
});
*/
/* INVENTORY 
$app->post('/inventory',function (Request $request, Response $response) {
    // add lead
    $requestdata = $request->getParsedBody();
    $userid=validateUser($requestdata,$this,$response);
    if(!$userid){
        $response_data=(object) array('message' => 'You must be logged in to make this request.');
        $json = $response->withJson($response_data,401);
        return $json;
        exit;
    }
    // user now validated
    $sql="select * from inventory_locations l
left join inventory_items i on i.location=l.rack";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $json = $response->withJson($response_data,500);
        return $json;
        exit;
    }
    while ($obj = $result->fetch_object()) {
        $dbdata[]=$obj;
    }
    $json = $response->withJson($dbdata,200);
    return $json;
});
*/
/* GET INVENTORY MOVES 
$app->post('/moves',function (Request $request, Response $response) {
    // add lead
    $requestdata = $request->getParsedBody();
    $userid=validateUser($requestdata,$this,$response);
    if(!$userid){
        $response_data=(object) array('message' => 'You must be logged in to make this request.');
        $json = $response->withJson($response_data,401);
        return $json;
        exit;
    }
    // user now validated
    $sql="select * from inventory_moves";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $json = $response->withJson($response_data,500);
        return $json;
        exit;
    }
    while ($obj = $result->fetch_object()) {
        $dbdata[]=$obj;
    }
    $json = $response->withJson($dbdata,200);
    return $json;
});
*/
/* ADD INVENTORY MOVE 
$app->post('/inventory-move',function (Request $request, Response $response) {
    // add lead
    $requestdata = $request->getParsedBody();
    $userid=validateUser($requestdata,$this,$response);
    if(!$userid){
        $response_data=(object) array('message' => 'You must be logged in to make this request.');
        $json = $response->withJson($response_data,401);
        return $json;
        exit;
    }
    // user now validated
    $fields=Array();
    $values=Array();
    foreach($requestdata['move'] as $key => $value){
        $fields[]=$key;
        $values[]="'".$value."'";
    }
    $sql="insert into inventory_moves (".implode(",",$fields).") values (".implode(",",$values).")";
    $result=$this->db->query($sql);
    $requestdata['move']['id']=$this->db->insert_id;
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $json = $response->withJson($response_data,500);
        return $json;
        exit;
    }
    $sql="insert into events (action,subject,subject_id,user_id,created_datetime,comments) values (
                                'Inventory Move',
                                'inventory_move',
                                ".$requestdata['move']['id'].",
                                ".$userid.",
                                NOW(),
                                '".$requestdata['move']['notes']."'
                                )";
    $result=$this->db->query($sql);
    $eventid=$this->db->insert_id;
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $json = $response->withJson($response_data,500);
        return $json;
        exit;
    }
    $sql="insert into event_actions (event_id,user_id,action,action_datetime) values (
                                ".$eventid.",
                                ".$userid.",
                                'Move Order Created',
                                NOW()
                                )";
    $result=$this->db->query($sql);
    if($this->db->error){
        $response_data=(object) array('message' => 'MySQL Error: '.$this->db->error);
        $json = $response->withJson($response_data,500);
        return $json;
        exit;
    }
    $json = $response->withJson((object)$requestdata['move'],200);
    return $json;
});
*/

/* EXAMPLE */
$app->get('/hello/{name}', function ($request, $response, $args) {
    return $response->write("Hello " . $args['name']);
}); 

$app->run();

<?php

session_name('membersArea');
@session_start();

#error_reporting(-1);
include("config.php");

$error = '';
if(isset($_POST['is_login'])){
	$sql = "SELECT * FROM v_users_login WHERE cleanPhoneNumber( mobile_phone) = cleanPhoneNumber('".mysql_real_escape_string($_POST['mobileNumber'])."') AND `id_number` = '".mysql_real_escape_string($_POST['idnumber'])."'";
	//error_log($sql, 0);
	$sql_result = mysql_query ($sql, $connection ) or die ('request "Could not execute SQL query" '.$sql);
	$user = mysql_fetch_assoc($sql_result);
	if(!empty($user)){
		$_SESSION['user_info'] = $user;
		$query = " UPDATE `student_online` SET last_login = NOW() WHERE student_web_id=".$user['student_web_id'];
		mysql_query ($query, $connection ) or die ('request "Could not execute SQL query" '.$query);
		
		$query2 = "INSERT INTO `student_login` (login_date,login_system, student_id)VALUES(CURRENT_TIMESTAMP,1,".$user['student_id'].")";
		mysql_query ($query2, $connection ) or die ('request "Could not execute SQL query" '.$query2);
		
		header("Location: learningStatement.php");
 
        /* Make sure that code below does not get executed when we redirect. */
        exit;
	}
	else{
		$error = 'Λάθος Αρ. Τηλεφώνου η Αρ. Ταυτότητας.';
	}
}

if(isset($_GET['ac']) && $_GET['ac'] == 'logout'){
	$_SESSION['user_info'] = null;
	unset($_SESSION['user_info']);
}

?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Ασφαλιστικό Ινστιτούτο Κύπρου - Σύνδεση</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

        <link rel="stylesheet" href="css/main.css">
        
        <link href='http://fonts.googleapis.com/css?family=Roboto:400,300,500' rel='stylesheet' type='text/css'>
        <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
        <script src="js/jquery-1.8.2.min.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <script src="js/main.js"></script>
    </head>
    <body>

	<?php if(isset($_SESSION['user_info']) && is_array($_SESSION['user_info'])) { ?>

	    <form id="login-form" class="login-form" name="form1">

	        <div id="form-content">
	            <div class="welcome">
					<?php echo $_SESSION['user_info']['name']  ?>, ειστε ηδη συνδεδεμενος/η. 
                    <br /><br />
                    
                    <a href="learningStatement.php" style="color:#3ec038">Εκπαιδευτικη Κατασταση</a>
                    <br />
                    <a href="index.php?ac=logout" style="color:#3ec038">Αποσυνδεση</a>
                    
				</div>	
	        </div>
	
	    </form>
        
	<?php } else { ?>
	    <form id="login-form" class="login-form" name="form1" method="post" action="index.php">
	    	<input type="hidden" name="is_login" value="1">
	    	<div class="centeredTitle"><b>Ασφαλιστικό Ινστιτούτο Κύπρου</b></div>
	        <div class="h1">Συνδεση Με Το Συστημα Διαχειρησης Μελων</div>
	        <div id="form-content">
	            <div class="group">
	                <label for="email">Αρ. Τηλεφώνου</label>
	                <div><input id="mobileNumber" name="mobileNumber" class="form-control required" type="text" placeholder="Αρ. Τηλεφώνου"></div>
	            </div>
	           <div class="group">
	                <label for="name">Αρ. Ταυτότητας</label>
	                <div><input id="idnumber" name="idnumber" class="form-control required" type="password" placeholder="Αρ. Ταυτότητας"></div>
	            </div>
	            <?php if($error) { ?>
	                <em>
						<label class="err" for="password" generated="true" style="display: block;"><?php echo $error ?></label>
					</em>
				<?php } ?>
	            <div class="group submit">
	                <label class="empty"></label>
	                <div><input name="submit" type="submit" value="Συνδεση"/></div>
	            </div>
	        </div>
	        <div id="form-loading" class="hide"><i class="fa fa-circle-o-notch fa-spin"></i></div>
	    </form>
	<?php } ?>   
    </body>
</html>

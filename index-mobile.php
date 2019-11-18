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
		
		header("Location: learningStatement-mobile.php");
 
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
<html lang="en">
    
  <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!-- Required meta tags -->
    
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://iic.org.cy/wp-content/themes/bridge/img/favicon.ico">
    <!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Ασφαλιστικό Ινστιτούτο Κύπρου - Σύνδεση</title>
   
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="css/signin.css" rel="stylesheet">
    
    </head>
    <body class="text-center">

	<?php if(isset($_SESSION['user_info']) && is_array($_SESSION['user_info'])) { ?>

	    <form id="login-form" class="login-form" name="form1">

	        <div id="form-content">
	            <div class="welcome">
					<?php echo $_SESSION['user_info']['name']  ?>, ειστε ηδη συνδεδεμενος/η. 
                    <br /><br />
                    
                    <a href="learningStatement-mobile.php" style="color:#3ec038">Εκπαιδευτικη Κατασταση</a>
                    <br />
                    <a href="index-mobile.php?ac=logout" style="color:#3ec038">Αποσυνδεση</a>
                    
				</div>	
	        </div>
	
	    </form>
        
	<?php } else { ?>
	    <form id="login-form" name="form1" method="post" action="index-mobile.php" class="form-signin">
	    	<input type="hidden" name="is_login" value="1">
	    	
	    	<img class="mb-4" src="https://iic.org.cy/wp-content/uploads/2019/01/output-onlinepngtools-1.png" alt="Ασφαλιστικό Ινστιτούτο Κύπρου">
	        <h1 class="h4 mb-4 font-weight-normal">Σύνδεση Με Το Σύστημα Διαχείρησης Μελών</h1>
	        <div id="form-content">
	            <label for="mobileNumber" class="sr-only">Αρ. Τηλεφώνου</label>
                <input id="mobileNumber" name="mobileNumber" class="form-control" type="text" placeholder="Αρ. Τηλεφώνου" required autofocus value="99611366">
                
                <label for="idnumber" class="sr-only">Password</label>
                <input id="idnumber" name="idnumber" class="form-control" type="password" placeholder="Αρ. Ταυτότητας" required value="726245">
                
                
                <!--div class="checkbox mb-3">
                <label>
                  <input type="checkbox" value="remember-me"> Remember me
                </label>
                </div-->
                
                <button id="btnSubmit" class="btn btn-lg btn-primary btn-block" type="button" onClick="doLogin()"> 
                   
                    Σύνδεση
                </button>
                
               
                 <span id="spinner" class="spinner-border spinner-grow-sm">  </span> 
                <p class="mt-5 mb-3 text-muted" id="status">Javascript not enabled :-(</p>
                <p class="mt-5 mb-3 text-muted">&copy; 2017-2019</p>
	        </div>
	       
	    </form>
	<?php } ?>
	
	    <!--script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script!-->

        <!--script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script-->
        <!--script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script-->
        
        <script src="js/jquery-1.8.2.min.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <script src="js/main.js"></script>

    </body>
</html>

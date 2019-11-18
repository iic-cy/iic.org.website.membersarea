<?php

session_name('membersArea');
@session_start();

include("config.php");
$error = '';
?>
<!DOCTYPE html>
<html lang="en">
    
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="https://iic.org.cy/wp-content/themes/bridge/img/favicon.ico">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Ασφαλιστικό Ινστιτούτο Κύπρου - Σύνδεση</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--link href='http://fonts.googleapis.com/css?family=Roboto:400,300,500' rel='stylesheet' type='text/css'-->
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    
    </head>
    <body class="text-center">
        <div class="container">
        <img class="mb-4" style="max-width:50%;" src="https://iic.org.cy/wp-content/uploads/2019/01/output-onlinepngtools-1.png" alt="Ασφαλιστικό Ινστιτούτο Κύπρου">
	    <h1 class="h3 mb-4 font-weight-normal">Εκπαιδευτική Κατάσταση - Learning Statement</h1>
	    
<?php if(isset($_SESSION['user_info']) && is_array($_SESSION['user_info'])) { 
    $date = new DateTime('now', new DateTimeZone('Europe/Athens'));
    $query = "SELECT s.student_id, s.student_earned_hours, s.lesson_description, s.lecture_title, DATE_FORMAT(s.lecture_date2, '%d-%m-%Y')  as lecture_date FROM learning_statement s where s.student_id=".mysql_real_escape_string($_SESSION['user_info']['student_id'])." order by s.lecture_date desc";
    echo '
      <p>'. $_SESSION['user_info']['name'] .'<br /> <a href="index-mobile.php?ac=logout" style="color:#3ec038">Αποσύνδεση</a> </p>
      <div class="table-responsive">
      <table id="tblLearningStatement" class="table table-striped"> 
      <thead class="thead-dark">
      <tr> 
          <th scope="col"> Ημερομηνία </th> 
          <th scope="col"> Σεμινάριο/Πρόγραμμα </th> 
          <th scope="col"> Ωρες </th> 
      </tr>
      </thead>
      <tbody>';
    $totalHours = 0;
    if ($result = $connection->query($query)) {
        while ($row = $result->fetch_assoc()) {
        
            $student_id = $row["student_id"];
            $lesson_description = $row["lesson_description"];
            $lecture_title = $row["lecture_title"];
            $lecture_date = $row["lecture_date"];
            $student_earned_hours = $row["student_earned_hours"];
            $totalHours = $totalHours + $student_earned_hours;
            echo '<tr> 
                      <td style="text-align:center">'.$lecture_date.'</td> 
                      <td>'.$lecture_title.'<br/>'.$lesson_description.'</td> 
                      <td style="text-align:right">'.$student_earned_hours.'</td> 
                  </tr>';
        }
        
        echo '  <tr> 
                  <td colspan="2" align="right">ΣΥΝΟΛΟ</td> 
                  <td  style="text-align:right">'. number_format ( $totalHours , 2 ) .'</td> 
              </tr>
              </tbody>
              <tfoot>
              <td colspan="3" style="text-align:center">
                Το περιεχόμενο της Εκπαιδευτική Κατάστασης είναι για πληροφοριακή Χρήση των Μελών του Ασφαλιστικού Ινστιτούτου Κύπρου.
                Για οποιανδήποτε επίσημη χρήση θα πρέπει να εκδίδεται από το Ασφαλιστικό Ινστιτούτο Κύπρου.
                <br />'.$date->format('D M d, Y G:i').'
                </td> </tfoot>
              </table></div></div>';
        
        $result->free();
    } 
    

 } else { ?>
    You are not logged in. Please login <a href="index-mobile.php">here</a>

<?php } ?>
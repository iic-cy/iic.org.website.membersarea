<?php

session_name('membersArea');
@session_start();

include("config.php");
$error = '';
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Εκπαιδευτική Κατάσταση</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="css/main.css">
        <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,500' rel='stylesheet' type='text/css'>
        <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
        <script src="js/jquery-1.8.2.min.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <script src="js/main.js"></script>
    </head>
    <body>

<?php if(isset($_SESSION['user_info']) && is_array($_SESSION['user_info'])) { 
    $date = new DateTime('now', new DateTimeZone('Europe/Athens'));
    $query = "SELECT s.student_id, s.student_earned_hours, s.lesson_description, s.lecture_title, DATE_FORMAT(s.lecture_date2, '%d-%m-%Y')  as lecture_date FROM learning_statement s where s.student_id=".mysql_real_escape_string($_SESSION['user_info']['student_id'])." order by s.lecture_date desc";
    echo '<table id="tblLearningStatement"> 
      <caption><b>Ασφαλιστικό Ινστιτούτο Κύπρου</b><br/>Εκπαιδευτική Κατάσταση - Learning Statement<br/>'. $_SESSION['user_info']['name'] .'<br /> <a href="index.php?ac=logout" style="color:#3ec038">Αποσύνδεση</a> </caption>
      <tr> 
          <th> Ημερομηνία </th> 
          <th> Σεμινάριο/Πρόγραμμα </th> 
          <th> Ωρες </th> 
      </tr><tbody>';
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
              </table>';
        
        $result->free();
    } 
    

 } else { ?>
    You are not logged in. Please login <a href="index.php">here</a>

<?php } ?>
<?php
###########################################################
/*
GuestBook Script
Copyright (C) StivaSoft ltd. All rights Reserved.


This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/gpl-3.0.html.

For further information visit:
http://www.phpjabbers.com/
info@phpjabbers.com

Version:  1.0
Released: 2014-11-25
*/
###########################################################
include_once('fix_mysql.inc.php');
# see: https://stackoverflow.com/a/37877644/1069083

/* Define MySQL connection details and database table name */ 
$SETTINGS["hostname"] = 'h40lg7qyub2umdvb.cbetxkdyhwsb.us-east-1.rds.amazonaws.com';
$SETTINGS["mysql_user"] = 'zaheai3jklgk2ltf';
$SETTINGS["mysql_pass"] = 'cvh6rk2hcvll98hw';
$SETTINGS["mysql_database"] = 'tsy1ng4y7q19kwvs';
$SETTINGS["USERS"] = 'php_users_login'; // this is the default table name that we used

/* Connect to MySQL */
$connection = mysql_connect($SETTINGS["hostname"], $SETTINGS["mysql_user"], $SETTINGS["mysql_pass"]) or die ('Unable to connect to MySQL server.<br ><br >Please make sure your MySQL login details are correct.');
$db = mysql_select_db($SETTINGS["mysql_database"], $connection) or die ('request "Unable to select database."');
?>
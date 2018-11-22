<?php
$GLOBAL_DB=new mysqli("","","","");
if ($GLOBAL_DB->connect_errno) die("Connection to the database failed!");

<?php
$cluster  = Cassandra::cluster()
                ->build();
$keyspace  = 'twitter';
$session  = $cluster->connect($keyspace);
if(!empty($_POST["usernamesignup"]))
{
    $anand=$_POST["usernamesignup"];
    $result = $session->execute(new Cassandra\SimpleStatement
    ("SELECT username from twitter.users where username='$anand'"));
    $cnt=0;
    foreach ($result as $row) {
	    $cnt++;
    }
	$yes=1;
	$anand=strtolower($anand);
    if (strpos($anand, '.com') !== false) {
    $yes=0;
    }
	if($cnt==0&&$yes==1)
	    echo "username available";
	else
	    echo "username not available";
}

?>
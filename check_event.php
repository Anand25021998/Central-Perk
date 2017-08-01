<?php
$cluster  = Cassandra::cluster()
                ->build();
$keyspace  = 'twitter';
$session  = $cluster->connect($keyspace);
if(!empty($_POST["eventname"]))
{
	$name=$_POST["eventname"];
	$result = $session->execute(new Cassandra\SimpleStatement("select * from events where ename='$name'"));
	$cnt=0;
	foreach($result as $row)
	{
		$name=$row['ename'];
		$cnt++;
	}
	if($cnt==0)
		echo "Event name available";
	else
		echo "Event already scheduled with this name";
}
?>
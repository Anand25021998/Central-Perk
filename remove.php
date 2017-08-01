<!DOCTYPE html>
<?php
$cluster  = Cassandra::cluster()
                ->build();
$keyspace  = 'twitter';
$session  = $cluster->connect($keyspace);
$user=$_COOKIE["username"];
$user1=$user;
$user1.="following";
?>
<?php
if(!empty($_POST["friend"]))
{
	$friend=$_POST["friend"];
	$result=$session->execute(new Cassandra\SimpleStatement("select * from $user1 where tofollow='$friend'"));
    foreach($result as $row)
	{
		$cnt=$row['cnt'];
	}
	if($cnt==1)
	{
		$result=$session->execute(new Cassandra\SimpleStatement("delete from $user1 where tofollow='$friend'"));
	}
	else
	{
		$result=$session->execute(new Cassandra\SimpleStatement("update $user1 set cnt=cnt-1 where tofollow='$friend'"));
	}
}
?>
<?php
session_start();
$cluster  = Cassandra::cluster()
                ->build();
$keyspace  = 'twitter';
$session  = $cluster->connect($keyspace);
if(!isset($_SESSION['user']))
{
	header("Location: index.php");
}
else
$user=$_SESSION['user'];

date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');
$imp=str_replace("-","",$date);
$imp=substr($imp,0,-2);
$month=(int)$imp;
?>
<?php
$user1=$user;
$user1.="like";
if(!empty($_POST["id"])) {
	$id=$_POST['id'];
	$char=substr($id,0,1);
	if($char=='1')
	{
		$id=substr($id,1);
		$tmp="t";
		$tmp.=$id;
		$id=substr_replace($tmp,'-',-6,0);
		switch($_POST["action"]){
			case "like":
			        $date1 = date('Y-m-d H:i:s');
					$query=$session->execute(new Cassandra\SimpleStatement("INSERT INTO $user1 (month,ltime,id) VALUES ($month,'$date1','$id')"));
					$query =$session->execute(new Cassandra\SimpleStatement("UPDATE tweetcnts SET likes = likes + 1 WHERE id='$id'"));		
			break;		
			case "unlike":
			    $query = $session->execute(new Cassandra\SimpleStatement("select * FROM $user1 WHERE id ='$id'"));
				foreach($query as $row)
				{
					$ctime=$row['ltime'];
				}
				$query = $session->execute(new Cassandra\SimpleStatement("DELETE FROM $user1 WHERE month=$month and ltime='$ctime'"));
					$query =$session->execute(new Cassandra\SimpleStatement("UPDATE tweetcnts SET likes = likes - 1 WHERE id='$id' "));
			break;		
		}
	}
	else if($char=='2')
	{
		$id=substr($id,1);
		$tmp="s";
		$tmp.=$id;
		$id=substr_replace($tmp,'-',-6,0);
		switch($_POST["action"]){
			case "love":
			        $date1 = date('Y-m-d H:i:s');
					$query=$session->execute(new Cassandra\SimpleStatement("INSERT INTO $user1 (month,ltime,id) VALUES ($month,'$date1','$id')"));
					$query =$session->execute(new Cassandra\SimpleStatement("UPDATE storiescnt SET loves = loves + 1 WHERE sid='$id'"));		
			break;		
			case "unlove":
				$query = $session->execute(new Cassandra\SimpleStatement("select * FROM $user1 WHERE id ='$id'"));
				foreach($query as $row)
				$ctime=$row['ltime'];
				$query = $session->execute(new Cassandra\SimpleStatement("DELETE FROM $user1 WHERE month=$month and ltime='$ctime'"));
					$query =$session->execute(new Cassandra\SimpleStatement("UPDATE storiescnt SET loves = loves - 1 WHERE sid='$id' "));
			break;		
		}		
	}
	else
	{
		$id=substr($id,1);//this id is simply the name of the event
		switch($_POST["action"]){
			case "like":
			$date1 = date('Y-m-d H:i:s');
			$query=$session->execute(new Cassandra\SimpleStatement("INSERT INTO $user1 (month,ltime,id) VALUES ($month,'$date1','$id')"));
			$query =$session->execute(new Cassandra\SimpleStatement("UPDATE eventcnts SET likes= likes + 1 WHERE name='$id'"));
			break;
			case "unlike":
				$query = $session->execute(new Cassandra\SimpleStatement("select * FROM $user1 WHERE id ='$id'"));
				foreach($query as $row)
				$ctime=$row['ltime'];
				$query = $session->execute(new Cassandra\SimpleStatement("DELETE FROM $user1 WHERE month=$month and ltime='$ctime'"));
					$query =$session->execute(new Cassandra\SimpleStatement("UPDATE eventcnts SET likes= likes - 1 WHERE name='$id' "));
			break;
		}
	}
}
?>
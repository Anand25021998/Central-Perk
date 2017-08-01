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
$user2=$user;
$user1.="tweet";
$user2.="retweet";
if(!empty($_POST["id"])) {
	$id=$_POST['id'];
	$id=substr($id,1);
	$tmp="t";
	$tmp.=$id;
	$id=substr_replace($tmp,'-',-6,0);
	$rid="r";
	$rid.=$id;
	switch($_POST["action"]){
		case "rtweet":
				$result=$session->execute(new Cassandra\SimpleStatement("select followed from followers where user='$user'"));
				foreach($result as $row)
				$fo=$row['followed'];
				$len=strlen($fo);
				$name="";
				for($i=0;$i<$len;$i++)
				{
					$char=substr($fo,$i,1);
					if($char=='%')
					{
						$user3=$user4=$name;
						$user3.="retweet";
						$user4.="tweet";
						
						$result1=$session->execute(new Cassandra\SimpleStatement("select * from $user3 where month=$month and rid='$rid'"));
						$cnt=0;
						foreach($result1 as $row)
						{
							$cnt++;
							$retweeters=$row['retweeters'];
						}
						if($cnt==0)
						{
							$rtweeters=$user;
							$rtweeters.="%";
							$statement=$session->execute(new Cassandra\SimpleStatement("insert into $user3(month,rid,retweeters) values($month,'$rid','$rtweeters')"));
							$date1 = date('Y-m-d H:i:s');
							if($name==$user)
								$statement=$session->execute(new Cassandra\SimpleStatement("insert into $user1(month,ttime,id,self) values($month,'$date1','$rid',1)"));
							else
								$statement=$session->execute(new Cassandra\SimpleStatement("insert into $user4(month,ttime,id,self) values($month,'$date1','$rid',0)"));
								
						}
						else
						{
							$retweeters.=$user;
							$retweeters.="%";
							$statement=$session->execute(new Cassandra\SimpleStatement("update $user3 set retweeters='$retweeters' where month=$month and rid='$rid'"));
							if($name==$user)//means $name's retweet table self will be converted to 1
							{
								$result2=$session->execute(new Cassandra\SimpleStatement("select * from $user1 where month=$month and id='$rid'"));
								foreach($result2 as $row)
								{
									$ttime=$row['ttime'];
								}
								$statement=$session->execute(new Cassandra\SimpleStatement("update $user1 set self=1 where month=$month and ttime='$ttime' and id='$rid'"));
							}
						}
						$name="";
					}
					else
						$name.=$char;
				}
			$statement=$session->execute(new Cassandra\SimpleStatement("update userscnt set retweets=retweets+1 where username='$user'"));
			$statement=$session->execute(new Cassandra\SimpleStatement("update tweetcnts set retweets=retweets+1 where id='$id'"));
		break;
		case "nrtweet":
		break;
	}
}
?>
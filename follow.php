<?php
//if a follow someone, all the users whom the friend is following will always be there in my following list(if not followed by me). even if my friend follows some one it will be added to my list by the heirarchy created.
$cluster  = Cassandra::cluster()
                ->build();
$keyspace  = 'twitter';
$session  = $cluster->connect($keyspace);
$user=$_COOKIE["username"];
$user2=$user;
$user2.="following";
date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');
$imp=str_replace("-","",$date);
$imp=substr($imp,0,-2);
$month=(int)$imp;


$result=$session->execute(new Cassandra\SimpleStatement("select * from followers where user='$user'"));
$following="";
foreach($result as $row)
$following=$row['following'];
$len1=strlen($following);
$folarr=array();//using associative array in php(hash map) so that we can directly check;
$name="";
for($i=0;$i<$len1;$i++)
{
	$char=substr($following,$i,1);
	if($char=='%')
	{
		$folarr[$name]=10;
		$name="";
	}
	else
		$name.=$char;
}
?>


<?php
if(!empty($_POST["friend"])&&!empty($_POST["action"]))
{
	$friend=$_POST["friend"];
	$action=$_POST["action"];
	if($action=="follow")
	{
		$statement=$session->execute(new Cassandra\SimpleStatement("update userscnt set followers=followers+1 where username='$friend'"));
		$statement=$session->execute(new Cassandra\SimpleStatement("update userscnt set following=following+1 where username='$user'"));
		$statement=$session->execute(new Cassandra\SimpleStatement("delete from $user2 where tofollow='$friend'"));//removing from my following table all rows having same user name
		$result=$session->execute(new Cassandra\SimpleStatement("select * from followers where user='$user'"));
		foreach($result as $row)
		{
			$following=$row['following'];
			$followed1=$row['followed'];
		}
		$following.=$friend;
		$following.="%";
		$result=$session->execute(new Cassandra\SimpleStatement("update followers set following='$following' where user='$user'"));//addition in my following list
		
		$result=$session->execute(new Cassandra\SimpleStatement("select * from followers where user='$friend'"));
		foreach($result as $row)
		{
			$followed=$row['followed'];
			$following=$row['following'];
		}
		$followed.=$user;
		$followed.="%";
		$result=$session->execute(new Cassandra\SimpleStatement("update followers set followed='$followed' where user='$friend'"));//jisko maine follow kiya uske followed list me khud ko daalo
		
		$len5=strlen($following);//ab uske following ko hm apne table me daalenge if i m not following that particular user
		$name="";
		for($i=0;$i<$len5;$i++)
		{
			$char=substr($following,$i,1);
			if($char=="%")
			{
				if($name!=$user&&!array_key_exists($name,$folarr))
					$result6=$session->execute(new Cassandra\SimpleStatement("update $user2 set cnt=cnt+1 where tofollow='$name'"));
				
				$name="";
			}
			else
				$name.=$char;
		}
		
		//I will be putting friend in those users list who are following me but are not in followed list of friend
		//make an associative list corresponding to followed list of friend($followed)
		
		$len1=strlen($followed);
		$folarr1=array();//using associative array in php(hash map) so that we can directly check;
		$name="";
		for($i=0;$i<$len1;$i++)
		{
			$char=substr($followed,$i,1);
			if($char=='%')
			{
				$folarr1[$name]=10;
				$name="";
			}
			else
				$name.=$char;
		}
		//my followed list is $followed1;
		$name="";
		$len=strlen($followed1);
		for($i=0;$i<$len;$i++)
		{
			$char=substr($followed1,$i,1);
			if($char=='%')
			{
				if($name!=$user&&!array_key_exists($name,$folarr1))
				{
					$name.="following";
					$result6=$session->execute(new Cassandra\SimpleStatement("update $name set cnt=cnt+1 where tofollow='$friend'"));
				}
				$name="";
			}
			else
				$name.=$char;
		}
		$user3=$user;
		$user3.="tweet";
		$friend1=$friend;
		$friend1.="tweet";
		$result=$session->execute(new Cassandra\SimpleStatement("select * from $friend1 where month=$month and self=1"));
		foreach($result as $row)
		{
			$id=$row['id'];
			$ttime=$row['ttime'];
			$start=substr($id,0,1);
			if($start=='r')
			{
				//putting the previous tweets only from friend timeline to my timeline(and not the retweets)
			}
			else
			$result=$session->execute(new Cassandra\SimpleStatement("insert into $user3(month,id,ttime,self) values($month,'$id','$ttime',0)"));
		}
		
		
		echo "<script type='text/javascript'>alert('You are now friend with $friend')</script>"; 
	}
	else
	{
		$statement=$session->execute(new Cassandra\SimpleStatement("update userscnt set followers=followers-1 where username='$friend'"));
		$statement=$session->execute(new Cassandra\SimpleStatement("update userscnt set following=following-1 where username='$user'"));
		
		//deleting friend from following list of mine
		$result=$session->execute(new Cassandra\SimpleStatement("select * from followers where user='$user'"));
		foreach($result as $row)
		{
			$following=$row['following'];
			$followed1=$row['followed'];
		}
		$str=$friend;
		$str.="%";
		$following=str_replace($str,"",$following);
		$result=$session->execute(new Cassandra\SimpleStatement("update followers set following='$following' where user='$user'"));
		
		//deleting myself from followed list of friend
		$result=$session->execute(new Cassandra\SimpleStatement("select * from followers where user='$friend'"));
		foreach($result as $row)
		{
			$followed=$row['followed'];
			$following=$row['following'];
		}
		$str=$user;
		$str.="%";
		$followed=str_replace($str,"",$followed);
		$result=$session->execute(new Cassandra\SimpleStatement("update followers set followed='$followed' where user='$friend'"));
		
		//decrementing count of users who are followed by friend and are in my following table
		$len5=strlen($following);
		$name="";
		for($i=0;$i<$len5;$i++)
		{
			$char=substr($following,$i,1);
			if($char=="%")
			{
				if($name!=$user&&!array_key_exists($name,$folarr))
				{
					$cnt=0;//if $name row is not present at all
					$result6=$session->execute(new Cassandra\SimpleStatement("select * from $user2 where tofollow='$name'"));
					    foreach($result6 as $row)
						{
							$cnt=$row['cnt'];
						}
						if($cnt<=1)
						{
							$result=$session->execute(new Cassandra\SimpleStatement("delete from $user2 where tofollow='$name'"));
						}
						else
						{
							$result=$session->execute(new Cassandra\SimpleStatement("update $user2 set cnt=cnt-1 where tofollow='$name'"));
						}
				}
				
				$name="";
			}
			else
				$name.=$char;
		}
		
		//making list of guys who are following friend
		$len1=strlen($followed);
		$folarr1=array();//using associative array in php(hash map) so that we can directly check;
		$name="";
		for($i=0;$i<$len1;$i++)
		{
			$char=substr($followed,$i,1);
			if($char=='%')
			{
				$folarr1[$name]=10;
				$name="";
			}
			else
				$name.=$char;
		}
		
		//my followed list is $followed1;
		$name="";
		$len=strlen($followed1);
		for($i=0;$i<$len;$i++)
		{
			$char=substr($followed,$i,1);
			if($char=='%')
			{
				if($name!=$user&&!array_key_exists($name,$folarr1))
				{
					$name.="following";
					$cnt=0;
					$result6=$session->execute(new Cassandra\SimpleStatement("select * from $name where tofollow='$friend'"));
					foreach($result6 as $row)
					{
						$cnt=$row['cnt'];
					}
					if($cnt<=1)
						$result=$session->execute(new Cassandra\SimpleStatement("delete from $name where tofollow='$friend'"));
					else
						$result=$session->execute(new Cassandra\SimpleStatement("update $name set cnt=cnt-1 where tofollow='$friend'"));
				}
				$name="";
			}
			else
				$name.=$char;
		}
		//removing each tweet of friend from my timeline
		$user3=$user4=$user;
		$user3.="tweet";
		$friend1=$friend;
		$friend1.="tweet";
		$user4.="retweet";
		$result=$session->execute(new Cassandra\SimpleStatement("select * from $friend1 where self=1"));
		foreach($result as $row)
		{
			$id=$row['id'];
			$month=$row['month'];
			$ttime=$row['ttime'];
			$result5=$session->execute(new Cassandra\SimpleStatement("select self from $user3 where id='$id' and month=$month and ttime='$ttime'"));
			$self=0;
			foreach($result5 as $row)
			$self=$row['self'];
			if($self!=1)//may i have retweeted the same tweet which is in friend's list
			$result=$session->execute(new Cassandra\SimpleStatement("delete from $user3 where id='$id' and month=$month and ttime='$ttime'"));
		    if($self==1)
			{
				$retweeters="";
				$res=$session->execute(new Cassandra\SimpleStatement("select retweeters from $user4 where rid='$id'"));
				foreach($res as $row)
				$retweeters=$row['retweeters'];
				$friend2=$friend;
				$friend2.="%";
				$retweeters=str_replace($friend2,"",$retweeters);
				$statement=$session->execute(new Cassandra\SimpleStatement("update $user4 set retweeters='$retweeters' where month=$month and rid='$id'"));
			}
		}
	}
}
?>
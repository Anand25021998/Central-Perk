<!DOCTYPE html>
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


$profilepic="";
date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');
$imp=str_replace("-","",$date);
$imp=substr($imp,0,-2);
$month=(int)$imp;
?>
<?php
if($_SERVER["REQUEST_METHOD"]=="POST")
{
	if(!empty($_POST["logout"])) {
		echo "hi";
		$_SESSION["user"] = "";
		session_destroy();
		header("Location: index.php");
	}
}
?>
<?php
if($_SERVER["REQUEST_METHOD"]=="POST")
{
	if(isset($_POST['postbtn']))
	{
		$story=$_POST["postbox"];
		$date = date('Y-m-d');
		$imp=str_replace("-","",$date);//remove -
		$imp=substr($imp,0,-2);//remove date
		$month=(int)$imp;
		$result=$session->execute(new Cassandra\SimpleStatement("select * from stories"));
		$cnt=0;
		foreach($result as $row)
		{
			$cnt++;
		}
		$cnt++;
		$id="s";
		$id.=$cnt;
		$id.="-";
		$id.=$month;
		$previd="s";
		$previd.=($cnt-1);
		$previd.="-";
		$previd.=$month;
		$photo="hi";
		$temp1=1;$message1="";
		$yes=1;
			  if(isset($_FILES['userImage1'])){
			  $target_dir = "images/";
			  $file_name = $_FILES['userImage1']['name'];//name of the userImage uploaded
			  $target_file1 = $target_dir . $_FILES["userImage1"]["name"];//target file is the full location of the userImage on the server
			  $file_size =$_FILES['userImage1']['size'];
			  $file_tmp =$_FILES['userImage1']['tmp_name'];//it  will contain the temporary file name of the file on the server. This is just a placeholder on your server until you process file
			  $file_type=$_FILES['userImage1']['type'];
			  $anand = explode('.', $file_name);
			  $file_ext=strtolower(end($anand));
			  
			  $expensions= array("jpeg","jpg","png");
			  
			  if(in_array($file_ext,$expensions)=== false){
				$message1="extension not allowed, please choose a JPEG or PNG file";
				 $temp1=0;
			  }
			  
			  if($file_size > 2097152){
				 $message1="File size must be excately 2 MB";
				 $temp1=0;
			  }
			  
			  if($temp1){
				 move_uploaded_file($file_tmp,"images/".$file_name);
				 $photo=$target_file1;
			  }
			  if($file_name=="")
				  $yes=0;
		    }
			$result=$session->execute(new Cassandra\SimpleStatement("select * from stories where sid='$previd'"));
			$prevtweeter="";
			foreach($result as $row)
			{
				$prevstory=$row['story'];
				$prevtweeter=$row['postedby'];
				$prevphoto=$row['photos'];
			}
			if($user==$prevtweeter && $prevstory==$story && $photo=$prevphoto)
			{
				
			}
			else
			{
				$date1 = date('Y-m-d H:i:s');
				$statement=$session->execute(new Cassandra\SimpleStatement("insert into stories(sid,story,postedby,stime,photos) values('$id','$story','$user','$date1','$photo')"));
				$statement=$session->execute(new Cassandra\SimpleStatement("update storiescnt set loves=loves+0 where sid='$id'"));
				$statement=$session->execute(new Cassandra\SimpleStatement("update userscnt set stories=stories+1 where username='$user'"));
				
				//handling the hash part----------------------------------------------------------------------------------------------------------------------------------------------
				$number=0;
				$result=$session->execute(new Cassandra\SimpleStatement("select * from hash"));
				foreach($result as $row)
				$number++;
				$number++;//for setting the hash id, instead of calculating in the loop every time calculate it once and manipulate in the loop
				preg_match_all('/(#[a-zA-z0-9_]\w+)/', $story, $tags);
				$sz=count($tags[1]);
				for($i=0;$i<$sz;$i++)
				{
					$hid="h";
					$hid.=$number;
					$hid.="-";
					$hid.=$month;
					$tagstr=$tags[1][$i];
					$result=$session->execute(new Cassandra\SimpleStatement("select * from hash where hmonth=$month and hashname='$tagstr'"));
					$cnt=0;
					foreach($result as $row)
					$cnt++;
					if($cnt==0)//means this hash is not present
					{
						$id1=$id;//joining the new hash in the beginning so that sorted acc to time
						$id1.="@";
						$number++;
						$statement=$session->execute(new Cassandra\SimpleStatement("insert into hash(hmonth,hid,hashname,tid,cnt) values($month,'$hid','$tagstr','$id1',1)"));
					}
					else//this hash is present. since cnt cannot be updated being a primary key so we will have to delete the already present row hash and insert a new row for it
					{
						foreach($result as $row)
						{
							$hid=$row['hid'];
							$cnt=$row['cnt'];
							$tid=$row['tid'];
						}
						$statement=$session->execute(new Cassandra\SimpleStatement("delete from hash where hmonth=$month and cnt=$cnt and hid='$hid'"));
						$cnt++;
						$pid=$id;
						$pid.="@";
						$pid.=$tid;
						$statement=$session->execute(new Cassandra\SimpleStatement("insert into hash(hmonth,hid,hashname,tid,cnt) values($month,'$hid','$tagstr','$pid',$cnt)"));
					}
				}
				//hash part over-------------------------------------------------------------------------------------------------------------------------------------------------
				
				//putting the stories in the followers table----------------------------------------------------------------------------------------------------------------------
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
						$name1=$name;
						$name.="tweet";
						$date1 = date('Y-m-d H:i:s');
						if($name1==$user)
							$statement=$session->execute(new Cassandra\SimpleStatement("insert into $name(month,id,ttime,self) values($month,'$id','$date1',1)"));
						else
							$statement=$session->execute(new Cassandra\SimpleStatement("insert into $name(month,id,ttime,self) values($month,'$id','$date1',0)"));
						$name="";
					}
					else
						$name.=$char;
				}
				//putting story in followers table over--------------------------------------------------------------------------------------------------------------------------------
				
				if($temp1||!$yes)
					echo "<script type='text/javascript'>alert('Story published sucessfuly')</script>"; 
				if(!$temp1&&$yes)
					echo "<script type='text/javascript'>alert('Story published but image could not be loaded as $message1')</script>"; 
			}
		}
}
?>
<?php
if($_SERVER["REQUEST_METHOD"]=="POST")
{
	if(isset($_POST['tweetbtn']))
	{
		$tweet=$_POST["tweetbox"];
		$result=$session->execute(new Cassandra\SimpleStatement("select * from tweets"));
		$cnt=0;
		foreach($result as $row)
		{
			$cnt++;
		}
		$cnt++;
		$previd="t";
		$previd.=($cnt-1);
		$previd.="-";
		$previd.=$month;
		$id="t";
		$id.=$cnt;
		$id.="-";
		$id.=$month;
		$photo="hi";
		$temp1=1;$message1="";
		$yes=1;//means image upload hua ya nahi.
			  if(isset($_FILES['userImage'])){
			  $target_dir = "images/";
			  $file_name = $_FILES['userImage']['name'];//name of the userImage uploaded
			  $target_file1 = $target_dir . $_FILES["userImage"]["name"];//target file is the full location of the userImage on the server
			  $file_size =$_FILES['userImage']['size'];
			  $file_tmp =$_FILES['userImage']['tmp_name'];//it  will contain the temporary file name of the file on the server. This is just a placeholder on your server until you process file
			  $file_type=$_FILES['userImage']['type'];
			  $anand = explode('.', $file_name);
			  $file_ext=strtolower(end($anand));
			  
			  $expensions= array("jpeg","jpg","png");
			  
			  if(in_array($file_ext,$expensions)=== false){
				$message1="extension not allowed, please choose a JPEG or PNG file";
				 $temp1=0;
			  }
			  
			  if($file_size > 2097152){
				 $message1="File size must be excately 2 MB";
				 $temp1=0;
			  }
			  
			  if($temp1){
				 move_uploaded_file($file_tmp,"images/".$file_name);
				 $photo=$target_file1;
			  }
			  if($file_name=="")
				  $yes=0;
		    }
			$result=$session->execute(new Cassandra\SimpleStatement("select * from tweets where id='$previd'"));
			$cnt=0;
			foreach($result as $row)
			{
				$cnt++;
				$prevtweet=$row['tweet'];
				$prevtweeter=$row['tweeter'];
				$prevphoto=$row['photos'];
			}
			if($cnt!=0&&$user==$prevtweeter && $prevtweet==$tweet && $photo=$prevphoto)
			{
			}
			else
			{
				$date1 = date('Y-m-d H:i:s');
				$statement=$session->execute(new Cassandra\SimpleStatement("insert into tweets(id,tweet,tweeter,ttime,photos) values('$id','$tweet','$user','$date1','$photo')"));
				$statement=$session->execute(new Cassandra\SimpleStatement("update tweetcnts set likes=likes+0,retweets=retweets+0 where id='$id'"));
				$statement=$session->execute(new Cassandra\SimpleStatement("update userscnt set tweets=tweets+1 where username='$user'"));
				
				//handling the hash part----------------------------------------------------------------------------------------------------------------------------------------------
				$number=0;
				$result=$session->execute(new Cassandra\SimpleStatement("select * from hash"));
				foreach($result as $row)
				$number++;
				$number++;//for setting the hash id, instead of calculating in the loop every time calculate it once and manipulate in the loop
				preg_match_all('/(#[a-zA-z0-9_]\w+)/', $tweet, $tags);
				$sz=count($tags[1]);
				for($i=0;$i<$sz;$i++)
				{
					$hid="h";
					$hid.=$number;
					$hid.="-";
					$hid.=$month;
					$tagstr=$tags[1][$i];
					$result=$session->execute(new Cassandra\SimpleStatement("select * from hash where hmonth=$month and hashname='$tagstr'"));
					$cnt=0;
					foreach($result as $row)
					$cnt++;
					if($cnt==0)//means this hash is not present
					{
						$id1=$id;
						$id1.="@";
						$number++;
						$statement=$session->execute(new Cassandra\SimpleStatement("insert into hash(hmonth,hid,hashname,tid,cnt) values($month,'$hid','$tagstr','$id1',1)"));
					}
					else//this hash is present. since cnt cannot be updated being a primary key so we will have to delete the already present row hash and insert a new row for it
					{
						foreach($result as $row)
						{
							$hid=$row['hid'];
							$cnt=$row['cnt'];
							$tid=$row['tid'];
						}
						$statement=$session->execute(new Cassandra\SimpleStatement("delete from hash where hmonth=$month and cnt=$cnt and hid='$hid'"));
						$cnt++;
						$pid=$id;
						$pid.="@";
						$pid.=$tid;
						$statement=$session->execute(new Cassandra\SimpleStatement("insert into hash(hmonth,hid,hashname,tid,cnt) values($month,'$hid','$tagstr','$pid',$cnt)"));
					}
				}
				//hash part over-------------------------------------------------------------------------------------------------------------------------------------------------
				
				
				//putting the tweet in the followers table----------------------------------------------------------------------------------------------------------------------
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
						$name1=$name;
						$name.="tweet";
						$date1 = date('Y-m-d H:i:s');
						if($name1==$user)
							$statement=$session->execute(new Cassandra\SimpleStatement("insert into $name(month,id,ttime,self) values($month,'$id','$date1',1)"));
						else
							$statement=$session->execute(new Cassandra\SimpleStatement("insert into $name(month,id,ttime,self) values($month,'$id','$date1',0)"));
						$name="";
					}
					else
						$name.=$char;
				}
				//putting tweet in followers table over--------------------------------------------------------------------------------------------------------------------------------
				
				if($temp1||!$yes)
					echo "<script type='text/javascript'>alert('Tweet posted sucessfuly')</script>"; 
				if(!$temp1&&$yes)
					echo "<script type='text/javascript'>alert('Tweet posted but image could not be loaded as $message1')</script>"; 
			}
		}
}
?>
<html>
    <head>
	
	<style>

.btn-likes input[type="button"]{width:32px;height:32px;border:0;cursor:pointer;}
.btn-loves input[type="button"]{width:32px;height:32px;border:0;cursor:pointer;}
.like {background:url('icons/3.png')}
.unlike {background:url('icons/4.png')}
.label-likes {font-size:12px;color:#2F529B;height:20px;}
.love{background:url('icons/2.png')}
.unlove{background:url('icons/1.png')}
.label-loves {font-size:12px;color:#2F529B;height:20px;}
.btn-rtweet input[type="button"]{width:32px;height:32px;border:0;cursor:pointer;}
.rtweet{background:url('icons/6.png')}
.nrtweet{background:url('icons/5.png')}
.label-rtweet {font-size:12px;color:#2F529B;height:20px;}
</style>
        <title>Login and Registration Form with HTML5 and CSS3</title>
		<link rel="stylesheet" type="text/css" href="css/home.css" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="js/home.js"></script>
<script>
function addrtweet(id,action) {
	$.ajax({
	url: "retweet.php",
	data:'id='+id+'&action='+action,
	type: "POST",
	beforeSend: function(){
		$('#feed-'+id+' .btn-rtweet').html("<img src='LoaderIcon1.gif' />");
	},
	success: function(data){
	var retweets = parseInt($('#rtweet-'+id).val());
	switch(action) {
		case "rtweet":
		$('#feed-'+id+' .btn-rtweet').html('<input type="button"  class="nrtweet" onClick="addrtweet('+id+',\'nrtweet\')" />');
		retweets = retweets+1;
		break;
		case "nrtweet":
		$('#feed-'+id+' .btn-rtweet').html('<input type="button"  class="nrtweet" onClick="addrtweet('+id+',\'nrtweet\')" />');
		break;
	}
	$("#pr").html(data);
	$('#rtweet-'+id).val(retweets);
		$('#feed-'+id+' .label-rtweet').html(retweets+" Retweets");
	}
	});
}
function addLoves(id,action) {
	$.ajax({
	url: "likes.php",
	data:'id='+id+'&action='+action,
	type: "POST",
	beforeSend: function(){
		$('#feed-'+id+' .btn-loves').html("<img src='LoaderIcon1.gif' />");
	},
	success: function(data){
	var loves = parseInt($('#loves-'+id).val());
	switch(action) {
		case "love":
		$('#feed-'+id+' .btn-loves').html('<input type="button"  class="unlove" onClick="addLoves('+id+',\'unlove\')" />');
		loves = loves+1;
		break;
		case "unlove":
		$('#feed-'+id+' .btn-loves').html('<input type="button" class="love"  onClick="addLoves('+id+',\'love\')" />')
		loves = loves-1;
		break;
	}
	$('#loves-'+id).val(loves);
		$('#feed-'+id+' .label-loves').html(loves+" Loves");
	}
	});
}
</script>
  <script>
function addLikes(id,action) {
	$.ajax({
	url: "likes.php",
	data:'id='+id+'&action='+action,
	type: "POST",
	beforeSend: function(){
		$('#feed-'+id+' .btn-likes').html("<img src='LoaderIcon1.gif' />");
	},
	success: function(data){
	var likes = parseInt($('#likes-'+id).val());
	switch(action) {
		case "like":
		$('#feed-'+id+' .btn-likes').html('<input type="button"  class="unlike" onClick="addLikes('+id+',\'unlike\')" />');
		likes = likes+1;
		break;
		case "unlike":
		$('#feed-'+id+' .btn-likes').html('<input type="button" class="like"  onClick="addLikes('+id+',\'like\')" />')
		likes = likes-1;
		break;
	}
	$('#likes-'+id).val(likes);
		$('#feed-'+id+' .label-likes').html(likes+" Likes");
	}
	});
}
</script>
<script>
function addfollow(friend,action)	
{
	$.ajax({
		url:"follow.php",
		data:'friend='+friend+'&action='+action,
		type:"POST",
		success:function(data){
			$('#line'+friend).html(data);
		}
	});
}
</script>
<script>
function removefollow(friend)	
{
	$.ajax({
		url:"remove.php",
		data:'friend='+friend,
		type:"POST",
		success:function(data){
			$('#line'+friend).html(data);
		}
	});
}
</script>
 </head>
	
	<body style="background:url('bg.jpg')">
	<nav class="navbar" role="navigation" style="min-height:67px;margin-bottom:3%">
	<div class="col-sm-1" style="padding-top:15px;text-align:right">
	<form method="post" action=" " enctype="multipart/form-data" class="form-horizontal">
		<div class="button-group" name="logout">
			<input type="submit" value="LogOut" name="logout" class="btn btn-primary">
		</div>
	</form>
	</div>
	<div class="col-sm-1" style="padding-top:15px">
	<div class="button-group">
		<a href="home.php" class="btn btn-primary">Home</a>
	</div>
    </div>
	<div class="col-sm-2" style="padding-top:15px;text-align:right">
		<div class="button-group">
			<a href="calendar.php" class="btn btn-primary">EventsNearU</a>
		</div>
	</div>
	<div class="col-sm-1">
	</div>
	<div class="col-sm-2" style="padding-top:10px;">
	    <p style="color:white;font-family:Monotype Corsiva;font-size:2em;">Central Perk</p>
	</div>
	<div class="col-sm-3" style="padding-top:10px;">
		<form class="navbar-form" role="search" style="text-align:right" action="profile.php" method="GET">
			<div class="input-group">
				<input type="text" class="form-control" placeholder="Search users" name="user" required="required">
				<div class="input-group-btn">
					<button class="btn btn-default" type="submit" name="usbutton"><i class="glyphicon glyphicon-search"></i></button>
				</div>
			</div>
		</form>
	</div>
	<div class="col-sm-1" style="text-align:center">
<?php
$result=$session->execute(new Cassandra\SimpleStatement("select profilepic from users where username='$user'"));
foreach($result as $row)
{
	$profilepic=$row['profilepic'];
}
echo "<a href='profile.php?user=" .$user . "'><img src='$profilepic' style='position:absolute;width:85px;height:80px;border-radius:4px;border:2px solid #333;'></a>";
?>
	</div>
	</nav>
	
	    <div class="col-sm-3">
		    <?php
			    $result=$session->execute(new Cassandra\SimpleStatement("select * from hash where hmonth=$month limit 20"))?>
				<div style="border:grey solid 2px;border-radius:10px;background-color:#f5f5ff;width:90%">
				<p style="padding-left:15%;padding-top:10%; font-size:1.2em">Trending this month</p>
			    <ul style="list-style-type: none;">
				<?php
				foreach($result as $row)
				{
					$hashname=$row['hashname'];
					$cnt=$row['cnt'];
					$hashname1=substr($hashname,1);
					echo "<div style='padding-top:3%'><a href='tags.php?tag=" . $hashname1 . "'>$hashname</a><br>--> $cnt tweets and posts</div><br>";
				}?>
				</ul>
				</div>
		</div>
		<div class="col-sm-6">
		
            <div class="panel with-nav-tabs panel-default">
                <div class="panel-heading">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab1default" data-toggle="tab">Tweets</a></li>
                            <li><a href="#tab2default" data-toggle="tab">Posts</a></li>
                        </ul>
                </div>			
				
                <div class="panel-body">
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tab1default">
						    
							<div class="row">
								<div class="col-sm-2">
									<?php
									echo "<img src='$profilepic' style='position:absolute;width:75px;height:75px;border-radius:50%;border:2px solid #C0C0C0;'>";
									?>
								</div>
								
								<div class="col-sm-10">
									<form method="post" action=" " enctype="multipart/form-data" class="form-horizontal">
										<textarea class="statusbox form-control tbox" rows="4" placeholder="Pen it down....." name="tweetbox" required="required" style="width:95%"></textarea>
										
										
										<div class="col-sm-6" style="margin-top:3%;">
											<input name="userImage" type="file" class="inputFile" /><br/>
										</div>	
										
										<div class="col-sm-6">
											<div class="col-sm-6">
												 <p class="counter" style="margin-top:16%;padding-left:80%">100</p>
											</div>
											<div class="col-sm-6" style="margin-top:3%">
												<div class="button-group">
												<input type="submit" value="Tweet" name="tweetbtn" class="btn btn-primary" id="tbtn" style="margin-top:5%;align:center">
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
							<hr>
							<?php
							    $user1=$user;
								$user1.="tweet";
								$result=$session->execute(new Cassandra\SimpleStatement("select * from $user1"));
								$marr=[];
								$i=0;
								$temp=array();//to encounter new values of month
								$store=[];
								$tot=0;
								foreach($result as $row)
								{
									$m=$row['month'];//month in the current row
									if(array_key_exists($m,$temp))
									{
										$tot++;
										array_push($store[$m],$row);
									}
									else
									{
										$tot++;
										$marr[$i]=$m;
										$i++;
										$temp[$m]=10;
										$store[$m]=[$row];
									}
								}
								$cnt=count($marr);
								if($cnt!=0)
								rsort($marr);
								
								for($i=0;$i<$cnt;$i++)
								{
                                    $mt=$marr[$i];
                                    $cnt1=count($store[$mt]);
								for($j=0;$j<$cnt1;$j++)
								{
									$self=$store[$mt][$j]['self'];
									$id=$store[$mt][$j]['id'];
									$time=$store[$mt][$j]['ttime'];
									$start=substr($id,0,1);
									if($start=='r')
									{
										$rid=$id;
										$id=substr($id,1);//r hata diya ab bs t se start ho raha
									}
									if($start=='t'||$start=='r')
									{
								        $result1=$session->execute(new Cassandra\SimpleStatement("select * from tweets where id='$id'"));
										foreach($result1 as $row)
										{
											$tweet=$row['tweet'];
											$tweeter=$row['tweeter'];
											$photo=$row['photos'];
										}
										$result2=$session->execute(new Cassandra\SimpleStatement("select * from tweetcnts where id='$id'"));
										foreach($result2 as $row)
										{
											$likes=$row['likes'];
											$retweets=$row['retweets'];
										}
										$result3=$session->execute(new Cassandra\SimpleStatement("select profilepic from users where username='$tweeter'"));
										foreach($result3 as $row)
										$ppic=$row['profilepic'];
										
										$forsearchuser=$tweeter;
										if($tweeter==$user)
										$tweeter="You";
									}
									if($start=='r')
									{
										$userx=$user;
										$userx.="retweet";
										$res=$session->execute(new Cassandra\SimpleStatement("select retweeters from $userx where rid='$rid'"));
										foreach($res as $row)
										$retweeters=$row['retweeters'];
										$length=strlen($retweeters);
										$name="";
										$str="";
										for($i=0;$i<$length;$i++)
										{
											$char=substr($retweeters,$i,1);
											if($char=="%")
											{
												if($name==$user)
													$str.="You";
												else
													$str.=$name;
												if($i!=$length-1)
													$str.=", ";
												$name="";
											}
											else
												$name.=$char;
										}
										echo "<div style='font-family:monotype corsiva;font-size:1.3em;margin-bottom:2%'><b><u>$str retweeted this tweet    ---- <br></b></u></div>";
									}
									?>
									<?php if($start=='r'||$start=='t')
									{
										$ids=$id;
										$id=substr($id,1);//ab id ka koi kaam nahi so we will be using it directly for like system purpose
										$id=str_replace("-","",$id);
										$tmp="1";
										$tmp.=$id;
										$id=(int)$tmp;
										?>
                                    <div style="margin-left:2%" id="feed-<?php echo $id; ?>">					
										<div class="row" style="margin-bottom:4%">
											<div class="col-sm-6">
												<div style="font-family:monotype corsiva;font-size:1.3em"><?php echo $tweeter?> posted this tweet....</div>
											</div>
											<div class="col-sm-6">
												<div style="font-family:monotype corsiva;font-size:1.3em"><?php echo $time?></div>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-2">
												<?php
												echo "<a href='profile.php?user=" .$forsearchuser. "'><img src='$ppic' style='position:relative;width:75px;height:75px;border-radius:50%;border:2px solid #C0C0C0;'></a>";
												?>
											</div>
											<div class="col-sm-10">
											<p style="font-family:times new roman;font-size:1.4em">
												<?php
													echo nl2br($tweet);	
												?>
											</p>
											<p>
												 <?php 
												 if($photo!="hi")
												 echo "<img src='$photo' style='position:relative;width:95%;height:290px;border:0px solid #C0C0C0;border-radius:4%'>";
												 ?>
											</p>
											<p style="margin-top:<?php if($photo!="hi") echo 6; else echo 14;?>%">
											    <div class="col-sm-3">
												    <input type="hidden" id="likes-<?php echo $id; ?>" value="<?php echo $likes; ?>">
													<?php
													   $user2=$user;
														$user2.="like";
														$res=$session->execute(new Cassandra\SimpleStatement("select * from $user2 where id='$ids'"));
														$count5=0;
														foreach ($res as $row)
														{
															$count5++;
														}
														$str_like = "like";
														if($count5!=0) {
														$str_like = "unlike";
														}
													?>
													<div class="btn-likes"><input type="button" class="<?php echo $str_like; ?>" onClick="addLikes(<?php echo $id; ?>,'<?php echo $str_like; ?>')" /></div>
													<div class="label-likes"><?php { echo $likes . " Likes"; } ?></div>
												</div>
												<div class="col-sm-3">
												    <input type="hidden" id="rtweet-<?php echo $id; ?>" value="<?php echo $retweets; ?>">
													<?php
													    $user3=$user;
														$user3.="tweet";
													    $tmp="r";
														$tmp.=$ids;
														$res=$session->execute(new Cassandra\SimpleStatement("select self from $user3 where id='$tmp'"));
														$s=0;
														foreach($res as $row)
														$s=$row['self'];//the row itself maynot be in table or the self value may be 0.
														$str_rt="rtweet";
														if($s!=0)
															$str_rt="nrtweet";
													?>
													<div class="btn-rtweet"><input type="button" class="<?php echo $str_rt; ?>" onClick="addrtweet(<?php echo $id; ?>,'<?php echo $str_rt; ?>')" /></div>
													<div class="label-rtweet"><?php { echo $retweets . " Retweets"; } ?></div>
												</div>
												<div class="col-sm-3" id="pr">
												</div>
											</p>
											</div>
										</div>
									</div>
									<hr>
								
								<?php
									}	
								}
								}
							?>
							
							
							
						</div>
						
						
						<div class="tab-pane fade" id="tab2default">
						    
							<div class="row">
								<div class="col-sm-2">
									<?php
									echo "<img src='$profilepic' style='position:absolute;width:75px;height:75px;border-radius:50%;border:2px solid #C0C0C0;'>";
									?>
								</div>
								
								<div class="col-sm-10">
								
									<form method="post" action=" " enctype="multipart/form-data" class="form-horizontal">
										<textarea class="statusbox form-control tbox" rows="8" placeholder="Express Your Feelings....." name="postbox" required="required" style="width:95%"></textarea>
										
										<div class="col-sm-8" style="margin-top:3%">
										<input name="userImage1" type="file" class="inputFile"/><br/>
										</div>
										<div class="col-sm-4" style="margin-top:2%">
											<div class="button-group">
												<input type="submit" value="Post" name="postbtn" class="btn btn-primary">
											</div>
										</div>
									</form>
								</div>
							</div>
							<hr>
							
							<?php
							$cnt=count($marr);
								for($i=0;$i<$cnt;$i++)
								{
                                    $mt=$marr[$i];
                                    $cnt1=count($store[$mt]);
								for($j=0;$j<$cnt1;$j++)
								{
									$self=$store[$mt][$j]['self'];
									$id=$store[$mt][$j]['id'];
									$time=$store[$mt][$j]['ttime'];
									$start=substr($id,0,1);
									if($start=='s')
									{
										$result1=$session->execute(new Cassandra\SimpleStatement("select * from stories where sid='$id'"));
										foreach($result1 as $row)
										{
											$story=$row['story'];
											$postedby=$row['postedby'];
											$stime=$row['stime'];
											$photo=$row['photos'];
										}
										$result2=$session->execute(new Cassandra\SimpleStatement("select * from storiescnt where sid='$id'"));
										foreach($result2 as $row)
										{
											$loves=$row['loves'];
										}
										$result2=$session->execute(new Cassandra\SimpleStatement("select profilepic from users where username='$postedby'"));
										foreach($result2 as $row)
										{
											$ppic=$row['profilepic'];
										}
										$forsearchuser=$postedby;
										if($postedby==$user)
											$postedby="You";
										
											$id1=substr($id,1);
											$id1=str_replace("-","",$id1);
											$tmp="2";
											$tmp.=$id1;
											$id1=(int)$tmp;
										?>
										
									<div style="margin-left:2%" id="feed-<?php echo $id1; ?>">		
										<div style="margin-left:2%">
											<div class="row" style="margin-bottom:4%">
												<div class="col-sm-6">
													<div style="font-family:monotype corsiva;font-size:1.3em"><?php echo $postedby?> posted this story....</div>
												</div>
												<div class="col-sm-6">
													<div style="font-family:monotype corsiva;font-size:1.3em"><?php echo $stime?></div>
												</div>
											</div>
											<div class="row">
												<div class="col-sm-2">
													<?php
													echo "<a href='profile.php?user=" .$forsearchuser. "'><img src='$ppic' style='position:relative;width:75px;height:75px;border-radius:50%;border:2px solid #C0C0C0;'></a>";
													?>
												</div>
												<div class="col-sm-10">
												<p style="font-family:times new roman;font-size:1.4em">
													<?php
														echo nl2br($story);	
													?>
												</p>
												<p>
													 <?php 
													 if($photo!="hi")
													 echo "<img src='$photo' style='position:relative;width:95%;height:290px;border:0px solid #C0C0C0;border-radius:4%'>";
													 ?>
												</p>
												<p style="margin-top:<?php if($photo!="hi") echo 6; else echo 14;?>%">
													<div class="col-sm-3">
														<input type="hidden" id="loves-<?php echo $id1; ?>" value="<?php echo $loves; ?>">
														<?php
														   $user2=$user;
															$user2.="like";
															$res=$session->execute(new Cassandra\SimpleStatement("select * from $user2 where id='$id'"));
															$count5=0;
															foreach ($res as $row)
															{
																$count5++;
															}
															$str_love = "love";
															if($count5!=0) {
															$str_love = "unlove";
															}
														?>
														<div class="btn-loves"><input type="button" class="<?php echo $str_love; ?>" onClick="addLoves(<?php echo $id1; ?>,'<?php echo $str_love; ?>')" /></div>
														<div class="label-loves"><?php { echo $loves . " Loves"; } ?></div>
													</div>
													<div class="col-sm-3" id="pro">
													</div>
													<div class="col-sm-3">
													</div>
												</p>
												</div>
											</div>
										</div>
									</div>
										<hr>									
										
										<?php
									}
								}
								}
							?>
							
							
							
							
							
						</div>
					</div>
				</div>
            </div>				
		</div>
		<div class="col-sm-3">
		<p style="font-family:algerian;font-size:2em;margin-left:6%;">Suggestions</p>
<?php
$pfoto="";
$count=0;
$user2=$user;
$user2.="following";
$result=$session->execute(new Cassandra\SimpleStatement("select * from $user2"));
foreach($result as $row)
{
	$count++;
	$friend=$row['tofollow'];
	$result1=$session->execute(new Cassandra\SimpleStatement("select profilepic from users where username='$friend'"));
	foreach($result1 as $row)
	$pfoto=$row['profilepic'];
	$action="follow";
	//$str = substr($followbtn, 0, strpos($followbtn, '@'));
	//echo "$str";
	//$str = substr($followbtn, strpos($followbtn, '@') + 1);
	//echo "$str";
?>
<div id="line<?php echo $friend;?>" style="height:115px;">
	    <div class="col-sm-4">
			<?php
			echo "<img src='$pfoto' style='position:relative;width:75px;height:75px;border-radius:50%;border:2px solid #C0C0C0;'>";
			?>
		</div>
		<div class="col-sm-6">
		    <p style="font-family:times new roman;font-size:1.4em;margin-left:6%;margin-top:2%">
			<?php
			    echo "<a href='profile.php?user=" .$friend. "'>$friend</a>";
			?></p>
		    <p>
				<div class="col-sm-6">
				<input type="submit" value="Follow" style="border-radius: 10px;font-family: 'Pacifico', cursive;font-size: 14px;color: #FFF;text-decoration: none;	background-color: #82BF56;border-bottom: 5px solid #669644;text-shadow: 0px -2px #669644;" onClick="addfollow('<?php echo $friend;?>','<?php echo $action;?>')"> 
				</div>
				<div class="col-sm-6">
				<input type="submit" value="Remove" style="border-radius: 10px;font-family: 'Pacifico', cursive;font-size: 14px;color: #FFF;text-decoration: none;	background-color: orange;border-bottom: 5px solid red;text-shadow: 0px -2px red;" onClick="removefollow('<?php echo $friend;?>')"> 
				</div>
			</p>
		</div>
</div>
<div id="pr">
</div>
<?php
}
if($count==0)
	echo "<div style='margin-left:10%'>No suggestions for you</div>";
?>
		
		</div>
	</body>
</html>
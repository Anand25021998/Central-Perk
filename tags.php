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
hr.style18:before { 
  display: block; 
  content: ""; 
  height: 30px; 
  margin-top: -31px; 
  border-style: solid; 
  border-color: #8c8b8b; 
  border-width: 0 0 1px 0; 
  border-radius: 20px; 
}
hr.style18 { 
  height: 20px; 
  border-style: solid; 
  border-color: #8c8b8b; 
  border-width: 1px 0 0 0; 
  border-radius: 20px; 
} 
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
	
	<body style="background:url('bg.jpg');">
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
	
	
<div class="row">	
	    <div class="col-sm-3">
				<?php
			$result=$session->execute(new Cassandra\SimpleStatement("select * from hash where hmonth=$month limit 20"));?>
			<div style="border:grey solid 2px;border-radius:10px;background-color:#f5f5ff;width:90%;margin-left:5%">
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
		<div class="col-sm-6" >		
				<form method="get" action=" " class="form-horizontal" style="width:60%;margin-left:10%;margin-bottom:6%">
					<div class="input-group">
						<input type="text" class="form-control" placeholder="Search hashtags" name="tag" required="required">
						<div class="input-group-btn">
							<button class="btn btn-default" type="submit" name="tagbtn"><i class="glyphicon glyphicon-search"></i></button>
						</div>
					</div>
				</form>
				<?php
				    $hname1=$_GET['tag'];
					$hname="#";
					$hname.=$hname1;
					$result=$session->execute(new Cassandra\SimpleStatement("select tid from hash where hashname='$hname'"));
					$no=0;
					foreach($result as $row)
					{
						$str=$row['tid'];
						$no++;
					}
					if($no==0)
						echo "No tweets or posts found";
					else
					{
						$strlen=strlen($str);
						$id="";
						for($i=0;$i<$strlen;$i++)
						{
							$char=substr($str,$i,1);
							if($char=='@')
							{
								$start=substr($id,0,1);
								if($start=='t')
								{
									$tweet=$tweeter=$time=$photo=$likes=$retweets=$ppic="";
									$result=$session->execute(new Cassandra\SimpleStatement("select * from tweets where id='$id'"));
									foreach($result as $row)
									{
										$tweet=$row['tweet'];
										$tweeter=$row['tweeter'];
										$time=$row['ttime'];
										$photo=$row['photos'];
									}
									$result=$session->execute(new Cassandra\SimpleStatement("select * from tweetcnts where id='$id'"));
									foreach($result as $row)
									{
										$likes=$row['likes'];
										$retweets=$row['retweets'];
									}
									$result=$session->execute(new Cassandra\SimpleStatement("select profilepic from users where username='$tweeter'"));
									foreach($result as $row)
									{
										$ppic=$row['profilepic'];
									}
									$forsearchuser=$tweeter;
									if($tweeter==$user)
										$tweeter="You";
									    
										$id1=substr($id,1);
										$id1=str_replace("-","",$id1);
										$tmp="1";
										$tmp.=$id1;
										$id1=(int)$tmp;
									?>
									    <div style="margin-left:2%" id="feed-<?php echo $id1; ?>">
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
													<p style="margin-top:<?php if($photo!="hi") echo 6; else echo 12;?>%">
														<div class="col-sm-3">
															<input type="hidden" id="likes-<?php echo $id1; ?>" value="<?php echo $likes; ?>">
															<?php
															   $user2=$user;
																$user2.="like";
																$res=$session->execute(new Cassandra\SimpleStatement("select * from $user2 where id='$id'"));
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
															<div class="btn-likes"><input type="button" class="<?php echo $str_like; ?>" onClick="addLikes(<?php echo $id1; ?>,'<?php echo $str_like; ?>')" /></div>
															<div class="label-likes"><?php { echo $likes . " Likes"; } ?></div>
														</div>
														<div class="col-sm-3">
															<input type="hidden" id="rtweet-<?php echo $id1; ?>" value="<?php echo $retweets; ?>">
																<?php
																	$user3=$user;
																	$user3.="tweet";
																	$tmp="r";
																	$tmp.=$id;
																	$res=$session->execute(new Cassandra\SimpleStatement("select self from $user3 where id='$tmp'"));
																	$s=0;
																	foreach($res as $row)
																	$s=$row['self'];//the row itself maynot be in table or the self value may be 0.
																	$str_rt="rtweet";
																	if($s!=0)
																		$str_rt="nrtweet";
																?>
															<div class="btn-rtweet"><input type="button" class="<?php echo $str_rt; ?>" onClick="addrtweet(<?php echo $id1; ?>,'<?php echo $str_rt; ?>')" /></div>
															<div class="label-rtweet"><?php { echo $retweets . " Retweets"; } ?></div>
														</div>
														<div class="col-sm-3" id="pr">
														</div>
													</p>
												</div>
											</div>
										</div>
											<hr class="style18" style="width:95%">
									<?php
								}
								else
								{
									$story=$postedby=$stime=$photo=$loves=$ppic="";
									$result=$session->execute(new Cassandra\SimpleStatement("select * from stories where sid='$id'"));
									foreach($result as $row)
									{
										$story=$row['story'];
										$postedby=$row['postedby'];
										$stime=$row['stime'];
										$photo=$row['photos'];
									}
									$result=$session->execute(new Cassandra\SimpleStatement("select * from storiescnt where sid='$id'"));
									foreach($result as $row)
									{
										$loves=$row['loves'];
									}
									$result=$session->execute(new Cassandra\SimpleStatement("select profilepic from users where username='$postedby'"));
									foreach($result as $row)
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
								    <p style="margin-top:<?php if($photo!="hi") echo 6; else echo 12;?>%">
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
									<hr class="style18" style="width:95%">
									
									<?php
								}
								$id="";
							}
							else
								$id.=$char;
						}
					}
				?>
		</div>
		<div class="col-sm-3" style="padding-left:0px;">
		<p style="font-family:algerian;font-size:2em;margin-left:6%;">Suggestions</p>
<?php
$pfoto="";
$user2=$user;
$user2.="following";
$result=$session->execute(new Cassandra\SimpleStatement("select * from $user2"));
$count=0;
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
				<input type="submit" value="Follow" style="border-radius: 10px;font-family: 'Pacifico', cursive;font-size: 14px;color: #FFF;text-decoration: none;	background-color: #82BF56;border-bottom: 5px solid #669644;text-shadow: 0px -2px #669644;" onClick="addfollow('<?php echo $friend;?>','<?php echo $action?>')">
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
</div>
	</body>
</html>
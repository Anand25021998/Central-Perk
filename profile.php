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

$search=$_GET['user'];
$result=$session->execute(new Cassandra\SimpleStatement("select * from users where username='$search'"));
$cnt=0;
foreach($result as $row)
{
	$cnt++;
}
if($cnt==0)
{
	        echo "<script type='text/javascript'>alert('User doesnot exists')</script>";
				echo "<script language='JavaScript' type='text/JavaScript'>
            <!--
                 window.location='home.php';
            //-->
             </script> ";
}
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
//if(array_key_exists("raja",$folarr))
//	echo "yes";

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
    $uploadok1=0;
	$message1="";
   if(isset($_FILES['image1'])){
	  $temp1=1;
	  $target_dir = "images/";
      $file_name = $_FILES['image1']['name'];//name of the image1 uploaded
	  $target_file1 = $target_dir . $_FILES["image1"]["name"];//target file is the full location of the image1 on the server
      $file_size =$_FILES['image1']['size'];
      $file_tmp =$_FILES['image1']['tmp_name'];//it  will contain the temporary file name of the file on the server. This is just a placeholder on your server until you process file
      $file_type=$_FILES['image1']['type'];
	  $anand = explode('.', $file_name);
      $file_ext=strtolower(end($anand));
      
      $expensions= array("jpeg","jpg","png");
      
      if(in_array($file_ext,$expensions)=== false){
        $message1="extension not allowed, please choose a JPEG or PNG file";
		 $temp1=0;
      }
      
      if($file_size > 2097152){
         $message1="File size must be less than 2 MB";
		 $temp1=0;
      }
      
	  if($temp1==1)
		  $uploadok1=1;
	  
      if($uploadok1){
		  $result1=$session->execute(new Cassandra\SimpleStatement("select * from users where username='$user'"));
		  foreach($result1 as $row)
		  {
			  $email=$row['email'];
			  $password=$row['password'];
		  }
		  $result = $session->execute(new Cassandra\SimpleStatement
          ("update users set coverpic='$target_file1' where username='$user' and email='$email' and password='$password'"));
         move_uploaded_file($file_tmp,"images/".$file_name);
      }
	  else
		  echo "<script type='text/javascript'>alert('Coverpic couldnot be updated as $message1')</script>"; 
   }
?>

<?php
    $uploadok2=0;
	$message2="";
   if(isset($_FILES['image2'])){
      $temp2=1;
	  $target_dir = "images/";
      $file_name = $_FILES['image2']['name'];//name of the image2 uploaded
	  $target_file2 = $target_dir . $_FILES["image2"]["name"];//target file is the full location of the image2 on the server
      $file_size =$_FILES['image2']['size'];
      $file_tmp =$_FILES['image2']['tmp_name'];//it  will contain the temporary file name of the file on the server. This is just a placeholder on your server until you process file
      $file_type=$_FILES['image2']['type'];
	  $anand = explode('.', $file_name);
      $file_ext=strtolower(end($anand));
      
      $expensions= array("jpeg","jpg","png");
      
      if(in_array($file_ext,$expensions)=== false){
          $message2="extension not allowed, please choose a JPEG or PNG file";
		 $temp2=0;
      }
      
      if($file_size > 2097152){
        $message2="File size must be excately 2 MB";
		 $temp2=0;
      }
	  if($temp2==1)
		  $uploadok2=1;
	  
      if($uploadok2){
		  $result1=$session->execute(new Cassandra\SimpleStatement("select * from users where username='$user'"));
		  foreach($result1 as $row)
		  {
			  $email=$row['email'];
			  $password=$row['password'];
		  }
		  $result = $session->execute(new Cassandra\SimpleStatement
          ("update users set profilepic='$target_file2' where username='$user' and email='$email' and password='$password'"));
         move_uploaded_file($file_tmp,"images/".$file_name);
      }
	  else
		  echo "<script type='text/javascript'>alert('Profilepic couldnot be updated as $message2')</script>"; 
   }
?>
<?php
$spic=$cpic="";
$result=$session->execute(new Cassandra\SimpleStatement("select profilepic, coverpic,joindate from users where username='$search'"));
foreach($result as $row)
{
	$spic=$row['profilepic'];
	$cpic=$row['coverpic'];
	$join=$row['joindate'];
}
$result=$session->execute(new Cassandra\SimpleStatement("select * from userscnt where username='$search'"));
foreach($result as $row)
{
	$followers=$row['followers'];
	$following=$row['following'];
	$retweets=$row['retweets'];
	$tweets=$row['tweets'];
	$stories=$row['stories'];
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

.follow{border-radius: 10px;font-family: 'Pacifico', cursive;font-size: 18px;color: #FFF;text-decoration: none;	background-color: #82BF56;border-bottom: 5px solid #669644;text-shadow: 0px -2px #669644;}
.unfollow{border-radius: 10px;font-family: 'Pacifico', cursive;font-size: 18px;color: #FFF;text-decoration: none;	background-color: orange;border-bottom: 5px solid red;text-shadow: 0px -2px red;}
</style>
        <title>Login and Registration Form with HTML5 and CSS3</title>
		<link rel="stylesheet" type="text/css" href="css/profile.css" />
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
function addfollow1(friend,action)	
{
	$.ajax({
		url:"follow.php",
		data:'friend='+friend+'&action='+action,
		type:"POST",
		success:function(data){
			switch(action){
				case "follow":
				$("#follow1-"+friend).replaceWith('<input type="submit" id="follow1-'+friend+'" class="unfollow" value="unfollow" onClick="addfollow1(\''+friend+'\',\'unfollow\')"/>');
				$("#raul").html(data);
				break;
				case "unfollow":
				$("#follow1-"+friend).replaceWith('<input type="submit" id="follow1-'+friend+'" class="follow" value="follow" onClick="addfollow1(\''+friend+'\',\'follow\')"/>');
				$("#raul").html(data);
				break;
			}
		}
	});
}
</script>
<script>
function addfollow2(friend,action)	
{
	$.ajax({
		url:"follow.php",
		data:'friend='+friend+'&action='+action,
		type:"POST",
		success:function(data){
			switch(action){
				case "follow":
				$("#follow2-"+friend).replaceWith('<input type="submit" id="follow2-'+friend+'" class="unfollow" value="unfollow" onClick="addfollow2(\''+friend+'\',\'unfollow\')"/>');
				$("#raul").html(data);
				break;
				case "unfollow":
				$("#follow2-"+friend).replaceWith('<input type="submit" id="follow2-'+friend+'" class="follow" value="follow" onClick="addfollow2(\''+friend+'\',\'follow\')"/>');
				$("#raul").html(data);
				break;
			}
		}
	});
}
</script>
<script>
function addfollow3(friend,action)	
{
	$.ajax({
		url:"follow.php",
		data:'friend='+friend+'&action='+action,
		type:"POST",
		success:function(data){
			switch(action){
				case "follow":
				$("#follow3-"+friend).replaceWith('<input type="submit" id="follow3-'+friend+'" class="unfollow" value="unfollow" onClick="addfollow3(\''+friend+'\',\'unfollow\')"/>');
				$("#raul").html(data);
				break;
				case "unfollow":
				$("#follow3-"+friend).replaceWith('<input type="submit" id="follow3-'+friend+'" class="follow" value="follow" onClick="addfollow3(\''+friend+'\',\'follow\')"/>');
				$("#raul").html(data);
				break;
			}
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
	
	
<div class="row">	
	<div class="col-sm-5">
	    <?php
		echo "<div class='col-sm-12'>";
		    echo "<img src='$cpic' style='position:relative;width:90%;border-radius:15px;border:4px solid white;'>";
			echo "<img src='$spic' style='position:absolute;left:6%;bottom:5%;width:134px;height:134px;border:3px solid white;border-radius:10px;'>";
		echo "</div>";
		?>
<?php if($search==$user)
{
	?>
		<div class="row" style="margin-left:2%;margin-bottom:2%">
			<div class="col-sm-6">
				<p>
					<form action="" method="POST" enctype="multipart/form-data" class="coverform">
						<input type="file" name="image1" class="coverpic"/>
						<br>
						<input type="submit" value="update coverpic"class="coversubmit"/>
					</form>
				</p>
			</div>
			<div class="col-sm-6">
				<p>
					<form action="" method="POST" enctype="multipart/form-data">
						<input type="file" name="image2" class="profilepic" />
						<br>
						<input type="submit" value="update profilepic" class="profilesubmit"/>
					</form>
				</p>
			</div>
		</div>
<?php
}
else
{
	$action="follow";
	if(array_key_exists($search,$folarr))
	$action="unfollow";
	$friend=$search;
?>
<div class="col-sm-12">
    <input type="submit" id="follow3-<?php echo $friend;?>" class="<?php echo $action;?>" value="<?php echo $action?>" onClick="addfollow3('<?php echo $friend;?>','<?php echo $action;?>')"> 
</div>
<?php
}
?>
<div class="col-sm-12" id="raul">
</div>
		<p style="font-family:algerian;font-size:2.5em;margin-left:6%;margin-top:2%"><?php echo "$search";?></p>
		<ul class="info" style="margin-left:5%;font-family:monotype corsiva;font-size:1.3em">
		<li>Joined on <?php echo "$join";?></li>
		<li><?php echo "$tweets";?> Tweets, <?php echo "$retweets";?> Retweets and <?php echo "$stories";?> Stories</li>
		<li>Following <?php echo "$following";?> users</li>
		<li>Followed by <?php echo "$followers";?> users</li>
		</ul>
		<hr>
		<p style="font-family:algerian;font-size:2.5em;margin-left:6%;margin-top:2%">Followers</p>
<?php
$result=$session->execute(new Cassandra\SimpleStatement("select * from followers where user='$search'"));
$cntf1=$cntf2=0;
foreach($result as $row)
{
	$followed=$row['followed'];
}
	$len=strlen($followed);
	$name="";
	for($i=0;$i<$len;$i++)
	{
		$char=substr($followed,$i,1);
		if($char=='%')
		{
			if($name!=$search)
			{
				$cntf1++;
				$result1=$session->execute(new Cassandra\SimpleStatement("select * from users where username='$name'"));
				foreach($result1 as $row)
				{
					$join=$row['joindate'];
					$pphoto=$row['profilepic'];
					$cphoto=$row['coverpic'];
				}
				$result2=$session->execute(new Cassandra\SimpleStatement("select * from userscnt where username='$name'"));
				foreach($result2 as $row)
				{
					$followers=$row['followers'];
					$following=$row['following'];
					$tweets=$row['tweets'];
					$stories=$row['stories'];
				}
				?>
				<div class="col-sm-6" style="border:grey solid 2px;border-radius:10px;background-color:#f5f5ff;margin-bottom:1%">
				    <p>
				    <?php
					echo "<img src='$pphoto' style='position:relative;right:7%;width:114%;height:120px;border-radius:10px;border:grey solid 2px;'>";?>
					</p>
					<div class="row">
						<div class="col-sm-6">
						<div style="font-size:1.7em;font-family:algerian"><?php echo "<a href='profile.php?user=" .$name . "'>$name</a>";?> </div>
						</div>
						<div class="col-sm-6">
						<?php
						    if($name!=$user)
							{
									$action="follow";
									if(array_key_exists($name,$folarr))
									$action="unfollow";
									$friend=$name;?>
								<input type="submit" id="follow1-<?php echo $name;?>" class="<?php echo $action;?>" value="<?php echo $action?>" onClick="addfollow1('<?php echo $name;?>','<?php echo $action;?>')"> 	
						<?php	}
						?>
						</div>
					</div>
					<div class="row" >
					    <div class="col-sm-3">
						    <p style="font-family:monotype corsiva;font-size:1.5em"><b><?php echo "$followers";?></b></p>
							<p style="font-family:monotype corsiva;">Followers</p>
						</div>
					    <div class="col-sm-3">
						    <p style="font-family:monotype corsiva;font-size:1.5em"><b><?php echo "$following";?></b></p>
							<p style="font-family:monotype corsiva">Following</p>
						</div>
					    <div class="col-sm-3">
						    <p style="font-family:monotype corsiva;font-size:1.5em"><b><?php echo "$tweets";?></b></p>
							<p style="font-family:monotype corsiva">Tweets</p>
						</div>
					    <div class="col-sm-3">
						    <p style="font-family:monotype corsiva;font-size:1.5em"><b><?php echo "$stories";?></b></p>
							<p style="font-family:monotype corsiva">Stories</p>
						</div>
					</div>
				</div>
				
				<?php
			}
			$name="";
		}
		else
			$name.=$char;
		
	}
if($cntf1==0)
	echo "<div style='margin-left:40%'>No Followers to show...</div>";
if($cntf1%2)
{
	echo "<p><div class='col-sm-12'></div></p>";
}
?>

<div class="col-sm-12"><p style="font-family:algerian;font-size:2.5em;margin-left:3%;margin-top:10%">Following</p></div>
<?php
foreach($result as $row)
{
	$following9=$row['following'];
}

	$len=strlen($following9);
	$name="";
	for($i=0;$i<$len;$i++)
	{
		$char=substr($following9,$i,1);
		if($char=='%')
		{
				$cntf2++;
				$result1=$session->execute(new Cassandra\SimpleStatement("select * from users where username='$name'"));
				foreach($result1 as $row)
				{
					$join=$row['joindate'];
					$pphoto=$row['profilepic'];
					$cphoto=$row['coverpic'];
				}
				$result2=$session->execute(new Cassandra\SimpleStatement("select * from userscnt where username='$name'"));
				foreach($result2 as $row)
				{
					$followers=$row['followers'];
					$following=$row['following'];
					$tweets=$row['tweets'];
					$stories=$row['stories'];
				}
				?>
				<div class="col-sm-6" style="border:grey solid 2px;border-radius:10px;background-color:#f5f5ff;margin-bottom:1%">
				    <p>
				    <?php
					echo "<img src='$pphoto' style='position:relative;right:7%;width:114%;height:120px;border-radius:10px;border:grey solid 2px;'>";?>
					</p>
					<div class="row">
						<div class="col-sm-6">
						<div style="font-size:1.7em;font-family:algerian"><?php echo "<a href='profile.php?user=" .$name . "'>$name</a>";?> </div>
						</div>
						<div class="col-sm-6">
							<?php
						    if($name!=$user)
							{
									$action="follow";
									if(array_key_exists($name,$folarr))
									$action="unfollow";
									$friend=$name;?>
								<input type="submit" id="follow2-<?php echo $name;?>" class="<?php echo $action;?>" value="<?php echo $action?>" onClick="addfollow2('<?php echo $name;?>','<?php echo $action;?>')"> 	
						<?php	}
						?>
						</div>
					</div>
					<div class="row" >
					    <div class="col-sm-3">
						    <p style="font-family:monotype corsiva;font-size:1.5em"><b><?php echo "$followers";?></b></p>
							<p style="font-family:monotype corsiva;">Followers</p>
						</div>
					    <div class="col-sm-3">
						    <p style="font-family:monotype corsiva;font-size:1.5em"><b><?php echo "$following";?></b></p>
							<p style="font-family:monotype corsiva">Following</p>
						</div>
					    <div class="col-sm-3">
						    <p style="font-family:monotype corsiva;font-size:1.5em"><b><?php echo "$tweets";?></b></p>
							<p style="font-family:monotype corsiva">Tweets</p>
						</div>
					    <div class="col-sm-3">
						    <p style="font-family:monotype corsiva;font-size:1.5em"><b><?php echo "$stories";?></b></p>
							<p style="font-family:monotype corsiva">Stories</p>
						</div>
					</div>
				</div>
				
				<?php
			$name="";
		}
		else
			$name.=$char;
		
	}
if($cntf2==0)
	echo "<div class='col-sm-12' style='margin-left:40%'>Not Following any user...</div>";
?>
		
	</div>
	<div class="col-sm-6" style="">
			
            <div class="panel with-nav-tabs panel-default">
                <div class="panel-heading">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab1default" data-toggle="tab">Tweets</a></li>
                            <li><a href="#tab2default" data-toggle="tab">Posts</a></li>
							<li><a href="#tab3default" data-toggle="tab">Liked feeds</a></li>
                        </ul>
                </div>			
				
                <div class="panel-body">
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tab1default">
							<?php
							    $user1=$search;
								$user1.="tweet";
								$result=$session->execute(new Cassandra\SimpleStatement("select * from $user1 where self=1"));
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
										$ppic=$spic;
										
										if($tweeter==$user)
										$tweeter="You";
									}
									if($start=='r')
									{
										$pri=$search;
										if($search==$user)
											$pri="You";
										echo "<div style='font-family:monotype corsiva;font-size:1.3em;margin-bottom:2%'><u><b>$pri retweeted this tweet----</b></u></div>";
										$res=$session->execute(new Cassandra\SimpleStatement("select tweeter from tweets where id='$id'"));
										foreach($res as $row)
										$tw=$row['tweeter'];
										$res=$session->execute(new Cassandra\SimpleStatement("select profilepic from users where username='$tw'"));
										foreach($res as $row)
										$ppic=$row['profilepic'];
										//hm searched user ka profile pic bs ek baar query kiye kyonki sb tweet usi ka hai. but agar usne kuch retweet kiya hai to ppic yaha pe jisne 
										//originally tweet kiya hai uska kar dena
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
												echo "<img src='$ppic' style='position:relative;width:75px;height:75px;border-radius:50%;border:2px solid #C0C0C0;'>";
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
							
							<?php
							$cnt=count($marr);
								for($i=0;$i<$cnt;$i++)
								{
                                    $mt=$marr[$i];
                                    $cnt1=count($store[$mt]);
								for($j=0;$j<$cnt1;$j++)
								{
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
										$ppic=$spic;
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
													echo "<img src='$ppic' style='position:relative;width:75px;height:75px;border-radius:50%;border:2px solid #C0C0C0;'>";
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
														   $user2=$user;//according to jisne visit kiya hai page ko
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
						<div class="tab-pane fade" id="tab3default">
					         <?php
							    $user1=$search;
								$user1.="like";
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
									$id=$store[$mt][$j]['id'];
									$liketime=$store[$mt][$j]['ltime'];
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
										
										if($tweeter==$user)
											$tweeter="You";
										
											$id1=substr($id,1);//ab id ka koi kaam nahi so we will be using it directly for like system purpose
											$id1=str_replace("-","",$id1);
											$tmp="1";
											$tmp.=$id1;
											$id1=(int)$tmp;
											
											$liker=$search;
											if($search==$user)
												$liker="you";
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
														echo "<img src='$ppic' style='position:relative;width:75px;height:75px;border-radius:50%;border:2px solid #C0C0C0;'>";
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
															<div class="col-sm-4">
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
															<div class="col-sm-8">
															<p style="font-family:monotype corsiva;font-size:1.2em">Liked by <?php echo $liker;?> on <?php echo $liketime;?></p>
															</div>
														</p>
													</div>
												</div>
											</div>
												<hr>
										<?php
									}
									else if($start=='s')
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
										if($postedby==$user)
											$postedby="You";
										
										$id1=substr($id,1);
										$id1=str_replace("-","",$id1);
										$tmp="2";
										$tmp.=$id1;
										$id1=(int)$tmp;
										
										    $liker=$search;
											if($search==$user)
												$liker="you";
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
												echo "<img src='$ppic' style='position:relative;width:75px;height:75px;border-radius:50%;border:2px solid #C0C0C0;'>";
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
											<div class="col-sm-4">
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
											<div class="col-sm-8">
											     <p style="font-family:monotype corsiva;font-size:1.2em">Liked by <?php echo $liker;?> on <?php echo $liketime;?></p>
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
					</div>
				</div>
            </div>				
		</div>
</div>
<section class="section footer" style="background-color: black;margin-top:2%">
<div style="color:white;font-family:monotype corsiva;font-size:1.4em;text-align:center">Copyright @Anand</div>
</section>
	</body>
</html>
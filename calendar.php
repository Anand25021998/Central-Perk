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
$year=substr($imp,0,-2);
$year=(int)$year;
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
	if(isset($_POST['cancel']))
	{
		$name=$_POST['name'];
		$result=$session->execute(new Cassandra\SimpleStatement("select * from events where ename='$name'"));
		$cnt=0;
		foreach($result as $row)
		{
			$time=$row['etime'];
			$y=$row['year'];
			$organiser=$row['organiser'];
			$cnt++;
		}
		if($cnt==0)
		echo "<script type='text/javascript'>alert('No event scheduled with the name you entered')</script>";
        else
		{
			if($organiser!=$user)
				echo "<script type='text/javascript'>alert('Sorry!! You donot have the proper authorization to delete this event')</script>";
            else
			{
					    $statement=$session->execute(new Cassandra\SimpleStatement("delete from events where year=$y and etime='$time'"));
						$statement=$session->execute(new Cassandra\SimpleStatement("delete from eventcnts where name='$name'"));
				        echo "<script type='text/javascript'>alert('The event named $name has been sucessfully cancelled')</script>";
			}				
		}			
	}
}
?>
<?php
if($_SERVER["REQUEST_METHOD"]=="POST")
{
	if(isset($_POST['event']))
	{
		$ename=$_POST["name"];
		$result=$session->execute(new Cassandra\SimpleStatement("select * from events where ename='$ename'"));
		$cnt=0;
		foreach($result as $row)
		$cnt++;
		if($cnt!=0)
			echo "<script type='text/javascript'>alert('You gotta take the event name seriously')</script>";
		else
		{
			$etime=$_POST["eventdate"];
			$eplace=$_POST["place"];
			$edesc=$_POST["description"];
			$var=substr($etime,-8);
			$etime=substr($etime,0,-8);
			$var1=substr($var,-2);
			$var=substr($var,0,-3);//remove PM from var
			
			if($var1=="PM")
			{
				$var2=substr($var,0,2);
				$var2+=12;
				$var=substr($var,2);
				$var2.=$var;
				$etime.=$var2;
			}
			else
			{
				$etime.=$var;
			}
			$temp1=$yes=1;
			$message="";
			$photo="hi";
			  if(isset($_FILES['eventimage'])){
			  $target_dir = "images/";
			  $file_name = $_FILES['eventimage']['name'];//name of the userImage uploaded
			  $target_file1 = $target_dir . $_FILES["eventimage"]["name"];//target file is the full location of the userImage on the server
			  $file_size =$_FILES['eventimage']['size'];
			  $file_tmp =$_FILES['eventimage']['tmp_name'];//it  will contain the temporary file name of the file on the server. This is just a placeholder on your server until you process file
			  $file_type=$_FILES['eventimage']['type'];
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
			$statement=$session->execute(new Cassandra\SimpleStatement("insert into events(year,etime,edesc,eimage,ename,eplace,organiser) values($year,'$etime','$edesc','$photo','$ename','$eplace','$user')"));
			$statement=$session->execute(new Cassandra\SimpleStatement("update eventcnts set likes=likes+0 where name='$ename'"));
						if($temp1||!$yes)
					echo "<script type='text/javascript'>alert('Your Event has been created sucessfully')</script>"; 
				if(!$temp1&&$yes)
					echo "<script type='text/javascript'>alert('Event created but poster could not be uploaded as $message1')</script>"; 
		}
}
}
?>
<html>
    <head>
	
<style>
.btn-likes input[type="button"]{width:32px;height:32px;border:0;cursor:pointer;}
.like {background:url('icons/3.png')}
.unlike {background:url('icons/4.png')}
.label-likes {font-size:12px;color:#2F529B;height:20px;}
.eventbtn{border-radius: 10px;font-family: 'Pacifico', cursive;font-size: 18px;color: #FFF;text-decoration: none;	background-color: #82BF56;border-bottom: 5px solid #669644;text-shadow: 0px -2px #669644;}
.cancelbtn{border-radius: 10px;font-family: 'Pacifico', cursive;font-size: 18px;color: #FFF;text-decoration: none;	background-color: orange;border-bottom: 5px solid red;text-shadow: 0px -2px red;}
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
			<link href="css/bootstrap-datetimepicker.css" rel="stylesheet">
			<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet">
			<link href="css/bootstrap-datetimepicker-standalone.css" rel="stylesheet">
		  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		  <script src="js/home.js"></script>
		  <script src="js/moment.js"></script>
		  <script src="js/bootstrap-datetimepicker.min.js"></script>
		  
<script>  
  $(function () {
	$('#datetimepicker1').datetimepicker();
});
</script>
  <script>
function addLikes(id,action) {
	$.ajax({
	url: "likes.php",
	data:'id='+id+'&action='+action,
	type: "POST",
	beforeSend: function(){
		$('#even-'+id+' .btn-likes').html("<img src='LoaderIcon1.gif' />");
	},
	success: function(data){
	var likes = parseInt($('#likes-'+id).val());
	switch(action) {
		case "like":
		$('#even-'+id+' .btn-likes').html('<input type="button"  class="unlike" onClick="addLikes(\''+id+'\',\'unlike\')" />');
		likes = likes+1;
		break;
		case "unlike":
		$('#even-'+id+' .btn-likes').html('<input type="button" class="like"  onClick="addLikes(\''+id+'\',\'like\')" />')
		likes = likes-1;
		break;
	}
	$('#likes-'+id).val(likes);
		$('#even-'+id+' .label-likes').html(likes+" Persons Interested");
	}
	});
}
</script>
<script>
function checkAvailability() {
	$("#loaderIcon").show();
	jQuery.ajax({
	url: "check_event.php",
	data:'eventname='+$("#name").val(),
	type: "POST",
	success:function(data){
		$("#event-availability-status").html(data);
		$("#loaderIcon").hide();
	},
	error:function (){}
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
	
	
	    <div class="col-sm-3" style="margin-left:2%">
			<p style="font-family:algerian;font-size:2.2em;margin-bottom:6%">Create Event</p>	
				<form  method="post" action="" enctype="multipart/form-data" class="form-horizontal">
					<p> 
						<input type="text" class="form-control" placeholder="Eventname" style="width:80%" id="name" name="name" required="required" onBlur="checkAvailability()"/>
						<span id="event-availability-status"></span>
					</p>
					<p><img src="LoaderIcon.gif" id="loaderIcon" style="display:none" /></p>
					<p> 
					    <div class='input-group' id='datetimepicker1'>
                            <input type='text' class="form-control" placeholder="Date & Time" name="eventdate" style="width:80%" required/>
                            <span style="position:relative;right:19%"class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                            </span>
					    </div>
					</p>
					
					<p> 
						<input name="place" class="form-control" required="required" type="text" style="width:80%"placeholder="Place" /> 
					</p>
					
					<p>
					    <textarea class="statusbox form-control tbox" rows="3" placeholder="Description of the event....." name="description" required="required" style="width:80%"></textarea>
					</p>
					<p style="font-family:monotype corsiva;font-size:1.2em">Upload Poster(optional)</p>
					<p>
						<input name="eventimage" type="file" class="inputFile" /><br/>
					</p>
					<p> 
						<input type="submit" class="eventbtn" value="Create Event" name="event"> 
					</p>
				</form>
		</div>
		
		<div class="col-sm-5" style="margin-left:4%;margin-right:2%">	
		<p style="font-family:algerian;font-size:2.2em;margin-bottom:3%;text-align:center"><u>Upcoming Events</u></p>	
<?php
$date1 = date('m/d/Y H:i');
$result=$session->execute(new Cassandra\SimpleStatement("select * from events where year=$year"));
$count=0;
foreach($result as $row)
{
	$count++;
	$etime=$row['etime'];
	$description=$row['edesc'];
	$ename=$row['ename'];
	$eplace=$row['eplace'];
	$organiser=$row['organiser'];
	$eimage=$row['eimage'];
	$result1=$session->execute(new Cassandra\SimpleStatement("select profilepic from users where username='$organiser'"));
	foreach($result1 as $row)
	$ppic=$row['profilepic'];
	$result1=$session->execute(new Cassandra\SimpleStatement("select * from eventcnts where name='$ename'"));
	foreach($result1 as $row)
	$likes=$row['likes'];
	$id1="3";
	$id1.=$ename;//using the event name as the id
	
if($etime>$date1)
{
?>
        <div id="even-<?php echo $id1;?>">
            <div class="col-sm-12">
			    <p style="text-align:center;font-size:2.2em;font-family:times new roman"><?php echo $ename;?></p>
			</div>
			<div class="col-sm-12">
			    <p style="text-align:center;font-family:monotype corsiva;font-size:1.4em"><i><u>Date of Event:</u></i>  <b> <?php echo $etime?></b></p>
			</div>
			<div class="col-sm-12">
			    <p style="text-align:center;font-family:monotype corsiva;font-size:1.4em"><i><u>Venue:</u></i> <b><?php echo $eplace?></b></p>
			</div>
			
			<div class="col-sm-12" style="margin-bottom:4%">
				 <?php 
				 if($eimage!="hi")
				 echo "<img src='$eimage' style='position:relative;width:95%;border:0px solid #C0C0C0;border-radius:4%'>";
				 ?>
			</div>
			<div class="col-sm-12">
			    <p style="font-family:monotype corsiva;font-size:1.5em"> <?php echo nl2br($description);?></p>
			</div>
			<div class="col-sm-12">
			    <div class="col-sm-5" style="margin-top:3%">
				    <input type="hidden" id="likes-<?php echo $id1; ?>" value="<?php echo $likes; ?>">
						<?php
						   $user2=$user;
							$user2.="like";
							$res=$session->execute(new Cassandra\SimpleStatement("select * from $user2 where id='$ename'"));
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
						<div class="btn-likes"><input type="button" class="<?php echo $str_like; ?>" onClick="addLikes('<?php echo $id1; ?>','<?php echo $str_like; ?>')" /></div>
						<div class="label-likes"><?php { echo $likes . " Persons Interested"; } ?></div>
				    <div id="pr">
					</div>
				</div>
				<div class="col-sm-7">
				    <div class="col-sm-6">
					<p style="font-family:monotype corsiva; font-size:1.6em">Organised by:<p>
					</div>
					<div class="col-sm-6">
					<?php
						echo "<a href='profile.php?user=" .$organiser. "'><img src='$ppic' style='position:relative;width:75px;height:75px;border-radius:50%;border:2px solid #C0C0C0;'></a>";
						?>
						<br>
					<p style="position:relative;left:15%"><?php echo $organiser;?></p>
					</div>
				</div>
			</div>
		</div>
		<hr class="style18" style="width:100%">
<?php
}
}
if($count==0)
	echo "No Events scheduled in the near future."
?>
		</div>
		<div class="col-sm-3">
		    	<p style="font-family:algerian;font-size:2.2em;margin-bottom:3%;">Cancel Event</p>		
				<form  method="post" action="" enctype="multipart/form-data" class="form-horizontal">
				    <p> 
						<input type="text" class="form-control" placeholder="Eventname" name="name" style="width:80%;margin-bottom:7%"required="required"/>
					</p>
					<p>
					    <input type="submit" class="cancelbtn" value="Cancel Event" name="cancel"/> 
					</p>
				</form>
		</div>
	</body>
</html>
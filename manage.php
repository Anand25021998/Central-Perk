# Code to let a user sign in or sign up and see his/her homepage.
<?php
$cluster  = Cassandra::cluster()
                ->build();
$keyspace  = 'twitter';
$session  = $cluster->connect($keyspace);
$uploadok1=$uploadok2=0;
$message1=$message2=$complete=$see="";
date_default_timezone_set('Asia/Kolkata');
if(isset($_COOKIE['username']))
{
		$user=$_COOKIE["username"];
		$email=$pass="";
		$result = $session->execute(new Cassandra\SimpleStatement
		("SELECT email, password FROM twitter.users
		 WHERE username = '$user'"));
		 foreach ($result as $row) {
			 $email=$row['email'];
			 $pass=$row['password'];
		 }
	  	$cookie_name = "done1";
		if((isset($_COOKIE['done1']))&&($_COOKIE['done1']==1))
		{
			
		}
		else
		{
			$cookie_value = 0;
			setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
		}
	    $cookie_name = "done2";
		if((isset($_COOKIE['done2']))&&($_COOKIE['done2']==1))
		{
			
		}
		else
		{
			$cookie_value = 0;
			setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
		}
}
?>
<?php
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
         $message1="File size must be excately 2 MB";
		 $temp1=0;
      }
      
	  if($temp1==1)
		  $uploadok1=1;
	  
	  	$cookie_name = "done1";//cookie to see if at any instant pics have been uploaded as cover pic similarly for the profile pic also.
        $cookie_value = $uploadok1;
        setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
	  
      if($uploadok1){
		  $result = $session->execute(new Cassandra\SimpleStatement
          ("update users set coverpic='$target_file1' where username='$user' and password='$pass' and email='$email'"));
         move_uploaded_file($file_tmp,"images/".$file_name);
         $message1="Cover pic sucessfully uploaded";
      }
   }
?>

<?php
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

	  	$cookie_name = "done2";
        $cookie_value = $uploadok2;
        setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
		
      if($uploadok2){
		  $result = $session->execute(new Cassandra\SimpleStatement
          ("update users set profilepic='$target_file2' where username='$user' and password='$pass' and email='$email'"));
         move_uploaded_file($file_tmp,"images/".$file_name);
         $message2="Profile pic sucessfully uploaded";
      }
   }
?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST")//completing the registration
{
	if(isset($_POST['button1']))
	{
		if((isset($_COOKIE['done1']))&&(isset($_COOKIE['done2'])))
		{
			$val1=$_COOKIE['done1'];
			$val2=$_COOKIE['done2'];
			if($val1==1&&$val2==1)
			{
				$t=$r=$f=$l=$user;//t will store name of tweet table, r will store name of retweet table, f will store name of following table and l will store name of like table.
				$t.="tweet";
				$r.="retweet";
				$f.="following";
				$l.="like";
				$user1=$user;
				$user1.="%";
				$statement=$session->execute(new Cassandra\SimpleStatement("insert into followers(user,followed) values ('$user','$user1')"));
				$statement=$session->execute(new Cassandra\SimpleStatement("create table $l(month int,ltime text,id text,primary key(month,ltime)) with clustering order by(ltime desc)"));
				$statement=$session->execute(new Cassandra\SimpleStatement("create index on $l(id)"));
				$statement=$session->execute(new Cassandra\SimpleStatement("create table $t(month int,id text,ttime text,self int,primary key(month,ttime,id)) with clustering 
				order by (ttime desc)"));
				
				$statement=$session->execute(new Cassandra\SimpleStatement("create index on $t(self)"));
				$statement=$session->execute(new Cassandra\SimpleStatement("create index on $t(id)"));
				echo "<script type='text/javascript'>alert('Congratulations!!!You have completed both stage of registration. Log in to continue')</script>"; 
				echo "<script language='JavaScript' type='text/JavaScript'>
                <!--
                window.location='index.php';
                //-->
                </script> ";
			}
			else
				$complete="Insert the pics in correct format"; 
		}
		else
			$complete="Insert the pics";
	}
}
?>


<html>
    <head>
        <title>Login and Registration Form with HTML5 and CSS3</title>
		<link rel="stylesheet" type="text/css" href="css/manage.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
    </head>
    <body style="background:url('bg.jpg')">
		<nav class="navbar" role="navigation" style="min-height:52px;margin-bottom:1%;background-color: #333;">
	<div style="padding-top:10px;text-align:center">
	    <p style="color:white;font-family:Monotype Corsiva;font-size:1.8em;text-align:center">Welcome To Central Perk</p>
	</div>
	</nav>
<?php
		 if($_COOKIE['kindofpic']==1)
		 {
			 printf ('<div style="color:rgb(6, 106, 117);text-align:center;font-family:Monotype Corsiva;font-size:20px;">Hello %s !!! You have sucessfully registered. get started below </div>',$user);
		 }
		else
		{
			printf ('<div style="color:rgb(6, 106, 117);text-align:center;font-family:Monotype Corsiva;font-size:20px;">Hello %s !!! You Left some unfinished tasks.lets finish it!!!</div>',$user);
		}
?>
		<p class="signup">SIGN UP</p>
		<p class="part2">STEP 2</p>
		<hr style="border: 1.6px solid rgb(6, 106, 117);width:40%;margin-bottom:2%"></hr>
	<div class="row">
	   <div class="col-sm-1">
	   </div>
	   <div class="col-sm-5">
		<p class="upcover">upload the cover pic</p>
		<form action="" method="POST" enctype="multipart/form-data" class="coverform">
            <input type="file" name="image1" class="coverpic"/>
		    <br>
<?php
printf('<div class="notify1">%s</div>',$message1);
?>
<br>
            <input type="submit" class="coversubmit"/>
        </form>
		<p class="upprofile">upload the profile pic</p>
	    <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="image2" class="profilepic" />
		    <br>
<?php
printf('<div class="notify2">%s</div>',$message2);
?>
<br>
            <input type="submit" class="profilesubmit"/>
        </form>
		</div>
		<div class="col-sm-6">
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if(isset($_POST['button2']))
	{
		if((isset($_COOKIE['done1']))&&(isset($_COOKIE['done2'])))
		{
			$val1=$_COOKIE['done1'];
			$val2=$_COOKIE['done2'];
			$ppic=$cpic="";
			if($val1==1&&$val2==1)
			{
				$result = $session->execute(new Cassandra\SimpleStatement
                ("SELECT coverpic,profilepic FROM twitter.users
                WHERE username='$user'"));
				 foreach ($result as $row)
				 {
					 $cpic=$row['coverpic'];
					 $ppic=$row['profilepic'];
				 }
				 echo "<div class='container1' style>";
				  echo "<img src='$cpic' style='position:absolute;width:494px;height:300px;border-radius:15px;border:4px solid white;'>";
				  echo "<img src='$ppic' style='position:absolute;left:4%;top:130px;width:154px;height:148px;border:3px solid white;border-radius:10px;'>";
				  echo "</div>";
			}
			else
				echo '<div class="notify1">Insert both pics first</div><br>';
		}
		
			else
			echo '<div class="notify1">Insert both pics first</div><br>';
	}
}
?>

		     <form action="" method="POST">
                 <input type="submit" value="Preview" class="preview" name="button2"/>
             </form>
		</div>
	</div>
			<form action="" method="POST" style="text-align:center;margin-top:5%">
         <input type="submit" value="Complete Registration" class="done" name="button1"/>
        </form>
	
	
<?php
printf('<div style="text-align:center;color:red;font-family:Monotype corsiva;font-size:25px;">%s</div>',$complete);
?>
<section class="section footer" style="background-color:black">
<div style="color:white;font-family:monotype corsiva;font-size:1.4em;text-align:center">Copyright @Anand</div>
</section>
	</body>
</html>
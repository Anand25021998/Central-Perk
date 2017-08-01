<!DOCTYPE html>
<?php
$cluster  = Cassandra::cluster()
                ->build();
$keyspace  = 'twitter';
$session  = $cluster->connect($keyspace);
$usernamesignup=$emailsignup=$passwordsignup=$passwordsignup_confirm="";
$signupok=1;
date_default_timezone_set('Asia/Kolkata');
?>



<script>
function checkAvailability() {
	$("#loaderIcon").show();
	jQuery.ajax({
	url: "check_availability.php",
	data:'usernamesignup='+$("#usernamesignup").val(),
	type: "POST",
	success:function(data){
		$("#user-availability-status").html(data);
		$("#loaderIcon").hide();
	},
	error:function (){}
	});
}
</script>
<html lang="en" class="no-js">
    <head>
        <meta charset="UTF-8" />
        <title>Login and Registration Form with HTML5 and CSS3</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <meta name="description" content="Login and Registration Form with HTML5 and CSS3" />
        <meta name="keywords" content="html5, css3, form, switch, animation, :target, pseudo-class" />
        <meta name="author" content="Codrops" />
        <link rel="shortcut icon" href="../favicon.ico"> 
        <link rel="stylesheet" type="text/css" href="css/demo.css" />
        <link rel="stylesheet" type="text/css" href="css/style.css" />
		<link rel="stylesheet" type="text/css" href="css/animate-custom.css" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    </head>
    <body style="background:url('bg.jpg')">
	<nav class="navbar" role="navigation" style="min-height:52px;margin-bottom:3%;background-color: #333;">
	<div style="padding-top:10px;text-align:center">
	    <p style="color:white;font-family:Monotype Corsiva;font-size:1.8em;">Welcome To Central Perk</p>
	</div>
	</nav>

<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if(isset($_POST['button1']))
	{
		$usernamelogin=$_POST["username"];
		$passwordlogin=$_POST["password"];
		$username=$usernamelogin;
		$usernamelogin=strtolower($usernamelogin);
		$yes=1;
		if (strpos($usernamelogin, '.com') !== false) {//means user has entered the email
        $yes=0;
        }
		if($yes==0)
		{
			$emaillogin=$username;
			$result = $session->execute(new Cassandra\SimpleStatement
          ("SELECT username,coverpic,profilepic FROM twitter.users WHERE email = '$emaillogin' and password='$passwordlogin' allow filtering"));
		  $cnt=0;
		  foreach ($result as $row)
		  $cnt++;
		  if($cnt==0)
		  {
			  $result = $session->execute(new Cassandra\SimpleStatement
              ("SELECT username FROM twitter.users WHERE email = '$emaillogin' allow filtering"));
		      $cnt1=0;
		      foreach ($result as $row)
		      $cnt1++;
			  if($cnt1==0)
				echo "<script type='text/javascript'>alert('The email is not registered with any account!!!')</script>";   
			  else
				 echo "<script type='text/javascript'>alert('Please enter the correct password!!!')</script>";    
		  } 
		  else
		  {
			  foreach ($result as $row)
			  {
			      $username=$row['username'];
				  $coverpic=$row['coverpic'];
				  $profilepic= $row['profilepic'];
			  }
			  if($coverpic=='hi'||$profilepic=='hi')//completing the pic insertion part which was due at the time of registration
			  {
				  $cookie_name = "kindofpic";//to verify if the guy who left inserting pics process has logged in or the usual registrant.
				  $cookie_value = 2;
				  setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
			       $cookie_name = "username";
                   $cookie_value = $username;
                   setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
				   echo "<script language='JavaScript' type='text/JavaScript'>
                   <!--
                   window.location='manage.php';
                   //-->
                    </script> ";
			  }
			  else
			  {
				   $cookie_name = "username";
                   $cookie_value = $username;
                   setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
				    echo "<script language='JavaScript' type='text/JavaScript'>
                   <!--
                   window.location='profile.php';
                   //-->
                    </script> ";
			  }
		  }
		}
		else
		{
			$result = $session->execute(new Cassandra\SimpleStatement
            ("SELECT username,coverpic,profilepic FROM twitter.users WHERE username = '$username' and password='$passwordlogin'"));
		    $cnt=0;
		    foreach ($result as $row)
		    $cnt++;
		  if($cnt==0)
		  {
			  $result = $session->execute(new Cassandra\SimpleStatement
              ("SELECT username FROM twitter.users WHERE username = '$usernamelogin'"));
		      $cnt1=0;
		      foreach ($result as $row)
		      $cnt1++;
			  if($cnt1==0)
				echo "<script type='text/javascript'>alert('The username doesnot exist!!!')</script>";   
			  else
				 echo "<script type='text/javascript'>alert('Please enter the correct password!!!')</script>";    
		  }
		   else
		   {
			  foreach ($result as $row)
			  {
			      $username=$row['username'];
				  $coverpic=$row['coverpic'];
				  $profilepic= $row['profilepic'];
			  }
			  if($coverpic=='hi'||$profilepic=='hi')
			  {
				  $cookie_name = "kindofpic";//to verify if the guy who left inserting pics process has logged in or the usual registrant.
				  $cookie_value = 2;
				  setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
				  
			       $cookie_name = "username";
                   $cookie_value = $username;
                   setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
				   echo "<script language='JavaScript' type='text/JavaScript'>
                   <!--
                   window.location='manage.php';
                   //-->
                    </script> ";
			  }
			  else
			  {
				   $cookie_name = "username";
                   $cookie_value = $username;
                   setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
				   $_SESSION['user']=$username;
				   echo "<script language='JavaScript' type='text/JavaScript'>
                   <!--
                   window.location='home.php';
                   //-->
                    </script> ";
			  }
		   }
		}
	}
}
?>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if(isset($_POST['button2']))
	{
		$usernamesignup=$_POST["usernamesignup"];
		$emailsignup=$_POST["emailsignup"];
		$passwordsignup=$_POST["passwordsignup"];
		$passwordsignup_confirm=$_POST["passwordsignup_confirm"];
		
		$cnt=0;
		$result = $session->execute(new Cassandra\SimpleStatement
          ("SELECT username FROM twitter.users
            WHERE username='$usernamesignup' and email='$emailsignup' allow filtering"));
			
			foreach($result as $row)
			$cnt++;
		if($cnt!=0)
		{
			$signupok=0;
			echo "<script type='text/javascript'>alert('You have already registered. Try logging in...')</script>";
		}
		if($cnt==0)
		{
		$cnt1=0;
	    $result = $session->execute(new Cassandra\SimpleStatement
          ("SELECT username FROM twitter.users
            WHERE username='$usernamesignup'"));
		foreach ($result as $row) {
			$cnt1++;
		}
		if($cnt1!=0)
		{
			echo "<script type='text/javascript'>alert('Take the username not available thing seriously...')</script>";
			$signupok=0;
		}
		
		$cnt2=0;
	    $result = $session->execute(new Cassandra\SimpleStatement
          ("SELECT username FROM twitter.users
            WHERE email='$emailsignup' allow filtering"));
		foreach ($result as $row) {
			$cnt2++;
		}
		if($cnt2!=0)
		{
			echo "<script type='text/javascript'>alert('This email is already registered with an existing account')</script>";
			$signupok=0;
		}
		}
		
		if($passwordsignup_confirm!=$passwordsignup)
		{
			$signupok=0;
			echo "<script type='text/javascript'>alert('passwords donot match! Please Try again..')</script>";
		}
		if($signupok==1)
		{
			$cookie_name = "username";
            $cookie_value = $usernamesignup;
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
			
			 $cookie_name = "kindofpic";//to verify if the guy who left inserting pics process has logged in or the usual registrant.
		     $cookie_value = 1;
		     setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
			 $date = date('Y-m-d H:i:s');
			$statement=$session->execute(new Cassandra\SimpleStatement
		    ("INSERT into users(username, password, email ,coverpic ,joindate ,profilepic) values('$usernamesignup','$passwordsignup','$emailsignup' , 'hi' , '$date','hi')"));
			$statement=$session->execute(new Cassandra\SimpleStatement("update userscnt set followers=followers+0,following=following+0,retweets=retweets+0,tweets=tweets+0,stories=stories+0 where username='$usernamesignup'"));
			$r=$f=$usernamesignup;
			$r.="retweet";
			$f.="following";
			$statement=$session->execute(new Cassandra\SimpleStatement("create table $r(month int,rid text,retweeters text,primary key(month,rid))"));
			$statement=$session->execute(new Cassandra\SimpleStatement("create index on $r(rid)"));
			$statement=$session->execute(new Cassandra\SimpleStatement("create table $f(cnt counter,tofollow text,primary key(tofollow))"));		
			echo "<script language='JavaScript' type='text/JavaScript'>
            <!--
                 window.location='manage.php';
            //-->
             </script> ";
		}
    }
}
?>


        <div class="container">
            <section>				
                <div id="container_demo" >
                    <a class="hiddenanchor" id="toregister"></a>
                    <a class="hiddenanchor" id="tologin"></a>
                    <div id="wrapper">
                        <div id="login" class="animate form">
                            <form  method="post" action="" autocomplete="on"> 
                                <h1>Log in</h1> 
                                <p> 
                                    <label for="username" class="uname" data-icon="u" > Your email or username </label>
                                    <input id="username" name="username" required="required" type="text" placeholder="myusername or mymail@mail.com"/>
                                </p>
                                <p> 
                                    <label for="password" class="youpasswd" data-icon="p"> Your password </label>
                                    <input id="password" name="password" required="required" type="password" placeholder="eg. X8df!90EO" /> 
                                </p>
								<!---------------------------------------------------------------------------------------------------------------------------------
                                <p class="keeplogin"> 
									<input type="checkbox" name="loginkeeping" id="loginkeeping" value="loginkeeping" /> 
									<label for="loginkeeping">Keep me logged in</label>
								</p>
								----------------------------------------------------------------------------------------------------------------------------------->
                                <p class="login button"> 
                                    <input type="submit" value="Login" name="button1"> 
								</p>
                                <p class="change_link">
									Not a member yet ?
									<a href="#toregister" class="to_register">Join us</a>
								</p>
                            </form>
                        </div>

                        <div id="register" class="animate form">
                            <form  method="post" action="index.php" autocomplete="on" enctype="multipart/form-data"> 
                                <h1> Sign up <br> Step 1</h1> 
                                <p> 
                                    <label for="usernamesignup" class="uname" data-icon="u">Your username</label>
									
									
                                 <input id="usernamesignup" name="usernamesignup" required="required" type="text" placeholder="mysuperusername690"  onBlur="checkAvailability()">
								 <span id="user-availability-status"></span>
 
                                </p>
								<p><img src="LoaderIcon.gif" id="loaderIcon" style="display:none" /></p>
                                <p> 
                                    <label for="emailsignup" class="youmail" data-icon="e" > Your email</label>
                                    <input id="emailsignup" name="emailsignup" required="required" type="email" placeholder="mysupermail@mail.com"/> 
                                </p>
                                <p> 
                                    <label for="passwordsignup" class="youpasswd" data-icon="p">Your password </label>
                                    <input id="passwordsignup" name="passwordsignup" required="required" type="password" placeholder="eg. X8df!90EO"/>
                                </p>
                                <p> 
                                    <label for="passwordsignup_confirm" class="youpasswd" data-icon="p">Please confirm your password </label>
                                    <input id="passwordsignup_confirm" name="passwordsignup_confirm" required="required" type="password" placeholder="eg. X8df!90EO"/>
                                </p>
                                <p class="signin button"> 
									<input type="submit" value="Sign up" name="button2"> 
								</p>
                                <p class="change_link">  
									Already a member ?
									<a href="#tologin" class="to_register"> Go and log in </a>
								</p>
                            </form>
                        </div>
						
                    </div>
                </div>  
            </section>
        </div>
<section class="section footer" style="background-color:black">
<div style="color:white;font-family:monotype corsiva;font-size:1.4em;text-align:center">Copyright @Anand</div>
</section>
    </body>
</html>
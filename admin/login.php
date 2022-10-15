<?PHP
session_start();
if(isset($_SESSION['user']) && $_SESSION['user']!=''){
	//logged in, forward to home page
	header("location:index.php");
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <title>Practical Path - Student Center</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">



    
    <!-- Custom styles for this template -->
    <link href="css/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>


    <div class="container">

    	<div class="row">
        <div class="col-md-4 offset-md-4">
            <h1 class="text-center login-title">Sign in to your Admin Area</h1>
            <div class="account-wall" style="text-align:center;">
                <img class="profile-img" src="../img/logo.png" style="max-width:125px;">
                <form class="form-signin" method="post" action="loginproc.php" style="margin-top:30px;">
                <input type="text" class="form-control" placeholder="Username" name="username" required autofocus>
                <input type="password" class="form-control" placeholder="Password" name="password" required>
                <button class="btn btn-lg btn-primary btn-block" type="submit">
                    Sign in</button>
                <!--<label class="checkbox pull-left">
                    <input type="checkbox" value="remember-me">
                    Remember me
                </label> 
                <a href="#" class="pull-right need-help">Need help? </a><span class="clearfix"></span> 
                <div style="text-align:center;margin-top:20px"><a href="https://studentcenter.thepracticalpath.com/forgotpw.php">Forgot your username or password?</a></div>-->
                </form>
            	<?PHP
            		if($_GET['e']=='1') {
            	
            			?><div class="alert alert-danger text-center" role="alert"><p>Invalid Username/Password.</p</div>
            	<?PHP
            		}
                    
            	?>
            
            </div>
            <!-- <a href="#" class="text-center new-account">Create an account </a> -->
        </div>
    </div>
<?PHP

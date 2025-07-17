<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <link rel="stylesheet" href="../css/signup.css">
</head>
<body>
<div class="login-form">

        <form action="student_login_process.php" method="post">

            <div class="content">

                <h2>LOGIN TO UP-DO</h2>

                <div class="inputbox">
                <input type="text" id="matric" name="matric" required>
                    <i>USER ID</i>
                </div>

                <div class="inputbox">
                <input type="password" id="password" name="password" required maxlength="8">
                    <i>PASSWORD</i>
                </div>
             
                <div class="inputbox">
                <input type="submit" value="LOGIN">
                </div>   
           
                    <div class="links">
                   <a href="student_signup.php">NEW USER</a>
                    </div> 
            
                <div class="links">
                    <a href="forget_password.php">FORGET PASSWORD</a>
                 </div>

                <div class="links">
                    <a href="partnership_home.php">DASHBOARD</a>
                </div>

            </div>
        </form>
    </div>
</body>
</html>

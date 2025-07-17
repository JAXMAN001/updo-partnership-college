<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PASSWORD RESET</title>
    <link rel="stylesheet" href="../css/signup.css">
</head>
<body>
<div class="login-form">

        <form action="student_forget_process.php" method="post" onsubmit="return captureSuspendedInfo();">

            <div class="content">

                <h2>INPUT YOUR EMAIL OR PHONE AND USER ID</h2>

                <div class="inputbox">
                    <select id="contact_method" name="contact_method" required onchange="toggleContactInput()">
                        <option value="email">Use Email</option>
                        <option value="phone">Use Phone Number</option>
                    </select>
                </div>

                <div class="inputbox" id="email_box">
                    <input type="email" id="email" name="email">
                    <i>EMAIL ADDRESS</i>
                </div>

                <div class="inputbox" id="phone_box" style="display:none;">
                    <input type="number" id="phone" name="phone" pattern="[0-9+ ]*">
                    <i>PHONE NUMBER</i>
                </div>

                <div class="inputbox">
                <input type="text" id="matric" name="matric" required placeholder="SUP_ID OR HOD_ID OR_MATRIC_NUMBER" >
                    <i>USER ID</i>
                </div>

                <div class="inputbox">
                    <input type="submit" value="PROCEED">
                </div>

          
                <div class="links">
                    <a href="student_login.php">BACK</a>
                </div>
                  
            </div>
        </form>
    </div>
<script>
function toggleContactInput() {
    var method = document.getElementById('contact_method').value;
    document.getElementById('email_box').style.display = (method === 'email') ? '' : 'none';
    document.getElementById('phone_box').style.display = (method === 'phone') ? '' : 'none';
    document.getElementById('email').required = (method === 'email');
    document.getElementById('phone').required = (method === 'phone');
}

// Optionally capture info in localStorage for real-time capture if needed
function captureSuspendedInfo() {
    // This is only a frontend helper; real-time DB insert is handled in PHP after 3 failed attempts
    // To provide info to PHP as soon as the submit happens, ensure you send all form fields.
    return true;
}
</script>
</body>
</html>

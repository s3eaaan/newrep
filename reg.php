<?php
    $host = 'localhost';
    $dbname = 'sports';
    $username = 'root';
    $password = '';  
    $message = '';
    $messageType = '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {  # I used REQUEST_METHOD because it is conventions standard
        
        # Get form data and prevent errors aswell
        $name = trim($_POST['name']);  # Asks for user's name
        $email = trim($_POST['email']);  # Asks for user's email
        $pass = $_POST['password'];  # Asks for user's ddesired password
        $confirmPass = $_POST['confirm_password'];  # Confirms password to prevent typos posiblity
        
        # Preventing errors or any unexpected cases
        if (empty($name) || empty($email) || empty($pass) || empty($confirmPass)) {
            # If any field is empty
            $message = 'All fields are required!';  # Message shown to user
            $messageType = 'error';  # Message format

        } elseif (preg_match('/[0-9]/', $name)) {
            # If the name contains any numbers
            $message = 'Name cannot contain numbers!';  # Message shown to user
            $messageType = 'error';  # Message format
            
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            # If the email has like different values or no relevant values such as '@'
            $message = 'Invalid email format!';  # Message shown to user
            $messageType = 'error';  # Message format

        } elseif (strlen($pass) < 6) {
            # If the password inputted by user is less than 6 chars
            $message = 'Password must be at least 6 characters!';  # Message shown to user
            $messageType = 'error';  # Message format

        } elseif ($pass !== $confirmPass) {
            # If the user confirms adifferent passord to the one they entered in the previous box
            $message = 'Passwords do not match!';  # Message shown to user
            $messageType = 'error';  # Message format

        } else {
            try {
                #  using try to Catch any unexpected errors
                #  Connecting to th edatabase
                $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                #  Checking if the email inputted is already in the database
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                #  If it shows up in the database
                if ($stmt->rowCount() > 0) {
                    $message = 'Email already registered! <a href="login.php">Login instead?</a>';  # Message shown to user
                    $messageType = 'error';  # Message format

                } else {
                    #  Hash the password (conventions for security and privacy reasons)
                    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);
                    
                    #  Insert user into database
                    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password', $hashedPassword);
                    
                    if ($stmt->execute()) {
                        #  If user creation is succesful, it will go to login page directly after
                        header("Location: login.php?registered=1");
                        exit();  # Leave registration page

                    } else {
                        #  If something goes wrong in daatabase or registration
                        $message = 'Registration failed. Please try again.';  # Message shown to user
                        $messageType = 'error';  # Message format
                    }
                }
            } catch(PDOException $e) {  # Just a safety net for unexpected errors
                $message = 'Database error: ' . $e->getMessage();  # For me to figure out what type of error
                $messageType = 'error';
            }
        }
    } 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="forms.css"> <!--All php/form related pages basically identical looks-->
    <title>User Registration</title>
</head>
<body>
    <a href="https://www.hhs.school.nz/" target="_blank">
        <img src="photos/hendersonhigh.png" alt="Henderson High School Logo" class="logo">
    </a>
    <div id="navbar">
        <ul>
            <li><a href="index.html" target='_blank' id="current">Home</a></li>
            <li>
                <a href="#" id="sports">Sports ▾</a>
                <ul class="dropdown">
                    <li><a href="basketball.html" target="_blank">Basketball</a></li>
                    <li><a href="football.html" target="_blank">Football</a></li>
                    <li><a href="netball.html" target="_blank">Netball</a></li>
                </ul>
            </li>
            <li><a href="reg.php" target="_blank">Sign Ups</a></li>
        </ul>
    </div>
    <div id="top">
        <button class="openbtn" onclick="openNav()">☰ Menu</button>
    </div>
    <div class="container">
        <h2>Create an account</h2>  <!--Tells user what to do-->   
        
        <?php
        #  Displays message to user
        if (!empty($message)) {
            echo "<div class='message $messageType'>$message</div>";
        }
        ?>
        
        <form method="POST" action="">  <!-- What the user actually sees -->
            <div class="form-group">
                <label for="name">First Name</label>  <!--Asks for first name of user-->
                <input type="text" id="name" name="name" required>  <!-- This input will be $user -->
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>  <!--Asks for mail of user-->
                <input type="email" id="email" name="email" required>  <!-- This input will be $email -->
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>  <!--Asks for  DESIRED password of user-->
                <input type="password" id="password" name="password" required>  <!-- This input will be $pass -->
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>  <!--Confirms with user if they inputted correct password-->
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn">Register</button>
        </form>
        
        <div class="bttm_txt">
            Already have an account? <a href="login.php">Login here</a>  <!--If user already has account-->
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
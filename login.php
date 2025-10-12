<?php
// Start session
session_start();

$host = 'localhost';
$dbname = 'sports';
$username = 'root'; 
$password = '';     

$message = '';
$messageType = '';

// Check if user was just registered
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $message = 'Registration successful! Please login with your credentials.';
    $messageType = 'success';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    
    // Validation
    if (empty($email) || empty($pass)) {
        $message = 'All fields are required!';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format!';
        $messageType = 'error';
    } else {
        try {
            // Create database connection
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($pass, $user['password'])) {
                    // Password is correct, create session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    
                    // Redirect to dashboard/home page
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $message = 'Invalid email or password!';
                    $messageType = 'error';
                }
            } else {
                $message = 'Invalid email or password!';
                $messageType = 'error';
            }
        } catch(PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
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
    <link rel="stylesheet" href="forms.css">
    <title>User Login</title>
    <style>

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #555;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

    </style>
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
                    <li><a href="basketball.html" target="_blank">Football</a></li>
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
        <h2>Login</h2>
        
        <?php
        // Display message
        if (!empty($message)) {
            echo "<div class='message $messageType'>$message</div>";
        }
        ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>    
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="bttm_txt">
            Don't have an account? <a href="reg.php">Register here</a>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>

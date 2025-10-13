<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$dbname = 'sports';
$username = 'root';
$password = '';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sport_name = trim($_POST['sport_name']);
    $full_name = trim($_POST['full_name']);
    $age = intval($_POST['age']);
    $year_level = intval($_POST['year_level']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $prior_experience = trim($_POST['prior_experience']);
    $additional_comments = trim($_POST['additional_comments']);
    $user_id = $_SESSION['user_id'];
    
    // Validation
    if (empty($sport_name) || empty($full_name) || empty($age) || empty($year_level) || empty($email) || empty($phone_number)) {
        $message = 'Please fill in all required fields!';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format!';
        $messageType = 'error';
    } elseif ($age < 5 || $age > 100) {
        $message = 'Please enter a valid age!';
        $messageType = 'error';
    } else {
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Insert registration
            $stmt = $conn->prepare("INSERT INTO sports_registrations (user_id, sport_name, full_name, age, year_level, email, phone_number, prior_experience, additional_comments, created_at) VALUES (:user_id, :sport_name, :full_name, :age, :year_level, :email, :phone_number, :prior_experience, :additional_comments, NOW())");
            
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':sport_name', $sport_name);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':year_level', $year_level);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':prior_experience', $prior_experience);
            $stmt->bindParam(':additional_comments', $additional_comments);
            
            $stmt->execute();
            
            $message = 'Sports registration submitted successfully!';
            $messageType = 'success';
        } catch(PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Fetch user's registrations
$registrations = [];
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("SELECT * FROM sports_registrations WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $message = 'Database error: ' . $e->getMessage();
    $messageType = 'error';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Registration Dashboard</title>
    <link rel="stylesheet" href="forms.css">
    <style>
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #333;
        }
        .user-info {
            color: #555;
        }
        .logout-btn {
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #c82333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            margin-top: 50px;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: Arial, sans-serif;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #9b2743;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .registrations-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .registration-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #9b2743;
        }
        .registration-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
        }

        .sport-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #9b2743;
            color: white;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .registration-date {
            color: #666;
            font-size: 14px;
        }
        .registration-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .detail-item {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 3px;
        }
        .detail-value {
            color: #333;
        }
        .no-registrations {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        @media (max-width: 768px) {
            .form-row,
            .registration-details {
                grid-template-columns: 1fr;
            }
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <a href="https://www.hhs.school.nz/" target="_blank">
        <img src="photos/hendersonhigh.png" alt="Henderson High School Logo" class="logo">
    </a>
    <div id="sidebar">
        <ul>
            <li><a href="index.html" target='_blank' id="current">Home</a></li>
            <li>
                <a href="#" id="sports">Sports ▾</a>
                <ul class="dropdown">
                    <li><a href="basketball.html" target="_blank">Basketball</a></li>
                    <li><a href="football.html" target="_blank">Football</a></li>
                    <li><a href="hockey.html" target="_blank">Hockey</a></li>
                </ul>
            </li>
            <li><a href="reg.php" target="_blank">Sign Ups</a></li>
        </ul>
    </div>
    <div id="top">
        <button class="openbtn" onclick="openNav()">☰ Menu</button>
    </div>
    <div class="container">
        <div class="header">
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
                <div class="user-info">
                    <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                </div>
            </div>
            <a href="reg.php" class="logout-btn">Logout</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h2>Sports Registration Form</h2>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="sport_name">Sport Name <span class="required">*</span></label>
                        <select id="sport_name" name="sport_name" required>
                            <option value="">Select a sport...</option>
                            <option value="Football">Football</option>
                            <option value="Netball">Netball</option>
                            <option value="Basketball">Basketball</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="age">Age <span class="required">*</span></label>
                        <input type="number" id="age" name="age" min="5" max="100" required>
                    </div>

                    <div class="form-group">
                        <label for="year_level">Year Level <span class="required">*</span></label>
                        <select id="year_level" name="year_level" required>
                            <option value="">Select year level...</option>
                            <option value="9">Year 9</option>
                            <option value="10">Year 10</option>
                            <option value="11">Year 11</option>
                            <option value="12">Year 12</option>
                            <option value="13">Year 13</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone_number">Phone Number <span class="required">*</span></label>
                        <input type="text" id="phone_number" name="phone_number" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="prior_experience">Prior Experience</label>
                    <textarea id="prior_experience" name="prior_experience" placeholder="Describe any previous experience with this sport..."></textarea>
                </div>

                <div class="form-group">
                    <label for="additional_comments">Additional Comments</label>
                    <textarea id="additional_comments" name="additional_comments" placeholder="Any additional information you'd like to share..."></textarea>
                </div>

                <button type="submit" class="btn">Submit Registration</button>
            </form>
        </div>

        <div class="registrations-container">
            <h2>Your Sports Registrations</h2>
            
            <?php if (count($registrations) > 0): ?>
                <?php foreach ($registrations as $reg): ?>
                    <div class="registration-card">
                        <div class="registration-header">
                            <span class="sport-badge"><?php echo htmlspecialchars($reg['sport_name']); ?></span>
                            <span class="registration-date">
                                Submitted: <?php echo date('M d, Y', strtotime($reg['created_at'])); ?>
                            </span>
                        </div>
                        
                        <div class="registration-details">
                            <div class="detail-item">
                                <div class="detail-label">Full Name:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($reg['full_name']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Age:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($reg['age']); ?> years old</div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Year Level:</div>
                                <div class="detail-value">Year <?php echo htmlspecialchars($reg['year_level']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Email:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($reg['email']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Phone Number:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($reg['phone_number']); ?></div>
                            </div>
                            
                            <?php if (!empty($reg['prior_experience'])): ?>
                                <div class="detail-item" style="grid-column: 1 / -1;">
                                    <div class="detail-label">Prior Experience:</div>
                                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($reg['prior_experience'])); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($reg['additional_comments'])): ?>
                                <div class="detail-item" style="grid-column: 1 / -1;">
                                    <div class="detail-label">Additional Comments:</div>
                                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($reg['additional_comments'])); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-registrations">
                    <p>You haven't submitted any sports registrations yet.</p>
                    <p>Fill out the form above to register for a sport!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
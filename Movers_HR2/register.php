<?php
include 'Models/config.php';
$conn = new ConnectionDb();

$registration_success = false; // Define the success flag
$error_message = ''; // Define the error message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    $recaptcha_response = $_POST['g-recaptcha-response']; // Get reCAPTCHA response

    // Verify reCAPTCHA with Google
    $secret_key = '6LcwsoYqAAAAAHbHfrbs0dRIHNup3reTLGAZHPuB';
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = [
        'secret' => $secret_key,
        'response' => $recaptcha_response,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $recaptcha_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $recaptcha_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $recaptcha_result = curl_exec($ch);
    curl_close($ch);

    $recaptcha_result = json_decode($recaptcha_result);

    // If reCAPTCHA is valid
    if ($recaptcha_result->success) {
        // Check if passwords match
        if ($password != $confirm_password) {
            $error_message = "Passwords do not match!";
        } else {
            // Hash the password and insert into the database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (firstname, lastname, username, email, password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->DbConnection()->prepare($sql);
            $stmt->bind_param("sssss", $firstname, $lastname, $username, $email, $hashed_password);
            if ($stmt->execute()) {
                $registration_success = true; // Set the success flag
            } else {
                $error_message = "Registration failed. Please try again.";
            }
        }
    } else {
        $error_message = "reCAPTCHA verification failed. Please try again.";
    }
}
$conn->DbConnection()->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="css/register.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script> <!-- Include the reCAPTCHA API -->
</head>

<body>

    <div class="main-content">

        <div class="register-container">
            <?php if ($registration_success): ?>
                <div class="success-message">
                    <h3>Successfully Registered!</h3>
                    <p>Your account has been created successfully.</p>
                    <p>Click below to log in.</p>
                    <a href="login.php" class="login-btn">LOGIN</a>
                </div>
            <?php else: ?>
                <form class="register-form" action="register.php" method="POST">
                    <h3>Register</h3>

                    <!-- Display error message if there is one -->
                    <?php if (!empty($error_message)): ?>
                        <div class="error-message">
                            <p style="color: red;"><?php echo $error_message; ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="input-group">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>
                    <div class="input-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="input-group">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" id="confirm-password" name="confirm-password" required>
                    </div>

                    <!-- Google reCAPTCHA widget -->
                    <div class="g-recaptcha" style="margin-bottom:10px" data-sitekey="6LcwsoYqAAAAAPxmZgMkH802nsLZneuBmsMrIGnY"></div> <!-- Replace with your Site Key -->

                    <button type="submit" class="register-btn">Register</button>
                    <div class="extra-links">
                        <a href="login.php">Already have an account? Login</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>
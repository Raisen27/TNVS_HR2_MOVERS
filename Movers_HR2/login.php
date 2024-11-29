<?php
include 'Models/config.php';
$conn = new ConnectionDb();

// Start output buffering and session
ob_start();
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST['username_or_email'];
    $password = $_POST['password'];
    $recaptcha_response = $_POST['g-recaptcha-response']; // Capture the reCAPTCHA response

    // Verify reCAPTCHA with Google
    $secret_key = '6LcwsoYqAAAAAHbHfrbs0dRIHNup3reTLGAZHPuB'; // Replace with your Secret Key
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = [
        'secret' => $secret_key,
        'response' => $recaptcha_response,
    ];

    // cURL request to verify reCAPTCHA
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
        // Proceed with login validation
        $sql = "SELECT * FROM users WHERE username=? OR email=?";
        $stmt = $conn->DbConnection()->prepare($sql);
        $stmt->bind_param("ss", $input, $input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_logged_in'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['firstname'] = $user['firstname'];

                // Redirect to dashboard
                header('Location: dashboard.php');
                exit();
            } else {
                echo "<script>alert('Invalid username/email or password!');</script>";
            }
        } else {
            echo "<script>alert('Invalid username/email or password!');</script>";
        }
    } else {
        echo "<script>alert('reCAPTCHA verification failed. Please try again.');</script>";
    }
}
$conn->DbConnection()->close();
?>


<!DOCTYPE html>
<html>

<head>
    <title>Sign In</title>
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script> <!-- Include the reCAPTCHA API -->
</head>

<body>

    <div class="login-container">
        <img alt="Movers logo with a car icon inside a location pin" src="assets/logo.png" />

        <form action="login.php" method="POST">
            <input name="username_or_email" placeholder="Username or Email" required="" type="text" />
            <input name="password" placeholder="Password" required="" type="password" />
            <div style="text-align: left; margin: 10px 0;">
                <input id="remember-me" type="checkbox" />
                <label for="remember-me">Remember me</label>
            </div>

            <!-- Google reCAPTCHA widget -->
            <div class="g-recaptcha" style="margin-left: 2px; margin-bottom: 20px;" data-sitekey="6LcwsoYqAAAAAPxmZgMkH802nsLZneuBmsMrIGnY"></div> <!-- Replace with your Site Key -->

            <button class="btn" type="submit">LOG IN</button>
        </form>

        <p>
            Don't have an account?
            <a href="register.php">Register</a>
        </p>
    </div>

</body>

</html>
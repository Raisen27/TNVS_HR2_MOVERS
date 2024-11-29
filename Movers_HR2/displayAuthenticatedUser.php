<?php


// Check if the user is authenticated and their data is in session
if (isset($_SESSION['firstname']) && isset($_SESSION['lastname'])) {
    $firstname = $_SESSION['firstname'];
    $lastname = $_SESSION['lastname'];
} else {
    $firstname = 'Guest';  // Default value if not authenticated
    $lastname = '';        // You can leave this empty or set to "Guest"
}

?>

<div class="header">
    <span>Welcome, <?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></span>
    <img src="assets/logo.png" alt="Logo">
</div>
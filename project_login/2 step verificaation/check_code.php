<?php
$mysqli = new mysqli("localhost", "root", "", "toyjoy");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$email = $_POST['email'];
$code = $_POST['code'];

$stmt = $mysqli->prepare("SELECT * FROM users WHERE email = ? AND verification_code = ?");
$stmt->bind_param("ss", $email, $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<h2 style='text-align:center; margin-top:50px;'>✅ Verification Successful! Welcome.</h2>";
    // You can redirect or start session here
} else {
    echo "<h2 style='text-align:center; margin-top:50px;'>❌ Invalid Code or Email!</h2>";
}
$stmt->close();
$mysqli->close();
?>

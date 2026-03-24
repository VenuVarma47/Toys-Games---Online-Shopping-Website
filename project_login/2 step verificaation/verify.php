<?php
$mysqli = new mysqli("localhost", "root", "", "toyjoy");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$code = rand(100000, 999999);

// Send email (Make sure mail() works on your server)
mail($email, "Your verification code", "Code: $code");

// Store in DB
$stmt = $mysqli->prepare("INSERT INTO users (email, password, verification_code) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $password, $code);
$stmt->execute();
$stmt->close();
?>

<!-- HTML to enter code -->
<!DOCTYPE html>
<html>
<head>
    <title>Verify Code</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <form action="check_code.php" method="POST" class="bg-white p-6 rounded shadow-md w-96">
        <h2 class="text-2xl font-bold mb-4">Enter Verification Code</h2>
        <input type="email" name="email" placeholder="Email" required class="w-full mb-3 p-2 border rounded" />
        <input type="text" name="code" placeholder="6-digit Code" required class="w-full mb-3 p-2 border rounded" />
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Verify</button>
    </form>
</body>
</html>

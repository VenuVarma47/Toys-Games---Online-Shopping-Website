<?php
// Database connection settings
$host = 'localhost';
$dbname = 'online_login'; // Replace with your database name
$username = 'root'; // Replace with your MySQL username
$password = ''; // Replace with your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $street_address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
    $zip_code = filter_input(INPUT_POST, 'zip', FILTER_SANITIZE_STRING);
    $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
    $shipping_method = filter_input(INPUT_POST, 'shipping_method', FILTER_SANITIZE_STRING);
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);

    // Validate required fields
    $errors = [];
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($street_address)) $errors[] = "Street address is required.";
    if (empty($city)) $errors[] = "City is required.";
    if (empty($state)) $errors[] = "State is required.";
    if (empty($zip_code)) $errors[] = "ZIP code is required.";
    if (empty($country)) $errors[] = "Country is required.";
    if (empty($shipping_method)) $errors[] = "Shipping method is required.";
    if (empty($payment_method)) $errors[] = "Payment method is required.";
    if (!isset($_POST['terms'])) $errors[] = "You must agree to the Terms & Conditions.";

    if (empty($errors)) {
        try {
            // Prepare and execute the SQL query
            $stmt = $pdo->prepare("
                INSERT INTO billing_information (
                    first_name, last_name, email, phone, street_address, city, state, zip_code, country, shipping_method, payment_method
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $first_name,
                $last_name,
                $email,
                $phone,
                $street_address,
                $city,
                $state,
                $zip_code,
                $country,
                $shipping_method,
                $payment_method
            ]);

            // Clear cart and redirect
            echo "<script>
                localStorage.removeItem('cart');
                alert('Order placed successfully! You will receive a confirmation email shortly.');
                window.location.href = 'index.html';
            </script>";
        } catch (PDOException $e) {
            echo "<script>alert('Error saving order: " . addslashes($e->getMessage()) . "');</script>";
        }
    } else {
        // Display errors
        $error_message = implode("\\n", array_map('addslashes', $errors));
        echo "<script>alert('Please correct the following errors:\\n$error_message');</script>";
    }
}
?>
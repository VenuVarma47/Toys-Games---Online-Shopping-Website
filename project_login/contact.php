<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'online_login';

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for form data and messages
$name = $email = $subject = $message = '';
$errors = [];
$success = '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }

    // If no errors, insert data into the database
    if (empty($errors)) {
        // Prepare the SQL statement
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Check if prepare() was successful
        if ($stmt === false) {
            $errors[] = 'Failed to prepare the SQL statement: ' . $conn->error;
        } else {
            // Bind parameters and execute
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $success = 'Your message has been sent successfully!';
                $name = $email = $subject = $message = '';
            } else {
                $errors[] = 'Failed to send your message: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToyJoy - Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
        }
        .error {
            color: #dc2626;
        }
        .success {
            color: #16a34a;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="bg-indigo-600 text-white p-4 sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">ToyJoy</h1>
            <div class="space-x-6">
                <a href="index.html" class="hover:text-indigo-200 transition">Home</a>
                <a href="shop.html" class="hover:text-indigo-200 transition">Shop</a>
                <a href="contact.html" class="text-yellow-300">Contact</a>
                <a href="#" class="bg-white text-indigo-600 px-4 py-2 rounded-full hover:bg-indigo-100 transition">Cart</a>
            </div>
        </div>
    </nav>

    <!-- Contact Section -->
    <section class="py-16 container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-8">Contact Us</h2>
        <p class="text-center text-gray-600 mb-12">We're here to help! Feel free to contact us with any questions or concerns.</p>
        
        <!-- Display success or error messages -->
        <?php if (!empty($success)): ?>
            <p class="success text-center mb-6"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="error text-center mb-6">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Contact Information -->
            <div class="md:w-1/3">
                <h3 class="text-xl font-semibold mb-4">Get in Touch</h3>
                <p class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Email: support@toyjoy.com
                </p>
                <p class="flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    Phone: (123) 456-7890
                </p>
                <p class="text-gray-600">We typically respond within 24 hours.</p>
            </div>
            <!-- Contact Form -->
            <div class="md:w-2/3">
                <form id="contact-form" action="contact.php" method="POST" class="space-y-4">
                    <div>
                        <label for="name" class="block text-gray-700 mb-1">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="email" class="block text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="subject" class="block text-gray-700 mb-1">Subject</label>
                        <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="message" class="block text-gray-700 mb-1">Message</label>
                        <textarea id="message" name="message" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" rows="5"><?php echo htmlspecialchars($message); ?></textarea>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-full hover:bg-indigo-700 transition w-full md:w-auto">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 px-4">
            <div>
                <h3 class="text-xl font-bold mb-4">ToyJoy</h3>
                <p>Bringing joy through toys and games since 2025.</p>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="hover:text-indigo-300 transition">About Us</a></li>
                    <li><a href="#" class="hover:text-indigo-300 transition">FAQ</a></li>
                    <li><a href="#" class="hover:text-indigo-300 transition">Shipping</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Contact</h3>
                <p>Email: support@toyjoy.com</p>
                <p>Phone: (123) 456-7890</p>
            </div>
        </div>
    </footer>
</body>
</html>
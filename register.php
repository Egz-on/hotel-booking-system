<?php 
session_start();
    include 'includes/db.php';
    if(isset($_SESSION['isloggedin'])) {
        if($_SESSION['isloggedin'] == true) {
            header('location: dashboard.php');
        }
    }
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/output.css" />
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-10 rounded-2xl shadow-xl w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Create a new account</h2>
        <?php
        if(isset($_POST['btn'])) {
            $fullname = $_POST['fullname'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'],PASSWORD_BCRYPT);

            $sql = "INSERT INTO `users` (`fullname`,`email`,`password`)VALUES(?,?,?)";
            $s = $pdo->prepare($sql);
            if($s->execute([$fullname,$email,$password])){
                header('location: login.php');
            } else{
                echo ('Error: Something went wrong');
            }
        }
        ?>

        <form action="<?=$_SERVER['PHP_SELF'] ?>" method="POST" class="space-y-5">
        <div>
            <label for="name" class="block text-gray-700">Name</label>
            <input type="text"  name="fullname" required class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <div>
            <label for="email" class="block text-gray-700">Email</label>
            <input type="email"  name="email" required class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <div>
            <label for="password" class="block text-gray-700">Password</label>
            <input type="password"  name="password" required class="w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <button name='btn' type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Register</button>
        </form>

        <p class="mt-6 text-center text-gray-600">
        Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login here</a>
        </p>
    </div>
    </body>
    </html>


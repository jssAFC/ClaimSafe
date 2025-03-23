<?php
session_start();
include('../includes/header.php');
include('../includes/db_connection.php');

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'user') {
                header("location: user_dashboard.php");
            } else if ($row['role'] == 'provider') {
                header("location: provider_dashboard.php");
            } else if ($row['role'] == 'admin') {
                header("location: admin_dashboard.php");
            }
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Invalid username";
    }
}
?>

<div class="flex min-h-screen flex-col sm:flex-row bg-slate-200">
    <div class="sm:w-1/2 w-full p-5 flex flex-col justify-center gap-8 items-center relative text-center sm:text-left">
        <div class="sm:absolute relative sm:top-16 sm:left-10 flex flex-col gap-3  ">
            <h1 class="font-sans font-bold text-purple-700 text-3xl sm:text-5xl lg:text-6xl">
                ClaimSafe
            </h1>
            <h3 class="text-sm  lg:text-xl font-mono">
                Accident Insurance Resolution System
            </h3>
        </div>
        <img src="../assets/aggrement-image.webp" class="w-[90%] max-w-md pl-12 sm:pl-4  h-auto mt-28 sm:mt-0 hidden sm:block" alt="Agreement">
    </div>

    <div class="w-[80vw] h-auto rounded-md shadow-2xl mx-auto sm:w-1/2 flex justify-center items-center bg-white p-8 sm:p-6 flex-col ">
        <div class="w-full max-w-md ">
            <h2 class="text-3xl sm:text-4xl mb-4 text-slate-500 text-center">Log in</h2>
            <div class="flex items-center gap-2 mb-2">
                <hr class="flex-grow border-t-2 border-gray-300">
                <span class="text-gray-600">or</span>
                <hr class="flex-grow border-t-2 border-gray-300">
            </div>

            <p class="mb-6 text-center">
                Not registered yet?
                <a href="register.php" target="_blank" class="text-purple-600 hover:underline hover:underline-offset-4">
                    Create an Account here
                </a>
            </p>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="flex flex-col mb-6 relative">
                    <label class="text-sm sm:text-base font-medium -mb-6">Username</label>
                    <svg class="w-[25px] h-[22px] fill-[#8c8c8c] absolute top-2 right-2 max-sm:top-1" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg">
                        <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"></path>
                    </svg>
                    <input placeholder="Enter Username" type="text" name="username" class="border-[1px] border-gray-400 rounded-md  w-full h-10 p-2  focus:outline-none focus:border-blue-500" required>
                </div>
                <div class="flex flex-col mb-10 relative">
                    <label class="text-sm sm:text-base font-medium -mb-6">Password</label>
                    <svg class="w-[25px] h-[22px] fill-[#8c8c8c] absolute top-2 right-2 max-sm:top-1" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg">
                        <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"></path>
                    </svg>
                    <input placeholder="Enter Password" type="password" name="password" class="border-[1px] border-gray-400 rounded-md w-full h-10 p-2  focus:outline-none focus:border-blue-500" required>
                </div>

                <div class="w-full">
                    <button type="submit" class="block w-full bg-purple-500 py-2 text-white hover:bg-blue-600 font-medium rounded-md text-center mb-8">
                        Log In
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
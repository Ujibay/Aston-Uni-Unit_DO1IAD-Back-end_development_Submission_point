<?php

    // create session if not in one
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        // If a form has POSTED encode results in JSON
        $message = json_encode([
            'username' => $_POST['username'],
            'password' => $_POST['password'],
            'email' => $_POST['email']
        ]);

        // Send JSON message to Node.js
        $curl = curl_init('http://127.0.0.1:3000/register');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $message);

        // Get response and store it, then close the connection
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        if (!empty($response['success'])) {
           header("Location: index.php");
           exit();
        } else {
            $error = $response['message'];
        }
    }
    ?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="icon" href="images/favicon.ico?v=1" type="image/x-icon"> 
        <title>Aston Software Ltd: Project Management Portal</title>
    </head>
    <body>
        <div class="oldSchoolFramesStyle">
            <header class="titleBar">
                <div class="header-left">
                    <h1>Aston Software Ltd</h1>
                </div>
                
                <div class="header-right">
                    <?php if (isset($_SESSION['uid'])): ?>
                        <span>Hello <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span><br>
                        <a href="projects.php"> Go to my projects</a><br>
                        <a href="logout.php">Log Out</a>

                    <?php else: ?>
                        <form action="login.php" method="POST" class="login-form">
                            <label for="userName">User Name</label>
                            <input type="text" id="userName" name="username" class="login-input" required>
                            <label class="padding"> </label>

                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="login-input" required>
                            <button type="submit" class="loginButton">Log in</button>
                        </form>

                        <?php if (isset($_SESSION['login_error'])): ?>
                            <span style="color: red; margin-left: 10px;">
                                <?php
                                    echo htmlspecialchars($_SESSION['login_error']);
                                    unset($_SESSION['login_error']);
                                ?>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
            </header>

            <nav class="navBar">
                <p>
                    <a href="index.php" class="button1">Home</a>
                </p>
                <p>
                    <a href="search.php" class="button1">Search for Project</a>
                </p>
                <p>
                    <a href="projectList.php" class="button1">Project List</a>
                </p>
                <?php if (isset($_SESSION['uid'])): ?>
                     <p>
                        <a href="updateProject.php" class="button1">Update a Project</a>
                    </p>
                    <p>
                        <a href="addProject.php" class="button1">Create New Project</a>
                    </p>

                <?php else: ?>
                    <p>
                        <a href="signup.php" class="button1">Sign Up</a>
                    </p>
                <?php endif; ?>
                <p>
                    <a href="about.php" class="button1">About us</a>
                </p>
            </nav>

            <main class="mainFrame">
                <h1> Sign-Up Page</h1>
                <p>
                    To gain full access to our portal you need to subscribe, but dont worry, its quick and painless.<br>
                    Simply give us a user name you would like to use, a password and your email address.<br>
                    The user name needs to be one that hasnt been used, so we will run a check on that.<br>
                    The password needs to be a mix of letters, numbers and special characters.<br>
                    Your email address has to be something like joe@aol.com.
                </p>

                <?php if (isset($error)) echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>"; ?>

                <p>
                    <form method="POST" action="signup.php">
                        <p><input type="text" name="username" placeholder="Username" required></p>
                        <p><input type="password" name="password" placeholder="Password" required></p>
                        <p><input type="email" name="email" placeholder="email address" required><br><br></p>
                        <p><button type="submit">Sign Up</button></p>
                </p>
            </main>
        </div>
    </body>
</html>
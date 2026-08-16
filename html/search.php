<?php
// create session if not in one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
            <!-- Top title bar -->
            <header class="titleBar">
                <div class="header-left">
                    <h1>Aston Software Ltd</h1>
                </div>
                
                <div class="header-right">
                    <!-- If logged in show log out and projects links -->
                    <?php if (isset($_SESSION['uid'])): ?>
                        <span>Hello <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span><br>
                        <a href="projects.php"> Go to projects</a><br>
                        <a href="logout.php">Log Out</a>

                    <!-- Else show login boxes -->
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

            <!-- Navigation side bar -->
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
                        <a href="projects.php" class="button1">Update a Project</a>
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

            <!-- Main content "frame" -->
            <main class="mainFrame">
                <h1>Search for Project: By name or start date.</h1>
                <p>Here you can search through our server for a specific project in one of two ways, either type a word thats in the project name in the one box and press search or a start date.</p>

                <!-- Big global search box, so people dont get lost -->
                <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">

                    <!-- Search by name -->
                     <div style="flex: 1; min-width: 200px; background-color: #dfdfdf; border: 1px solid #ccc; padding: 20px; border-radius: 5px;">
                        <h3>Keyword search</h3>

                        <form action="searchResults.php" method="GET">
                            <input type="hidden" name="searchType" value="name">
                            <div style="margin-bottom: 15px;">
                                <label for="title">Project Name:</label><br>
                                <input type="text" id="title" name="title" placeholder="Type word here" style="width:75%; padding: 8px; box-sizing: border-box; margin-top: 5px;" required>
                            </div>
                            <button type="submit" class="button1">Search by project name</button>
                        </form>
                    </div>

                    <!-- Search by date -->
                    <div style="flex: 1; min-width: 200px; background-color: #dfdfdf; border: 1px solid #ccc; padding: 20px; border-radius: 5px;">
                        <h3>Search by start date</h3>

                        <form action="searchResults.php" method="GET">
                            <input type="hidden" name="searchType" value="date">
                            <div style="margin-bottom: 15px;">
                                <label for="startDate">Start Date:</label><br>
                                <input type="date" id="startDate" name="startDate" style="width: 75%; padding: 8px; box-sizing: border-box; margin-top: 5px;" required>
                            </div>
                            <button type="submit" class="button1">Search by start date</button>
                        </form>
                    </div>
                </div>

            </main>
        </div>
    </body>
</html>
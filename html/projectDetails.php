<?php
// create session if not in one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the title from the URL (otherwise $urlTitle = null)
$urlTitle = $_GET['title'] ?? null;

// Clear down $project
$project = null;

// if $urlTitle has something in it, use it to create a JSON request for the information we want
if ($urlTitle) {
    // create a variable with the link to the NodeJS call
    $url = 'http://127.0.0.1:3000/projectDetails';
    $message = json_encode(['title' => $urlTitle]);

    // Create HTML header in array ready to send
    $options = [
        'http' => [
            'header' => "Content-Type: application/json",
            'method' => 'POST',
            'content' => $message,
        ],
    ];

    //Send message and capture reply
    $stream = stream_context_create($options);
    $response = @file_get_contents($url, false, $stream);

    //var_dump($response); // Debug code chasing an email bug
    //exit;

    // Take response and parse it into the $project array we cleared down at the start
    if ($response !== false) {
        $result = json_decode($response, true);
        if (!empty($result['success']) && !empty($result['project'])) {
            $project = $result['project'];
        }
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
                <h1>Deailed Project View</h1>
                <p>This is the detailed project view, here you can see extra information on the project, including contact information and current development phase<br>
                To return to the previous screen please press the back button on your browser, the "project list" in the navigation bar to your left or the back button below.</p><br>
                <h2><?= htmlspecialchars($project['title']) ?></h2>
                <h3>Short project description:</h3>
                <p><?= htmlspecialchars($project['shortDescription']) ?></p>
                <p>Start Date: <?= date('d-m-Y', strtotime($project['startDate'])) ?></p>
                <p>End Date: <?= !empty($project['endDate']) ? date('d-m-Y', strtotime($project['endDate'])) : "Empty" ?></p>
                <p>Current Phase: <?= htmlspecialchars($project['phase']) ?></p>
                <p>Contact email: <?= !empty($project['email']) ? htmlspecialchars($project['email']) : "Empty" ?></p>
                <br><br>
                <p><a href="projectList.php" class="button1">Back</a></p>

            </main>
        </div>
    </body>
</html>
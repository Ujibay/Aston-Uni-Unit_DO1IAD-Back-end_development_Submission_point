<?php
// create session if not in one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kick someone out who accessed this page without being logged in
if (empty($_SESSION['username'])) {
    header('Location: signup.php');
    exit();
}

// Send JSON request for project list
// create a variable with the link to the NodeJS call
$url = 'http://127.0.0.1:3000/projects';

// set document header to be json, end of line characters, using POST and no content
$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode(['username' => $_SESSION['username']])
    ],
];

// create stream in varaible called context
$context = stream_context_create($options);

// send context to url and get a response back saved to response.
$response = @file_get_contents($url, false, $context);

// clear projects array
$projects =[];

// if it did not fail (it infers a true from text strings so better to do not false)
if ($response !== false) {
    // decode response into result
    $result = json_decode($response, true);
    // if it gets back 'success' and projects actually has projects in it...
    if (!empty($result['success']) && isset($result['projects'])) {
        // save projects to $projects
        $projects = $result['projects'];
    }
    // all that just to get the data, gezz
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
                <h1>My Project</h1>
                <p>This is a list of all of your projects, click on the project name if you wish to edit or delete it.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Start Date</th>
                            <th>Short Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($projects)): ?>
                            <?php foreach ($projects as $row): ?>
                                <tr>
                                    <td>
                                        <a href="updateProject.php?title=<?= urlencode($row['title']) ?>">
                                            <?= htmlspecialchars($row['title'] ?? '') ?>
                                        </a>
                                    </td>
                                    <td><?= date('d-m-Y', strtotime($row['startDate'])) ?></td>
                                    <td><?= htmlspecialchars($row['shortDescription'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td>No projects found!</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </main>
        </div>
    </body>
</html>
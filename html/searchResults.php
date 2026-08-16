<?php
// create session if not in one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// lets create an array to put all the results in
$projects = [];
// And somewhere for error messages to go so we can display those
$errorMessage = '';
// Lets get the searchType thats been passed from search.php
$searchType = $_GET['searchType'] ?? '';

// Now to figure out if its a word or a date... or empty
if (!empty($searchType)) {
    // The title and date will be varified by the node.js before it gets to the sql server, so we just need to bundle it up here

    $message = [
        'title' => trim($_GET['title'] ?? ''),
        'startDate' => trim($_GET['startDate'] ?? '')
    ];

    // Now to make a json packet with it
    $jsonMessage = json_encode($message);

    // And set up the HTTP POST request:
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => $jsonMessage,
        ],
    ];

    //Send message and capture reply
    $stream = stream_context_create($options);
    $response = @file_get_contents('http://127.0.0.1:3000/search', false, $stream);

    // Take response and parse it into the $project array we cleared down at the start
    if ($response !== false) {
        $result = json_decode($response, true);

        if (!empty($result['success']) && !empty($result['project'])) {
            $projects = $result['project'];
        } else {
            $errorMessage = $result['message'] ?? 'No project matched your search.';
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
                  <main class="mainFrame">
                <h1>Search results</h1>
                <p>This is a list of all projects on the database that match your search criteria, for more detailed information on the project please click on the project title.</p>
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
                                        <a href="projectDetails.php?title=<?= urlencode($row['title']) ?>">
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
            </main>
        </div>
    </body>
</html>
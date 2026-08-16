<?php
// create session if not in one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Editing a project is based on the code for creating a project, as it will have all the layout already done
// combine this with the code from projectDetails.php and it should work quite well

// First off this needs to be a secure page so you need to be logged in, so is uid set in the session information
// if not then go away back to the index page.
if (!isset($_SESSION['uid'])) {
    header('Location: signup.php');
    exit();
}

// STEP 1: GET TITLE FOR PROJET WE WANT TO EDIT
// now lets Get the title from the URL (otherwise $urlTitle = null)
$urlTitle = $_GET['title'] ?? null;

// Nw we clear down $project, ready to get data about the current project
$project = null;

// STEP 2: FETCH THE PROJET DETAILS USING THE PROJETDETAILS FUNCTION
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

    // Take response and parse it into the $project array we cleared down at the start
    if ($response !== false) {
        $result = json_decode($response, true);
        if (!empty($result['success']) && !empty($result['project'])) {
            $project = $result['project'];
        } else {
            $errorMessage = 'Project could not be found';
        }
    } else {
        $errorMessage = 'No message returned from server, connection issue?';
    }
}
// END OF PROJECT DETAILS LOADING

// MAPPING OF VARIABLES FORM $_POST OR $PROJECT TO FORM VARIABLES
// Each line is proessed from left to right, so a post variable overrules a preloaded table which overrules an empty string
$title              = $_POST['title']               ?? $project['title']            ?? '';
$shortDescription   = $_POST['shortDescription']    ?? $project['shortDescription'] ?? '';
$startDate          = $_POST['startDate']           ?? $project['startDate']        ?? '';
$endDate            = $_POST['endDate']             ?? $project['endDate']          ?? '';
$phase              = $_POST['phase']               ?? $project['phase']            ?? '';
$username           = $_POST['username']            ?? $project['username']         ?? $_SESSION['username']    ?? '';
   
    // Check date format is correct by running both through a loop with date (Y-m-d), 
    // if its correct nothing changes, if its not it gets fixed
    if (!empty($startDate)) {
        $startDate = date('Y-m-d', strtotime($startDate));
    }

    if (!empty($endDate)) {
        $endDate = date('Y-m-d', strtotime($endDate));
    }

// START OF POSTING UPDATED PROJET TO SQL SERVER
//Need a place to put usernames
$users = [];

// now to call a function in server.js to get a list of users for the drop down menu
$userList = @file_get_contents('http://127.0.0.1:3000/users');

// if user list is not empty then...
if ($userList !== false) {

    // decode the message
    $userData = json_decode($userList, true);

    // check if userdata.users has things in it and if so send it over to users, ready for use in the form...
    if (!empty($userData['success']) && !empty($userData['users'])) {
        $users = $userData['users'];
    }
}

// Posting of the Form (AKA creating the project)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // a lot of 'trim'ing of spaces before and after each entry is loaded into $message ready
    $title             = trim($_POST['title'] ?? '');
    $shortDescription  = trim($_POST['shortDescription'] ?? '');
    $startDate         = trim($_POST['startDate'] ?? '');
    $endDate           = trim($_POST['endDate'] ?? '');
    $phase             = trim($_POST['phase'] ?? '');
    $username          = trim($_POST['username'] ?? '');
    $originalTitle     = trim($_POST['originalTitle'] ?? '');
     


    // Clear any past error messages, or create variables if its the first time:
    $errorMessage = '';
    $successMessage = '';

    // If the user pushes the post button with errors in the form lets catch it and return it for edit
    //first lets check if both dates are not empty, and then that the end date isnt
    if (!empty($startDate) && !empty($endDate) && (strtotime($endDate) < strtotime($startDate))) {
        $errorMessage = 'Project cant complete before it starts, move End Date to a date later than Start Date.';

    // Lets check the development phase is valid    
    } else if (!in_array($phase, ['design', 'development', 'testing', 'deployment', 'completed'])){
        $errorMessage = 'Development phase not valid';

    // and lets check project title length is 255 or less
    } else if (strlen($title) > 255){
        $errorMessage = 'Project title must be 255 characters or less';

    // and lets check project title length is 255 or less
    } else if (strlen($shortDescription) > 255){
        $errorMessage = 'Project description must be 255 characters or less';
    
    } else {

        // Create the message after all checks are done
        $message = [
            'title'             => $title,
            'shortDescription'  => $shortDescription,
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'phase'             => $phase,
            'username'          => $username,
            'originalTitle'     => $originalTitle
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($message),
            ],
        ];

        //Send message and capture reply
        $stream = stream_context_create($options);
        $response = @file_get_contents('http://127.0.0.1:3000/updateProject', false, $stream);

        // now for some original code, as the above was taken from projectDetails.php
        // And all it needs to do is a nice message to say project created or project failed.
        if ($response !== false) {
            $result = json_decode($response, true);
            if (!empty($result['success'])) {
                $successMessage = 'Project updated successfully!';

                // Lets send them back to the projects.php page so they can edit another and also see the update.
                header('Location: projects.php');
                exit();

            } else {
                $errorMessage = $result['message'] ?? 'Failed to create new project';
            }
        } else {
            $errorMessage = 'Can not connect to database server';
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
                <h1>Update project</h1>

                <!-- Form to create new project -->
                <form action="updateProject.php" method="POST" style="max-width: 500px; background-color: #dfdfdf; padding: 20px; border-radius: 5px; margin: 0 auto;">

                    <div style="margin-bottom: 15px;">
                        <label for="title">Project Title:</label><br>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" maxlength="255" required style="width: 50%; padding: 8px; box-sizing: border-box; margin-top: 5px;">
                        <input type="hidden" name="originalTitle" value="<?= htmlspecialchars($urlTitle ?? '') ?>">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="username">Project Manager: </label><br>
                        <select id="username" name="username" required style="width: 50%; padding: 8px; box-sizing: border-box; margin-top: 5px;">
                            <option value="">Select project manager</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= htmlspecialchars($user['username']) ?>" <?= ($username === $user['username']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="phase">Project Phase: </label><br>
                        <select id="phase" name="phase" required style="width: 50%; padding: 8px; box-sizing: border-box; margin-top: 5px;">
                            <option value=""> Select a project phase </option>
                            <option value="design" <?= ($phase === 'design') ? 'selected' : '' ?>> Design </option>
                            <option value="development" <?= ($phase === 'development') ? 'selected' : '' ?>>Development</option>
                            <option value="testing" <?= ($phase === 'testing') ? 'selected' : '' ?>>Testing</option>
                            <option value="deployment" <?= ($phase === 'deployment') ? 'selected' : '' ?>>Deployment</option>
                            <option value="completed" <?= ($phase === 'completed') ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="startDate">Start Date: </label><br>
                        <input type="date" id="startDate" name="startDate" value="<?= htmlspecialchars($startDate) ?>" required style="width: 50%; padding: 8px; box-sizing: border-box; margin-top: 5px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="endDate">End Date: </label><br>
                        <input type="date" id="endDate" name="endDate" value="<?= htmlspecialchars($endDate) ?>" required style="width: 50%; padding: 8px; box-sizing: border-box; margin-top: 5px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="shortDescription">Short Description: </label><br>
                        <textarea id="shortDescription" name="shortDescription" maxlength="255" rows="4" required style="width: 50%; padding: 8px; box-sizing: border-box; margin-top: 5px;"><?= htmlspecialchars($shortDescription) ?></textarea>
                    </div>

                    <button type="submit" class="button1">Update Project</button>
                </form>
                <p></p>

                <!-- Form error/success messages -->
                <!-- New project completed ok! -->
                <?php if (!empty($successMessage)): ?>
                    <p><?= htmlspecialchars($successMessage) ?></p>
                <?php endif; ?>

                <!-- And when it doesnt, and theres an error message explaining why that goes here -->
                <?php if (!empty($errorMessage)): ?>
                    <p style="color: red; font-weight: bold; text-align: center;"><?= htmlspecialchars($errorMessage) ?></p>
                <?php endif; ?>

            </main>
        </div>
    </body>
</html>
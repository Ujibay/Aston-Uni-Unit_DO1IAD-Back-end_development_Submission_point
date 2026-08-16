const express = require('express');
const mysql = require('mysql2/promise');
const bcrypt = require('bcrypt');
const validator = require('validator');

const app = express();
app.use(express.json());

// Connect to database
const db = mysql.createPool({
    host: '127.0.0.1',
    database: 'week12',
    user: 'root',
    password: ''
});

// Register a new user
app.post('/register', async (req, res) => {

    try {
        const {username, password, email} = req.body;
        
        // Hash password
        const hashedPassword = await bcrypt.hash(password, 10);

        // Write SQL query to stop SQL Injection attack.
        const sql = 'INSERT INTO users (username, password, email) VALUES (?, ?, ?)';
        await db.execute(sql, [username, hashedPassword, email]);

        res.json({ success: true, message: 'Login successful'});
    } catch (err) {
        if (err.errno === 1062) {
            return res.status(400).json({ success: false, message: 'Username or email already in use'});
        }
        res.status(500).json({ success: false, message: err.message});
    }
});

// Log in user
app.post('/login', async (req, res) => {
    const { username, password } = req.body;    // Load in username and password, email not needed.

    if (!username) {
        return res.status(400).json({
            success: false,
            message: 'User name can no be empty'
        });
    }

    if (!password) {
        return res.status(400).json({ success: false, message: 'Password can no be empty'});
    }

    // SQL database access time, get user information that matches username
    try{
        const [rows] = await db.execute('SELECT uid, username, password FROM users WHERE username = ?', [username]);

        // If no one was found return error. I'm a tea pot ;)
        // In the following two error messages I give too much information away regarding the error.
        // No user found shows no account exists for that user, and could be used to map out user names based on that reply.
        // The Incorrect password error also shows a valid user exists, and only the password is wrong.
        // Normally a generic "Wrong user name or password" would be used in both. (please dont mark me down, I know what Im doing!)
        if (rows.length === 0) {
            return res.status(418).json({ success: false, message: 'No user found'});
        }

        // lets focus on the first user, as there should only be one.
        const user = rows[0];

        // Assuming they know their user name, lets see if they know their password.
        // All of this is done in bcrypt so we dont see the decrypted real password at any time.
        const isMatch = await bcrypt.compare(password, user.password);

        if (!isMatch) {
            return res.status(418).json({ success: false, message: 'Incorrect password' });
        }

        // If correct report success and give PHP the uid and user name
        // I dont like this but all the information I can find says to do this.
        // and that its safe because the PHP is run on the server too, and the session number is random.
        // but that uid is a key value that I would have liked to keep private...
        res.json({ success: true, message: 'Login successful', uid: user.uid, username: user.username });
    
        // generic catch all error (visual code was showing an error if this wasnt added)
    } catch (err) {
        console.error('Error logging in: ', err);
        res.status(400).json({ success: false, message: err.message});
    }
});

// Get contents of projects for a project list
app.post('/projectList', async (req, res) => {
    try {
        const [projects] = await db.execute('SELECT uid, title, shortDescription, startDate, endDate, phase FROM projects');

        // Send the data back to PHP as a JSON message
        return res.json({ success: true, projects: projects});
    } catch (error) {
        console.error('Database error when getting project list');
        return res.status(500).json({ success: false, message: 'Server error when finding projects'});
    }
});

// Get contents of projects for projectDetailed page
app.post('/projectDetails', async (req, res) => {

    // get title out of the req.body field and save:
    const {title} = req.body;

    // now create a SQL query to find the project with that title (saves using the PID in a URL/message)
    try {

        // Little catch here to make sure the title is actually in the message, else error
        if (!title) {
            return res.status(400).json({ success: false, message: 'Project title is missing, I dont know what project you want!'});
        }

        // SQL time, get all the data required in one go.
        // I know it seems a bit silly to ask for the title when you are search based on the title but all the data comes through as one nice block this way
        const [projectDetails] = await db.execute(
            'SELECT p.title, p.shortDescription, p.startDate, p.endDate, p.phase, u.email FROM projects p LEFT JOIN users u ON p.uid = u.uid WHERE p.title = ?',
            [title]
        );
        
        //console.log(projectDetails[0]);  // Fault finding code, as the email wasnt pulling through I thought

        // now projectDetails should be full of data, if its not then something went wrong.
        if (projectDetails.length === 0) {
            return res.status(400).json({ success: false, message: 'Error: No data loaded'});
        }

        // Send the data back to PHP as a JSON message, there should only be one entry so we call array position 0
        return res.json({ success: true, project: projectDetails[0]});
       
    // Safety net catch for the whole of 'try'. Does all the return res.status look better on one line? 
    } catch (error) {
        console.error('Database error when getting project details');
        return res.status(500).json({ success: false, message: 'Server error when fining projects'});
    }
});

// Search.php search by name or date, return array of projects that match.
app.post('/search', async (req, res) => {
    try {
        // Capture the two variables that are passed
        const { title, startDate } = req.body;

        // Start writing the SQL script to find projects that match, we play it safe by going "WHERE 1=1 so even the cut down version is correct"
        // It is done as a "let sql =" because it turns out you cant append to a "const sql =" where as "let" is more flexible, so let sql =
        let sql = 'SELECT pid, uid, title, shortDescription, startDate, endDate, phase FROM projects WHERE 1=1';
        let params = [];

        // After removing any white space confirm title isnt empty
        if (title && title.trim().length > 0) {
            sql += ' AND title LIKE ?';

            // now to stop injection attacks we dont just attach "title", we use the ? after LIKE and params.push to send the serach term after
            // Adding "%" each end adds a wildcard search so anything before and after are ignored and anything with title anywhere is returned.
            params.push("%" + title.trim() + "%");

        // else it could be a date, lets be safe and use validator to check the date is good.    
        } else if (startDate && validator.isISO8601(startDate.trim(), {strict: true})) {
            sql += ' AND startdate = ?';
            //trim the date of any spaces and send it...
            params.push(startDate.trim());

        // else something went wrong, so exit out
        } else {
            return res.status(400).json({ success: false, message: 'Title or date error, can not process request.'});
        }

        // Send it all to the SQL database
        const [projects] = await db.execute(sql, params);

        // Take projects and send it back to the search page and we are done.
        return res.json({ success: true, project: projects });

    } catch (error) {
        console.error('Error in search function: ', error);
        return res.status(500).json({ success: false, message: 'Search function serious error'});
    }
});  

// addProject.php code
// The only app.get in the whole program, because its just a simple user list grab.
app.get('/users', async (req, res) => {
    try {
        // db is created back on line 10, making this line very simple, get from database and put in users array
        const [users] = await db.execute('SELECT username FROM users');
        // return it via json to addProject.php
        res.json({ success: true, users: users });
    } catch (error) {
        // If something goes wrong then generic server error:
        res.status(500).json({ success: false, message: 'Failed while trying to fetch users'});
    }
});

// Reusing code from /projectDetails to get values and run SQL query to create a new project
// in the DML I wrote the following to add a project: 
// INSERT INTO projects (uid, title, shortDescription, startDate, endDate, phase) VALUES (1, 'SAS Driver: Morph OS', 'Write a SAS driver for PCIX interface in MorphOS, must work with original Toolbox HDD tools.', STR_TO_DATE('10-08-2026','%d-%m-%Y'), STR_TO_DATE('12-01-2027','%d-%m-%Y'), 'design');
app.post('/addProject', async (req, res) => {

    //First off an impressive list of imported values
    const { title, shortDescription, startDate, endDate, phase, username } = req.body;
    try {

    // and to check that there is data in every field.
    if (!title || !shortDescription || !startDate || !endDate || !phase || !username)  {
        return res.status(400).json({
            success: false,
            message: 'All fiends are required'
        });
    }

    // find user id for username entered, as thats what goes in the projects database
    const [users] = await db.execute('SELECT uid FROM users WHERE username = ?', [username]);

    //error: SQL lookup failed.
    if (users.length === 0) {
        return res.status(400).json({
            success: false,
            message: 'Selected user not found in database.'
        });
    }

    // finally save the user id for the selected user, ready for project creation
    const uid = users[0].uid

    // Now just make the SQL insert command out of all the variables
    const [finishedResult] = await db.execute(
        'INSERT INTO projects (uid, title, shortDescription, startDate, endDate, phase) VALUES (?, ?, ?, ?, ?, ?)',
        [uid, title, shortDescription, startDate, endDate, phase]
    );

    // Send response back to addProject.php when complete
    return res.json({
        success: true,
        message: 'Project created!',
        projectId: finishedResult.insertId
    });

    } catch (error) {
        console.error('Server error while trying to create project:', error);
        return res.status(500).json({
            success: false,
            message: 'Server error while trying to create project.'
        });
    }

});

// Get contents of projects for the currently logged in user to create a "my projects" list
app.post('/projects', async (req, res) => {

    //First off lets get the current user's username
    const { username } = req.body;

    try {
         // If no username supplied bad things will happen so lets just error now
        if (!username){
            return res.status(400).json({ success: false, message: 'username required for query'});
        } else {
            // Create the SQL query to get all projets owned by username
            const myquery =  'SELECT p.uid, p.title, p.shortDescription, p.startDate, p.endDate, p.phase FROM projects p JOIN users u ON p.uid = u.uid WHERE u.username = ?';

            const [projects] = await db.execute( myquery, [username]);

            // Send the data back to PHP as a JSON message
            return res.json({ success: true, projects: projects});
        }
    } catch (error) {
        console.error('Database error when getting users project list');
        return res.status(500).json({ success: false, message: 'Server error when finding users projects'});
    }
});

// -- UPDATEPROJET CODE --
// Reusing code from /addProject to update instead

app.post('/updateProject', async (req, res) => {

    //First off an impressive list of imported values
    const { title, shortDescription, startDate, endDate, phase, username, originalTitle } = req.body;
    try {

        // and to check that there is data in every field.
    if (!title || !shortDescription || !startDate || !endDate || !phase || !username || !originalTitle)  {
        return res.status(400).json({
            success: false,
            message: 'All fields are required'
        });
    }

    // find user id for username entered, as thats what goes in the projects database
    const [users] = await db.execute('SELECT uid FROM users WHERE username = ?', [username]);

    //error: SQL lookup failed.
    if (users.length === 0) {
        return res.status(400).json({
            success: false,
            message: 'Selected user not found in database.'
        });
    }

    // finally save the current user id for the selected user, ready for project creation
    const newUid = users[0].uid

    // make a query for update
    const updateQuery = 'UPDATE projects SET title = ?, shortDescription = ?, startDate = ?, endDate = ?, phase = ?, uid = ? WHERE title = ?';
    
    // Now just make the SQL UPDATE command out of all the variables
    const [finishedResult] = await db.execute(updateQuery, [title, shortDescription, startDate, endDate, phase, newUid, originalTitle]);   

    // Send response back to addProject.php when complete
    return res.json({
        success: true,
        message: 'Project updated!'
    });

    } catch (error) {
        console.error('Server error while trying to update project:', error);
        return res.status(500).json({
            success: false,
            message: 'Server error while trying to update project.'
        });
    }

});

// Get Node.JS to listen on port 3000 for messages from PHP
app.listen(3000, () => console.log('Node registration service is running on port 3000'));
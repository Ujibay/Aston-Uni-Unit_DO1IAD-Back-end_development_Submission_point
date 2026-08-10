# Aston-Uni-Unit_DO1IAD-Back-end_development_Submission_point
to develop and deploy a secure and performant database-driven website that manages software projects for a company.

The plan

DATABASE
Create .sql document containing the following tables:
  users with entries (uid, username, password, email) uid primary key, others required not blank
  projects (pid, title, start date, end date, short description, phase, uid) pid primary key, uid foreign key
  Note: A project can have only one user but a user can have many projects, that works out fine with the above table, no work needed.
  Note: Phase needs to be limited to the following list: "design", "development", "testing", "deployment", "complete". (We did this in an earlier assignment, dig out the code and you are done.
  Note: I am free to add additional tables and fields if necessary.

  Populate the database with test data (Can be done manually but lets go for automatic as part of an SQL query like we did before)
  Ruberic: 16%
    First: Comprehensive database design, effectively implemented; all relationships, constraints and foreign keys used correctly, no errors.
    upper second: Good database design with minor flaws; most relationships and constraints are implemented correctly.
    Lower second: Functional database design with noticeable gaps, some relationships missing or implemented incorrectly
    third: Basic database design, lacks key relationships and constraints, leading to limited functionality
    Fail: Database design is fragmented or absent, doesnt support project objectives or lacks essential relationships and functionality
    
  BACKEND
  Thinking of using Node.JS (it seems more my sort of language) but probably some PHP too if needed. (There may be things Node.JS cant do, dont know yet) 

  Public user requirements: (not logged in)
    View a list of all projects (title, start date, short description). // (List looks important here)
    View project details (including end date, phase and user email). // As in click on an item on a list and see basic details
    A search option to search by title or start date (need to think of a good way of searching by date, as thats not the normal search bar, backend is easy enough I would think)
    Register as a new user. (we have done that already, could reuse some code)
    
    Registered user requirements (logged in)
    Way to Log into the system
    A way to Add new project.
    A way to update a project.
    And a log out button/system

RUBERIC 165
  first:         All functional requirements are implemented to a high standard; 
                 the website is fully dynamic and responsive. 
                 User management and project CRUD operations function flawlessly.
  Upper Second:  Most functional requirements are implemented with minor issues
                 the website is largely dynamic and responsive.
  Lower Second:  Some functional requirements are implemented but significant functionality is missing or buggy.
  Third:         Minimal functionality implemented;
                 key features (like CRUD operations or login) are flawed or missing.
  Fail:          Very limited or no functionality implemented.
                 critical server-side components are broken or absent.

    SECURITY MEASURES
    I MUST IMPLEMENT FIVE SECURITY FEATURES ON WEBSITE
      Authentication (user login) / Well thats easy enough, but need to make sure its secure, password is encrypted etc.
      Authorisation (Limit editing of projects to the person whos UID is assigned to that project) / That would be filtered anyway but I guess lets try and stop cross site scripting, so check all the time that the user is the intended user (repudiation?)
      Form Validation (validation on both client and server side)
      SQL/HTML injection protection
      Password hashing (thats an easy one)
      cross-site request forgery (csrf) prevention

      RUBERIC 16%
        First:        Implements at least 5 security features with robust protection against common vulnerabilities 
                      (for example, CSRF, SQL injection); 
                      excellent password handling and data validation.
        Upper Second: Implements 4-5 security features
                      good protection against vulnerabilities but some minor weaknesses.
        Lower second: Implements 3-4 security features; protection is incomplete or lacks robustness.
        Third:        Implements 2-3 security features; basic protection measures in place but with clear gaps.
        fail:         Little or no attention to security; 
                      significant vulnerabilities present (for example, no password hashing, lack of validation).
      
    GOOD CODING PRACTICE
      Proper use of comments.
      Consistent and logical naming conventions.
      Efficient and modular code

      RUBERIC DETAILS: 16%
      First:  The code follows excellent structure, readability, and modularity.
              and uses best practices consistently (for example, commenting, naming conventions).
      Upper Second: The code is well-structured with some minor issues; 
                    generally follows good practices with occasional lapses.
      Lower Second: The code is functional but lacks proper structure and consistency; and occasional use of best practices.
      Third:        The code is poorly structured with minimal attention to coding conventions; and lacks maintainability.
      Fail:         The code is disorganised, inconsistent, and largely unreadable; 
                    no consideration of best practices or maintainability.

    USER INTERFACE
      The user interface must be intuitive, clean and responsive
      All webpages should be easy to navigate, with appropriate text size, font and colour. (be boring)
      Use descriptive names for links and buttons (Easy)
      Ensure a consistent layout across pages. (colours, layout, header, footer etc)
      You are free to use any front-end technologies (HTML, CSS, Javascript and libraries) to enhance your UI. (CSS would be good, although Im used to just frames generating a nice layout so HTML... will see where the project takes me)

      RUBERIC 16%
        First:         Interface is intuitive, clean, and fully responsive; 
                       excellent usability and aesthetic appeal; seamless navigation across the site.
        Upper Second:  Good interface with minor issues; generally clean and responsive; navigation is mostly clear.
        Lower Second:  Acceptable interface with some usability issues; navigation is not always intuitive; design is basic.
        Third:         Poor interface with significant usability issues; navigation is confusing or incomplete; design is messy.
        Fail:          No meaningful user interface; site is difficult or impossible to navigate; 
                       major usability issues make the site non-functional.

      PROJET REPORT
      You are required to submit a two-page project report (in PDF format) that includes the following:
        Basic information: Your name, student D Hyperlink to your systems entry page, and a test user's credentials (username and password)
        Source code link: A GitHub (or other platform) link to your source code (if used)
        Functionality: A list of the required functions implemented and the corresponding source files.
        Security features: A list of the implemented security features with corresponding source files.
        Additional notes: Any other information relevant to running or evaluating your website.
        Avoid screenshots
        Ensure report is clear and concise to avoid penalties for missing or unclear information.
        
      RUBERIC 20%
        First:        Clear, concise, and complete report; 
                      all required information present with thoughtful analysis of functionality and security.
        Upper Second:  Good report with minor omissions; presents most of the required information clearly.
        Lower Second:  Report is mostly complete but lacks depth in the analysis or is missing key details.
        Third:        Report is incomplete or poorly written; lacks significant information about functionality or security.
        Fail:          Report is missing or extremely unclear, omitting critical details about the project or its implementation.

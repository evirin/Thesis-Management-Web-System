<?php 
require_once(__DIR__.'/config.php');


if (session_status() == PHP_SESSION_NONE) {
	// Session is not started, so start it
	session_start();
}

//Return array of pages allowed for logged in users
function getpublicPages(){
	$public_pages = ['login', 'sign-up'];

	return $public_pages;
}

// Check if user role has permision to view page, return bool
function checkViewPermission($view, $user_role){
    $pages = [
        'create-thesis' => [2],
        'dashboard' => [2],
        'examination-fields' => [1],
        'examination-report' => [1, 2, 3],
        'assigned-thesis' => [1],
        'invite-committee' => [1],
        'create-thesis' => [2],
        'list-thesis' => [2,3],
        'list-users' => [3],
        'profile' => [1],
        'view-thesis' => [2,3],
    ];

    if (array_key_exists($view, $pages)) {
        if (in_array($user_role, $pages[$view])) {
            return true;
        }
        else{
            return false;
        }
    }
    else{
        return true;
    }
}

//Based on the data provided get the view file from (/views) and return the html content of this view template. The javascript uses this html to paste it into #main-container
function getView($view = 'home', $params=[]){
    $mysqli = DbConnect();

    if($view == 'home'){
        if (getLoggedUser($mysqli)) {
            $user_role = getLoggedUser($mysqli)['role'];
            if($user_role == 1){
                $view = 'assigned-thesis';
            }
            else if($user_role == 2){
                $view = 'dashboard';
            }
            else if($user_role == 3){
                $view = 'list-thesis';
            }
            else{
                $view = 'login';
            }
        }
        else{
            $view = 'login';
        }
    }

	$views_path = ROOT_PATH.'/views';
	$view_file = $views_path.'/'.$view.'.tpl.php';
	$missing_page = $views_path.'/404.tpl.php';
	$access_denied_page = $views_path.'/denied.tpl.php';
	
	//If the view file exists show the contents of the file. Else return error
	if (file_exists($view_file)) {

        if (getLoggedUser($mysqli)) {
            $user_role = getLoggedUser($mysqli)['role'];
        }
        else{
            $user_role = 0;
        }

        if (checkViewPermission($view, $user_role)) {
            extract($params);
            return include($view_file);
        }
        else{
            return include($access_denied_page);
        }
        
	}
	else{
		return include($missing_page);
	}

}

// Get the current route
/*Return route object =
[
	'view' = 'page',
	'subpages' = ['page-1', 'page-2'],
]

Analyses the url and returns the data we need in a form of an array
*/ 
function getRoute($url = false){

	if ($url == false) {
		$url = $_SERVER['REQUEST_URI'];
	}

	//removing the BASE_PATH from the url to find the page
	$route = str_replace(BASE_PATH, '', parse_url($url, PHP_URL_PATH));

	$trimmed_route = trim($route, '/');

	$route_breakdown = explode('/', $trimmed_route);
	$route_object = [];

	if (userLoggedIn() || in_array($route_breakdown[0], getpublicPages())) {

		$route_object['view'] = $route_breakdown[0];

		if (count($route_breakdown) > 1) {
			$route_object['subpages'] = [];
			for ($i=1; $i < count($route_breakdown); $i++) {

				array_push($route_object['subpages'], $route_breakdown[$i]);

			}
		}
	}
	else{
		$route_object['view'] = 'login';
	}
	
	return $route_object;
}

// Connect to the database.
function DbConnect(){
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if ($mysqli->connect_error) {
    	die("Connection failed: " . $mysqli->connect_error);
	}

	return $mysqli;
}

// Set individual user. Works for all roles if the correct fields are provided
function setUser($mysqli, $name, $surname, $email, $password, $role, $topic = NULL, $landline = NULL, $mobile = NULL, $department = NULL, $university = NULL, $student_number = NULL, $street = NULL, $number = NULL, $city = NULL, $postcode = NULL, $mobile_telephone = NULL, $landline_telephone = NULL, $father_name = NULL) {

    $return_object = [
        'message' => [],
        'redirect_to' => '',
    ]; 

    try {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Start a transaction
        $mysqli->begin_transaction();

        // Insert the user and retrieve the new user ID
        $user_sql = "INSERT INTO users (name, surname, email, password, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($user_sql);
        if (!$stmt) {
            $return_object['message']['type'] = 'error';
            $return_object['message']['content'] = $mysqli->error;
            $return_object['redirect_to'] = 'sign-up';
            return $return_object;
        }
        $stmt->bind_param("ssssi", $name, $surname, $email, $hashed_password, $role);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            $return_object['message']['type'] = 'error';
            $return_object['message']['content'] = 'Failed to insert user';
            $return_object['redirect_to'] = 'sign-up';
            return $return_object;
        }
        $user_id = $stmt->insert_id;
        $stmt->close();

        //Check if user is a professor
        if ($role == 2) {
            // Check if the department exists
            $department_id = null;
            $department_check_sql = "SELECT id FROM departments WHERE name = ?";
            $department_stmt = $mysqli->prepare($department_check_sql);
            
            if ($department_stmt) {
                $department_stmt->bind_param("s", $department);
                $department_stmt->execute();
                $department_stmt->bind_result($department_id);
                $department_stmt->fetch();
                $department_stmt->close();
            }
            
            // If the department doesn't exist, insert it
            if (!$department_id) {
                $insert_department_sql = "INSERT INTO departments (name) VALUES (?)";
                $insert_department_stmt = $mysqli->prepare($insert_department_sql);

                if ($insert_department_stmt) {
                    $insert_department_stmt->bind_param("s", $department);
                    $insert_department_stmt->execute();
                    $department_id = $insert_department_stmt->insert_id;
                    $insert_department_stmt->close();
                }
            }

            // Check if the university exists
            $university_id = null;
            $university_check_sql = "SELECT id FROM universities WHERE name = ?";
            $university_stmt = $mysqli->prepare($university_check_sql);

            if ($university_stmt) {
                $university_stmt->bind_param("s", $university);
                $university_stmt->execute();
                $university_stmt->bind_result($university_id);
                $university_stmt->fetch();
                $university_stmt->close();
            }

            // If the university doesn't exist, insert it
            if (!$university_id) {
                $insert_university_sql = "INSERT INTO universities (name) VALUES (?)";
                $insert_university_stmt = $mysqli->prepare($insert_university_sql);

                if ($insert_university_stmt) {
                    $insert_university_stmt->bind_param("s", $university);
                    $insert_university_stmt->execute();
                    $university_id = $insert_university_stmt->insert_id;
                    $insert_university_stmt->close();
                }
            }

            // Insert the professor with the retrieved department_id and university_id
            $professor_sql = "INSERT INTO professors (user_id, topic, landline, mobile, department, university) VALUES (?, ?, ?, ?, ?, ?)";
            $professor_stmt = $mysqli->prepare($professor_sql);

            if (!$professor_stmt) {
                $return_object['message']['type'] = 'error';
                $return_object['message']['content'] = $mysqli->error;
                $return_object['redirect_to'] = 'sign-up';
                return $return_object;
            }

            $professor_stmt->bind_param("isssii", $user_id, $topic, $landline, $mobile, $department_id, $university_id);

            $professor_stmt->execute();

            $professor_stmt->close();
        }

        //Check if user is a student
        if ($role == 1) {
            // Check if  department exists

            // Insert the professor with the retrieved department_id and university_id
            $professor_sql = "INSERT INTO students (user_id, student_number, street, number, city, postcode, mobile_telephone, landline_telephone, father_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $professor_stmt = $mysqli->prepare($professor_sql);

            if (!$professor_stmt) {
                $return_object['message']['type'] = 'error';
                $return_object['message']['content'] = $mysqli->error;
                $return_object['redirect_to'] = 'sign-up';
                return $return_object;
            }

            $professor_stmt->bind_param("iisssssss", $user_id, $student_number, $street, $number, $city, $postcode, $mobile_telephone, $landline_telephone, $father_name);

            $professor_stmt->execute();

            $professor_stmt->close();
        }


        // Commit the transaction
        $mysqli->commit();

        // Success message
        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = 'User created successfully';
        $return_object['redirect_to'] = 'assigned-thesis';

    } catch (Exception $e) {
        // Rollback the transaction on error
        $mysqli->rollback();

        // Error message
        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = 'sign-up';
    }

    return $return_object;
}

// Check session cookies to see if user is logged in
function userLoggedIn(){

	if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true; 
    }
    else{
        return false; 
    }

}

// Return the details of the connected user
function getLoggedUser($mysqli){
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $id = $_SESSION['user_id'];

        $sql = "SELECT * FROM users WHERE id = ?";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            return $user;
        }
        else{
            return false;
        }
    }
    else{
        return false; 
    }
}

// Return the user data based on the user id provided
function getUser($id, $mysqli){
	$sql = "SELECT * FROM users WHERE id = ?";

	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
    	$user = $result->fetch_assoc();
    	return $user;
    }
    else{
    	return 'user not found';
    }
}

// Get all users based on role
function getUsers($mysqli, $type){
	if ($type != null) {
        $sql = "SELECT * FROM users WHERE role = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $type);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $users = $result->fetch_all(MYSQLI_ASSOC);
            return $users;
        }
        else{
            return [];
        }
	}
}

// Set the thesis status based on thesis id
function setStatus($thesis_id, $status, $mysqli) {
    $mysqli->begin_transaction();
    try {
        // Determine the current timestamp for `completed_on`
        $completed_on = null;
        if ($status == 5) { // If status is 5, set the `completed_on` timestamp
            date_default_timezone_set("Europe/Athens"); // Adjust to your timezone
            $completed_on = date("Y-m-d H:i:s");
        }

        // Update the `status` and optionally `completed_on` in the `theses` table
        if ($completed_on) {
            $query = "
                UPDATE theses 
                SET status = ?, completed_on = ? 
                WHERE id = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("isi", $status, $completed_on, $thesis_id);
        } else {
            $query = "
                UPDATE theses 
                SET status = ? 
                WHERE id = ?
            ";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ii", $status, $thesis_id);
        }

        $stmt->execute();
        $stmt->close();

        setLog($thesis_id, $status, $mysqli);

        // Commit the transaction
        $mysqli->commit();

        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = 'Status updated successfully';
        $return_object['redirect_to'] = 'view-thesis/'.$thesis_id;
    }
    catch (Exception $e) {
        // Rollback the transaction on error
        $mysqli->rollback();

        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = 'view-thesis/'.$thesis_id;
    }

    return $return_object;
}

// Update the student email based on user id
function updateStudentEmail($student_id, $email, $mysqli){
    $mysqli->begin_transaction();
    try {
        // SQL query to update student details
        $sql = "
            UPDATE users 
            SET 
                email = ?
            WHERE 
                id = ?
        ";

        // Prepare the query
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $mysqli->error);
        }

        // Bind parameters
        $stmt->bind_param("si", $email, $student_id);

        // Execute the query
        $stmt->execute();

        // Check if any rows were affected
        if ($stmt->affected_rows === 0) {
            throw new Exception("No rows were updated. Ensure the user_id exists or data is different.");
        }

        // Commit the transaction
        $mysqli->commit();

        // Close the statement
        $stmt->close();

        // Return success response
        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = 'Email updated successfully!';
        $return_object['redirect_to'] = 'profile/';
    }
    catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();

        // Return error response
        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = 'profile';
    }

    return $return_object;
}

// Update student details based on user id
function updateStudentDetails($student_id, $city, $street, $number, $postcode, $landline_telephone, $mobile_telephone, $mysqli) {
    // Begin transaction
    $mysqli->begin_transaction();
    try {
        // SQL query to update student details
        $sql = "
            UPDATE students 
            SET 
                city = ?, 
                street = ?, 
                number = ?, 
                postcode = ?, 
                landline_telephone = ?, 
                mobile_telephone = ? 
            WHERE 
                user_id = ?
        ";

        // Prepare the query
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $mysqli->error);
        }

        // Bind parameters
        $stmt->bind_param("sssissi", $city, $street, $number, $postcode, $landline_telephone, $mobile_telephone, $student_id);

        // Execute the query
        $stmt->execute();

        // Check if any rows were affected
        if ($stmt->affected_rows === 0) {
            throw new Exception("No rows were updated. Ensure the user_id exists or data is different.");
        }

        // Commit the transaction
        $mysqli->commit();

        // Close the statement
        $stmt->close();

        // Return success response
        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = 'Profile updated successfully!';
        $return_object['redirect_to'] = 'profile/';
    }
    catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();

        // Return error response
        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = 'profile';
    }

    return $return_object;
}

// Get student data by id
function getStudent($id, $mysqli) {
    try {
        // SQL query to join users and students based on user_id
        $sql = "
            SELECT 
                users.*, 
                students.*
            FROM 
                users
            INNER JOIN 
                students 
            ON 
                users.id = students.user_id
            WHERE 
                users.id = ?
        ";

        // Prepare and execute the query
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("i", $id); // Bind the user ID parameter
            $stmt->execute();

            // Get the result
            $result = $stmt->get_result();

            // Fetch all rows as associative arrays
            $students = [];
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }

            // Free the result set
            $result->free();

            // Close the statement
            $stmt->close();

            // Return the student data
            return $students;
        } else {
            throw new Exception("Failed to prepare statement: " . $mysqli->error);
        }
    } catch (Exception $e) {
        // Handle exceptions and return an error message
        return ["error" => $e->getMessage()];
    }
}

// Get all student data. Also filter students that are not assigned to thesis
function getStudents($mysqli, $only_available = false) {
    $students = [];

    try {
        // Base SQL query to join users and students based on user_id
        $sql = "
            SELECT 
                users.*, 
                students.*
            FROM 
                users
            INNER JOIN 
                students 
            ON 
                users.id = students.user_id
            WHERE 
                users.role = 1
        ";

        // If we want only available students, modify the query to exclude those in the theses table
        if ($only_available) {
            $sql .= "
                AND users.id NOT IN (SELECT student FROM theses WHERE student IS NOT NULL)
            ";
        }

        // Prepare and execute the query
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->execute();

            // Get the result
            $result = $stmt->get_result();

            // Fetch all rows as associative arrays
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }

            // Free the result set
            $result->free();

            // Close the statement
            $stmt->close();
        }
        else {
            throw new Exception("Failed to prepare statement: " . $mysqli->error);
        }
    }
    catch (Exception $e) {
        // Log or handle exceptions
        return ["error" => $e->getMessage()];
    }

    return $students;
}

// Get professors that do not prticipate in the committee or have declined an invitation of a certain thesis
function getAvailableProfessors($mysqli, $thesis_id) {
    try {
        // SQL query to fetch professors not already in the committee or those in the committee but not accepted
        $sql = "
            SELECT 
                u.id, 
                u.name, 
                u.surname
            FROM 
                users u
            WHERE 
                u.id NOT IN (
                    SELECT 
                        c.user_id
                    FROM 
                        committee c
                    WHERE 
                        c.thesis_id = ? AND (c.accepted IS NULL OR c.accepted = 1)
                )
                AND u.role = '2'
        ";

        // Prepare the statement
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Statement preparation failed: " . $mysqli->error);
        }

        // Bind the parameter
        $stmt->bind_param("i", $thesis_id);

        // Execute the query
        $stmt->execute();

        // Fetch results
        $result = $stmt->get_result();
        $availableProfessors = [];
        while ($row = $result->fetch_assoc()) {
            $availableProfessors[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'surname' => $row['surname']
            ];
        }

        // Close the statement
        $stmt->close();

        return $availableProfessors;

    } catch (Exception $e) {
        // Handle errors
        return [
            'error' => true,
            'message' => $e->getMessage()
        ];
    }
}

// Login functionality
function login($email, $password, $mysqli) {

    $sql = "SELECT id, email, password, role FROM users WHERE email = ?";
    // Prepare a SQL statement to fetch user details
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify the password using password_verify
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            if ($user['role'] == 1) {
                $redirect_to = 'assigned-thesis';
            }
            else if($user['role'] == 2){
                $redirect_to = 'dashboard';
            }
            else if($user['role'] == 3){
                $redirect_to = 'list-thesis';
            }
            else{
                $redirect_to = 'login';
            }

           	$return_object['message']['type'] = 'success';
	        $return_object['message']['content'] = 'Welcome!!!';
	        $return_object['redirect_to'] = $redirect_to;
            $return_object['callback'] = 'generateMenu';

        }	
        else {
            $return_object['message']['type'] = 'error';
	        $return_object['message']['content'] = "Your credentials are incorrect";
	        $return_object['redirect_to'] = 'login';
        }
    } 
    else {
        $return_object['message']['type'] = 'error';
	    $return_object['message']['content'] = "Your credentials are incorrect";
	    $return_object['redirect_to'] = 'login';
    }

    return $return_object;
}

// Logout functionality
function logout(){
	// Clear session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();

    $return_object['message']['type'] = 'success';
	$return_object['message']['content'] = "You successfully logged out";
	$return_object['redirect_to'] = 'login';


	return $return_object;
}

// Get thesis logs (status changes) by providng thesis id
function getLogs($thesis_id, $mysqli) {
    $logSQL = "
        SELECT 
            t.id AS thesis_id,
            t.topic AS topic,
            s.id AS status_id,
            s.status AS status,
            l.updated_on AS updated_on
        FROM 
            thesis_logs l
        LEFT JOIN theses t ON l.thesis_id = t.id
        LEFT JOIN thesis_status s ON l.status_id = s.id
        WHERE 
            l.thesis_id = ?
        ORDER BY 
            l.updated_on ASC
    ";

    try {
        // Prepare the statement
        $stmt = $mysqli->prepare($logSQL);
        if (!$stmt) {
            throw new Exception("Statement preparation failed: " . $mysqli->error);
        }

        // Bind parameters
        $stmt->bind_param("i", $thesis_id);

        // Execute the query
        $stmt->execute();

        // Fetch results
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Query execution failed: " . $mysqli->error);
        }

        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = [
                'thesis_id' => $row['thesis_id'],
                'topic' => $row['topic'],
                'status' => [
                    'id' => $row['status_id'],
                    'status' => $row['status']
                ],
                'updated_on' => $row['updated_on']
            ];
        }

        // Close statement
        $stmt->close();
    } catch (Exception $e) {
        // Handle errors
        return [
            'error' => true,
            'message' => $e->getMessage()
        ];
    }

    // Return the logs
    return $logs;
}

// insert log into the thesis_logs table
function setLog($thesis_id, $status_id, $mysqli){
    // Begin transaction
    $mysqli->begin_transaction();

    try {
        $stmt = $mysqli->prepare("
            INSERT INTO thesis_logs (thesis_id, status_id) VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $thesis_id, $status_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $mysqli->commit();

        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = 'Log was added successfully';
        $return_object['redirect_to'] = '';
    }
    catch(Exception $e){
        // Rollback transaction on error
        $mysqli->rollback();
        
        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = '';
    }

    return $return_object;
}

// Create a thesis
function createThesis($topic, $summary, $instructor, $student, $status, $uploaded_files, $assigned_on, $mysqli) {

    // Begin transaction
    $mysqli->begin_transaction();

    try {
        // Step 1: Insert thesis into the `thesis` table
        $stmt = $mysqli->prepare("INSERT INTO theses (topic, summary, instructor, student, status, assigned_on) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiiis", $topic, $summary, $instructor, $student, $status, $assigned_on);
        $stmt->execute();
        $thesis_id = $stmt->insert_id; // Get the ID of the newly created thesis
        $stmt->close();

        // Step 2: Handle file uploads and insert into `uploads` table
        $upload_ids = [];

        if ($uploaded_files) {
            foreach ($uploaded_files['name'] as $key => $filename) {
                if ($uploaded_files['error'][$key] === UPLOAD_ERR_OK) {
                    // Generate a unique file name
                    $unique_name = uniqid() . "_" . basename($filename);
                    $upload_path = __DIR__."/uploads/" . $unique_name;

                    // Move file to the uploads directory
                    if (move_uploaded_file($uploaded_files['tmp_name'][$key], $upload_path)) {
                        // Insert file info into `uploads` table
                        $upload_stmt = $mysqli->prepare("INSERT INTO uploads (filename) VALUES (?)");
                        $upload_stmt->bind_param("s", $unique_name);
                        $upload_stmt->execute();
                        $upload_ids[] = $upload_stmt->insert_id; // Get the ID of the uploaded file
                        $upload_stmt->close();
                    }
                }
            }

            // Step 3: Link thesis and uploaded files in the `thesis_uploads` table
            foreach ($upload_ids as $file_id) {
                $thesis_upload_stmt = $mysqli->prepare("INSERT INTO thesis_uploads (thesis_id, file_id) VALUES (?, ?)");
                $thesis_upload_stmt->bind_param("ii", $thesis_id, $file_id);
                $thesis_upload_stmt->execute();
                $thesis_upload_stmt->close();
            }
        }

        // Step 4: Add the instructor to the `committee` table
        $current_timestamp = date("Y-m-d H:i:s");
        $committee_stmt = $mysqli->prepare("INSERT INTO committee (user_id, thesis_id, accepted, invited_on, accepted_on) VALUES (?, ?, 1, ?, ?)");
        $committee_stmt->bind_param("iiss", $instructor, $thesis_id, $current_timestamp, $current_timestamp);
        $committee_stmt->execute();
        $committee_stmt->close();

        setLog($thesis_id, $status, $mysqli);
        // Commit transaction
        $mysqli->commit();
        
        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = "You successfully created a new thesis";
        $return_object['redirect_to'] = 'list-thesis';

    } catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();
        
        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = 'create-thesis';
    }

    return $return_object;
}

// Set the final text
function setFinalText($thesis_id, $text_link, $mysqli){
    $mysqli->begin_transaction();
    try {
        // Update the `final_text_file` field in the `theses` table
        $query = "
            UPDATE theses 
            SET final_text_file = ? 
            WHERE id = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $text_link, $thesis_id);
        $stmt->execute();
        $stmt->close();

        // Commit the transaction
        $mysqli->commit();

        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = "Link was set successfully";
        $return_object['redirect_to'] = 'assigned-thesis/';
    }
    catch (Exception $e) {
        // Rollback the transaction on error
        $mysqli->rollback();

        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = 'assigned-thesis';
    }

    return $return_object;
}

// Set the AP number
function setAp($thesis_id, $ap_number, $mysqli){
    $mysqli->begin_transaction();
    try {
        // Update the `ap_number` in the `theses` table
        $query = "
            UPDATE theses 
            SET ap_number = ? 
            WHERE id = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $ap_number, $thesis_id);
        $stmt->execute();
        $stmt->close();

        // Commit the transaction
        $mysqli->commit();

        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = "AP Number Updated successfully";
        $return_object['redirect_to'] = 'view-thesis/'.$thesis_id;

    }
    catch (Exception $e) {
        // Rollback the transaction on error
        $mysqli->rollback();

        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = 'view-thesis/'.$thesis_id;
    }

    return $return_object;
}

// Get the information of a cancelled thesis
function getCancelInfo($thesis_id, $mysqli){
    try {
        // Prepare the query to retrieve the cancellation info
        $query = "
            SELECT gen_number, gen_date, thesis_id, reason 
            FROM cancelled_theses 
            WHERE thesis_id = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $thesis_id);
        $stmt->execute();

        // Fetch the result
        $result = $stmt->get_result();
        $cancel_info = $result->fetch_assoc();

        $stmt->close();

        return $cancel_info;
    }
    catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// Cancel a thesis
function cancelThesis($mysqli, $thesis_id, $gen_number = NULL, $gen_year = NULL, $reason = NULL){
    $mysqli->begin_transaction();
    try {
        // Step 1: Update the status of the thesis in the `theses` table
        $update_status_query = "
            UPDATE theses 
            SET status = 6 
            WHERE id = ?
        ";
        $update_stmt = $mysqli->prepare($update_status_query);
        $update_stmt->bind_param("i", $thesis_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Step 2: Insert the cancellation details into the `cancelled_thesis` table
        $insert_cancelled_query = "
            INSERT INTO cancelled_theses (gen_number, gen_date, thesis_id, reason) 
            VALUES (?, ?, ?, ?)
        ";
        $insert_stmt = $mysqli->prepare($insert_cancelled_query);
        $insert_stmt->bind_param("iiis", $gen_number, $gen_year, $thesis_id, $reason);
        $insert_stmt->execute();
        $insert_stmt->close();

        // Commit the transaction
        $mysqli->commit();

        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = 'Thesis successfully cancelled.';
        $return_object['redirect_to'] = 'list-thesis';

    } catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();

        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = '';
    }

    return $return_object;
}

// Enable grading
function enableGrading($thesis_id, $grade_status, $mysqli){
    $mysqli->begin_transaction();
    try {
        $query = "
            UPDATE theses 
            SET grading_enabled = ? 
            WHERE id = ?
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $grade_status, $thesis_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $mysqli->commit();
        
        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = "You successfully enabled grading";
        $return_object['redirect_to'] = 'view-thesis/'.$thesis_id;
    }
    catch(Exception $e){
        $mysqli->rollback();
        
        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = '';
    }

    return $return_object;
}

// Set a grade to a thesis
function setGrade($thesis_id, $instructor_id, $grade, $mysqli) {
    $mysqli->begin_transaction();
    try {
        // Step 1: Update the grade in the `committee` table
        $query = "
            UPDATE committee 
            SET grade = ? 
            WHERE thesis_id = ? AND user_id = ?
        ";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("iii", $grade, $thesis_id, $instructor_id);
        $stmt->execute();
        $stmt->close();

        // Step 2: Check if all instructors have set their grades (inside transaction)
        $check_query = "
            SELECT COUNT(*) AS total_grades, 
                   (SELECT COUNT(*) FROM committee WHERE thesis_id = ? AND grade IS NOT NULL) AS graded
            FROM committee 
            WHERE thesis_id = ?
        ";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("ii", $thesis_id, $thesis_id);
        $check_stmt->execute();
        $check_stmt->bind_result($total_grades, $graded);
        $check_stmt->fetch();
        $check_stmt->close();

        // Step 3: Handle final grade logic
        if ($total_grades === $graded) {
            // All grades are set, calculate the average
            $average_query = "
                SELECT AVG(grade) AS average_grade 
                FROM committee 
                WHERE thesis_id = ? AND grade IS NOT NULL
            ";
            $average_stmt = $mysqli->prepare($average_query);
            $average_stmt->bind_param("i", $thesis_id);
            $average_stmt->execute();
            $average_stmt->bind_result($average_grade);
            $average_stmt->fetch();
            $average_stmt->close();

            // Update `final_grade` in the `theses` table
            $update_final_grade_query = "
                UPDATE theses 
                SET final_grade = ? 
                WHERE id = ?
            ";
            $update_stmt = $mysqli->prepare($update_final_grade_query);
            $update_stmt->bind_param("di", $average_grade, $thesis_id);
            $update_stmt->execute();
            $update_stmt->close();
        }

        // Commit transaction after all checks and updates
        $mysqli->commit();
        
        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = "You successfully submitted your grade";
        $return_object['redirect_to'] = 'view-thesis/'.$thesis_id;

    } catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();
        
        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = '';
    }

    return $return_object;
}

// Update a thesis by posting fields
function updateThesis($id, $topic, $summary, $instructor, $student, $status, $uploaded_files, $assigned_on, $mysqli) {
    // Begin transaction
    $mysqli->begin_transaction();
    try {
        // Step 1: Check the current status of the thesis
        $current_status_query = "SELECT status FROM theses WHERE id = ?";
        $current_status_stmt = $mysqli->prepare($current_status_query);
        $current_status_stmt->bind_param("i", $id);
        $current_status_stmt->execute();
        $current_status_stmt->bind_result($current_status);
        $current_status_stmt->fetch();
        $current_status_stmt->close();

        // Step 2: Update thesis details in the `theses` table
        $query = "
            UPDATE theses 
            SET topic = ?, summary = ?, instructor = ?, student = ?, status = ?, assigned_on = ? 
            WHERE id = ?
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssiiisi", $topic, $summary, $instructor, $student, $status, $assigned_on, $id);
        $stmt->execute();
        $stmt->close();

        // Step 3: Handle file uploads and insert into `uploads` table
        $upload_ids = [];

        if ($uploaded_files) {
            foreach ($uploaded_files['name'] as $key => $filename) {
                if ($uploaded_files['error'][$key] === UPLOAD_ERR_OK) {
                    // Generate a unique file name
                    $unique_name = uniqid() . "_" . basename($filename);
                    $upload_path = __DIR__ . "/uploads/" . $unique_name;

                    // Move file to the uploads directory
                    if (move_uploaded_file($uploaded_files['tmp_name'][$key], $upload_path)) {
                        // Insert file info into `uploads` table
                        $upload_stmt = $mysqli->prepare("INSERT INTO uploads (filename) VALUES (?)");
                        $upload_stmt->bind_param("s", $unique_name);
                        $upload_stmt->execute();
                        $upload_ids[] = $upload_stmt->insert_id; // Get the ID of the uploaded file
                        $upload_stmt->close();
                    }
                }
            }

            // Step 4: Link thesis and uploaded files in the `thesis_uploads` table
            foreach ($upload_ids as $file_id) {
                $thesis_upload_stmt = $mysqli->prepare("INSERT INTO thesis_uploads (thesis_id, file_id) VALUES (?, ?)");
                $thesis_upload_stmt->bind_param("ii", $id, $file_id);
                $thesis_upload_stmt->execute();
                $thesis_upload_stmt->close();
            }
        }

        // Step 5: Check if the status has changed and call `setLog`
        if ($current_status != $status) {
            setLog($id, $status, $mysqli);
        }

        // Commit transaction
        $mysqli->commit();

        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = 'Thesis was updated successfully!';
        $return_object['redirect_to'] = 'list-thesis';

    } catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();

        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = 'view-thesis';
    }

    return $return_object;
}

// Update the student examination information
function updateExaminationFields($id, $student_id, $links, $method, $place, $date, $uploaded_files, $mysqli) {
    // Begin transaction
    $mysqli->begin_transaction();

    try {
        // Step 1: Update method, place, and date in the `theses` table
        $stmt = $mysqli->prepare("
            UPDATE theses 
            SET examination_method = ?, examination_place = ?, examination_date = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("sssi", $method, $place, $date, $id);
        $stmt->execute();
        $stmt->close();

        // Step 2: Handle links (student_uploads table)
        // Delete existing links for this thesis and student
        $delete_stmt = $mysqli->prepare("DELETE FROM student_uploads WHERE thesis_id = ? AND student_id = ?");
        $delete_stmt->bind_param("ii", $id, $student_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        // Insert new links
        foreach ($links as $link) {
            $insert_link_stmt = $mysqli->prepare("
                INSERT INTO student_uploads (student_id, thesis_id, file_name) 
                VALUES (?, ?, ?)
            ");
            $insert_link_stmt->bind_param("iis", $student_id, $id, $link);
            $insert_link_stmt->execute();
            $insert_link_stmt->close();
        }

        // Step 3: Handle file uploads
        if ($uploaded_files && $uploaded_files['error'] === UPLOAD_ERR_OK) {
            // Extract the file name
            $filename = $uploaded_files['name'];

            // Generate a unique file name
            $unique_name = uniqid() . "_" . basename($filename);
            $upload_path = __DIR__ . "/uploads/" . $unique_name;

            // Move the file to the uploads directory
            if (move_uploaded_file($uploaded_files['tmp_name'], $upload_path)) {
                // Update the thesis table with the file's unique name
                $upload_stmt = $mysqli->prepare("UPDATE theses SET draft_text = ? WHERE id = ?");
                $upload_stmt->bind_param("si", $unique_name, $id);
                $upload_stmt->execute();
                $upload_stmt->close();
            }
        }

        // Commit transaction
        $mysqli->commit();

        // Return success response
        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = "Examination fields updated successfully!";
        $return_object['redirect_to'] = 'assigned-thesis';

    } catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();

        // Return error response
        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = 'edit-thesis';
    }

    return $return_object;
}

// Get the thesis of an instructor where he is involved as a committee memeber
function getInstructorThesesAsCommittee($instructor_id, $mysqli) {
    try {
        // SQL query to fetch theses where the instructor is a committee member but not the instructor
        $query = "
            SELECT 
                t.id AS thesis_id,
                t.topic,
                t.created_on,
                t.assigned_on,
                t.completed_on,
                t.final_grade,
                t.status AS status_id,
                ts.status AS status_name,
                c.invited_on,
                c.accepted_on,
                c.grade,
                i.name AS instructor_name,
                i.surname AS instructor_surname
            FROM 
                committee c
            INNER JOIN theses t ON c.thesis_id = t.id
            INNER JOIN thesis_status ts ON t.status = ts.id
            INNER JOIN users i ON t.instructor = i.id
            WHERE 
                c.user_id = ? AND 
                t.instructor != ? AND 
                c.accepted = 1
        ";

        // Prepare the statement
        $stmt = $mysqli->prepare($query);

        // Bind parameters
        $stmt->bind_param("ii", $instructor_id, $instructor_id);

        // Execute the statement
        $stmt->execute();

        // Fetch the results
        $result = $stmt->get_result();

        $theses = [];
        while ($row = $result->fetch_assoc()) {
            $theses[] = $row;
        }

        $stmt->close();

        return $theses;

    } catch (Exception $e) {
        // Handle exception
        return [
            'error' => true,
            'message' => $e->getMessage()
        ];
    }
}

// Get the thesis of a proffessor where he participates as instructor
function getInstructorTheses($instructor_id, $mysqli) {
    // Prepare the query
    $query = "
        SELECT 
            t.id,
            t.created_on,
            t.assigned_on,
            t.completed_on,
            t.final_grade,
            t.status AS thesis_status_id,
            ts.status AS thesis_status
        FROM 
            theses t
        LEFT JOIN 
            thesis_status ts ON t.status = ts.id
        WHERE 
            t.instructor = ?
    ";

    try {
        // Prepare the statement
        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            throw new Exception("Statement preparation failed: " . $mysqli->error);
        }

        // Bind parameters
        $stmt->bind_param("i", $instructor_id);

        // Execute the query
        $stmt->execute();

        // Get results
        $result = $stmt->get_result();

        // Fetch all rows
        $theses = [];
        while ($row = $result->fetch_assoc()) {
            $theses[] = $row;
        }

        // Close the statement
        $stmt->close();

        // Return the theses
        return $theses;

    } catch (Exception $e) {
        // Handle errors
        return [
            'error' => true,
            'message' => $e->getMessage()
        ];
    }
}

// REturn all thesis data
function getAllTheses($mysqli, $is_instructor = false) {
    try {
        $logged_user_id = getLoggedUser($mysqli)['id'];

        // Base SQL Query to fetch thesis details
        $thesisSql = "
            SELECT 
                t.id AS thesis_id,
                t.topic AS topic,
                t.instructor AS instructor_id,
                u1.name AS instructor_name,
                u1.surname AS instructor_surname,
                u3.name AS student_name,
                u3.surname AS student_surname,
                t.created_on AS created_on,
                s.status AS thesis_status,
                s.id AS thesis_status_id
            FROM 
                theses t
            LEFT JOIN users u1 ON t.instructor = u1.id
            LEFT JOIN users u3 ON t.student = u3.id
            LEFT JOIN thesis_status s ON t.status = s.id
        ";

        // Add condition based on $is_instructor
        if ($is_instructor) {
            $thesisSql .= "
                WHERE 
                    t.instructor = ? 
                    OR t.id IN (
                        SELECT 
                            DISTINCT c.thesis_id
                        FROM 
                            committee c
                        WHERE 
                            c.user_id = ?
                    )
            ";
        } else {
            $thesisSql .= " WHERE s.id >= 3";
        }

        $thesisSql .= " ORDER BY t.created_on DESC";

        // Prepare and execute the query
        $stmt = $mysqli->prepare($thesisSql);
        if ($is_instructor) {
            $stmt->bind_param("ii", $logged_user_id, $logged_user_id);
        }
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        if (!$result) {
            throw new Exception("Thesis Query Failed: " . $mysqli->error);
        }

        $theses = [];
        while ($row = $result->fetch_assoc()) {
            $theses[$row['thesis_id']] = [
                'thesis_id' => $row['thesis_id'],
                'topic' => $row['topic'],
                'created_by' => [
                    'id' => $row['instructor_id'],
                    'name' => $row['instructor_name'],
                    'surname' => $row['instructor_surname']
                ],
                'assigned_to' => [
                    'name' => $row['student_name'],
                    'surname' => $row['student_surname']
                ],
                'created_at' => $row['created_on'],
                'status' => [
                    'status' => $row['thesis_status'],
                    'id' => $row['thesis_status_id']
                ],
                'committee_members' => [] // Placeholder for committee members
            ];
        }
        $stmt->close();

        // SQL Query to fetch committee members grouped by thesis
        $committeeSql = "
            SELECT 
                c.thesis_id,
                c.accepted,
                u.id AS user_id,
                u.name,
                u.surname
            FROM 
                committee c
            LEFT JOIN users u ON c.user_id = u.id
        ";

        // Execute the committee query
        $committeeResult = $mysqli->query($committeeSql);

        if (!$committeeResult) {
            throw new Exception("Committee Query Failed: " . $mysqli->error);
        }

        // Populate committee members for each thesis
        while ($row = $committeeResult->fetch_assoc()) {
            $thesisId = $row['thesis_id'];

            if (isset($theses[$thesisId])) {
                $theses[$thesisId]['committee_members'][] = [
                    'user_id' => $row['user_id'],
                    'accepted' => $row['accepted'],
                    'name' => $row['name'],
                    'surname' => $row['surname']
                ];
            }
        }

        // Return theses as an indexed array
        return array_values($theses);

    } catch (Exception $e) {
        // Return error message in case of failure
        return ["error" => $e->getMessage()];
    }
}

// get under exam theses for publicly available url
function showUnderExamTheses($mysqli, $startDate = NULL, $endDate = NULL) {
    // Begin transaction
    $mysqli->begin_transaction();
    
    // Base query with a join to the users table
    $query = "SELECT t.examination_date, t.examination_place, t.topic, u.name, u.surname
              FROM theses t
              JOIN users u ON t.student = u.id
              WHERE t.examination_date IS NOT NULL";
    
    // Add date conditions based on provided parameters
    if ($startDate && $endDate) {
        $query .= " AND t.created_on >= '$startDate' AND t.created_on <= '$endDate'";
    } elseif ($startDate) {
        $query .= " AND t.created_on >= '$startDate'";
    } elseif ($endDate) {
        $query .= " AND t.created_on <= '$endDate'";
    }

    // Prepare and execute the query
    $stmt = $mysqli->prepare($query);
    $stmt->execute();

    // Fetch the results
    $result = $stmt->get_result();
    $theses = array();
    while ($row = $result->fetch_assoc()) {
        $theses[] = $row;
    }
    
    // Commit transaction
    $mysqli->commit();
    
    return $theses;
}


function getUploads($thesis_id, $mysqli) {
    try {
        // SQL query to fetch uploads for the given thesis_id
        $sql = "
            SELECT u.id, u.filename 
            FROM uploads u
            INNER JOIN thesis_uploads tu ON u.id = tu.file_id
            INNER JOIN theses t ON t.id = tu.thesis_id
            WHERE t.id = ?
        ";

        // Prepare the statement
        if ($stmt = $mysqli->prepare($sql)) {
            // Bind the thesis_id parameter
            $stmt->bind_param("i", $thesis_id);

            // Execute the query
            $stmt->execute();

            // Fetch the results
            $result = $stmt->get_result();
            $uploads = [];
            while ($row = $result->fetch_assoc()) {
                $uploads[] = $row; // Add each row (id and filename) to the uploads array
            }

            // Close the statement
            $stmt->close();

            // Return the uploads
            return $uploads;
        } else {
            // If the statement could not be prepared, throw an error
            throw new Exception("Failed to prepare statement: " . $mysqli->error);
        }
    } catch (Exception $e) {
        // Return error message in case of failure
        return ["error" => $e->getMessage()];
    }
}

// GEt thesis data by thesis id
function getThesis($id, $mysqli){
    try {
        // SQL Query to fetch thesis details
        $thesisSql = "
            SELECT 
                t.id AS thesis_id,
                t.topic AS topic,
                t.summary AS summary,
                t.grading_enabled AS grading_enabled,
                t.final_grade AS final_grade,
                t.ap_number AS ap_number,
                u1.id AS instructor_id,
                u1.name AS instructor_name,
                u1.surname AS instructor_surname,
                u3.id AS student_id,
                u3.name AS student_name,
                u3.surname AS student_surname,
                t.created_on AS created_on,
                t.assigned_on AS assigned_on,
                t.draft_text AS draft_text,
                t.final_text_file AS final_text_file,
                t.completed_on AS completed_on, 
                t.examination_date AS examination_date, 
                e.method AS examination_method,
                e.id AS examination_method_id,
                t.examination_place AS examination_place,
                s.status AS thesis_status,
                s.id AS thesis_status_id
            FROM 
                theses t
            LEFT JOIN users u1 ON t.instructor = u1.id
            LEFT JOIN users u3 ON t.student = u3.id
            LEFT JOIN thesis_status s ON t.status = s.id
            LEFT JOIN examination_methods e ON t.examination_method = e.id
            WHERE t.id = $id
        ";

        // Execute the main thesis query
        $thesisResult = $mysqli->query($thesisSql);

        if (!$thesisResult) {
            throw new Exception("Database Query Failed: " . $mysqli->error);
        }

        $theses = [];
        while ($row = $thesisResult->fetch_assoc()) {
            $theses[$row['thesis_id']] = [
                'thesis_id' => $row['thesis_id'],
                'topic' => $row['topic'],
                'summary' => $row['summary'],
                'instructor' => [
                    'id' => $row['instructor_id'],
                    'name' => $row['instructor_name'],
                    'surname' => $row['instructor_surname']
                ],
                'student' => [
                    'id' => $row['student_id'],
                    'name' => $row['student_name'],
                    'surname' => $row['student_surname']
                ],
                'created_on' => $row['created_on'],
                'assigned_on' => $row['assigned_on'],
                'draft_text' => $row['draft_text'],
                'final_text_file' => $row['final_text_file'],
                'completed_on' => $row['completed_on'],
                'examination_date' => $row['examination_date'],
                'examination_place' => $row['examination_place'],
                'grading_enabled' => $row['grading_enabled'],
                'final_grade' => $row['final_grade'],
                'ap_number' => $row['ap_number'],
                'method' => [
                    'method' => $row['examination_method'],
                    'id' => $row['examination_method_id']
                ],
                'status' => [
                    'status' => $row['thesis_status'],
                    'id' => $row['thesis_status_id']
                ],
                'links' => [],
                'committee_members' => []
            ];
        }

        // SQL Query to fetch committee members grouped by thesis
        $committeeSql = "
            SELECT 
                c.thesis_id,
                c.accepted,
                c.invited_on,
                c.accepted_on,
                c.grade,
                u.id AS user_id,
                u.name,
                u.surname,
                p.topic AS p_topic
            FROM 
                committee c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN professors p ON p.user_id = u.id
        ";

        // Execute the committee query
        $committeeResult = $mysqli->query($committeeSql);

        if (!$committeeResult) {
            throw new Exception("Committee Query Failed: " . $mysqli->error);
        }

        // Populate committee members for each thesis
        while ($row = $committeeResult->fetch_assoc()) {
            $thesisId = $row['thesis_id'];

            if (isset($theses[$thesisId])) {
                $theses[$thesisId]['committee_members'][] = [
                    'user_id' => $row['user_id'],
                    'accepted' => $row['accepted'],
                    'invited_on' => $row['invited_on'],
                    'accepted_on' => $row['accepted_on'],
                    'grade' => $row['grade'],
                    'name' => $row['name'],
                    'surname' => $row['surname'],
                    'topic' => $row['p_topic']
                ];
            }
        }

        // SQL Query to fetch links
        $linksSQL = "
            SELECT 
                s.file_name,
                t.id AS thesis_id
            FROM 
                student_uploads s
            LEFT JOIN theses t ON s.thesis_id = t.id
        ";

        // Execute the committee query
        $linksResult = $mysqli->query($linksSQL);

        if (!$linksResult) {
            throw new Exception("Links Query Failed: " . $mysqli->error);
        }

        // Populate committee members for each thesis
        while ($row = $linksResult->fetch_assoc()) {
            $thesisId = $row['thesis_id'];

            if (isset($theses[$thesisId])) {
                $theses[$thesisId]['links'][] = [
                    'file_name' => $row['file_name'],
                ];
            }
        }

        // Return theses as an indexed array
        return array_values($theses);

    } catch (Exception $e) {
        // Return error message in case of failure
        return ["error" => $e->getMessage()];
    }
}

function getTimePassed($timestamp1, $timestamp2) {
    // Convert timestamps to DateTime objects
    $date1 = new DateTime("@$timestamp1");
    $date2 = new DateTime("@$timestamp2");

    // Calculate the difference between the two dates
    $interval = $date1->diff($date2);

    // Return the number of years
    return $interval->y; // 'y' represents years in the DateInterval object
}

function setComment($thesis_id, $instructor, $comment, $mysqli){
    try {
        // Start a transaction
        $mysqli->begin_transaction();

        // Insert the user and retrieve the new user ID
        $comment_sql = "INSERT INTO thesis_comments (thesis_id, instructor, comment) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($comment_sql);

        if (!$comment_sql) {
            $return_object['message']['type'] = 'error';
            $return_object['message']['content'] = $mysqli->error;
            $return_object['redirect_to'] = 'sign-up';
            return $return_object;
        }
        $stmt->bind_param("iis", $thesis_id, $instructor, $comment);

        $stmt->execute();
        $stmt->close();


        // Commit the transaction
        $mysqli->commit();

        // Success message
        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = 'Comment added successfully';
        $return_object['redirect_to'] = 'view-thesis/'.$thesis_id;
    } catch (Exception $e) {
        // Rollback the transaction on error
        $mysqli->rollback();

        // Error message
        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = '';
    }

    return $return_object;
}

function getComments($thesis_id, $instructor, $mysqli){
    $sql = "SELECT id, comment, date_added FROM thesis_comments WHERE thesis_id = ? AND instructor = ? ORDER BY id DESC";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $thesis_id, $instructor);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $comments = $result->fetch_all();
        return $comments;
    }
    else{
        return false;
    }
}

function getStudentThesis($student_id, $mysqli){
    $sql = "SELECT * FROM theses WHERE student = ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $thesis = $result->fetch_assoc();
        return $thesis;
    }
    else{
        return false;
    }
}

function sendInvite($thesis_id, $professor_id, $mysqli){

    $connected_student_id = getLoggedUser($mysqli)['id'];
    $connected_student_thesis = getStudentThesis($connected_student_id, $mysqli);

    if ($connected_student_thesis) {
        if ($connected_student_thesis['id'] == $thesis_id) {
            try{
                $sql = "INSERT INTO committee (user_id, thesis_id) VALUES (?, ?)";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param("ii", $professor_id, $thesis_id);
                $stmt->execute();

                $stmt->close();

                $return_object['message']['type'] = 'success';
                $return_object['message']['content'] = 'Invite Sent Successfully';
                $return_object['redirect_to'] = '';
            }
            catch(Exception $e){
                $return_object['message']['type'] = 'error';
                $return_object['message']['content'] = $e->getMessage();
                $return_object['redirect_to'] = '';
            }
        }
        else{
            $return_object['message']['type'] = 'error';
            $return_object['message']['content'] = 'Please play fair';
            $return_object['redirect_to'] = '';
        }
    }
    else{
        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = 'Seems like someone is trying to hack';
        $return_object['redirect_to'] = '';
    }

    return $return_object;
}

function getInvites($mysqli) {
    $connected_professor_id = getLoggedUser($mysqli)['id'];

    // SQL query with multiple JOINs to include thesis topic, student name, and student surname
    $sql = "
        SELECT 
            committee.*,
            theses.topic AS thesis_topic,
            theses.student AS thesis_student_id,
            users.name AS student_name,
            users.surname AS student_surname
        FROM 
            committee
        JOIN 
            theses
        ON 
            committee.thesis_id = theses.id
        JOIN 
            users
        ON 
            theses.student = users.id
        WHERE 
            committee.user_id = ? AND committee.accepted IS NULL
    ";

    // Prepare the statement
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        // Handle query preparation error
        return ["error" => $mysqli->error];
    }

    // Bind parameters
    $stmt->bind_param("i", $connected_professor_id);

    // Execute the query
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check if there are rows and fetch as an associative array
    if ($result->num_rows > 0) {
        $invites = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $invites;
    } else {
        $stmt->close();
        return []; // Return an empty array if no rows found
    }
}

function acceptInvite($thesis_id, $mysqli) {
    $connected_professor_id = getLoggedUser($mysqli)['id'];
    date_default_timezone_set("Europe/Athens");
    $current_timestamp = date("Y-m-d H:i:s");

    // Begin transaction
    $mysqli->begin_transaction();

    try {
        // Step 1: Update the committee entry to accepted
        $sql = "UPDATE committee SET accepted = 1, accepted_on = ? WHERE user_id = ? AND thesis_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('sii', $current_timestamp, $connected_professor_id, $thesis_id);
        $stmt->execute();
        $stmt->close();

        // Step 2: Count accepted committee members for the thesis
        $count_sql = "SELECT COUNT(*) AS accepted_count FROM committee WHERE thesis_id = ? AND accepted = 1";
        $count_stmt = $mysqli->prepare($count_sql);
        $count_stmt->bind_param('i', $thesis_id);
        $count_stmt->execute();
        $count_stmt->bind_result($accepted_count);
        $count_stmt->fetch();
        $count_stmt->close();

        // Step 3: Check if there are 3 accepted members
        if ($accepted_count === 3) {
            // Step 4: Delete unaccepted committee entries
            $delete_sql = "DELETE FROM committee WHERE thesis_id = ? AND (accepted IS NULL OR accepted != 1)";
            $delete_stmt = $mysqli->prepare($delete_sql);
            $delete_stmt->bind_param('i', $thesis_id);
            $delete_stmt->execute();
            $delete_stmt->close();

            // // Step 5: Update thesis status to 4
            setStatus($thesis_id, 3, $mysqli);
        }

        // Commit the transaction
        $mysqli->commit();

        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = 'Invitation accepted';
        $return_object['redirect_to'] = '';
    } catch (Exception $e) {
        // Rollback the transaction on error
        $mysqli->rollback();

        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = '';
    }

    return $return_object;
}

function declineInvite($thesis_id, $mysqli){

    $connected_professor_id = getLoggedUser($mysqli)['id'];
    date_default_timezone_set("Europe/Athens");
    $current_timestamp = date("Y-m-d H:i:s");

    $sql = "UPDATE committee SET accepted = 0, accepted_on = ? WHERE user_id = ? AND thesis_id = ?";
    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param('sii', $current_timestamp, $connected_professor_id, $thesis_id);
    $stmt->execute();

    $stmt->close();

    $return_object['message']['type'] = 'success';
    $return_object['message']['content'] = 'Invitation declined';
    $return_object['redirect_to'] = '';

    return $return_object;
}

function cancelInvite($thesis_id, $professor_id, $mysqli) {
    try {
        $return_object['message']['type'] = 'success';
        $return_object['message']['content'] = 'Invite was revoked';
        $return_object['redirect_to'] = '';
        // Prepare the SQL query to delete the record
        $sql = "DELETE FROM committee WHERE user_id = ? AND thesis_id = ?";

        // Prepare the statement
        if ($stmt = $mysqli->prepare($sql)) {
            // Bind the parameters
            $stmt->bind_param("ii", $professor_id, $thesis_id);

            // Execute the statement
            if ($stmt->execute()) {
                // Check if any rows were affected
                if ($stmt->affected_rows > 0) {
                    $return_object['message']['type'] = 'success';
                    $return_object['message']['content'] = 'Invite was revoked';
                    $return_object['redirect_to'] = '';
                } else {
                    $return_object['message']['type'] = 'error';
                    $return_object['message']['content'] = 'No matching invite found';
                    $return_object['redirect_to'] = '';
                }
            }
            else {
                $return_object['message']['type'] = 'error';
                $return_object['message']['content'] = 'Something went wrong';
                $return_object['redirect_to'] = '';
            }

            // Close the statement
            $stmt->close();
        }
        else {
            $return_object['message']['type'] = 'error';
            $return_object['message']['content'] = $mysqli->error;
            $return_object['redirect_to'] = '';
        }
        return $return_object;
    }
    catch (Exception $e) {
        $return_object['message']['type'] = 'error';
        $return_object['message']['content'] = $e->getMessage();
        $return_object['redirect_to'] = '';
        return $return_object;
    }
}


?>
<?php 
require_once(__DIR__.'/../functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploaded_files = isset($_FILES) ? $_FILES['files'] : null;

    $mysqli = DbConnect();
    header('Content-Type: application/json; charset=utf-8');

    // Check if files were uploaded
    if ($uploaded_files && is_array($uploaded_files['tmp_name'])) {
       
        foreach ($uploaded_files['tmp_name'] as $index => $tmp_name) {
            // Read the file contents
            $file_contents = file_get_contents($tmp_name);

            $decoded_data = json_decode($file_contents, true);

            $students = $decoded_data['students'];
            $professors = $decoded_data['professors'];

            foreach ($students as $student) {
                $name = $student['name'] ?? null;
                $surname = $student['surname'] ?? null;
                $email = $student['email'] ?? null;
                $password = 12345;
                $role = 1;
                $topic = $student['topic'] ?? null;
                $landline = $student['landline'] ?? null;
                $mobile = $student['mobile'] ?? null;
                $department = $student['department'] ?? null;
                $university = $student['university'] ?? null;
                $student_number = $student['student_number'] ?? null;
                $street = $student['street'] ?? null;
                $number = $student['number'] ?? null;
                $city = $student['city'] ?? null;
                $postcode = $student['postcode'] ?? null;
                $mobile_telephone = $student['mobile_telephone'] ?? null;
                $landline_telephone = $student['landline_telephone'] ?? null;
                $father_name = $student['father_name'] ?? null;

                $result = setUser(
                    $mysqli,
                    $name,
                    $surname,
                    $email,
                    $password,
                    $role,
                    $topic,
                    $landline,
                    $mobile,
                    $department,
                    $university,
                    $student_number,
                    $street,
                    $number,
                    $city,
                    $postcode,
                    $mobile_telephone,
                    $landline_telephone,
                    $father_name
                );

                if ($result['message']['type'] === 'success') {
                    $return_object['message']['type'] = 'success';
                    $return_object['message']['content'] = 'Users Added successfully';
                    $return_object['redirect_to'] = 'list-users';
                }
                else {
                    $return_object['message']['type'] = 'error';
                    $return_object['message']['content'] = $result;
                    $return_object['redirect_to'] = 'list-users';
                }
            }

            foreach ($professors as $professor) {
                $name = $professor['name'] ?? null;
                $surname = $professor['surname'] ?? null;
                $email = $professor['email'] ?? null;
                $password = 12345;
                $role = 2;
                $topic = $professor['topic'] ?? null;
                $landline = $professor['landline'] ?? null;
                $mobile = $professor['mobile'] ?? null;
                $department = $professor['department'] ?? null;
                $university = $professor['university'] ?? null;
                $student_number = $professor['student_number'] ?? null;
                $street = $professor['street'] ?? null;
                $number = $professor['number'] ?? null;
                $city = $professor['city'] ?? null;
                $postcode = $professor['postcode'] ?? null;
                $mobile_telephone = $professor['mobile_telephone'] ?? null;
                $landline_telephone = $professor['landline_telephone'] ?? null;
                $father_name = $professor['father_name'] ?? null;

                $result = setUser(
                    $mysqli,
                    $name,
                    $surname,
                    $email,
                    $password,
                    $role,
                    $topic,
                    $landline,
                    $mobile,
                    $department,
                    $university,
                    $student_number,
                    $street,
                    $number,
                    $city,
                    $postcode,
                    $mobile_telephone,
                    $landline_telephone,
                    $father_name
                );

                if ($result['message']['type'] === 'success') {
                    $return_object['message']['type'] = 'success';
                    $return_object['message']['content'] = 'Users Addes successfully';
                    $return_object['redirect_to'] = 'list-users';
                }
                else {
                    $return_object['message']['type'] = 'error';
                    $return_object['message']['content'] = 'Error Adding User';
                    $return_object['redirect_to'] = 'list-users';
                }
            }


            
        }

        // Return the response
        echo json_encode($return_object);
    } else {
        echo json_encode(['error' => 'No files uploaded']);
    }
}

function getRandomString($n) {
    return bin2hex(random_bytes($n / 2));
}

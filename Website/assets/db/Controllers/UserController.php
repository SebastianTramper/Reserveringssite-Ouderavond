    <?php
/*
    This class extends the Database class
    The functions in this class are all the functions the user will need to talk to the database except connecting etc.
*/

// Require database class
require("database.class.php");
// Start session
session_start();

class UserController extends Database {
    // Function to add array of students
    public function AddStudents($studentArray, $classArray) {
        // Combine both arrays into a associative array
        $studentAndClass = array_combine($studentArray, $classArray);

        // Loop through the students and classes
        foreach($studentAndClass as $student => $class) {
            // Generate unique token
            $token = $this->UniqueLinkGenerator();
            // SQL INSERT Query
            $sql = "INSERT INTO `student` (id, naam, klas_id, uniqueLink) VALUES (NULL, '$student', $class, '$token')";

            // Execute into database and check for errors
            if(mysqli_query($this->mysqli, $sql)) {
                echo "Students Added";
            } else {
                die(mysqli_error($this->mysqli));
                echo "Failed to add students";
            }
        }
    }

    // Login function for a 'mentor'
    public function MentorLoginProcess($username, $password) {
        // Input checks
        if($username == "") {
            $_SESSION['errormsg'] = "U heeft geen naam ingevuld!";
        }

        if($password == "") {
            $_SESSION['errormsg'] = "U heeft geen wachtwoord ingevuld!";
        }

        // SQL Query: Search in database for rows with the username
        $sql = "SELECT * FROM mentor WHERE naam='$username'";
        $result = mysqli_query($this->mysqli, $sql);

        // Row with the username
        $row = mysqli_fetch_array($result);

        // Count all rows returned from $result
        $rowcount = mysqli_num_rows($result);

        // Password validation
        if($rowcount > 0) {
            if(password_verify($password, $row['wachtwoord'])) {
                $_SESSION['username'] = $row['naam'];
                return true;
            } else {
                $_SESSION['errormsg'] = "Uw ingevulde wachtwoord is niet correct!";
                return false;
            }
        } else {
            $_SESSION['errormsg'] = "Er bestaan geen mentoren met de ingevulde naam.";
            return false;
        }
    }

    // Unique login link generator
    public function UniqueLinkGenerator($length = 100) {
        // Charactars possible for the unique link
        $charactars = "abcdefghijklmnopqrstuvwzyxABCDEFGHIJKLMNOPQRSTUVWXYZ012345678!@#$%^&*()-=_+";

        // Return the unique link, hashed with sha1
        return substr(sha1(str_shuffle($charactars)), 0, $length);
    }

    // Unique link validator
    public function UniqueLinkValidation($token = "") {
        // Check if token is empty
        if($token == "") {
            // If empty, send a error message back.
            $_SESSION['errormsg'] = "Er is geen token ingevuld.";
            return false;
        } else {
            // Get data from the student where the token is in use
            $sql = "SELECT * FROM student WHERE uniqueLink = '$token'";
            $result = mysqli_query($this->mysqli, $sql);
            $row = mysqli_fetch_array($result);
            $rowCount = mysqli_num_rows($result);

            // Check if any rows exist with that specific token
            if($rowCount > 0) {
                session_unset();
                $_SESSION['username'] = $row['naam'];
                $_SESSION['class_id'] = $row['klas_id'];
                return true;
            } else {
                // Error message
                $_SESSION['errormsg'] = "Uw token is ongeldig!";
                return false;
            }
        }
    }

    public function Logout() {
        // Unset all variables in session
        if(session_unset()) {
            // Destroy session
            session_destroy();            echo '<script>alert("U heeft geen wachtwoord ingevuld!")</script>';

            return true;
        } else {
            $_SESSION['errormsg'] = "Er is iets foutgegegaan met het uitloggen!";
            return false;
        }
    }
}

?>
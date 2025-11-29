<?php
// Include database connection (if needed)
include('../config/db.php');
include('../admin/includes/notification_helper.php');


// Declare variables to store form data
$full_name = $id_number = $postal_address = $gender = $chief = $contact_details_work = $contact_details = "";
$marital_status = $dob = $occupation = $employment_number = $name_of_employer = $address_of_employer = $residential_address = "";
$savings = $entrance_fee = $shares_capital = $laws = $nominee = $date = $signature = $contact = "";

// Define error array for form validation
$error_message = " ";
$success_message = " ";

// Function to sanitize form input and avoid XSS (Cross-Site Scripting) attacks
function sanitize_input($data) {
    return htmlspecialchars(trim($data)); // Remove unwanted spaces and encode special characters
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate form inputs
    $full_name = isset($_POST['full_name']) ? htmlspecialchars(trim($_POST['full_name'])) : '';
    $id_number = isset($_POST['id_number']) ? htmlspecialchars(trim($_POST['id_number'])) : '';
    $postal_address = isset($_POST['postal_address']) ? htmlspecialchars(trim($_POST['postal_address'])) : ''; 
    $residential_address = isset($_POST['residential_address']) ? htmlspecialchars(trim($_POST['residential_address'])) : ''; 
    $gender = isset($_POST['gender']) ? htmlspecialchars(trim($_POST['gender'])) : '';
    // $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $chief = isset($_POST['chief']) ? htmlspecialchars(trim($_POST['chief'])) : '';
    $contact_details_work = isset($_POST['contact_details_work']) ? htmlspecialchars(trim($_POST['contact_details_work'])) : '';
    $contact_details = isset($_POST['contact_details']) ? htmlspecialchars(trim($_POST['contact_details'])) : '';
    $marital_status = isset($_POST['marital_status']) ? htmlspecialchars(trim($_POST['marital_status'])) : ''; // Corrected key
    $dob = isset($_POST['dob']) ? htmlspecialchars(trim($_POST['dob'])) : '';
    $occupation = isset($_POST['occupation']) ? htmlspecialchars(trim($_POST['occupation'])) : '';
    $employment_number = isset($_POST['employment_number']) ? htmlspecialchars(trim($_POST['employment_number'])) : '';
    $name_of_employer = isset($_POST['name_of_employer']) ? htmlspecialchars(trim($_POST['name_of_employer'])) : '';
    $address_of_employer = isset($_POST['address_of_employer']) ? htmlspecialchars(trim($_POST['address_of_employer'])) : '';
    $savings = isset($_POST['savings']) ? htmlspecialchars(trim($_POST['savings'])) : '';
    $entrance_fee = isset($_POST['entrance_fee']) ? htmlspecialchars(trim($_POST['entrance_fee'])) : '';
    $shares_capital = isset($_POST['shares_capital']) ? htmlspecialchars(trim($_POST['shares_capital'])) : '';
    $laws = isset($_POST['laws']) ? 1 : 0; // If checkbox is checked, set to 1, else 0
    $nominee = isset($_POST['nominee']) ? htmlspecialchars(trim($_POST['nominee'])) : '';
    $date = isset($_POST['date']) ? htmlspecialchars(trim($_POST['date'])) : '';
    $signature = isset($_POST['signature']) ? htmlspecialchars(trim($_POST['signature'])) : '';
    $contact = isset($_POST['contact']) ? htmlspecialchars(trim($_POST['contact'])) : '';



    


    // Further validation, e.g., checking if fields are not empty or email format
    if (empty($full_name)) {
        // Handle validation errors
        $error_message = "Please fill in all required fields correctly.";
    } else {
        // Prepare the SQL statement with placeholders
        $sql = "INSERT INTO membership_applications (full_name, id_number, postal_address, gender, chief, contact_details_work, contact_details, marital_status, dob, occupation, employment_number, name_of_employer, address_of_employer, savings, residential_address, entrance_fee, shares_capital, laws, nominee, date, signature, contact)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);

        // Check if the statement was prepared successfully
        if ($stmt === false) {
        die('Error preparing the query: ' . mysqli_error($conn));
        }

        // Bind the parameters to the prepared statement
        mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssss", $full_name, $id_number, $postal_address, $gender, $chief, $contact_details_work, $contact_details, $marital_status, $dob, $occupation, $employment_number, $name_of_employer, $address_of_employer, $savings, $residential_address, $entrance_fee, $shares_capital, $laws, $nominee, $date, $signature, $contact);

        // Execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {

        // GET THE AUTO GENERATED APPLICANT ID
        $applicant_id = $conn->insert_id;
        // Loan Id is 0 here


        // Successfully inserted data, now send email or notification
        $success_message = "Your membership application has been submitted successfully!";
        // echo "<p>Thank you for applying! Your application has been submitted and is under review.</p>";
        $title="New Loan Application";
        $message="A new member " . $full_name . " has just applied!";
        createNotification($conn, $title, $message, 'Membership Request', 'admin');
        // echo "<script>
        //     Swal.fire({
        //     title: 'Good job!',
        //     text: 'You clicked the button!',
        //     icon: 'success'
        //     });
        // </script>";


        

        // Optionally send an email to the user
        // mail($email, "Membership Application", "Your application has been received.");
        } else {
        // Handle database error
        $error_message = "There was an issue submitting your application.";
        }

        // Close the statement
        mysqli_stmt_close($stmt);

    }


    $target_dir = "../uploads/";
    $uploadOk = 1;
    $allowedFileTypes = ["jpg", "jpeg", "png", "pdf"];

    $successFileMessage = "";
    $errorFileMessage = "";


    // Array to hold file upload data
    $files = [
        "fileToUpload" => "id_card",
        "recent_payslip" => "recent_payslip"
    ];

    // Database connection (Ensure $conn is properly initialized)


    foreach ($files as $inputName => $fileType) {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]["error"] == 0) {
            $newFileName = uniqid() . "_" . basename($_FILES["fileToUpload"]["name"]); // ADDED NOW NOW
            $target_file = $target_dir . $newFileName;
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Validate file type
            if (!in_array($fileType, $allowedFileTypes)) {
                $errorFileMessage .= "Sorry, only JPG, JPEG, PNG & PDF files are allowed.<br>";
                $uploadOk = 0;
            }
            
            // Validate file size
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            if ($_FILES[$inputName]["size"] > $maxFileSize) {
                $errorFileMessage .= "Sorry, your file is too large.<br>";
                $uploadOk = 0;
            }
            
            // Upload file if no errors
            if ($uploadOk) {
                if (move_uploaded_file($_FILES[$inputName]["tmp_name"], $target_file)) {
                   
                    // Insert file details into database
                    $sql = "INSERT INTO uploads (applicant_id, file_path) VALUES ('$applicant_id', '$target_file')";
                    if ($conn->query($sql) === TRUE) {
                        $successFileMessage .= "File " . basename($_FILES[$inputName]["name"]) . " uploaded successfully.<br>";
                    } else {
                        $errorFileMessage .= "Error saving file to database: " . $conn->error . "<br>";
                    }
                } else {
                    $errorFileMessage .= "Sorry, there was an error uploading your file.<br>";
                }
            }
        }
}
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply - CedisPay Savings & Credit Co-operative</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <style>
         .download-section {
            text-align: center;
            padding: 20px;
            background-color: #e8f4fd;
            border-radius: 8px;
            margin-bottom: 20px;

            max-width: 700px;
            margin: 10px auto 30px auto;

        }
        .download-btn {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .download-btn:hover {
            background-color: #2980b9;
            color: #fff;
        }

        .download-section h2, .download-section p, .download-section a {
            margin: 10px 0;
        }
        .or {
            margin-bottom: 20px;
        }

        .application-form-container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            /* padding: 50px; */
        }

        @media (min-width: 768px) {
            .application-form-container {
                padding: 12px !important;
            }
        }

        .laws-container label{
            /* display: flex;
            align-items: center;
            gap: 0; */
            display: inline;
           
        }

        .laws-container input {
            width: 20px !important;
        }

        .alert {
            margin-bottom: 0;
        }
  
    </style>
</head>

<body>
    <!-- ALERT MESSAGES -->

    <!-- Header Section -->
    <header>
        <div class="logo">CedisPay</div>
        <i class="fa-solid fa-bars fa-xl"></i>

        <!-- Navigation Links -->
        <nav>
            <ul>
                <li><a href="./index.html">Home</a></li>
                <li><a href="./membership-apply.php" class="active">Apply</a></li>
                <li><a href="./faqs.html">FAQs</a></li>
                <li><a href="./about.html">About</a></li>
            </ul>
        </nav>
        <!-- Contact Information -->
        <div class="phone">
            <a href="tel:+1234567890"><i class="fa-solid fa-phone"></i> Call Us Now: +268 2404 0000</a>
        </div>
        <button class="login-btn"><a href="./login.html">Login</a></button>

    </header>

    <!-- Membership Application Section -->
    <section class="membership-application">
        
        <div class="container">

            <section class="download-section">
                 <img src="../assets/8542038_download_data_icon.png" alt="" style="width: 60px;">

                <h2>Download Form</h2>
                <p>Download the application form and submit it personally to our office.</p>
                <a href="./download-forms.html" class="download-btn">Download Application Form</a>
                <p>Please print, fill out, and bring the form to our office during business hours.</p>
            </section>

            <h2 class="or">or</h2>

            <div class="application-form-container">
                <h2>Apply for Membership Online</h2>
                <p>Join CedisPay yeMaswati Savings & Credit Co-Operative today and access a variety of financial benefits.</p>

                <!-- Membership Application Form -->
                <form action="#" method="POST" enctype="multipart/form-data">
                    <!-- Full Name -->
                    <label for="full-name">Full Name</label>
                    <input type="text" id="full-name" name="full_name" placeholder="Enter your full name" required>

                    <!-- ID Number -->
                    <label for="id-number">ID Number</label>
                    <input type="text" id="id-number" name="id_number" placeholder="Enter your ID number" required>

                    <!-- Postal Address -->
                    <label for="postal-address">Postal Address</label>
                    <input type="text" id="postal-address" name="postal_address" placeholder="Enter your postal address" required>

                    <!-- Gender -->
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>

                    <!-- Residential Address -->
                    <label for="residential-address">Residential Address</label>
                    <input type="text" id="residential_address" name="residential_address" placeholder="Enter your residential address" required>

                    <!-- Chief -->
                    <label for="chief">Chief</label>
                    <input type="text" id="chief" name="chief" placeholder="Enter your chief" required>

                    <!-- Work Contact -->
                    <label for="contact-details-work">Contact (Work)</label>
                    <input type="number" id="contact-details-work" name="contact_details_work" placeholder="Enter your work contact" required>

                    <!-- Cell Contact -->
                    <label for="contact-details">Contact (Cell)</label>
                    <input type="number" id="contact-details" name="contact_details" placeholder="Enter your cell number" required>

                    <!-- Marital Status -->
                    <label for="marital-status">Marital Status</label>
                    <input type="text" id="marital_status" name="marital_status" placeholder="Enter your marital status" required>

                    <!-- Date of Birth -->
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required>

                    <!-- Occupation -->
                    <label for="occupation">Occupation</label>
                    <input type="text" id="occupation" name="occupation" placeholder="Enter your occupation" required>

                    <!-- Employment Number -->
                    <label for="employment-number">Employment#</label>
                    <input type="text" id="employment-number" name="employment_number" placeholder="Enter your employment number" required>

                    <!-- Name of Employer -->
                    <label for="name-of-employer">Name of Employer</label>
                    <input type="text" id="name-of-employer" name="name_of_employer" placeholder="Enter your employer's name" required>

                    <!-- Employer's Address -->
                    <label for="address-of-employer">Address of Employer</label>
                    <input type="text" id="address-of-employer" name="address_of_employer" placeholder="Enter your employer's address" required>



                    <!-- Email -->
                    <!-- <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address" required> -->

                
                    <!-- Minimum Savings Agreement -->
                    <label for="savings">I agree to make a minimum savings of:</label>
                    <input type="number" id="savings" name="savings" required placeholder="Enter amount" min="1">

                    <!-- Entrance Fee and Shares Capital -->
                    <p>If application is accepted I agree to pay an Entrance fee of E <input type="number" name="entrance_fee" required> & Shares Capital of E <input type="number" name="shares_capital" required></p>

                    
                    

                    <!-- Nominee -->
                    <label for="nominee">Nominee</label>
                    <input type="text" id="nominee" name="nominee" placeholder="Enter nominee details" required>

                    <!-- New Fields -->
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" required>

                    <label for="signature">Signature</label>
                    <input type="text" id="signature" name="signature" placeholder="Enter your signature" required>

                    <label for="contact">Contact</label>
                    <input type="text" id="contact" name="contact" placeholder="Enter your contact number" required>

                    <!-- DOCUMENTS -->
                    <label for="id_card">Identity Document</label>
                    Select an image to upload:
                    <input type="file" name="fileToUpload" id="fileToUpload"> 

                    <label for="id_card">Recent Payslip</label>
                    Select an image to Upload
                    <input type="file" name="recent_payslip" id="recent_payslip"> 

                    <!-- Agreement to Abide by Society Laws -->
                    <div class="laws-container">
                        <input type="checkbox" id="laws" name="laws" required>
                        <label for="laws">I agree to abide by all the laws of the society:</label>
                     </div>

                    <!-- Submit Button -->
                    <button type="submit" class="submit-btn">Apply for Membership</button>
                </form>
            </div>
        </div>
    </section>

        
    <footer>
    <div class="footer-container">
        <!-- About Section -->
        <div class="footer-section">
            <h4>About Us</h4>
            <p>We are a trusted savings and credit cooperative dedicated to empowering our members with financial solutions, including loans and secure savings options.</p>
        </div>

        <!-- Quick Links -->
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="./membership-apply.php">Apply</a></li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="faqs.html">FAQs</a></li>
            </ul>
        </div>

        <!-- Contact Section -->
        <div class="footer-section">
            <h4>Contact Us</h4>
            <p><i class="fa-solid fa-location-dot"></i> Mbabane, Eswatini</p>
            <p><i class="fa-solid fa-phone"></i> <a href="tel:+1234567890">+268 2404 2876</a></p>
            <p><i class="fa-solid fa-envelope"></i> <a href="mailto:info@coopfinance.com">info@coopfinance.com</a></p>
        </div>

        <!-- Social Media -->
        <!-- <div class="footer-section">
            <h4>Follow Us</h4>
            <div class="social-icons">
                <a href="#"><i class="fa-brands fa-facebook"></i></a>
                <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#"><i class="fa-brands fa-linkedin"></i></a>
            </div>
        </div> -->
    </div>

    <div class="footer-bottom">
        <p>&copy; 2025 CedisPay Co-op Savings & Loans. All Rights Reserved.</p>
    </div>
</footer>

    <script>
        // Select all navigation links
        const navLinks = document.querySelectorAll('nav ul li a');

        // Get the current page URL
        const currentUrl = window.location.pathname;

        // Loop through each link to check if it matches the current URL
        navLinks.forEach(link => {
            // Remove 'active' class from all links
            link.classList.remove('active');

            // If the link's href matches the current URL, add 'active' class
            if (link.getAttribute('href').includes(currentUrl.split('/').pop())) {
                link.classList.add('active');
            }
        });

    </script>

<script>
        // Select the menu icon
        const menuIcon = document.querySelector('.fa-bars');
        // Select the navigation menu
        const navMenu = document.querySelector('nav');

        // Add a click event listener to the menu icon
        menuIcon.addEventListener('click', () => {
            // Toggle the 'active' class on the navigation menu
            navMenu.classList.toggle('active');
        });
    </script>

    

</body>
</html>


<!-- HAVE TO ADD IMMEDIATE FEEDBACK, THAT THE FORM WAS SUCCESSFULLY RECIEVED WAITING FOR REVIEW. MAYBE GIVE LOGIN DETAILS IF SUCCESSFUL -->
 <!-- THERE IS A BUG ON RESIDENTIAL ADDRESS, IT IS NOT SHOWING IN THE DATA BASE, FIX IT  -->

<!-- HAVE TO ADD IMMEDIATE FEEDBACK, THAT THE FORM WAS SUCCESSFULLY RECIEVED WAITING FOR REVIEW. MAYBE GIVE LOGIN DETAILS IF SUCCESSFUL -->
 <!-- THERE IS A BUG ON RESIDENTIAL ADDRESS, IT IS NOT SHOWING IN THE DATA BASE, FIX IT  -->

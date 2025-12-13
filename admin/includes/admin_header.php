<?php
// Start session and check if admin is logged in.
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

// $is_admin = $_SESSION['role'] === 'admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Admin & Staff'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- member application -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- end of it -->

    <style>
      /* Basic Reset */
      /* body { margin:0; font-family: Arial, sans-serif; background-color: #f4f4f4; } */
      
      /* Sidebar styles */
      /* .sidebar {
          position: fixed;
          top: 0;
          left: 0;
          width: 250px;
          height: 100vh;
          background: #003366;
          color: #fff;
          padding: 20px;
          box-sizing: border-box;
      } */
      /* .sidebar h2 { text-align: center; margin-bottom: 30px; }
      .sidebar ul { list-style: none; padding: 0; }
      .sidebar ul li { margin-bottom: 15px; }
      .sidebar ul li a { color: #fff; text-decoration: none; }
      .sidebar ul li a:hover { text-decoration: underline; }
       */
      /* Main content */
      /* .main { margin-left: 270px; padding: 20px; }
      @media (max-width: 768px) {
          .sidebar { width: 200px; }
          .content { margin-left: 220px; }
      }
       */
      /* Header inside the content area */


      /* DASHBOARD */


      .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }

        .kpi-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.2s ease-in-out;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
        }

        .icon {
            font-size: 2.5rem;
            color: #4CAF50;
        }

        .content {
            display: flex;
            flex-direction: column;
        }

        .kpi-value {
            font-size: 1.8rem;
            font-weight: bold;
        }

        .kpi-label {
            font-size: 1rem;
            color: #777;
        }

        .charts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;

            position: relative;
            height:40vh;
            width:80vw;

    
        }

        canvas {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .charts {
                grid-template-columns: 1fr;
            }
        }


        /* SETTINGS */
        .settings-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .settings-title {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="email"], input[type="password"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-group input[type="checkbox"] {
            margin-right: 10px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .notification-count {
        position: absolute;
        top: 10px;
        right: 20px;
        width: 18px;
        height: 18px;
        background-color: red;
        color: white;
        border-radius: 50%;
        font-size: 12px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
      }

      .sidebar-link {
        position: relative;
  }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./admin.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" />
</head>
<body>


    <div class="wrapper">
      <aside id="sidebar">
        <div class="d-flex">
          <button id="toggle-btn" type="button">
            <img src="../assets/grid-icon.png" alt="grid-icon">
          </button>

          <div class="sidebar-logo">
            <a href="#">CedisPay</a>
          </div>
        </div>

        <ul class="sidebar-nav">

            <li class="sidebar-item">
                <a href="./dashboard.php" class="sidebar-link">
                <i class="fa-solid fa-gauge"></i>
                <span>Overview</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a href="./member_management.php" class="sidebar-link" onclick="showContent('members')">
              <i class="fa-solid fa-user-group"></i>
              <span>Members</span>
            </a>
          </li>


          <li class="sidebar-item">
            <a href="./loan_management.php" class="sidebar-link">
              <i class="fa-solid fa-money-bill"></i>
              <span>Loan Management</span>
            </a>
          </li>


          <!-- <li class="sidebar-item">
            <a href="./loan_products.php" class="sidebar-link">
              <i class="fa-solid fa-book"></i>
              <span>Loan Products</span>
            </a>
          </li> -->



            <!-- <//?php if($is_admin): ?> -->
          <!-- <li class="sidebar-item">
            <a href="./reports.php" class="sidebar-link">
              <i class="fas fa-chart-line"></i>
              <span>Reports</span>
            </a>
          </li> -->

          <!-- <//?php endif; ?> -->
        


          <li class="sidebar-item">
            <a href="./manage_users.php" class="sidebar-link">
              <i class="fa-solid fa-users-line"></i>
              <span>Manage Users</span>
            </a>
          </li>

          
  
          <li class="sidebar-item">
            <a class="sidebar-link" href="./settings.php">
              <i class="fa-solid fa-cog"></i>
              <span>Settings</span>
            </a>
          </li>
        </ul>
        
        <div class="sidebar-footer">
          <div class="sidebar-link">
            <a href="./logout.php">
                <i class="fa-solid fa-sign-out-alt"></i>
            </a>
            <span>Logout</span>
          </div>
        </div>
      
      </aside>

  

<?php
// Start session and check if admin is logged in.
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

// $is_verified = $_SESSION['role'] === 'member';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Client Page'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./member.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <!-- LOAN APPLICATION -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script type="text/javascript" src="./app.js" defer></script>
<!-- 
<style>
    .sidebar {
        width: 250px;
        height: 100vh;
        background: #111;
        color: white;
        position: fixed;
        left: -250px;
        top: 0;
        transition: left 0.3s ease-in-out;
        padding-top: 20px;
        z-index: 1000;
    }

    .sidebar .logo {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
    }

    .logo h2 {
        color: white;
    }

    .close-btn {
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
    }

    .menu {
        list-style: none;
        padding: 20px;
    }

    .menu li {
        margin: 15px 0;
    }

    .menu li a {
        color: white;
        text-decoration: none;
        font-size: 18px;
        display: flex;
        align-items: center;
    }

    .menu li a i {
        margin-right: 10px;
    }

    .sidebar.active {
        left: 0;
    }

    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        z-index: 999;
    }

    .sidebar.active ~ .overlay {
        display: block;
    }

    .main {
        flex: 1;
        padding: 20px;
    }

    .open-btn {
        font-size: 24px;
        cursor: pointer;
        background: none;
        border: none;
    }

@media (min-width: 768px) {
    .sidebar {
        left: 0;
    }

    .open-btn, .close-btn {
        display: none;
    }

    .overlay {
        display: none;
    }
}
</style> -->


<style>
    .sidebar {
      width: 250px;
      height: 100vh;
      background: #3b82f6;
      color: white;
      position: fixed;
      left: -250px;
      top: 0;
      transition: left 0.3s ease-in-out;
      padding-top: 20px;
      z-index: 1000;
  }
  .main {
  flex: 1;
  padding: 20px;
  }

  .sidebar .logo {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
}


.logo h2 {
    color: white;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
}

.menu {
    list-style: none;
    padding: 20px;
}


.menu li {
    margin: 15px 0;
}
.menu li a {
    color: white;
    text-decoration: none;
    font-size: 1rem;
    display: flex;
    align-items: center;
}
.menu li a i {
    margin-right: 10px;
}
.sidebar.active {
    left: 0;
}
.sidebar.active ~ .overlay {
    display: block;
}
.open-btn {
    font-size: 24px;
    cursor: pointer;
    background: none;
    border: none;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    z-index: 999;
}

/* Desktop View */



  @media (min-width: 768px) {
    .sidebar {
        left: 0;
    }
    .open-btn, .close-btn {
        display: none;
    }

    .overlay {
        display: none;
    }
    .main {
        margin-left: 250px;
    }
  }
</style>
</head>
<body>


      
    <!-- <nav id="sidebar">
        <ul>
            <li>
                <span class="logo">CedisPay</span>
                <button onclick="toggleSidebar()" id="toggle-btn">
                  <i class="fa-solid fa-bars fa-lg"></i>
                </button>
            </li>
            
            <li>
              <button onclick="toggleSubMenu(this)" class="dropdown-btn" >
                <i class="fa-solid fa-file-pen fa-lg"  data-bs-toggle="tooltip" title="Apply For A Loan"></i>
                <span>Apply for Loan</span>
                <i class="fa-solid fa-angle-down fa-lg"></i>
              </button>
              <ul class="sub-menu">
                <div>
                  <li><a href="./short-term-loan.php">Short Term Loan</a></li>
                  <li><a href="./emergency-loan.php">Emergency Loan</a></li>
                  <li><a href="./long-term-loan.php">Long Term Loan</a></li>
                </div>
                
              </ul>
            </li>

            <li>
                <a href="./settings.php">
                    <i class="fa-solid fa-cog fa-lg" data-bs-toggle="tooltip" title="Settings"></i>
                    <span>Settings</span>
                </a>
            </li>

        </ul>
        <li>
                <a href="./logout.php">
                  <i class="fa-solid fa-sign-out fa-lg" data-bs-toggle="tooltip" title="Profile"></i>
                  <span>Logout</span>
                </a>
            </li>
    </nav> -->


  
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <h2>CedisPay</h2>
            <button class="close-btn" onclick="toggleSidebar()">&#10005;</button>
        </div>
        <ul class="menu">
            <p style="font-size: 0.9rem; border-top: 1px solid white; padding-top: 10px; margin-bottom: 40px;">Apply for loans:</p>
        <li><a href="./short-term-loan.php"><i class="fas fa-hand-holding-usd"></i> Short Term Loan</a></li>
        <li><a href="./emergency-loan.php"><i class="fas fa-exclamation-triangle"></i> Emergency Loan</a></li>
        <li><a href="./long-term-loan.php"><i class="fas fa-piggy-bank"></i> Long Term Loan</a></li>
        <li><a href="./settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        <li><a href="./logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

        </ul>
    </div>

    <!-- Background Overlay (Only for mobile) -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>


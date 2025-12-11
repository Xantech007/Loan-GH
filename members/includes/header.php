<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CedisPay Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
            color: #333;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #001f3f; /* Dark blue */
            color: white;
            padding: 20px;
            box-sizing: border-box;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .navbar {
            background-color: #001f3f; /* Dark blue */
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar .logo {
            font-size: 1.5em;
            font-weight: bold;
        }
        .navbar .user-info {
            display: flex;
            align-items: center;
        }
        .navbar .user-info span {
            margin-right: 10px;
        }
        .footer {
            background-color: #001f3f; /* Dark blue */
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .card {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h2 {
            color: #001f3f; /* Dark blue */
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #001f3f; /* Dark blue */
            color: white;
        }
        .btn {
            background-color: #001f3f; /* Dark blue */
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        .btn:hover {
            background-color: #004080; /* Lighter dark blue */
        }
        .notification {
            background-color: #e9ecef;
            border-left: 5px solid #001f3f;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">

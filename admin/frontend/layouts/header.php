<!DOCTYPE html>
<html lang="<?php echo $_SESSION['admin_language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'SmartHealth Nepal - Admin'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Admin CSS -->
    <link href="/smarthealth_nepal/admin/frontend/css/admin.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0056b3;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            overflow-y: auto;
            position: fixed;
            height: 100vh;
        }
        
        .admin-main {
            margin-left: 250px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .admin-header {
            background-color: white;
            border-bottom: 1px solid #ddd;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-content {
            flex: 1;
            padding: 30px;
        }
        
        .sidebar-brand {
            padding: 30px 20px;
            border-bottom: 1px solid #34495e;
            font-size: 20px;
            font-weight: bold;
            color: white;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-nav li {
            border-bottom: 1px solid #34495e;
        }
        
        .sidebar-nav a {
            display: block;
            padding: 15px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-nav a:hover,
        .sidebar-nav .active a {
            background-color: var(--primary-color);
            color: white;
            padding-left: 30px;
        }
        
        .stat-updated {
            animation: pulse 0.5s ease-in-out;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
    </style>
</head>
<body>
<div class="admin-container">

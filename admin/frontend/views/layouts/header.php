<?php
/**
 * Admin Header Layout
 */

if (!isset($_SESSION['admin_language'])) {
    $_SESSION['admin_language'] = 'en';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['admin_language']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'SmartHealth Admin'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/smarthealth_nepal/admin/public/assets/css/admin.css">
    
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #6c757d;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #003d99 100%);
            color: white;
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .admin-container {
            display: flex;
            min-height: calc(100vh - 60px);
        }
        
        .sidebar {
            width: 250px;
            background-color: #f8f9fa;
            border-right: 1px solid #ddd;
            padding: 2rem 0;
            position: fixed;
            height: calc(100vh - 60px);
            overflow-y: auto;
        }
        
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 2rem;
        }
        
        .sidebar a {
            color: #333;
            text-decoration: none;
            display: block;
            padding: 0.75rem 1.5rem;
            transition: background-color 0.3s;
        }
        
        .sidebar a:hover,
        .sidebar a.active {
            background-color: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <header class="admin-header d-flex justify-content-between align-items-center">
        <h5 class=\"mb-0\"><i class=\"fas fa-hospital\"></i> SmartHealth Admin</h5>
        <div>
            <form method=\"GET\" style=\"display: inline;\">
                <select name=\"lang\" onchange=\"this.form.submit()\" class=\"form-select form-select-sm d-inline-block\" style=\"width: auto;\">
                    <option value=\"en\" <?php echo $_SESSION['admin_language'] === 'en' ? 'selected' : ''; ?>>English</option>
                    <option value=\"ne\" <?php echo $_SESSION['admin_language'] === 'ne' ? 'selected' : ''; ?>>नेपाली</option>
                </select>
            </form>
            <a href=\"?logout=true\" class=\"btn btn-light btn-sm ms-3\"><i class=\"fas fa-sign-out-alt\"></i> Logout</a>
        </div>
    </header>
    
    <div class=\"admin-container\">

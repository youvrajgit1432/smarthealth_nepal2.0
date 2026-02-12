<?php
/**
 * Header Layout
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure language is set
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'SmartHealth Nepal'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/smarthealth_nepal/frontend/public/assets/css/main.css">
    <link rel="stylesheet" href="/smarthealth_nepal/frontend/public/assets/css/responsive.css">
    
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        
        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #003d99 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            color: white !important;
            font-weight: 700;
            transition: opacity 0.3s;
        }
        
        .navbar-brand:hover {
            opacity: 0.9;
        }
        
        .navbar-brand i {
            font-size: 1.8rem;
            margin-right: 0.5rem;
        }
        
        /* Navigation Links */
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            margin: 0 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            font-size: 0.95rem;
        }
        
        .navbar-nav .nav-link:hover {
            color: white !important;
        }
        
        .navbar-nav .nav-link.active {
            color: white !important;
            border-bottom: 3px solid white;
            padding-bottom: 0.5rem;
        }
        
        /* Button Groups (Language Switcher) */
        .btn-group .btn {
            border-color: rgba(255, 255, 255, 0.5);
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
        }
        
        .btn-group .btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: white;
        }
        
        .btn-group .btn.active {
            background-color: white;
            color: var(--primary-color);
            border-color: white;
        }
        
        /* Login Button */
        .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: white;
            color: white;
        }
        
        /* Primary CTA: Get Token */
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000 !important;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        }
        
        .btn-warning:hover {
            background-color: #ffb300;
            border-color: #ffb300;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(255, 193, 7, 0.4);
        }
        
        /* Mobile Responsive */
        @media (max-width: 991px) {
            .navbar-collapse {
                background-color: rgba(0, 0, 0, 0.1);
                border-radius: 0.5rem;
                margin-top: 1rem;
                padding: 1rem;
            }
            
            .navbar-nav {
                flex-direction: column;
            }
            
            .navbar-nav .nav-link {
                padding: 0.5rem 0 !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .navbar-nav .nav-link:last-child {
                border-bottom: none;
            }
            
            .navbar-nav .nav-link.active {
                border-bottom: none;
                border-left: 3px solid white;
                padding-left: 1rem !important;
            }
            
            .navbar-nav .gap-2 {
                gap: 0.5rem !important;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .btn-group,
            .btn-sm {
                width: 100%;
            }
            
            .btn-group .btn {
                flex: 1;
            }
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #003d99 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        header h1 {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
        }
        
        header p {
            font-size: 0.9rem;
            margin: 0;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <!-- Logo Section -->
            <a class="navbar-brand fw-bold" href="/smarthealth_nepal/frontend/public/">
                <i class="fas fa-hospital-user"></i>
                <span class="d-none d-md-inline ms-2">SmartHealth Nepal</span>
            </a>

            <!-- Toggle Button for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Left Navigation -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo isset($activePage) && $activePage === 'home' ? 'active' : ''; ?>" href="/smarthealth_nepal/frontend/public/">
                            <?php echo isset($lang['home']) ? $lang['home'] : 'Home'; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">
                            <?php echo isset($lang['how_it_works']) ? $lang['how_it_works'] : 'How It Works'; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/smarthealth_nepal/frontend/views/services/">
                            <?php echo isset($lang['services']) ? $lang['services'] : 'Services'; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#offices">
                            <?php echo isset($lang['offices']) ? $lang['offices'] : 'Offices'; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isset($activePage) && $activePage === 'status' ? 'active' : ''; ?>" href="/smarthealth_nepal/frontend/views/token/status.php">
                            <?php echo isset($lang['track_status']) ? $lang['track_status'] : 'Track Status'; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">
                            <?php echo isset($lang['about']) ? $lang['about'] : 'About'; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">
                            <?php echo isset($lang['contact']) ? $lang['contact'] : 'Contact'; ?>
                        </a>
                    </li>
                </ul>

                <!-- Right Navigation -->
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <!-- Language Switcher (Toggle Button Style) -->
                    <li class="nav-item">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="?lang=en" class="btn btn-outline-light <?php echo $_SESSION['language'] === 'en' ? 'active' : ''; ?>">EN</a>
                            <a href="?lang=ne" class="btn btn-outline-light <?php echo $_SESSION['language'] === 'ne' ? 'active' : ''; ?>">NE</a>
                        </div>
                    </li>

                    <!-- Login Button (Secondary Action) -->
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a href="/smarthealth_nepal/frontend/views/auth/login.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-in-alt"></i> <span class="ms-1"><?php echo isset($lang['login']) ? $lang['login'] : 'Login'; ?></span>
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a href="/smarthealth_nepal/frontend/views/auth/login.php?action=logout" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt"></i> <span class="ms-1"><?php echo isset($lang['logout']) ? $lang['logout'] : 'Logout'; ?></span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Primary CTA: Get Token (Prominent Button) -->
                    <li class="nav-item">
                        <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-warning btn-sm fw-bold">
                            <i class="fas fa-ticket-alt"></i> <span class="ms-1"><?php echo isset($lang['get_token']) ? $lang['get_token'] : 'Get Token'; ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <main class="container my-5">

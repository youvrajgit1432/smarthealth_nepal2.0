<?php
/**
 * Footer Layout
 */
?>
    </main>
    <style>
        :root {
            --primary-color: #0ea5e9;
            --secondary-color: #0d9488;
            --accent-color: #10b981;
            --dark-color: #0f172a;
            --light-color: #f8fafc;
        }
        
        .footer {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color), var(--accent-color));
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            color: var(--light-color);
            padding: 60px 0 30px;
            position: relative;
            overflow: hidden;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            z-index: 0;
        }
        
        .footer-content {
            position: relative;
            z-index: 1;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .footer-title {
            font-weight: 700;
            margin-bottom: 25px;
            position: relative;
            display: inline-block;
        }
        
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--light-color);
            border-radius: 3px;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            position: relative;
        }
        
        .footer-links a:hover {
            color: var(--light-color);
            transform: translateX(5px);
        }
        
        .footer-links a::before {
            content: '\f0da';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 8px;
            opacity: 0.7;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover::before {
            opacity: 1;
            transform: translateX(3px);
        }
        
        .contact-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: all 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }
        
        .contact-info {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .contact-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
        }
        
        .contact-info:nth-child(2) .contact-icon {
            animation-delay: 0.5s;
        }
        
        .contact-info:nth-child(3) .contact-icon {
            animation-delay: 1s;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0px); }
        }
        
        .contact-info:hover .contact-icon {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--light-color);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .social-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            opacity: 0;
            transition: all 0.3s ease;
            z-index: -1;
        }
        
        .social-icon:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .social-icon:hover::before {
            opacity: 1;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            margin-top: 40px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .footer {
                text-align: center;
            }
            
            .footer-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .contact-info {
                justify-content: center;
            }
            
            .social-icons {
                justify-content: center;
            }
        }
    </style>
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>SmartHealth Nepal</h5>
                    <p><?php echo isset($lang['tagline']) ? $lang['tagline'] : 'Sustainable Digital Healthcare'; ?></p>
                </div>
                <div class="col-md-4">
                    <h5><?php echo isset($lang['about']) ? $lang['about'] : 'About'; ?></h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-decoration-none text-light">About Us</a></li>
                        <li><a href="#" class="text-decoration-none text-light"><?php echo isset($lang['contact']) ? $lang['contact'] : 'Contact'; ?></a></li>
                        <li><a href="#" class="text-decoration-none text-light"><?php echo isset($lang['services']) ? $lang['services'] : 'Services'; ?></a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5><?php echo isset($lang['contact_us']) ? $lang['contact_us'] : 'Contact Us'; ?></h5>
                    <p>
                        <i class="fas fa-phone"></i> +977-1-XXXXXXX<br>
                        <i class="fas fa-envelope"></i> info@smarthealth.npl<br>
                        <i class="fas fa-map-marker-alt"></i> Kathmandu, Nepal
                    </p>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center">
                <p>&copy; 2024 SmartHealth Nepal. All rights reserved.</p>
                <p>
                    <a href="#" class="text-decoration-none text-light"><?php echo isset($lang['privacy_policy']) ? $lang['privacy_policy'] : 'Privacy Policy'; ?></a> |
                    <a href="#" class="text-decoration-none text-light"><?php echo isset($lang['terms_conditions']) ? $lang['terms_conditions'] : 'Terms & Conditions'; ?></a>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/smarthealth_nepal/frontend/public/assets/js/main.js"></script>
    
    <script>
        // Language switcher
        document.addEventListener('DOMContentLoaded', function() {
            const langSelect = document.querySelector('.language-switcher');
            if (langSelect) {
                langSelect.addEventListener('change', function() {
                    const currentUrl = window.location.href;
                    const separator = currentUrl.includes('?') ? '&' : '?';
                    window.location.href = currentUrl + separator + 'lang=' + this.value;
                });
            }
        });
    </script>
</body>
</html>

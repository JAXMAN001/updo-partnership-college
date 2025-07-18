<p style="color: black;"><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UP-DO | Student-Supervisor Partnership System</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/partnership.css">
    <script>
    // Animate body on load & other DOM ready scripts
    document.addEventListener('DOMContentLoaded', function() {
        // Body animation
        document.body.style.opacity = '1';
        document.body.style.transform = 'none';

        // Custom cursor logic
        const cursor = document.querySelector('.custom-cursor');

        document.addEventListener('mousemove', e => {
            // Use requestAnimationFrame for smoother animation
            requestAnimationFrame(() => {
                cursor.style.left = e.clientX + 'px';
                cursor.style.top = e.clientY + 'px';
            });
        });

        const hoverElements = document.querySelectorAll('a, button, .cta-btn, .login-btn, .institution-card, .contact-item, .social-links a');
        hoverElements.forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hovered'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hovered'));
        });

        // Intersection Observer for section animations
        const sections = document.querySelectorAll('section, footer, header');
        sections.forEach(sec => sec.classList.add('animated-section'));

        const observer = new window.IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                } else {
                    entry.target.classList.remove('visible');
                }
            });
        }, { threshold: 0.15 });

        sections.forEach(sec => observer.observe(sec));
    });
    </script>
    <style>
        @keyframes zoomInOut {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        #partnership-feature img {
            animation: zoomInOut 4s ease-in-out infinite;
        }

        /* Custom Cursor Styles */
        body {
            cursor: none;
        }
        .custom-cursor {
            width: 12px;
            height: 12px;
            background-color: blue;
            border-radius: 50%;
            position: fixed;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 10000;
            transition: transform 0.2s ease-out, width 0.2s ease-out, height 0.2s ease-out, background-color 0.2s ease-out, border 0.2s ease-out;
            border: 2px solid transparent;
        }
        .custom-cursor.hovered {
            width: 40px;
            height: 40px;
            background-color: rgba(0, 0, 255, 0.3);
            border: 2px solid blue;
        }
    </style>
</head>
<body style="opacity:0; transform:translateY(20px);">
    <div class="custom-cursor"></div>
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <span style="display:flex; align-items:center;">
                    <img src="updo.png" alt="UP-DO Logo" style="height:38px; margin-right:10px;">
                    <a href="#" class="logo">UP-DO</a>
                </span>
                
                <a href="student_login.php" class="login-btn">Student/Staff Login</a>                  
                 <a href="#" class="login-btn open-partnership-modal">Make a Partnership with UP-DO</a>
                 <a href="#" class="login-btn">Farming Pool</a>
                  <a href="add_new_cyber_security.php" class="login-btn">CYBER REGISTER</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Academic Supervision Made Simple</h1>
            <p>Connecting students with the perfect supervisors across universities, colleges, and nursing schools nationwide</p>
            <a href="#contact" class="cta-btn">Request Information</a>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <h2>About UP-DO</h2>
                <p>UP-DO (Upload Documents) revolutionizes academic supervision by intelligently pairing students with qualified supervisors based on research interests, department requirements, and institutional policies.</p>
                <p>Our platform serves:</p>
                <ul style="margin-left: 2rem; margin-bottom: 1.5rem;">
                    <li>State Universities</li>
                    <li>Federal Institutions</li>
                    <li>Accredited Nursing Schools</li>
                    <li>Technical Colleges</li>
                </ul>
                <p>Key features include document tracking, progress monitoring, and compliance management for both students and supervisors.</p>
            </div>
            <div class="about-images-wrapper">
                <div class="about-images">
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <img src="web1.png" alt="Academic Director" class="owner-image1">
                        <span class="owner-name">Bilal Muhammed Shuaibu</span>
                        <span class="position">PROPRIETOR AND WEBMASTER</span>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <img src="web2.png" alt="Academic Director" class="owner-image2">
                        <span class="owner-name">Ahmad Jafar Waziri</span>
                         <span class="position">DIRECTOR OF ACADEMIC PARTNERSHIP</span>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:center;">
                        <img src="web3.png" alt="Academic Director" class="owner-image3">
                        <span class="owner-name">Dr. Ashiru Muhammad Umar</span>
                         <span class="position">FOUNDER AND CEO OF (UP-D0)</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Institutions Section -->
    <section id="institutions" class="institutions">
        <div class="container">
            <h2>Partner Institutions</h2>
            <div class="institution-grid">
                <div class="institution-card">
                    <i class="fas fa-university"></i>
                    <h3 style="color: black;">State Universities</h3>
                    <p style="color: black;">Comprehensive support for all state-funded higher education institutions</p>
                </div>
                <div class="institution-card">
                    <i class="fas fa-landmark"></i>
                    <h3 style="color: black;">Federal Institutions</h3>
                    <p style="color: black;">Specialized workflows for federal university requirements</p>
                </div>
                <div class="institution-card">
                    <i class="fas fa-user-md"></i>
                    <h3 style="color: black;">Nursing Schools</h3>
                    <p style="color: black;">Clinical supervision tracking for nursing programs</p>
                </div>
                <div class="institution-card">
                    <i class="fas fa-graduation-cap"></i>
                    <h3 style="color: black;">Colleges of Education</h3>
                    <p style="color: black;">Teaching practice supervision solutions</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Partnership Feature Section -->
    <section id="partnership-feature" class="about" style="background-color: #e0e0e0;">
        <div class="container" style="display: flex; align-items: center; padding: 4rem 0;">
            <!-- Image on the left -->
            <div style="flex: 1.5; text-align: center; padding-right: 3rem;">
                <img src="ROLES.png" alt="Academic Partnership" style="width: 100%; max-width: 650px; height: auto; border-radius: 32px; box-shadow: 0 60px 25px rgba(0,0,0,0.1);">
            </div>
            
            <!-- Vertical Divider -->
            <div style="width: 2px; background-color: blue; align-self: stretch;"></div>

            <!-- Text on the right -->
            <div style="flex: 1; padding-left: 3rem;">
                <h2 style="color: blue">A Growing Network of Excellence</h2>
                <p style="color: black;">UP-DO is committed to expanding its network, bringing more institutions into a unified ecosystem of academic supervision. Our platform facilitates meaningful connections that enhance research quality and student success.</p>
                <p style="color: black;">By joining our network, institutions gain access to a streamlined process for managing student-supervisor relationships, ensuring compliance and fostering academic growth.</p>
                <a href="#contact" class="cta-btn" style="margin-top: 1rem; background: #e0e0e0; border: 2px solid blue; color: black">Become a Partner</a>
            </div>
        </div>
    </section>

    <!-- Second Feature Section -->
    <section id="second-feature" class="about" style="background-color: black;">
        <div class="container" style="display: flex; align-items: center; padding: 4rem 0;">
            <!-- Text on the left -->
            <div style="flex: 1; padding-right: 3rem; color: wheat;">
                <h2>Streamlined Document Management</h2>
                <p>Our platform provides a centralized hub for students to upload and manage their project documents, from proposals to final theses. Supervisors can review, comment, and approve submissions seamlessly.</p>
                <p>This organized workflow ensures that no deadline is missed and all feedback is tracked, leading to a more efficient and less stressful academic journey for everyone involved.</p>
                <a href="cyber_secure_login.php" class="cta-btn" style="margin-top: 1rem; border: 2px solid orange;">Cyber and School Management Features</a>
            </div>

            <!-- Vertical Divider -->
            <div style="width: 2px; background-color: orange; align-self: stretch;"></div>

            <!-- Image on the right -->
            <div style="flex: 1.5; text-align: center; padding-left: 3rem;">
                <img src="web1.png" alt="Document Management" style="width: 100%; max-width: 650px; height: auto; border-radius: 32px; box-shadow: 0 60px 25px rgba(0,0,0,0.1);">
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2>Contact Our Academic Team</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>agentacademic@updo.edu.ng</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+ (234) 808-2991-133</span>
                </div>
            </div>
            <div class="social-links">
                <a href="#"><i class="fab fa-linkedin fa-2x"></i></a>
                <a href="#"><i class="fab fa-twitter fa-2x"></i></a>
                <a href="#"><i class="fab fa-facebook fa-2x"></i></a>
            </div>
        </div><br><br><br>
         <a href="#" class="login-btn open-partnership-modal">Make a Partnership with UP-DO</a>
    </section>

    <!-- Modal for Institutions List -->
    <div id="institutionsModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:38px 32px 28px 32px; border-radius:12px; min-width:340px; max-width:96vw; max-height:90vh; box-shadow:0 8px 32px r; position:relative; overflow-y:auto;">
            <button onclick="closeInstitutionsModal()" style="position:absolute; top:10px; right:16px; background:none; border:none; font-size:1.5em; color:#e74c3c; cursor:pointer;">&times;</button>
        
            <table style="width:100%; border-collapse:collapse; background:#f9f9f9; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(44,62,80,0.08);">
                <thead>
                    <tr style="background:blue; color:#fff;">
                        <th style="padding:10px 18px; text-align:center; width:60px;">S/N</th>
                        <th style="padding:10px 18px; text-align:left;">INSTITUTIONS</th>
                    </tr>
                </thead>
                <tbody style="color: black;">
                    <?php
                    $institutions = [
                        "Abia State University, Uturu (ABSU)",
                        "Abubakar Tafawa Balewa University, Bauchi (ATBU)",
                        "Adekunle Ajasin University, Akungba (AAUA)",
                        "Adeyemi College of Education, Ondo (Affiliated to OAU)",
                        "Adamawa State University, Mubi (ADSU)",
                        "Ahmadu Bello University, Zaria (ABU)",
                        "Akwa Ibom State University (AKSU)",
                        "Alvan Ikoku College of Education, Owerri",
                        "Bauchi State University, Gadau (BASUG)",
                        "Bayero University, Kano (BUK)",
                        "Benue State University, Makurdi (BSU)",
                        "Chukwuemeka Odumegwu Ojukwu University, Uli (COOU)",
                        "College of Education, Ikere-Ekiti",
                        "Cross River University of Technology (CRUTECH)",
                        "Delta State University, Abraka (DELSU)",
                        "Ebonyi State University, Abakaliki (EBSU)",
                        "Edo State University, Uzairue (EDSU)",
                        "Ekiti State University, Ado-Ekiti (EKSU)",
                        "Enugu State University of Science and Technology (ESUT)",
                        "Federal College of Education (Technical), Akoka",
                        "Federal College of Education, Eha-Amufu",
                        "Federal College of Education, Kano",
                        "Federal College of Education, Zaria",
                        "Federal University Gashua, Yobe (FUGASHUA)",
                        "Federal University of Petroleum Resources, Effurun (FUPRE)",
                        "Federal University of Technology, Akure (FUTA)",
                        "Federal University of Technology, Minna (FUTMINNA)",
                        "Federal University of Technology, Owerri (FUTO)",
                        "Federal University, Dutse, Jigawa (FUD)",
                        "Federal University, Dutsin-Ma, Katsina (FUDMA)",
                        "Federal University, Gusau, Zamfara (FUGUS)",
                        "Federal University, Kashere, Gombe (FUKASHERE)",
                        "Federal University, Lafia, Nasarawa (FULAFIA)",
                        "Federal University, Lokoja, Kogi (FULOKOJA)",
                        "Federal University, Otuoke, Bayelsa (FUOTUOKE)",
                        "Federal University, Oye-Ekiti, Ekiti (FUOYE)",
                        "Federal University, Wukari, Taraba (FUWUKARI)",
                        "Gombe State University (GSU)",
                        "Ibrahim Badamasi Babangida University, Lapai (IBBUL)",
                        "Imo State University, Owerri (IMSU)",
                        "Kebbi State University of Science and Technology, Aliero (KSUSTA)",
                        "Kogi State University, Anyigba (KSU)",
                        "Kwara State College of Education, Ilorin",
                        "Lagos State University (LASU)",
                        "Michael Okpara University of Agriculture, Umudike (MOUAU)",
                        "Modibbo Adama University of Technology, Yola (MAUTECH)",
                        "National Open University of Nigeria (NOUN)",
                        "Nasarawa State University, Keffi (NSUK)",
                        "Nigerian Defence Academy, Kaduna (NDA)",
                        "Nnamdi Azikiwe University, Awka (UNIZIK)",
                        "Obafemi Awolowo University, Ile-Ife (OAU)",
                        "Olabisi Onabanjo University, Ago-Iwoye (OOU)",
                        "Osun State University, Osogbo (UNIOSUN)",
                        "Plateau State University, Bokkos (PLASU)",
                        "Rivers State University (RSU)",
                        "Sacred Heart School of Nursing, Abeokuta",
                        "School of Nursing, Ahmadu Bello University Teaching Hospital, Zaria",
                        "School of Nursing, Lagos University Teaching Hospital (LUTH)",
                        "School of Nursing, National Orthopaedic Hospital, Enugu",
                        "School of Nursing, Obafemi Awolowo University Teaching Hospital (OAUTH), Ile-Ife",
                        "School of Nursing, University College Hospital (UCH), Ibadan",
                        "School of Nursing, University of Nigeria Teaching Hospital (UNTH), Enugu",
                        "St. Gerard’s Catholic School of Nursing, Kaduna",
                        "Sokoto State University (SSU)",
                        "Tai Solarin College of Education, Omu-Ijebu",
                        "Tai Solarin University of Education, Ijagun (TASUED)",
                        "Taraba State University, Jalingo (TSU)",
                        "University of Abuja, Gwagwalada (UNIABUJA)",
                        "University of Agriculture, Abeokuta (FUNAAB)",
                        "University of Agriculture, Makurdi (UAM)",
                        "University of Benin (UNIBEN)",
                        "University of Calabar (UNICAL)",
                        "University of Ibadan (UI)",
                        "University of Ilorin (UNILORIN)",
                        "University of Jos (UNIJOS)",
                        "University of Lagos (UNILAG)",
                        "University of Maiduguri (UNIMAID)",
                        "University of Nigeria, Nsukka (UNN)",
                        "University of Port Harcourt (UNIPORT)",
                        "University of Uyo (UNIUYO)",
                        "Usmanu Danfodiyo University, Sokoto (UDUSOK)",
                        "Yobe State University, Damaturu (YSU)",
                        "Zamfara State University, Talata Mafara"
                    ];
                    foreach ($institutions as $i => $name) {
                        echo '<tr style="border-bottom:1px solid #eaeaea;"><td style="text-align:center; padding:8px 0;">'.($i+1).'</td><td style="padding:8px 10px;">'.htmlspecialchars($name).'</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Partnership Options Modal -->
<div id="partnershipModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:30px; border-radius:12px; width:90%; max-width:500px; text-align:center; box-shadow:0 5px 15px rgba(0,0,0,0.3);">
        <h2 style="color:blue; margin-bottom:20px;">Partnership Options</h2>
        <p style="margin-bottom:30px; color:#555;">Please choose how you'd like to proceed with your partnership application:</p>
        
        <div style="display:flex; flex-direction:column; gap:15px;">
            <a href="partnership_form.php" style="background:blue; color:white; padding:12px; border-radius:6px; text-decoration:none; font-weight:bold; transition:0.3s;">
                Fresh Registration
            </a>
            <a href="partnership_continue_payment.php" style="background:green; color:white; padding:12px; border-radius:6px; text-decoration:none; font-weight:bold; transition:0.3s;" title="For users who have registered but not yet paid">
                Continue to Payment
            </a>
        </div>
        
        <button onclick="closePartnershipModal()" style="margin-top:30px; background:none; border:1px solid #ccc; padding:8px 20px; border-radius:4px; cursor:pointer;">
            Cancel
        </button>
    </div>
</div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-links">
                <a href="#">Home</a>
                <a href="#about">About</a>
                <a href="#institutions">Institutions</a>
                <a href="#contact">Contact</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Academic Policy</a>
            </div>
            <p class="copyright">© 2025 UP-D0 (Upload Document) Academic Partnership System. All Rights Reserved.</p>
        </div>
    </footer>
    <script>
    // Modal logic for Institutions
    document.querySelectorAll('a[href="#institutions"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('institutionsModal').style.display = 'flex';
        });
    });
    function closeInstitutionsModal() {
        document.getElementById('institutionsModal').style.display = 'none';
    }

    // Partnership Modal logic
document.querySelectorAll('.open-partnership-modal').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('partnershipModal').style.display = 'flex';
    });
});

function closePartnershipModal() {
    document.getElementById('partnershipModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('partnershipModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePartnershipModal();
    }
});
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورتل - رتل القرآن ترتيلاً</title>
    <link rel="icon" href="{{ asset('images/mainlogo.png') }}" type="image/png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

    <style>
        :root {
            /* Brand Identity */
            --primary-dark: #2d8a74;
            --primary-medium: #4fb299;
            --primary-light: #f0f9f7;
            --gold-main: #d4a753;
            --gold-light: #fdf5e6;

            /* UI Colors */
            --bg-body: #ffffff;
            --text-main: #1e4d42;
            --text-muted: #6a8d85;
            --card-cream: #fcf8f0;
            --btn-orange: #d4a753;
            --btn-orange-shadow: #b3893f;
        }

        /* --- Global Reset & Scroll Locking --- */
        html,
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden !important;
            width: 100%;
            -webkit-font-smoothing: antialiased;
        }

        body.loading-locked {
            overflow: hidden !important;
            height: 100vh;
        }

        /* =========================================
           1. CINEMATIC LOADER
           ========================================= */
        #loader-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            z-index: 9999999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.9s cubic-bezier(0.77, 0, 0.175, 1);
        }

        .loader-container {
            position: relative;
            overflow: hidden;
            padding: 20px;
        }

        .loader-brand {
            font-size: clamp(3rem, 10vw, 5rem);
            font-weight: 900;
            line-height: 1;
            position: relative;
            color: #f3f3f3;
            margin: 0;
            letter-spacing: -2px;
        }

        .loader-brand::before {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            color: var(--primary-dark);
            border-right: 4px solid var(--gold-main);
            overflow: hidden;
            animation: fillText 2.5s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            white-space: nowrap;
        }

        .loader-sub {
            margin-top: 10px;
            font-size: clamp(0.9rem, 3vw, 1.2rem);
            color: var(--gold-main);
            font-weight: 700;
            opacity: 0;
            transform: translateY(15px);
            animation: fadeUp 0.8s ease forwards 1.8s;
            text-align: center;
            letter-spacing: 1px;
        }

        @keyframes fillText {
            0% {
                width: 0;
            }

            100% {
                width: 100%;
            }
        }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up-exit {
            transform: translateY(-100%);
        }


        /* =========================================
           2. NAVBAR
           ========================================= */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            transition: all 0.4s ease;
            z-index: 1000;
        }

        .nav-link {
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0 10px;
            font-size: 1rem;
            position: relative;
        }

        @media (min-width: 992px) {
            .nav-link::before {
                content: '';
                position: absolute;
                bottom: 0;
                right: 0;
                width: 0;
                height: 3px;
                background: var(--gold-main);
                transition: 0.3s;
                border-radius: 2px;
            }

            .nav-link:hover::before,
            .nav-link.active::before {
                width: 100%;
            }
        }

        .nav-link-teacher {
            color: var(--gold-main) !important;
            border: 1px solid var(--gold-main);
            border-radius: 50px;
            padding: 6px 20px !important;
            margin-left: 10px;
            transition: 0.3s;
            font-weight: 700;
            display: inline-block;
            text-decoration: none;
        }

        .nav-link-teacher:hover {
            background: var(--gold-main);
            color: white !important;
        }

        .btn-nav-cta {
            background: var(--primary-dark);
            color: white;
            padding: 8px 25px;
            border-radius: 50px;
            font-weight: 700;
            box-shadow: 0 5px 15px rgba(26, 77, 46, 0.2);
            transition: 0.3s;
            border: none;
            font-size: 0.95rem;
            white-space: nowrap;
        }

        .btn-nav-cta:hover {
            transform: translateY(-2px);
            background: var(--gold-main);
            color: white;
        }

        .navbar-toggler {
            border: none;
            padding: 0;
            color: var(--primary-dark);
            font-size: 1.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        /* =========================================
           3. HERO SECTION
           ========================================= */
        .hero-section {
            padding-top: 140px;
            padding-bottom: 80px;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .hero-bg-anim {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 0% 0%, #f1fcf5 0%, transparent 60%), radial-gradient(circle at 100% 100%, #fffbf0 0%, transparent 60%);
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.5;
            animation: floatBubble 10s infinite alternate;
        }

        .shape-1 {
            width: 350px;
            height: 350px;
            background: #e0f2f1;
            top: -50px;
            left: -50px;
        }

        .shape-2 {
            width: 250px;
            height: 250px;
            background: #fff8e1;
            bottom: 0;
            right: -20px;
            animation-delay: -5s;
        }

        @keyframes floatBubble {
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(30px, 30px);
            }
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 900;
            line-height: 1.2;
            color: var(--primary-dark);
            margin-bottom: 20px;
            background: linear-gradient(to right, var(--primary-dark) 0%, var(--gold-main) 50%, var(--primary-dark) 100%);
            background-size: 200% auto;
            color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
            animation: shineText 5s linear infinite;
        }

        @keyframes shineText {
            to {
                background-position: 200% center;
            }
        }

        .hero-subtitle {
            font-size: 1.15rem;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 30px;
            max-width: 550px;
        }

        .hero-image-wrapper {
            position: relative;
            perspective: 1000px;
            margin-top: 10px;
        }

        .hero-phone {
            width: 100%;
            max-width: 360px;
            border-radius: 40px;
            box-shadow: 0 40px 80px rgba(26, 77, 46, 0.15);
            border: 8px solid white;
            animation: floatPhone 6s ease-in-out infinite;
            transform: rotateY(-10deg) rotateX(5deg);
        }

        @keyframes floatPhone {

            0%,
            100% {
                transform: translateY(0) rotateY(-10deg);
            }

            50% {
                transform: translateY(-15px) rotateY(-5deg);
            }
        }

        .store-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border-radius: 14px;
            text-decoration: none;
            transition: 0.3s;
            border: 1px solid rgba(0, 0, 0, 0.1);
            min-width: 150px;
        }

        .store-btn-dark {
            background: #1f2937;
            color: white;
        }

        .store-btn-dark:hover {
            background: black;
            transform: translateY(-4px);
            color: white;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .store-btn-light {
            background: white;
            color: #1f2937;
        }

        .store-btn-light:hover {
            background: #f9fafb;
            transform: translateY(-4px);
            color: #1f2937;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .store-btn-icon {
            font-size: 24px;
            margin-left: 10px;
        }

        .store-btn-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
            text-align: left;
        }

        .store-btn-small {
            font-size: 10px;
            opacity: 0.8;
        }

        .store-btn-big {
            font-size: 15px;
            font-weight: 700;
        }

        .teacher-separator {
            display: flex;
            align-items: center;
            margin: 30px 0 15px 0;
            color: var(--gold-main);
            font-weight: 700;
            font-size: 0.9rem;
        }

        .teacher-separator::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(196, 154, 70, 0.3);
            margin-right: 15px;
        }


        /* =========================================
           4. NEW STATS SECTION (COUNTER)
           ========================================= */
        .stats-section {
            background: var(--primary-dark);
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .stats-bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.05;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .stat-item {
            text-align: center;
            position: relative;
            z-index: 2;
            padding: 20px;
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--gold-main);
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 600;
        }


        /* =========================================
           OTHER SECTIONS
           ========================================= */
        .why-box {
            text-align: center;
            padding: 30px;
            height: 100%;
            transition: 0.3s;
        }

        .why-icon {
            width: 80px;
            height: 80px;
            background: var(--gold-light);
            color: var(--gold-main);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
            transition: 0.3s;
        }

        .why-box:hover .why-icon {
            background: var(--gold-main);
            color: white;
            transform: rotateY(180deg);
        }

        .about-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border-right: 4px solid var(--gold-main);
            height: 100%;
        }

        .about-icon-small {
            color: var(--primary-dark);
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .section-header {
            text-center: center;
            margin-bottom: 50px;
        }

        .section-tag {
            color: var(--gold-main);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .section-title {
            font-weight: 800;
            color: var(--primary-dark);
            font-size: 2.2rem;
        }

        .feature-box {
            background: white;
            padding: 35px 25px;
            border-radius: 24px;
            transition: 0.4s;
            border: 1px solid #f0f0f0;
            height: 100%;
        }

        .feature-box:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
            border-color: var(--gold-main);
        }

        .icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: var(--primary-light);
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
            transition: 0.4s;
        }

        .feature-box:hover .icon-wrapper {
            background: var(--primary-dark);
            color: var(--gold-main);
            transform: rotate(5deg);
        }

        .pricing-section {
            background-color: #fafafa;
            padding: 80px 0;
        }

        .pkg-card {
            background: white;
            border-radius: 28px;
            padding: 35px 25px;
            text-align: center;
            border: 1px solid #eee;
            transition: 0.3s;
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .pkg-card.featured {
            background-color: var(--card-cream);
            border: 2px solid #fff;
            box-shadow: 0 20px 50px rgba(228, 178, 86, 0.15);
            transform: scale(1.05);
            z-index: 5;
        }

        .badge-popular {
            position: absolute;
            top: 25px;
            left: -30px;
            background: var(--primary-dark);
            color: var(--gold-main);
            padding: 5px 40px;
            font-weight: 800;
            font-size: 0.8rem;
            transform: rotate(-45deg);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        .pkg-name {
            font-weight: 800;
            font-size: 1.3rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }

        .pkg-gift {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 10px;
            padding: 6px 12px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .btn-3d {
            width: 100%;
            border: none;
            padding: 12px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1.05rem;
            position: relative;
            transition: 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-3d.orange {
            background: var(--btn-orange);
            color: white;
            box-shadow: 0 5px 0 var(--btn-orange-shadow);
        }

        .btn-3d.green {
            background: var(--primary-dark);
            color: white;
            box-shadow: 0 5px 0 #0f3d22;
        }

        .btn-3d.outline {
            background: white;
            color: var(--primary-dark);
            border: 2px solid var(--primary-dark);
            box-shadow: 0 5px 0 #e2e8f0;
        }

        .btn-3d:active {
            transform: translateY(5px);
            box-shadow: none !important;
        }

        .cursor-icon {
            position: absolute;
            bottom: -12px;
            left: 12px;
            width: 24px;
            filter: drop-shadow(0 3px 3px rgba(0, 0, 0, 0.2));
            animation: floatCursor 2.5s infinite ease-in-out;
            pointer-events: none;
        }

        .teacher-slide {
            padding: 10px;
        }

        .teacher-card {
            background: white;
            border-radius: 24px;
            padding: 25px 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03);
            border: 1px solid #f5f5f5;
            transition: 0.3s;
        }

        .teacher-card:hover {
            transform: translateY(-5px);
            border-color: var(--gold-main);
        }

        .teacher-img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            margin: 0 auto 12px;
            padding: 3px;
            background: linear-gradient(to bottom, var(--primary-dark), var(--gold-main));
        }

        .teacher-img img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }

        /* =========================================
           6. NEW SECTIONS (TESTIMONIALS & CONTACT)
           ========================================= */

        /* Testimonials Section */
        .testimonials-section {
            background-color: #f9f9f9;
            padding: 80px 0;
            position: relative;
        }

        .testimonial-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            position: relative;
            margin-top: 30px;
            transition: 0.3s;
            border: 1px solid #f0f0f0;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: var(--gold-main);
        }

        .testimonial-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffffff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: absolute;
            top: -40px;
            right: 30px;
        }

        .quote-icon {
            font-size: 3rem;
            color: var(--primary-light);
            position: absolute;
            top: 20px;
            left: 30px;
        }

        .stars {
            color: #ffc107;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        /* Contact Section */
        .contact-section {
            padding: 100px 0;
            background: #ffffff;
        }

        .contact-info-box {
            padding: 30px;
            border-radius: 20px;
            background: var(--primary-light);
            height: 100%;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: #ffffff;
            color: var(--primary-dark);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-left: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .contact-form {
            background: #ffffff;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f0f0;
        }

        .form-control {
            padding: 15px 20px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            background-color: #fbfbfb;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            background-color: #ffffff;
            border-color: var(--primary-main);
            box-shadow: 0 0 0 4px rgba(45, 138, 116, 0.1);
        }

        .btn-submit {
            background: var(--primary-dark);
            color: #ffffff;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 700;
            border: none;
            transition: 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            background: var(--gold-main);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(212, 167, 83, 0.3);
        }

        /* =========================================
           5. PROFESSIONAL MEGA FOOTER
           ========================================= */
        footer {
            background-color: #0f2922;
            /* Darker than primary-dark */
            color: #ecf0f1;
            padding: 80px 0 30px;
            font-size: 0.95rem;
        }

        .footer-logo {
            height: 70px;
            margin-bottom: 25px;
        }

        .footer-desc {
            color: #bdc3c7;
            margin-bottom: 30px;
            line-height: 1.8;
            max-width: 90%;
        }

        .footer-title {
            color: var(--gold-main);
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 1.1rem;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40px;
            height: 3px;
            background: var(--gold-main);
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            text-decoration: none;
            color: #d1d5db;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }

        .footer-links a:hover {
            color: var(--gold-main);
            transform: translateX(-5px);
        }

        .footer-links a i {
            font-size: 12px;
            margin-left: 8px;
            color: var(--gold-main);
        }

        .social-icons a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            margin-left: 10px;
            transition: 0.3s;
            font-size: 1.1rem;
            text-decoration: none;
        }

        .social-icons a:hover {
            background: var(--gold-main);
            transform: translateY(-3px);
        }

        .newsletter-form {
            position: relative;
            margin-top: 20px;
        }

        .newsletter-form input {
            width: 100%;
            padding: 12px 20px;
            padding-left: 50px;
            border-radius: 50px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .newsletter-form input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
        }

        .newsletter-form button {
            position: absolute;
            top: 5px;
            left: 5px;
            height: 38px;
            width: 38px;
            border-radius: 50%;
            border: none;
            background: var(--gold-main);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: 0.3s;
        }

        .newsletter-form button:hover {
            background: white;
            color: var(--gold-main);
        }

        .footer-bottom {
            margin-top: 60px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Responsive Fixes */
        @media (max-width: 991px) {
            .navbar {
                padding: 8px 0;
            }

            .navbar-collapse {
                background: white;
                padding: 25px 20px;
                border-radius: 20px;
                margin-top: 15px;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
                text-align: center;
            }

            .nav-link {
                padding: 12px;
                border-bottom: 1px solid #f5f5f5;
                font-size: 1.1rem;
            }

            .nav-link::before {
                display: none;
            }

            .nav-link-teacher {
                display: block;
                width: 100%;
                margin: 20px 0 10px 0;
                padding: 10px !important;
                background: #fff8e1;
                color: var(--gold-main) !important;
                border: 1px solid var(--gold-main);
            }

            .btn-nav-cta {
                width: 100%;
                padding: 12px;
                margin-top: 10px;
            }

            .hero-section {
                padding-top: 100px;
                padding-bottom: 50px;
                text-align: center;
            }

            .hero-title {
                font-size: 2.2rem;
                margin-bottom: 15px;
            }

            .hero-subtitle {
                font-size: 1rem;
                margin: 0 auto 25px auto;
                padding: 0 10px;
            }

            .hero-btns-group {
                justify-content: center !important;
                gap: 10px !important;
            }

            .store-btn {
                flex: 1 1 auto;
                min-width: 130px;
                max-width: 180px;
                padding: 8px 12px;
            }

            .store-btn-icon {
                font-size: 20px;
                margin-left: 6px;
            }

            .store-btn-big {
                font-size: 12px;
            }

            .teacher-separator {
                justify-content: center;
                font-size: 0.85rem;
                margin: 30px 0 15px;
            }

            .teacher-separator::after {
                display: none;
            }

            .hero-image-wrapper {
                margin-top: 40px;
            }

            .hero-phone {
                max-width: 260px;
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
                animation: none !important;
                transform: none !important;
            }

            .hero-bg-anim .floating-shape {
                display: none;
            }

            .pkg-card.featured {
                transform: none;
                margin: 15px 0;
                z-index: 1;
            }

            .badge-popular {
                top: -15px;
                left: 50%;
                transform: translateX(-50%) rotate(0deg);
                padding: 5px 20px;
                border-radius: 20px;
                width: auto;
            }

            /* Stats on Mobile */
            .stats-section {
                padding: 50px 0;
            }

            .stat-item {
                margin-bottom: 30px;
            }

            .stat-number {
                font-size: 2.5rem;
            }
        }

        /* --- Enhanced Tracks Section --- */
        .track-card-pro {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .track-card-pro:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: var(--gold-main);
        }

        .track-header {
            padding: 30px 25px 20px;
            text-align: center;
            background: linear-gradient(180deg, #fbfbfb 0%, #fff 100%);
            border-bottom: 1px dashed #eee;
        }

        .track-icon-lg {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-light);
            color: var(--primary-main);
            border-radius: 50%;
            font-size: 2rem;
            transition: 0.4s;
        }

        .track-card-pro:hover .track-icon-lg {
            background: var(--primary-main);
            color: #fff;
            transform: rotateY(180deg);
        }

        .track-body {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .track-features {
            list-style: none;
            padding: 0;
            margin: 15px 0 25px;
            flex-grow: 1;
            /* Pushes button down */
        }

        .track-features li {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
        }

        .track-features li i {
            color: var(--gold-main);
            margin-left: 10px;
            margin-top: 5px;
            font-size: 0.8rem;
        }

        .btn-track-outline {
            width: 100%;
            padding: 12px;
            border-radius: 50px;
            border: 2px solid var(--primary-light);
            color: var(--primary-dark);
            font-weight: 700;
            background: transparent;
            transition: 0.3s;
        }

        .btn-track-outline:hover {
            border-color: var(--primary-main);
            background: var(--primary-main);
            color: #fff;
        }
    </style>
</head>

<body>

    <div id="loader-screen">
        <div class="loader-container">
            <h1 class="loader-brand" data-text="ورتل">ورتل</h1>
            <div class="loader-sub">رتل القرآن ترتيلاً</div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><img width="60px" height="60px"
                    src="{{ asset('images/mainlogo.png') }}" alt="ورتل"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="#">الرئيسية</a></li>
                    <li class="nav-item"><a class="nav-link" href="#stats">الإحصائيات</a></li>
                    <li class="nav-item"><a class="nav-link" href="#why-us">لماذا ورتل</a></li>
                    <li class="nav-item"><a class="nav-link" href="#packages">الباقات</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">تواصل معنا</a></li>
                </ul>
                <div class="d-flex flex-column flex-lg-row align-items-center">
                    <a href="{{ route('teacher.index') }}" class="nav-link-teacher"><i
                            class="fa-solid fa-chalkboard-user ms-1"></i> انضم كمعلم</a>
                    <button class="btn-nav-cta">حمل التطبيق <i class="fa-solid fa-arrow-down-long ms-2"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-section d-flex align-items-center">
        <div class="hero-bg-anim">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
        </div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div data-aos="fade-up" data-aos-delay="0">
                        <div class="d-inline-block px-3 py-2 rounded-pill bg-white shadow-sm mb-3 border border-1"><span
                                class="fw-bold text-success small"><i class="fa-solid fa-star me-2 text-warning"></i>
                                التطبيق الإسلامي الأول</span></div>
                    </div>
                    <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">احفظ القرآن الكريم<br>بإتقان وسند
                        متصل</h1>
                    <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="400">ابدأ رحلتك مع كتاب الله الآن برفقة
                        نخبة من المعلمون والمعلمات المجازون بالسند المتصل</p>
                    <div class="d-flex hero-btns-group mb-2" data-aos="fade-up" data-aos-delay="600">
                        <a href="#" class="store-btn store-btn-dark"><i class="fab fa-apple store-btn-icon"></i>
                            <div class="store-btn-text"><span class="store-btn-small">Download on the</span><span
                                    class="store-btn-big">App Store</span></div>
                        </a>
                        <a href="#" class="store-btn store-btn-light"><i
                                class="fab fa-google-play store-btn-icon text-success"></i>
                            <div class="store-btn-text"><span class="store-btn-small">GET IT ON</span><span
                                    class="store-btn-big">Google Play</span></div>
                        </a>
                    </div>
                    <div class="teacher-separator" data-aos="fade-in" data-aos-delay="800"><i
                            class="fa-solid fa-user-tie ms-2"></i> نسخة المعلمين</div>
                    <div class="d-flex hero-btns-group" data-aos="fade-up" data-aos-delay="900">
                        <a href="#" class="store-btn store-btn-dark"
                            style="background: var(--primary-dark); border: 1px solid transparent;"><i
                                class="fab fa-apple store-btn-icon"></i>
                            <div class="store-btn-text"><span class="store-btn-small">تطبيق المعلم</span><span
                                    class="store-btn-big">App Store</span></div>
                        </a>
                        <a href="#" class="store-btn store-btn-light"
                            style="border-color: var(--primary-dark); color: var(--primary-dark);"><i
                                class="fab fa-google-play store-btn-icon"></i>
                            <div class="store-btn-text"><span class="store-btn-small">تطبيق المعلم</span><span
                                    class="store-btn-big">Google Play</span></div>
                        </a>
                    </div>
                    <div class="mt-4" data-aos="fade-in" data-aos-delay="1000"><a
                            href="{{ route('teacher.index') }}"
                            class="text-decoration-none fw-bold small text-muted">هل ترغب في الانضمام لفريقنا؟ <span
                                style="color: var(--gold-main); text-decoration: underline;">قدم طلبك الآن <i
                                    class="fa-solid fa-arrow-left"></i></span></a></div>
                </div>
                <div class="col-lg-6 text-center" data-aos="zoom-in" data-aos-duration="1200" data-aos-delay="300">
                    <div class="hero-image-wrapper"><img src="{{ asset('images/msa.jpeg') }}" alt="App UI"
                            class="hero-phone"></div>
                </div>
            </div>
        </div>
    </section>

    <section id="stats" class="stats-section">
        <div class="stats-bg-pattern"></div>
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-item">
                        <div class="stat-number" data-target="50000">0</div>
                        <div class="stat-label">طالب نشط</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-item">
                        <div class="stat-number" data-target="1200">0</div>
                        <div class="stat-label">معلم مجاز</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-item">
                        <div class="stat-number" data-target="500000">0</div>
                        <div class="stat-label">دقيقة تلاوة</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-item">
                        <div class="stat-number" data-target="4.9">0</div>
                        <div class="stat-label">تقييم التطبيق</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="why-us" class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="section-tag">لماذا نحن؟</span>
                <h2 class="section-title">تجربة تعليمية فريدة</h2>
                <p class="text-muted">نجمع بين الأصالة والتقنية الحديثة لتحقيق أهدافك</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="why-box">
                        <div class="why-icon"><i class="fa-solid fa-clock"></i></div>
                        <h4 class="fw-bold mb-3 text-dark">على مدار الساعة</h4>
                        <p class="text-muted">جلسات مباشرة بالصوت أو الفيديو متاحة لك في أي وقت، 24 ساعة يومياً.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="why-box">
                        <div class="why-icon"><i class="fa-solid fa-users-rays"></i></div>
                        <h4 class="fw-bold mb-3 text-dark">مختلف الأعمار</h4>
                        <p class="text-muted">مهما كان عمرك أو مستواك، ستجد معلماً متخصصاً ومنهجاً يناسبك.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="why-box">
                        <div class="why-icon"><i class="fa-solid fa-calendar-check"></i></div>
                        <h4 class="fw-bold mb-3 text-dark">خطط مرنة</h4>
                        <p class="text-muted">اختر خطتك ومسارك التعليمي المفضل بلا قيود وبحسب وقت فراغك.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="tracks" class="section-padding" style="background-color: #fcfcfc;">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-tag">مسارات التعلم</span>
                <h2 class="section-title">برامج تعليمية شاملة ومخصصة</h2>
                <p class="section-desc">صممنا مسارات تعليمية متنوعة لتناسب جميع المستويات والأعمار، سواء كنت مبتدئاً أو
                    متقناً، ستجد المسار الذي يحقق هدفك.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="track-card-pro">
                        <div class="track-header">
                            <div class="track-icon-lg"><i class="fa-solid fa-book-open"></i></div>
                            <h4 class="fw-bold mb-2">تحفيظ القرآن</h4>
                            <span class="badge bg-light text-dark border fw-normal px-3 py-2 rounded-pill">لجميع
                                المستويات</span>
                        </div>
                        <div class="track-body">
                            <p class="text-muted small text-center mb-0">
                                خطة منهجية لحفظ كتاب الله كاملاً أو أجزاء منه مع المراجعة والتثبيت.
                            </p>
                            <ul class="track-features">
                                <li><i class="fa-solid fa-circle-check"></i> خطة حفظ فردية مخصصة</li>
                                <li><i class="fa-solid fa-circle-check"></i> تسميع يومي مع المعلم</li>
                                <li><i class="fa-solid fa-circle-check"></i> اختبارات مرحلية للتثبيت</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="track-card-pro">
                        <div class="track-header">
                            <div class="track-icon-lg"><i class="fa-solid fa-microphone-lines"></i></div>
                            <h4 class="fw-bold mb-2">إتقان التلاوة</h4>
                            <span class="badge bg-light text-dark border fw-normal px-3 py-2 rounded-pill">نظري
                                وعملي</span>
                        </div>
                        <div class="track-body">
                            <p class="text-muted small text-center mb-0">
                                تصحيح المخارج والصفات ودراسة أحكام التجويد بشكل نظري وتطبيقي.
                            </p>
                            <ul class="track-features">
                                <li><i class="fa-solid fa-circle-check"></i> شرح مبسط لأحكام التجويد</li>
                                <li><i class="fa-solid fa-circle-check"></i> تدريب عملي على المخارج</li>
                                <li><i class="fa-solid fa-circle-check"></i> تصحيح الأخطاء الشائعة</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="track-card-pro">
                        <div class="track-header">
                            <div class="track-icon-lg" style="color: var(--gold-main); background: var(--gold-fade);">
                                <i class="fa-solid fa-certificate"></i>
                            </div>
                            <h4 class="fw-bold mb-2">الإجازة والسند</h4>
                            <span
                                class="badge bg-warning text-dark border border-warning fw-normal px-3 py-2 rounded-pill">للمتقنين</span>
                        </div>
                        <div class="track-body">
                            <p class="text-muted small text-center mb-0">
                                للحفاظ المتقنين، قراءة ختمة كاملة للحصول على السند المتصل للنبي ﷺ.
                            </p>
                            <ul class="track-features">
                                <li><i class="fa-solid fa-circle-check"></i> شيوخ مجازون بالقراءات</li>
                                <li><i class="fa-solid fa-circle-check"></i> إجازة مكتوبة وموثقة</li>
                                <li><i class="fa-solid fa-circle-check"></i> سجل بالأسانيد المتصلة</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="track-card-pro">
                        <div class="track-header">
                            <div class="track-icon-lg"><i class="fa-solid fa-child-reaching"></i></div>
                            <h4 class="fw-bold mb-2">براعم القرآن</h4>
                            <span class="badge bg-light text-dark border fw-normal px-3 py-2 rounded-pill">للأطفال
                                (5-12)</span>
                        </div>
                        <div class="track-body">
                            <p class="text-muted small text-center mb-0">
                                تأسيس الأطفال في القراءة العربية (نور البيان) وقصار السور بأسلوب ممتع.
                            </p>
                            <ul class="track-features">
                                <li><i class="fa-solid fa-circle-check"></i> منهج نور البيان التأسيسي</li>
                                <li><i class="fa-solid fa-circle-check"></i> تحفيظ قصار السور</li>
                                <li><i class="fa-solid fa-circle-check"></i> ألعاب تعليمية وتفاعلية</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="about" class="py-5" style="background: var(--primary-light);">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-left">
                    <span class="section-tag">من نحن</span>
                    <h2 class="section-title mb-4" style="text-align: right;">عن تطبيق ورتل</h2>
                    <p class="text-muted lead" style="line-height: 1.8;">
                        تطبيق "ورتل" هو منصة تفاعلية رائدة لتعليم القرآن الكريم. نجمع بين أحدث التقنيات وأفضل الكفاءات
                        التعليمية لتمكين المسلمين حول العالم من تلاوة وحفظ كتاب الله.
                    </p>
                    <p class="text-muted">يتيح لك التطبيق القراءة على مقرئين مجازين، وتكرار الآيات، واستخدام أدوات ذكية
                        للمراجعة وتتبع التقدم.</p>
                </div>
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="about-card">
                                <i class="fa-solid fa-bullseye about-icon-small"></i>
                                <h5 class="fw-bold text-success">هدفنا</h5>
                                <p class="text-muted m-0 small">تمكين أي شخص، في أي عمر ومكان، من تعلم القرآن بسهولة
                                    وإتقان مع أفضل المعلمين.</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="about-card">
                                <i class="fa-solid fa-envelope-open-text about-icon-small"></i>
                                <h5 class="fw-bold text-success">رسالتنا</h5>
                                <p class="text-muted m-0 small">إيصال القرآن إلى قلوب المسلمين عبر تعليم متقن يربط
                                    الأجيال بكلام الله حفظاً وتدبراً.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="packages" class="pricing-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-tag">الباقات</span>
                <h2 class="section-title">اختر خطتك التعليمية</h2>
            </div>
            <div class="row g-4 justify-content-center align-items-center">

                @foreach ($packages as $package)
                    @php
                        $hasDiscount = $package->discount > 0;
                        $isVip = str_contains($package->name, 'VIP');

                        $originalPrice = $package->price;
                        $finalPrice = $hasDiscount
                            ? $originalPrice - $originalPrice * ($package->discount / 100)
                            : $originalPrice;
                    @endphp

                    <div class="col-lg-4 col-md-6" data-aos="{{ $hasDiscount ? 'zoom-in' : 'fade-up' }}">
                        <div class="pkg-card {{ $hasDiscount ? 'featured' : '' }}">
                            @if ($hasDiscount)
                                <div class="badge-popular">الأكثر طلباً</div>
                            @endif

                            <div>
                                <h4 class="pkg-name {{ $isVip ? 'text-success' : '' }}">{{ $package->name }}</h4>
                                <div class="pkg-gift {{ $hasDiscount ? '' : 'bg-light' }}">
                                    @if ($isVip)
                                        <i class="fa-solid fa-crown text-warning"></i>
                                    @else
                                        <i
                                            class="fa-solid fa-gift {{ $hasDiscount ? 'text-danger fa-lg' : 'text-warning' }}"></i>
                                    @endif
                                    <span class="{{ $hasDiscount ? 'fs-6 ms-2' : '' }}">
                                        {{ $package->base_minutes }} دقيقة
                                        @if ($package->bonus_minutes > 0)
                                            + {{ $package->bonus_minutes }} د
                                        @endif
                                    </span>
                                </div>
                                @if ($hasDiscount)
                                    <p class="small text-muted mb-4 fw-bold">
                                        {{ $package->description ?? 'باقة مثالية للبدء في الحفظ والمراجعة' }}</p>
                                @else
                                    <ul class="list-unstyled text-muted text-start mx-auto small"
                                        style="max-width: 200px;">
                                        <li class="mb-3"><i class="fa-solid fa-check text-success me-2"></i> صلاحية
                                            {{ $package->validity_days }} يوم</li>
                                        @if ($package->description)
                                            <li class="mb-3"><i class="fa-solid fa-check text-success me-2"></i>
                                                {{ Str::limit($package->description, 25) }}</li>
                                        @endif
                                        @if ($isVip)
                                            <li class="mb-3"><i class="fa-solid fa-check text-success me-2"></i>
                                                معلم خاص</li>
                                        @endif
                                    </ul>
                                @endif
                            </div>

                            @if ($hasDiscount)
                                <button class="btn-3d orange">
                                    <span>{{ number_format($finalPrice, 2) }}</span> <small>EGP</small>
                                    <span
                                        class="text-decoration-line-through text-white-50 ms-2 small">{{ number_format($originalPrice, 0) }}</span>
                                    <svg class="cursor-icon" viewBox="0 0 24 24" fill="white">
                                        <path d="M5.5 2L18.5 13.5L12 14.5L16 22L13 23.5L9 16L3 18V2Z" stroke="black"
                                            stroke-width="1.5" />
                                    </svg>
                                </button>
                            @elseif($isVip)
                                <button class="btn-3d green">{{ number_format($finalPrice, 0) }} <span
                                        class="small ms-1">EGP</span></button>
                            @else
                                <button class="btn-3d outline">{{ number_format($finalPrice, 0) }} <span
                                        class="small ms-1">EGP</span></button>
                            @endif
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>

    <section id="teachers" class="py-5">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div><span class="section-tag">الكادر التعليمي</span>
                    <h2 class="section-title m-0">نخبة من العلماء</h2>
                </div>
            </div>
            <div class="owl-carousel teachers-carousel owl-theme">
                <div class="teacher-slide">
                    <div class="teacher-card">
                        <div class="teacher-img"><img src="{{ asset('images/a1.jpg.webp') }}" alt="Sheikh"></div>
                        <h5 class="fw-bold mb-1">الشيخ أحمد</h5>
                        <p class="text-muted small">القراءات العشر</p>
                        <div class="text-warning small"><i class="fa-solid fa-star"></i> 4.9</div>
                    </div>
                </div>
                <div class="teacher-slide">
                    <div class="teacher-card">
                        <div class="teacher-img"><img src="{{ asset('images/a2.webp') }}" alt="Sheikh"></div>
                        <h5 class="fw-bold mb-1">الشيخ علي </h5>
                        <p class="text-muted small">أطفال ونساء</p>
                        <div class="text-warning small"><i class="fa-solid fa-star"></i> 5.0</div>
                    </div>
                </div>
                <div class="teacher-slide">
                    <div class="teacher-card">
                        <div class="teacher-img"><img src="{{ asset('images/a3.webp') }}" alt="Sheikh"></div>
                        <h5 class="fw-bold mb-1">الشيخ محمد</h5>
                        <p class="text-muted small">إجازة حفص</p>
                        <div class="text-warning small"><i class="fa-solid fa-star"></i> 4.8</div>
                    </div>
                </div>
                <div class="teacher-slide">
                    <div class="teacher-card">
                        <div class="teacher-img"><img src="{{ asset('images/a4.jpg') }}" alt="Sheikh"></div>
                        <h5 class="fw-bold mb-1">الشيخ يوسف</h5>
                        <p class="text-muted small">المقامات</p>
                        <div class="text-warning small"><i class="fa-solid fa-star"></i> 4.9</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-tag">قصص نجاح</span>
                <h2 class="section-title">ماذا يقول طلابنا؟</h2>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <img src="https://ui-avatars.com/api/?name=Ahmed+Ali&background=d4a753&color=fff"
                            class="testimonial-avatar" alt="User">
                        <i class="fa-solid fa-quote-left quote-icon"></i>
                        <div class="mt-4">
                            <h5 class="fw-bold text-dark mb-1">أحمد علي</h5>
                            <small class="text-muted d-block mb-3">ختم القرآن في سنة</small>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <p class="text-muted small m-0">
                                "تجربة رائعة بكل المقاييس. سهولة في التواصل مع المشايخ، ومرونة في المواعيد ساعدتني
                                كثيراً في الاستمرار بالحفظ رغم انشغالي بالعمل."
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card">
                        <img src="https://ui-avatars.com/api/?name=Sara+Mohamed&background=2d8a74&color=fff"
                            class="testimonial-avatar" alt="User">
                        <i class="fa-solid fa-quote-left quote-icon"></i>
                        <div class="mt-4">
                            <h5 class="fw-bold text-dark mb-1">سارة محمد</h5>
                            <small class="text-muted d-block mb-3">أم لطالبين</small>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <p class="text-muted small m-0">
                                "تطبيق ممتاز للأطفال! واجهة محببة ومعلمون متخصصون في التعامل التربوي. لاحظت تحسناً
                                كبيراً في مخارج الحروف لدى أبنائي."
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card">
                        <img src="https://ui-avatars.com/api/?name=Omar+Khaled&background=4fb299&color=fff"
                            class="testimonial-avatar" alt="User">
                        <i class="fa-solid fa-quote-left quote-icon"></i>
                        <div class="mt-4">
                            <h5 class="fw-bold text-dark mb-1">عمر خالد</h5>
                            <small class="text-muted d-block mb-3">مغترب</small>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star-half-stroke"></i>
                            </div>
                            <p class="text-muted small m-0">
                                "كنت أبحث عن شيخ متقن للقراءات وأنا في الخارج، ووجدته في ورتل. جودة الصوت والصورة ممتازة
                                وكأنني في حلقة حقيقية."
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5" data-aos="fade-left">
                    <span class="section-tag">تواصل معنا</span>
                    <h2 class="section-title mb-4">نحن هنا لمساعدتك</h2>
                    <p class="text-muted mb-5">فريق دعم "ورتل" متاح على مدار الساعة للإجابة على استفساراتكم ومقترحاتكم.
                        لا تتردد في مراسلتنا.</p>

                    <div class="contact-info-box">
<div class="contact-item">
        <div class="contact-icon">
            <i class="fa-solid fa-envelope"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-1 text-dark">البريد الإلكتروني</h6>
            {{-- Dynamic Email Link --}}
            <a href="mailto:{{ $contact->email ?? 'info@wartel.app' }}" class="text-muted text-decoration-none">
                {{ $contact->email ?? 'info@wartel.app' }}
            </a>
        </div>
    </div>

    <div class="contact-item mb-0">
        <div class="contact-icon">
            <i class="fa-solid fa-phone-volume"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-1 text-dark">خدمة العملاء</h6>

            {{--
                Dynamic WhatsApp Link
                We use str_replace to remove '+' or spaces for the URL,
                but keep the original format for display.
            --}}
            @php
                $phoneDisplay = $contact->phone ?? '+201110562097';
                // Clean phone for URL (remove non-numeric chars)
                $phoneUrl = preg_replace('/[^0-9]/', '', $phoneDisplay);
            @endphp

            <a
                href="https://wa.me/{{ $phoneUrl }}?text=مرحبًا%20فريق%20ورتل%2C%20لدي%20استفسار%20حول%20التطبيق"
                target="_blank"
                class="text-muted text-decoration-none"
                dir="ltr"
            >
                +{{ $phoneDisplay }}
            </a>

        </div>
    </div>
</div>

                </div>

                <div class="col-lg-7" data-aos="fade-right">
                    <div class="contact-form">
                        <h4 class="fw-bold mb-4 text-dark">أرسل لنا رسالة</h4>
<form id="contactForm">
    @csrf {{-- Still needed for AJAX headers --}}

    {{-- Success/Error Message Container --}}
    <div id="formMessage" class="alert d-none mb-3"></div>

    <div class="row">
        <div class="col-md-6">
            <input type="text" name="name" class="form-control" placeholder="الاسم الكامل" required>
        </div>
        <div class="col-md-6">
            <input type="email" name="email" class="form-control" placeholder="البريد الإلكتروني" required>
        </div>
        <div class="col-12">
            <input type="text" name="subject" class="form-control" placeholder="موضوع الرسالة" required>
        </div>
        <div class="col-12">
            <textarea name="message" class="form-control" rows="5" placeholder="اكتب رسالتك هنا..." required></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn-submit" id="submitBtn">
                <span class="btn-text">إرسال الرسالة</span>
                <i class="fa-solid fa-paper-plane ms-2"></i>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer>
        <div class="container">
            <div class="row gy-5">
                <div class="col-lg-4 col-md-6">
                    <img src="{{ asset('images/mainlogo.png') }}" alt="Logo" class="footer-logo">
                    <p class="footer-desc">ورتل.. رفيقك في رحلة تعلم القرآن الكريم. نسعى لربط المسلمين بكتاب الله عبر
                        تقنيات حديثة وكوادر تعليمية مؤهلة.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fa-brands fa-x"></i></a>
                        <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">خريطة الموقع</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fa-solid fa-chevron-left text-xs"></i> الرئيسية</a></li>
                        <li><a href="#about"><i class="fa-solid fa-chevron-left text-xs"></i> عن التطبيق</a></li>
                        <li><a href="#packages"><i class="fa-solid fa-chevron-left text-xs"></i> الأسعار</a></li>
                        <li><a href="{{ route('teacher.index') }}"><i class="fa-solid fa-chevron-left text-xs"></i>
                                انضم كمعلم</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    {{-- <h5 class="footer-title">المساعدة</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fa-solid fa-chevron-left text-xs"></i> الأسئلة الشائعة</a>
                        </li>
                        <li><a href="#"><i class="fa-solid fa-chevron-left text-xs"></i> سياسة الخصوصية</a></li>
                        <li><a href="#"><i class="fa-solid fa-chevron-left text-xs"></i> الشروط والأحكام</a>
                        </li>
                        <li><a href="#contact"><i class="fa-solid fa-chevron-left text-xs"></i> اتصل بنا</a></li>
                    </ul> --}}
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">اشترك في النشرة</h5>
                    <p class="text-white-50 small mb-3">احصل على أحدث المقالات والنصائح القرآنية.</p>
                    <form class="position-relative mb-4">
                        <input type="email"
                            class="form-control bg-dark border-secondary text-white rounded-pill ps-4"
                            placeholder="البريد الإلكتروني">
                        <button class="btn position-absolute top-0 start-0 h-100 text-warning pe-3"><i
                                class="fa-solid fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>

            <div class="copyright">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-end">
                        &copy; {{ date('Y') }} جميع الحقوق محفوظة لـ <strong>ورتل</strong>.
                    </div>
                    <div class="col-md-6 text-center text-md-start mt-2 mt-md-0">
                        تم التطوير بكل ❤️ لخدمة القرآن الكريم
                    </div>
                </div>
            </div>
        </div>
    </footer>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <script>
        // ADD NO SCROLL ON LOAD
        document.body.classList.add('loading-locked');

        $(document).ready(function() {
            // Loader Exit Logic
            setTimeout(() => {
                document.getElementById('loader-screen').classList.add('slide-up-exit');
                document.body.classList.remove('loading-locked');
            }, 3000);

            // Init Animations
            AOS.init({
                once: true,
                offset: 50,
                duration: 800
            });

            // Init Teachers Carousel
            $(".teachers-carousel").owlCarousel({
                rtl: true,
                loop: true,
                margin: 0,
                nav: false,
                dots: true,
                autoplay: true,
                autoplayTimeout: 3000,
                autoplayHoverPause: true,
                smartSpeed: 800,
                responsive: {
                    0: {
                        items: 1
                    },
                    600: {
                        items: 2
                    },
                    1000: {
                        items: 3
                    },
                    1200: {
                        items: 4
                    }
                }
            });

            // --- COUNTER ANIMATION LOGIC ---
            const counters = document.querySelectorAll('.stat-number');
            const speed = 200; // The lower the slower

            const animateCounter = (counter) => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(() => animateCounter(counter), 20); // updates every 20ms
                } else {
                    counter.innerText = target;
                    // Optional: Add K or M suffixes manually if needed, or handle in PHP
                    if (target > 1000) counter.innerText += '+';
                }
            };

            // Trigger animation when section is in view
            const observerOptions = {
                threshold: 0.5
            };
            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target;
                        animateCounter(counter);
                        observer.unobserve(counter); // Only run once
                    }
                });
            }, observerOptions);

            counters.forEach(counter => {
                observer.observe(counter);
            });
        });
    </script>
    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const btn = document.getElementById('submitBtn');
    const msgBox = document.getElementById('formMessage');
    const spinner = btn.querySelector('.spinner-border');

    // UI Loading State
    btn.disabled = true;
    if(spinner) spinner.classList.remove('d-none');
    msgBox.classList.add('d-none');

    const formData = new FormData(form);

    fetch("{{ route('contact.send') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json' // Forces Laravel to return JSON validation errors
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        if(spinner) spinner.classList.add('d-none');

        msgBox.classList.remove('d-none', 'alert-success', 'alert-danger');

        if (data.status === 'success') {
            // Success Logic
            msgBox.classList.add('alert-success');
            msgBox.innerText = data.message;
            form.reset();
        } else if (data.errors) {
            // Validation Error Logic
            msgBox.classList.add('alert-danger');
            // Show the first validation error found
            msgBox.innerText = Object.values(data.errors)[0][0];
        } else {
            // General Error Logic
            msgBox.classList.add('alert-danger');
            msgBox.innerText = data.message || 'حدث خطأ غير متوقع.';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        if(spinner) spinner.classList.add('d-none');
        msgBox.classList.remove('d-none', 'alert-success');
        msgBox.classList.add('alert-danger');
        msgBox.innerText = 'فشل الاتصال بالخادم.';
    });
});
    </script>
</body>

</html>

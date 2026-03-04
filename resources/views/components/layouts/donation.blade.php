<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="stripe-key" content="{{ config('services.stripe.key') }}">

    <link rel="icon" href="{{ asset('img/favicon-32x32.png') }}" sizes="32x32" />
    <link rel="icon" href="{{ asset('img/favicon-192x192.png') }}" sizes="192x192" />
    <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}" />
    <meta name="msapplication-TileImage" content="{{ asset('img/mstile-270x270.png') }}" />

    <title>{{ $title ?? 'IBE Foundation Donation' }}</title>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-3RV71BM8Y5"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-3RV71BM8Y5');
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places"></script>
    
    <style>
        body {
            font-weight: 500 !important;
            background-color: rgb(243, 244, 246) !important;
        }
        .logo {
            height: auto;
            max-height: 50px;
            transform: scale(1.1);
            transform-origin: top center;
        }
        .logo2 {
            max-height: 80px;
            width: auto;
        }
        .bg-ibeBlue {
            background-color: #183762 !important;
        }
        .roboto {
            font-family: 'Roboto', Helvetica, Arial, Lucida, sans-serif !important;
        }
        .heading {
            margin-top: 1.5rem;
        }
        .heading h1 {
            font-size: 1.5rem;
            font-weight: 500;
        }
        .heading h2 {
            font-size: 1.25rem;
            font-weight: 500;
        }
        /* Menu Styles from Legacy */
        .et_pb_menu .et-menu-nav > ul {
            padding: 0 !important;
            line-height: 1.7em !important;
            list-style: none !important;
        }
        .et_pb_menu .et_pb_menu__menu > nav > ul {
            display: flex !important;
            align-items: stretch !important;
            flex-wrap: wrap !important;
            justify-content: flex-start !important;
        }
        .et_pb_menu .et_pb_menu__menu > nav > ul > li {
            position: relative !important;
            display: flex !important;
            align-items: stretch !important;
            margin: 0 !important;
        }
        .et_pb_menu--with-logo .et_pb_menu__menu > nav > ul > li > a {
            display: flex !important;
            align-items: center !important;
            padding: 31px 0 !important;
            white-space: nowrap !important;
        }
        .et_pb_menu .et-menu {
            margin-left: -11px !important;
            margin-right: -11px !important;
        }
        .et_pb_menu .et-menu > li {
            padding-left: 11px !important;
            padding-right: 11px !important;
            display: inline-block !important;
            font-size: 14px !important;
        }
        .et-menu a {
            color: rgba(0, 0, 0, 0.6) !important;
            text-decoration: none !important;
            display: block !important;
            position: relative !important;
            font-weight: 400 !important;
            transition: all 0.4s ease-in-out !important;
        }
        .et-menu a:hover {
            opacity: 0.7 !important;
        }
        .et_pb_menu .et_pb_menu__wrap {
            flex: 1 1 auto !important;
            display: flex !important;
            justify-content: flex-start !important;
            align-items: stretch !important;
            flex-wrap: wrap !important;
        }
        .et_pb_menu .et_pb_menu__menu {
            flex: 0 1 auto !important;
            justify-content: flex-start !important;
            display: flex !important;
            align-items: stretch !important;
        }
        .et_pb_menu--style-left_aligned.et_pb_text_align_right .et_pb_menu__menu > nav > ul,
        .et_pb_menu--style-left_aligned.et_pb_text_align_right .et_pb_menu__wrap {
            justify-content: flex-end !important;
        }
        
        /* Specific styles for et_pb_menu_0_tb_header */
        .et_pb_menu_0_tb_header.et_pb_menu ul li a {
            font-weight: 700 !important;
            font-size: 20px !important;
            color: #fff !important;
        }
        .et_pb_menu_0_tb_header.et_pb_menu {
            background-color: transparent !important; /* #fff0 */
        }
        .et_pb_menu_0_tb_header {
            margin-bottom: -15px !important;
        }
        .et_pb_menu_0_tb_header.et_pb_menu ul li.current-menu-item a {
            color: #fff !important;
        }
        .et_pb_menu_0_tb_header .et_pb_menu__logo-wrap .et_pb_menu__logo img {
            width: auto !important;
        }
        .et_pb_menu_0_tb_header .et_pb_menu_inner_container > .et_pb_menu__logo-wrap,
        .et_pb_menu_0_tb_header .et_pb_menu__logo-slot {
            width: auto !important;
            max-width: 20% !important;
        }
        .et_pb_menu {
            margin-left: 30% !important;
        }

        /* Extracted Legacy Navbar CSS */
        .et-menu li { display: inline-block; font-size: 14px; padding-right: 22px; }
        .et-menu > li:last-child { padding-right: 0; }
        .et-menu a { color: rgba(0, 0, 0, 0.6); text-decoration: none; display: block; position: relative; transition: all 0.4s ease-in-out; }
        .et-menu a:hover { opacity: 0.7; }
        .et-menu li > a { padding-bottom: 29px; word-wrap: break-word; }
        .et_pb_menu__wrap { flex: 1 1 auto; display: flex; justify-content: flex-start; align-items: stretch; flex-wrap: wrap; opacity: 1; }
        .et_pb_menu__menu, .et_pb_menu__menu > nav, .et_pb_menu__menu > nav > ul { display: flex; align-items: stretch; }
        .et_pb_menu__menu > nav > ul { flex-wrap: wrap; justify-content: flex-start; }
        .et_pb_menu__menu > nav > ul > li { position: relative; display: flex; align-items: stretch; margin: 0; }
        .et_pb_menu--with-logo .et_pb_menu__menu > nav > ul > li > a { display: flex; align-items: center; padding: 31px 0; white-space: nowrap; }
        .et_pb_menu .et-menu { margin-left: -11px; margin-right: -11px; }
        .et_pb_menu .et-menu > li { padding-left: 11px; padding-right: 11px; }
        .et_pb_menu--style-left_aligned .et_pb_menu_inner_container, .et_pb_menu--style-left_aligned .et_pb_row { display: flex; align-items: stretch; }
        .et_pb_menu--style-left_aligned.et_pb_text_align_right .et_pb_menu__menu > nav > ul, .et_pb_menu--style-left_aligned.et_pb_text_align_right .et_pb_menu__wrap { justify-content: flex-end; }
        .et_pb_menu_0_tb_header.et_pb_menu ul li a { font-weight: 700; font-size: 20px; color: #fff !important; }
        .et_pb_menu_0_tb_header.et_pb_menu { background-color: transparent; }
        .et_pb_menu_0_tb_header { margin-bottom: -15px !important; }
        .et_pb_menu_0_tb_header.et_pb_menu ul li.current-menu-item a { color: #fff !important; }
        .et_pb_menu_0_tb_header .et_pb_menu__logo-wrap .et_pb_menu__logo img { width: auto; }
        .et_pb_menu_0_tb_header .et_pb_menu_inner_container > .et_pb_menu__logo-wrap, .et_pb_menu_0_tb_header .et_pb_menu__logo-slot { width: auto; max-width: 20%; }
        .et_pb_menu_0_tb_header .et_pb_menu_inner_container > .et_pb_menu__logo-wrap .et_pb_menu__logo img, .et_pb_menu_0_tb_header .et_pb_menu__logo-slot .et_pb_menu__logo-wrap img { height: auto; max-height: 100px; }
        .et_pb_menu { margin-left: 30%; }
        @media (max-width: 980px) {
            .et_pb_menu--style-left_aligned .et_pb_menu__wrap { justify-content: flex-end; }
            .et_pb_menu .et_pb_menu__menu { display: none; }
            .et_pb_menu .et_mobile_nav_menu { float: none; margin: 0 6px; display: flex; align-items: center; }
        }
    </style>
</head>
<body>
    <div class="grid">
        <!-- Header -->
        <x-header />

        <div class="d-flex flex-column text-center heading">
            <h1>IBE Foundation Donation</h1>
            <h2></h2>
        </div>

        <!-- Main Content -->
        {{ $slot }}

        <!-- Footer -->
        <x-footer />
    </div>

    @livewireScripts
    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
      var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
      (function(){
      var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
      s1.async=true;
      s1.src='https://embed.tawk.to/67ca5ff7374e52190e33d1b7/1iln8dnok';
      s1.charset='UTF-8';
      s1.setAttribute('crossorigin','*');
      s0.parentNode.insertBefore(s1,s0);
      })();
    </script>
    <!--End of Tawk.to Script-->
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .cloud-bg {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                position: relative;
                overflow: hidden;
            }
            
            .cloud {
                position: absolute;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 100px;
                animation: float 20s infinite ease-in-out;
            }
            
            .cloud::before,
            .cloud::after {
                content: '';
                position: absolute;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 100px;
            }
            
            .cloud-1 {
                width: 100px;
                height: 40px;
                top: 10%;
                left: 10%;
                animation-delay: 0s;
            }
            .cloud-1::before {
                width: 50px;
                height: 50px;
                top: -25px;
                left: 10px;
            }
            .cloud-1::after {
                width: 60px;
                height: 40px;
                top: -15px;
                right: 10px;
            }
            
            .cloud-2 {
                width: 120px;
                height: 45px;
                top: 25%;
                right: 15%;
                animation-delay: 3s;
            }
            .cloud-2::before {
                width: 60px;
                height: 60px;
                top: -30px;
                left: 15px;
            }
            .cloud-2::after {
                width: 70px;
                height: 45px;
                top: -20px;
                right: 15px;
            }
            
            .cloud-3 {
                width: 90px;
                height: 35px;
                top: 60%;
                left: 20%;
                animation-delay: 6s;
            }
            .cloud-3::before {
                width: 45px;
                height: 45px;
                top: -20px;
                left: 8px;
            }
            .cloud-3::after {
                width: 50px;
                height: 35px;
                top: -12px;
                right: 8px;
            }
            
            .cloud-4 {
                width: 110px;
                height: 42px;
                bottom: 15%;
                right: 10%;
                animation-delay: 9s;
            }
            .cloud-4::before {
                width: 55px;
                height: 55px;
                top: -27px;
                left: 12px;
            }
            .cloud-4::after {
                width: 65px;
                height: 42px;
                top: -18px;
                right: 12px;
            }
            
            @keyframes float {
                0%, 100% {
                    transform: translateY(0) translateX(0);
                }
                50% {
                    transform: translateY(-20px) translateX(10px);
                }
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 cloud-bg">
            <!-- Cloud shapes -->
            <div class="cloud cloud-1"></div>
            <div class="cloud cloud-2"></div>
            <div class="cloud cloud-3"></div>
            <div class="cloud cloud-4"></div>
            {{-- Logo hidden: comment out to remove Laravel logo from auth pages --
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>
            --}}

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

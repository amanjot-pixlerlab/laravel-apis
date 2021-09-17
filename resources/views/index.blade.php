<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        body {
            background: url('./images/background-image.jpg') center center;
            background-attachment: fixed;
        }    
        .login-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            text-align: center;
            transform: translate(-50%,-50%);
        }
        .login-wrapper a {
            background: #F7323F;
            border-radius: 5px;
            padding: 10px 30px;
            display: inline-block;
            color: #fff;
            margin: 10px 0 0;
            font-weight: 500;
            text-decoration: none;
            outline: none;
        }
        .header-outer { 
            padding: 10px 20px;
        }
    </style> 
</head>  
  
<body class="antialiased">
    <div class="header-outer">
        <figure>
            <img src="{{asset('images/collab-logo.svg')}}">
        </figure>
    </div>

    <div class="login-wrapper">
        <h3>Reporting Dashboard</h3>
        <a href="https://ads.tiktok.com/marketing_api/auth?app_id=6890637669785665537&redirect_uri=https://rankzoo.com/rankzoo-publisher/public/select-client">Login via TikTok</a>
    </div>

    <!-- <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0 red-background">
        @if (Route::has('login'))
        <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
            @auth
            <a href="{{ url('/dashboard') }}" class="text-sm text-white-700">Dashboard</a>
            @else
            <a href="{{ route('login') }}" class="text-sm text-white-700">Login</a>

             @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 underline">Register</a>
                        @endif 
            @endauth
        </div>
        @endif

        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="mt-8 overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-1 title-dahsboard text-center">
                    Collab Reporting<br>Dashboard
                </div>
            </div>


        </div>
    </div> -->
</body>

</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        @hasSection('title')
            @yield('title') | PalengKita
        @else
            PalengKita
        @endif
    </title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-dark">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ Auth::check() ? url('/home') : url('/') }}">
                    PalengKita
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            @if(Auth::user()->type === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('customers.index') }}">{{ __('Customers') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('transactions.index') }}">{{ __('Transactions') }}</a>
                                </li>
                            @endif

                            @if(Auth::user()->type === 'customer')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('customer.purchases.index') }}">{{ __('Make Purchase') }}</a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('signin'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('signin') }}">{{ __('Sign In') }}</a>
                                </li>
                            @endif

                            @if (Route::has('signup'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('signup') }}">{{ __('Sign Up') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <span class="nav-link">{{ Auth::user()->name }}</span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>

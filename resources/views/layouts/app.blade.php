<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EasyColoc')</title>

    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <!-- Tailwind CSS (for new components) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#BB060B'
                    }
                }
            }
        }
    </script>
    @yield('styles')
</head>

<body class="@yield('body-class')">

    @yield('content')

    <footer>
        <span class="footer-marquee">EasyColoc - Gestion simplifiée de votre colocation. Suivez vos dépenses, gérez vos
            dettes, et gardez une vision claire.</span>
    </footer>

    <!-- Pass data to JS -->
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            userId: {{ auth()->id() ?? 'null' }},
            userData: @json(auth()->user()),
            routes: {
                login: '{{ route('login.post') }}',
                register: '{{ route('register') }}',
                dashboard: '{{ route('dashboard') }}'
            }
        };

        // Generic AJAX Form Handler to bridge your existing HTML with Laravel JSON responses
        document.addEventListener('submit', async function (e) {
            const form = e.target;
            if (form.tagName === 'FORM' && !form.hasAttribute('data-no-ajax')) {
                const method = (form.getAttribute('method') || 'GET').toUpperCase();
                if (method !== 'POST') return;

                e.preventDefault();
                const formData = new FormData(form);
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': window.Laravel.csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    let data;
                    try {
                        data = await response.json();
                    } catch (parseError) {
                        console.error('Failed to parse JSON:', parseError);
                        alert('Une erreur inattendue est survenue au niveau du serveur.');
                        return;
                    }

                    if (!response.ok) {
                        if (response.status === 422) {
                            alert(data.message || 'Validation échouée');
                        } else if (response.status === 403) {
                            alert(data.message || 'Accès refusé (Peut-être avez-vous déjà une collocation ?)');
                        } else {
                            alert('Erreur: ' + (data.message || 'Une erreur est survenue.'));
                        }
                        return;
                    }

                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.message) {
                        alert(data.message);
                        if (data.success) window.location.reload();
                    }
                } catch (error) {
                    console.error('Submission failed:', error);
                    alert('Erreur de connexion au serveur.');
                }
            }
        });
    </script>

    <!-- Main JS -->
    <script src="{{ asset('js/main.js') }}"></script>

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-3"></div>

    <!-- Global Notification Modal -->
    <div class="notification_modal" id="notification_modal" style="display: none; z-index: 50;">
        <div class="notification">
            <p>Bienvenue sur EasyColoc!</p>
            <span>{{ now()->format('H:i d/m/Y') }}</span>
        </div>
    </div>

    <style>
        .toast-enter {
            animation: slideIn 0.3s ease-out forwards;
        }

        .toast-leave {
            animation: fadeOut 0.3s ease-in forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }
    </style>

    <script>
        window.showToast = function (message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');

            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };

            toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg flex items-center toast-enter min-w-[250px]`;
            toast.innerHTML = `
            <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'} mr-3"></i>
            <span>${message}</span>
        `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.replace('toast-enter', 'toast-leave');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        };

    // Auto-Display Laravel Session Flashes
    @if(session('success')) window.showToast("{{ session('success') }}", 'success'); @endif
        @if(session('error')) window.showToast("{{ session('error') }}", 'error'); @endif
        @if($errors->any()) window.showToast("{{ $errors->first() }}", 'error'); @endif

    // Override alert globally for our fetch catchers
    const originalAlert = window.alert;
        window.alert = function (msg) {
            window.showToast(msg, 'warning');
        };
    </script>
    @yield('scripts')
</body>

</html>
@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/Dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/colocation.css') }}">
@endsection

@section('body-class', 'body_apresConn')

@section('content')
    <header>
        <div class="main_icons">
            <div><a href="{{ route('dashboard') }}"><i class="fa-solid fa-house"></i></a></div>
            <div><a href="{{ route('members') }}" class="active"><i class="fa-solid fa-users"></i></a></div>
            <div><button style="background-color: transparent; border: none;" id="notification_btn"
                    class="notification_btn"><i class="fa-solid fa-bell"></i></button></div>
            <div><a href="{{ route('profile') }}"><i class="fa-solid fa-circle-user"></i></a></div>
            @if(auth()->check() && auth()->user()->is_admin)
                <div title="Admin Dashboard"><a href="{{ route('admin.dashboard') }}" class="active"><i
                            class="fa-solid fa-shield-halved text-white" style="color: white !important;"></i></a></div>
            @endif
        </div>
        <div class="darkMenu_icon">
            <i class="fa-solid fa-moon"></i>
        </div>
        <div style="margin-left: auto; padding-right: 20px;">
            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                @csrf
                <button type="submit" style="background:none; border:none; color:inherit; cursor:pointer;">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </header>


    <div class="min-h-screen bg-gray-100 p-8" style="padding-top: 120px;">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8"><i class="fa-solid fa-shield-halved mr-2"></i> Global Admin
                Dashboard</h1>

            <!-- Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-500 font-semibold uppercase">Total Users</p>
                    <h2 class="text-3xl font-bold text-gray-800">{{ $users->total() }}</h2>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <p class="text-sm text-gray-500 font-semibold uppercase">Colocations</p>
                    <h2 class="text-3xl font-bold text-gray-800">{{ $totalColocs }}</h2>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
                    <p class="text-sm text-gray-500 font-semibold uppercase">Total Volume Roulé</p>
                    <h2 class="text-3xl font-bold text-gray-800">{{ number_format($totalExpenses, 0) }} DH</h2>
                </div>
                <a href="{{ request('filter') === 'banned' ? route('admin.dashboard') : route('admin.dashboard', ['filter' => 'banned']) }}"
                    class="block bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500 hover:bg-red-50 transition cursor-pointer">
                    <p class="text-sm text-gray-500 font-semibold uppercase">Banned Users</p>
                    <h2 class="text-3xl font-bold text-gray-800">{{ $bannedCount }}</h2>
                </a>
            </div>

            <!-- Users Table Matrix -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">
                        User Management
                        @if(request('filter') === 'banned')
                            <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Filtré: Bannis</span>
                        @endif
                    </h3>
                    @if(request('filter') === 'banned')
                        <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:underline">Voir tous les
                            utilisateurs</a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 text-sm">
                            <tr>
                                <th class="px-6 py-3 font-medium">Nom & Email</th>
                                <th class="px-6 py-3 font-medium">Réputation</th>
                                <th class="px-6 py-3 font-medium">Colocation Active</th>
                                <th class="px-6 py-3 font-medium">Statut</th>
                                <th class="px-6 py-3 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-800">{{ $user->name }}</div>
                                        <div class="text-gray-500 text-xs">{{ $user->email }}</div>
                                    </td>
                                    <td
                                        class="px-6 py-4 font-semibold {{ $user->reputation_points < 0 ? 'text-red-500' : 'text-green-500' }}">
                                        {{ $user->reputation_points }} pts
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2 py-1 bg-gray-100 rounded text-xs text-gray-600">{{ $user->memberships_count > 0 ? 'Oui' : 'Non' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($user->is_admin)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Admin</span>
                                        @elseif($user->is_banned)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Banni</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Actif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(!$user->is_admin)
                                            @if($user->is_banned)
                                                <button onclick="toggleBan({{ $user->id }}, 'unban')"
                                                    class="text-green-600 hover:text-green-900 font-medium">Débannir</button>
                                            @else
                                                <button onclick="toggleBan({{ $user->id }}, 'ban')"
                                                    class="text-red-600 hover:text-red-900 font-medium">Bannir</button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleBan(userId, action) {
            if (!confirm(action === 'ban' ? 'Êtes-vous sûr de vouloir bannir cet utilisateur ?' : 'Réactiver ce compte ?')) return;

            fetch(`/api/admin/users/${userId}/${action}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (window.showToast) window.showToast('Action réussie', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        if (window.showToast) window.showToast(data.message, 'error');
                        else alert(data.message);
                    }
                });
        }
    </script>
@endsection
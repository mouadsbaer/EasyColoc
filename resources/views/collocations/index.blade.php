@extends('layouts.app')

@section('title', 'Accueil - EasyColoc')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/Dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/colocation.css') }}">
@endsection

@section('body-class', 'body_apresConn')

@section('content')
    <header>
        <div class="main_icons">
            <div><a href="{{ route('dashboard') }}" class="active"><i class="fa-solid fa-house"></i></a></div>
            <div><a href="{{ route('members') }}"><i class="fa-solid fa-users"></i></a></div>
            <div><button style="background-color: transparent; border: none;" id="notification_btn"
                    class="notification_btn"><i class="fa-solid fa-bell"></i></button></div>
            <div><a href="{{ route('profile') }}"><i class="fa-solid fa-circle-user"></i></a></div>
            @if(auth()->user()->is_admin)
                <div title="Admin Dashboard"><a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-user-shield"
                            style="color: white !important;"></i></a></div>
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
    <div class="logo">
        <div class="logo_imgs">
            <div><img src="{{ asset('imgs/logo_projet2.png') }}" alt=""></div>
            <div class="second_img"><img src="{{ asset('imgs/logo_projet.png') }}" alt="">
                <p>Easy<span>CoLoc</span></p>
            </div>
        </div>
        <div class="search">
            <input type="search" placeholder="Chercher Une Collocation">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
    </div>
    <main>
        <section class="bienvenue_partie">
            <div class="bienvenue_partie_img">
                <div class="bienvenue_partie_img_cont"><img src="{{ asset('imgs/profile.jpg') }}" alt=""></div>
            </div>
            <div class="bienvenue_partie_msg">
                <p>Bienvenue <span>{{ auth()->user()->name }}</span> ({{ auth()->user()->email }} - Admin:
                    {{ auth()->user()->is_admin ? 'YES' : 'NO' }}), Suivez vos dépenses communes, calculez automatiquement
                    les dettes et
                    gardez une vision claire de qui doit quoi à qui. Fini les calculs manuels et les tensions.
                </p>
            </div>
        </section>

        @if(session('success'))
            <div class="success-message"
                style="background-color: #d4edda; color: #155724; padding: 15px; margin: 20px 50px; border-radius: 10px; border: 1px solid #c3e6cb; text-align: center; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        <section class="section_colocations" id="colocations-list">
            <div class="section_titres">
                <h2>Mes Collocations</h2>
                <button id="ajouter_colocation">+ Ajouter</button>
            </div>

            @forelse($collocations as $collocation)
                <div class="colocation" data-id="{{ $collocation->id }}">
                    <h4>{{ $collocation->name }}</h4>
                    <p>Membres: {{ $collocation->users()->count() }}</p>
                    <p>Créé le: {{ $collocation->created_at->format('d/m/Y') }}</p>
                    <div class="btns_colocation">
                        <a href="#" class="ajouter_membre" data-collocation-id="{{ $collocation->id }}"><i
                                class="fa-solid fa-user-plus"></i></a>
                        <a class="details_cat" href="{{ route('collocation', $collocation->id) }}"><i
                                class="fa-solid fa-bars"></i></a>
                    </div>
                </div>
            @empty
                <p>Vous n'avez pas encore de collocation. Créez-en une !</p>
            @endforelse
        </section>

        <!-- Use existing modal structure from PageMain.html -->
        <div class="modal_ajout_colocation modal_ajout_membre" id="modal_ajout_membre">
            <form id="invite-member-form" action="{{ url('/api/invitations') }}" method="post">
                @csrf
                <input type="hidden" name="collocation_id" id="invite-collocation-id">
                <div class="titre_form">Inviter Un Ami</div>
                <div class="contenair_form_inputs">
                    <div class="champ1_modal ">
                        <input type="email" name="email" placeholder="Adresse Email de votre ami" required>
                    </div>
                    <div class="champ1_modal">
                        <textarea name="customMessage" placeholder="Petit message (Optionnel)"
                            style="width: 100%; border: 2px solid #BB060B; border-radius: 10px; padding: 10px;"></textarea>
                    </div>
                    <div class="btn_champ1_modal">
                        <input type="submit" value="Ajouter">
                    </div>
                </div>
                <button type="button" class="btn_fermer">X</button>
            </form>
        </div>
    </main>

    <div class="modal_ajout_colocation ajout_colocation" id="modal_ajout_colocation">
        <form id="create-collocation-form" action="{{ route('collocations.store') }}" method="post">
            @csrf
            <div class="titre_form">Nouvelle Collocation</div>
            <div class="champ1_modal">
                <input type="text" name="name" placeholder="Nom de la collocation" required>
            </div>
            <div class="champ1_modal">
                <textarea name="description" placeholder="Description (Optionnel)"
                    style="width: 100%; border: 2px solid #BB060B; border-radius: 10px; padding: 10px;"></textarea>
            </div>
            <div class="btn_champ1_modal">
                <input type="submit" value="CRÉER">
            </div>
            <button type="button" class="btn_fermer">X</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('modal_ajout_membre');
            const closeBtn = modal.querySelector('.btn_fermer');
            const collocationIdInput = document.getElementById('invite-collocation-id');

            // Open modal on click of any "ajouter_membre" link
            document.querySelectorAll('.ajouter_membre').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Store the collocation id in the hidden input
                    if (collocationIdInput) {
                        collocationIdInput.value = this.dataset.collocationId;
                    }
                    modal.style.display = 'flex';
                });
            });

            // Close modal on X button click
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    modal.style.display = 'none';
                });
            }

            // Close modal when clicking outside of it
            window.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
@endsection
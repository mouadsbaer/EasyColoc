@extends('layouts.app')

@section('title', 'Bienvenue - EasyColoc')

@section('content')
    <div class="download_app">Télécharger l'App</div>
    <div class="dessin_form"></div>
    <div class="contenair_top" id="contenair_top">
        <div>
            <div class="contenair_logo">
                <div class="logo">
                    <div class="logo_partie"><img src="{{ asset('imgs/logo_projet.png') }}" alt=""></div>
                </div>
            </div>
            <h1><span>Easy</span>Coloc</h1>
            <div class="titre_desc">
                <h3>Simplifiez la gestion financière de votre colocation</h3>
            </div>
            <div class="options_btns">
                <div class="btn_option" id="btn_register">
                    <i class="fa-solid fa-user-plus fa-flip"></i>
                    <p>Nouveaux Compte</p>
                </div>
                <div class="btn_option" id="btn_connexion">
                    <i class="fa-solid fa-user fa-flip"></i>
                    <p>Se connecter</p>
                </div>
            </div>
        </div>
    </div>
    <div class="contenair_middle" id="contenair_middle">
        <div class="logo">
            <div class="logo_partie1"><img src="{{ asset('imgs/logo_projet.png') }}" alt=""></div>
        </div>

        <div class="contenair_main">
            <div class="main_photo"><img src="{{ asset('imgs/register_img.png') }}" alt=""></div>
            <div class="main_form">
                @include('auth.register-form')
            </div>
        </div>
    </div>

    <div class="contenair_middle contenair_bottom" id="contenair_bottom">
        <div class="logo">
            <div class="logo_partie1"><img src="{{ asset('imgs/logo_projet.png') }}" alt=""></div>
        </div>

        <div class="contenair_main">
            <div class="main_photo" id="contenair_bottom_img"><img src="{{ asset('imgs/login_img.png') }}" alt=""></div>
            <div class="main_form">
                @include('auth.login-form')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Handle session messages
            @if(session('success'))
                alert('{{ session('success') }}');
            @endif
            @if(session('error'))
                alert('{{ session('error') }}');
            @endif
            @if(session('info'))
                alert('{{ session('info') }}');
            @endif

            // Auto open register if URL action is register
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'register') {
                document.getElementById('contenair_top').style.display = 'none';
                document.getElementById('contenair_middle').style.display = 'flex';
            }
        });
    </script>
@endsection
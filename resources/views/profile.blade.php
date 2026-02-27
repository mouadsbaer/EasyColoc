@extends('layouts.app')

@section('title', 'Profile - EasyColoc')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
    <div class="download_app">Télécharger l'App</div>
    <div class="dessin_form"></div>
    <div class="contenair_middle contenair_profile">
        <div class="logo1 logo_top_right">
            <div class="logo_partie1"><img src="{{ asset('imgs/logo_projet2.png') }}" alt=""></div>
            <div class="logo_partie2"><img src="{{ asset('imgs/logo_projet.png') }}" alt=""></div>
        </div>

        <div class="profile_stats_left">
            <div class="stat_item" id="linkedin_section">
                <i class="fa-brands fa-linkedin"></i>
                @if(auth()->user()->linkedin)
                    <a href="{{ auth()->user()->linkedin }}" target="_blank" style="color: inherit; text-decoration: none;">Voir
                        Profil</a>
                @else
                    <div id="add_linkedin_container" style="display: flex; gap: 5px; align-items: center;">
                        <input type="url" id="linkedin_input" placeholder="Lien LinkedIn"
                            style="padding: 2px 5px; font-size: 0.8rem; border: 1px solid #ccc; border-radius: 4px; width: 120px; outline: none;">
                        <button type="button" id="btn_save_linkedin"
                            style="background:#0a66c2; color:white; border:none; border-radius:4px; padding:2px 6px; cursor:pointer;"
                            title="Enregistrer"><i class="fa-solid fa-check"></i></button>
                    </div>
                @endif
                <span id="linkedin_feedback"
                    style="font-size: 0.75rem; color: #78080E; font-weight: bold; margin-left: 5px;"></span>
            </div>
            <div class="stat_item">
                <i class="fa-solid fa-award"></i>
                <div class="stars">
                    @php
                        $stars = floor(auth()->user()->reputation_points / 20);
                        $stars = max(0, min(5, $stars));
                    @endphp
                    @for($i = 0; $i < 5; $i++)
                        <i class="{{ $i < $stars ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                    @endfor
                </div>
            </div>
            <div class="stat_item">
                <i class="fa-solid fa-chart-line"></i>
                <span>Points: {{ auth()->user()->reputation_points }}</span>
            </div>
        </div>

        <div class="contenair_main contenair_main_profile contenair_main_paiement">
            <div class="main_photo profile_img" id="profile_img_container">
                <div>
                    <img src="{{ auth()->user()->image ? asset('storage/' . auth()->user()->image) : asset('imgs/profile.jpg') }}"
                        alt="" id="profile_display">
                    <div class="camera_overlay">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>
                <input type="file" id="profile_upload" accept="image/*" style="display: none;">
            </div>

            <div class="main_form ">
                <form action="{{ route('profile.update') }}" method="post" id="profile-update-form">
                    @csrf
                    @method('PATCH')
                    <div class="contenair_top_profile">
                        <div class="titre_form">
                            <p>PROFILE</p>
                        </div>
                        <div>
                            <button type="button" id="btn_edit_profile"><i class="fa-solid fa-pen-to-square"></i></button>
                        </div>
                    </div>

                    <div class="champs">
                        <div class="champ1_register">
                            <input type="text" name="name" value="{{ auth()->user()->name }}" placeholder="UserName"
                                readonly>
                        </div>
                        <div class="champ1_register">
                            <input type="text" name="firstName" value="{{ auth()->user()->firstName }}" placeholder="Prénom"
                                readonly>
                        </div>
                        <div class="champ1_register">
                            <input type="text" name="lastName" value="{{ auth()->user()->lastName }}" placeholder="Nom"
                                readonly>
                        </div>
                        <div class="champ1_register">
                            <input type="text" name="about" value="{{ auth()->user()->about }}" placeholder="About"
                                readonly>
                        </div>
                        <div class="champ1_register">
                            <input type="date" name="datOfBirth" value="{{ auth()->user()->datOfBirth }}"
                                placeholder="Date de Naissance" readonly>
                        </div>

                        <div class="btn_register">
                            <input type="submit" id="btn_submit_profile" value="Mis à jour" style="display: none;">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnSaveLinkedin = document.getElementById('btn_save_linkedin');
            if (btnSaveLinkedin) {
                btnSaveLinkedin.addEventListener('click', function () {
                    const linkedinInput = document.getElementById('linkedin_input');
                    const feedback = document.getElementById('linkedin_feedback');
                    const url = linkedinInput.value.trim();

                    if (!url) {
                        feedback.textContent = 'Lien requis';
                        setTimeout(() => feedback.textContent = '', 3000);
                        return;
                    }

                    feedback.textContent = 'Envoi...';

                    fetch('{{ route("profile.linkedin.update") }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ linkedin: url })
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                feedback.textContent = '';
                                document.getElementById('linkedin_section').innerHTML = `
                                <i class="fa-brands fa-linkedin"></i>
                                <a href="${data.linkedin}" target="_blank" style="color: inherit; text-decoration: none;">Voir Profil</a>
                            `;
                            } else {
                                feedback.textContent = data.message || 'Erreur.';
                            }
                        })
                        .catch(() => {
                            feedback.textContent = 'Erreur réseau.';
                        });
                });
            }
        });
    </script>
@endsection
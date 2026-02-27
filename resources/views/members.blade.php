@extends('layouts.app')

@section('title', 'Membres - EasyColoc')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/Dash.css') }}">
    <style>
        .star-rating {
            display: flex;
            gap: 4px;
            justify-content: center;
            margin-top: 8px;
            cursor: pointer;
        }

        .star-rating i {
            font-size: 1.3rem;
            color: #ddd;
            transition: color .15s;
        }

        .star-rating i.active,
        .star-rating i.hovered {
            color: #f5a623;
        }

        .rating-feedback {
            font-size: .75rem;
            text-align: center;
            margin-top: 4px;
            min-height: 16px;
            color: #78080E;
            font-weight: bold;
        }
    </style>
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
            @if(auth()->user()->is_admin)
                <div title="Admin Dashboard"><a href="{{ route('admin.dashboard') }}"><i
                            class="fa-solid fa-shield-halved text-white" style="color: white !important;"></i></a></div>
            @endif
        </div>
        <div class="darkMenu_icon">
            <i class="fa-solid fa-moon"></i>
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
        <section class="section_colocations">
            <div class="section_titres">
                <h2>Vos Partenaires de Colocation</h2>
            </div>
        </section>
        <section class="bienvenue_partie section_membres">
            @php
                // Get unique members from all collocations the user is in
                $members = auth()->user()->collocations->flatMap->users->unique('id');
            @endphp

            @foreach($members as $member)
                @php
                    $myStars = $myRatings[$member['id']] ?? 0;
                    $repStars = min(5, floor($member['reputation'] / 20));
                @endphp
                <div class="bienvenue_partie_img" data-member-id="{{ $member['id'] }}">
                    <div class="ajouter_relation" onclick="showMemberDetails({{ $member['id'] }})">
                        <p>+</p>
                    </div>
                    <div class="bienvenue_partie_img_cont">
                        <img src="{{ asset('imgs/profile.jpg') }}" alt="">
                        <h4>{{ $member['name'] }}</h4>

                        {{-- Read-only avg reputation stars --}}
                        <div class="stars" title="Réputation moyenne">
                            @for($i = 0; $i < 5; $i++)
                                <i class="{{ $i < $repStars ? 'fa-solid' : 'fa-regular' }} fa-star" style="color:#f5a623;"></i>
                            @endfor
                        </div>

                        {{-- Interactive rating widget --}}
                        <div class="star-rating" data-member-id="{{ $member['id'] }}" data-my-rating="{{ $myStars }}">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $myStars ? 'fa-solid active' : 'fa-regular' }} fa-star"
                                    data-value="{{ $i }}"></i>
                            @endfor
                        </div>
                        <div class="rating-feedback" id="feedback-{{ $member['id'] }}"></div>
                    </div>
                </div>
            @endforeach
        </section>
    </main>

    <div class="modal_ajout_colocation modal_details_membre" id="modal_details_membre" style="display:none;">
        <form action="#" method="post">
            <div class="titre_form">Détails du Membre</div>
            <div class="contenair_details_infos">
                <div class="bienvenue_partie_img">
                    <div class="bienvenue_partie_img_cont">
                        <img src="{{ asset('imgs/profile.jpg') }}" alt="">
                    </div>
                </div>
                <div class="profile_rate">
                    <div class="stars" id="member_stars">
                        <!-- Stars injected by JS -->
                    </div>
                    <div>
                        <p id="member_level">Level: Unknown</p>
                    </div>
                </div>
                <div class="details_infos">
                    <h3 id="member_colocs_count">N° Total de colocations: 0</h3>
                    <h3 id="member_reputation">Points de réputation: 0</h3>
                </div>
            </div>
            <button type="button" class="btn_fermer"
                onclick="this.closest('.modal_ajout_colocation').style.display='none'">X</button>
        </form>
    </div>

@endsection

@section('scripts')
    @php
        $membersJson = $members->toArray();
    @endphp
    <script>
        const membersData = @json($membersJson);
        const csrfToken = '{{ csrf_token() }}';

        // ── Star rating interaction ────────────────────────────────────────
        document.querySelectorAll('.star-rating').forEach(function (widget) {
            const stars = widget.querySelectorAll('i');
            const memberId = parseInt(widget.dataset.memberId);
            const feedback = document.getElementById('feedback-' + memberId);

            stars.forEach(function (star) {
                star.addEventListener('mouseenter', function () {
                    const val = parseInt(this.dataset.value);
                    stars.forEach(s => s.classList.toggle('hovered', parseInt(s.dataset.value) <= val));
                });
                star.addEventListener('mouseleave', function () {
                    stars.forEach(s => s.classList.remove('hovered'));
                });

                star.addEventListener('click', function () {
                    const starsVal = parseInt(this.dataset.value);

                    stars.forEach(s => {
                        const v = parseInt(s.dataset.value);
                        s.className = v <= starsVal ? 'fa-solid fa-star active' : 'fa-regular fa-star';
                    });

                    feedback.textContent = 'Envoi...';

                    fetch('/api/ratings', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ rated_id: memberId, stars: starsVal })
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                feedback.textContent = '✓ Noté ' + starsVal + '/5 !';
                                setTimeout(() => feedback.textContent = '', 3000);
                            } else {
                                feedback.textContent = data.message || 'Erreur.';
                            }
                        })
                        .catch(() => { feedback.textContent = 'Erreur réseau.'; });
                });
            });
        });

        // ── Member details modal ───────────────────────────────────────────
        function showMemberDetails(id) {
            const member = membersData.find(m => m.id === id);
            if (!member) return;

            document.getElementById('modal_details_membre').querySelector('.titre_form').textContent
                = 'Concernant ' + member.name;

            const starsEl = document.getElementById('member_stars');
            const starCount = Math.min(5, Math.floor(member.reputation / 20));
            starsEl.innerHTML = '';
            for (let i = 0; i < 5; i++) {
                const icon = document.createElement('i');
                icon.className = i < starCount ? 'fa-solid fa-star' : 'fa-regular fa-star';
                icon.style.color = '#f5a623';
                starsEl.appendChild(icon);
            }

            document.getElementById('member_level').textContent
                = 'Rôle : ' + member.role.charAt(0).toUpperCase() + member.role.slice(1);
            document.getElementById('member_colocs_count').textContent
                = 'N° Total de colocations : ' + member.collocations;
            document.getElementById('member_reputation').textContent
                = 'Points de réputation : ' + member.reputation;

            document.getElementById('modal_details_membre').style.display = 'flex';
        }

        // Auto-open modal if ?member_id is in the URL
        document.addEventListener('DOMContentLoaded', function () {
            const params = new URLSearchParams(window.location.search);
            const memberId = parseInt(params.get('member_id'));
            if (memberId) showMemberDetails(memberId);
        });
    </script>
@endsection
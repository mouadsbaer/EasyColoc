@extends('layouts.app')

@section('title', $collocation->name . ' - EasyColoc')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/Dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/colocation.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('body-class', 'body_apresConn')

@section('content')
<header>
    <div class="main_icons">
        <div><a href="{{ route('dashboard') }}"><i class="fa-solid fa-house"></i></a></div>
        <div><a href="{{ route('members') }}"><i class="fa-solid fa-users"></i></a></div>
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
@if(session('success'))
    <div
        style="background:#d4edda;color:#155724;padding:13px 50px;border-radius:8px;margin:20px 50px;border:1px solid #c3e6cb;font-weight:bold;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif
<main>
    <section class="statistiques">
        <div class="stats">
            <div style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="color: #BB060B; margin-bottom: 0;">{{ $collocation->name }}</h2>
                    @can('manage', $collocation)
                        <form action="{{ route('collocations.cancel', $collocation->id) }}" method="POST"
                            onsubmit="return confirm('Êtes-vous sûr de vouloir annuler et détruire cette colocation ? Cette action dissoudra le groupe entier de manière irréversible !');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="hover:bg-red-800 transition"
                                style="background-color: #BB060B; color: white; padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
                                <i class="fa-solid fa-skull mr-2"></i> Annuler la Colocation
                            </button>
                        </form>
                    @endcan
                </div>
                <div class="deux_stats">
                    <div class="stat">
                        <h4>Dépenses Totales</h4>
                        <div class="collection_stat">
                            <p>{{ number_format($collocation->expenses->sum('amount'), 2) }} DH</p>
                        </div>
                    </div>
                    <div class="stat stat_colore">
                        <h4>Ma Balance</h4>
                        <div class="collection_stat">
                            @php
                                $myMembership = $collocation->memberships()->where('user_id', auth()->id())->first();
                                $balance = $myMembership ? $myMembership->balance : 0;
                            @endphp
                            <p>{{ number_format($balance, 2) }} DH</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="graphe">
                <div class="circle_graphe"><canvas id="graph_comp"></canvas></div>
                <div class="circle_graphe" id="container_not_circle"><canvas id="graph_not_circle"></canvas></div>
            </div>
        </div>
    </section>

    <section class="section_colocations section_colocations_categories">
        <div class="categories">
            <div class="section_titres">
                <h2>Les Catégories</h2>
                <button onclick="document.getElementById('modal_nouvelle_categorie').style.display='flex'">+
                    Ajouter</button>
            </div>
            @forelse($collocation->categories as $category)
                <div class="colocation">
                    <h4>{{ $category->name }}</h4>
                    <p>{{ $category->expenses->count() }} dépenses</p>
                    <p>Total: {{ number_format($category->expenses->sum('amount'), 2) }} DH</p>
                    <div class="btns_colocation">
                        <a href="{{ route('collocations.settlements', $collocation->id) }}" class="pay_btn">Règlements</a>
                        <button class="details_cat" onclick="showCategoryDetails({{ $category->id }})"
                            id="details_category"><i class="fa-solid fa-bars"></i></button>
                    </div>
                </div>
            @empty
                <p>Aucune catégorie définie.</p>
            @endforelse
        </div>

        <div class="membres">
            <div class="section_titres">
                <h2>Les Membres</h2>
            </div>
            @foreach($collocation->users as $member)
            <div class="colocation membre">
                <div class="img_membre"><img src="{{ asset('imgs/profile.jpg') }}" alt=""></div>
                <h4>{{ $member->name }}</h4>
                <p>{{ $member->pivot->role }}</p>
                <div class="btns_colocation">
                    @owner($collocation)
                    @if($member->id !== auth()->id() && $member->pivot->role !== 'owner')
                        <button type="button"
                            onclick="kickMember({{ $collocation->id }}, {{ $member->id }}, '{{ addslashes($member->name) }}', {{ $member->pivot->balance }})"
                            class="text-red-500 hover:text-red-700">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    @endif
                    @endowner
                    <a href="{{ route('members', ['member_id' => $member->id]) }}" class="details_membres"
                        style="padding: 0 5px;">
                        <i class="fa-solid fa-bars"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Member Details Modal -->
    <div class="modal_ajout_colocation modal_details_membre" id="modal_details_membre" style="display:none;">
        <form action="#" method="post">
            <div class="titre_form" id="dm_title">Concernant</div>
            <div class="contenair_details_infos">
                <div class="bienvenue_partie_img">
                    <div class="ajouter_relation">
                        <p>+</p>
                    </div>
                    <div class="bienvenue_partie_img_cont">
                        <img src="{{ asset('imgs/profile.jpg') }}" alt="">
                    </div>
                </div>
                <div class="profile_rate">
                    <div class="stars" id="dm_stars">
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                    </div>
                    <div>
                        <p id="dm_role">Rôle</p>
                    </div>
                </div>
                <div class="details_infos">
                    <h3>N° Total de colocations : <span id="dm_collocations">—</span></h3>
                    <h3>Points de réputation : <span id="dm_reputation">—</span></h3>
                </div>
            </div>
            <button type="button" class="btn_fermer"
                onclick="document.getElementById('modal_details_membre').style.display='none'">X</button>
        </form>
    </div>

    <!-- Modals -->
    <div class="modal_ajout_colocation" id="modal_nouvelle_categorie" style="display:none;">
        <form action="{{ route('categories.store') }}" method="POST" data-no-ajax>
            @csrf
            <input type="hidden" name="collocation_id" value="{{ $collocation->id }}">
            <div class="titre_form">Nouvelle Catégorie</div>
            <div class="champ1_modal">
                <input type="text" name="name" placeholder="Titre de catégorie" required>
            </div>
            <div class="champ1_modal">
                <textarea name="description" placeholder="Description (optionnel)"
                    style="width:100%;border:1px solid #BB060B;border-radius:7px;padding:10px;font-size:.9rem;resize:vertical;min-height:80px;"></textarea>
            </div>
            <div class="btn_champ1_modal">
                <input type="submit" value="Créer">
            </div>
            <button type="button" class="btn_fermer"
                onclick="this.closest('.modal_ajout_colocation').style.display='none'">X</button>
        </form>
    </div>

    <div class="modal_ajout_colocation modal_nouvelle_depense" id="nouvelle_depense" style="display:none;">
        <form action="{{ url('/api/expenses') }}" method="post">
            @csrf
            <input type="hidden" name="category_id" id="expense_category_id">
            <div class="titre_form">Nouvelle Dépense</div>
            <div class="champ1_modal">
                <input type="text" name="title" placeholder="Titre de dépense" required>
            </div>
            <div class="champ1_modal">
                <input type="number" step="0.01" name="amount" placeholder="Montant Précis" required>
            </div>
            <div class="champ1_modal">
                <input type="date" name="date" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="btn_champ1_modal">
                <input type="submit" value="Créer">
            </div>
            <button type="button" class="btn_fermer"
                onclick="this.closest('.modal_ajout_colocation').style.display='none'">X</button>
        </form>
    </div>

    <!-- Edit Expense Modal -->
    <div class="modal_ajout_colocation modal_nouvelle_depense" id="edit_depense" style="display:none;">
        <form id="edit_expense_form" method="post">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <div class="titre_form">Modifier Dépense</div>
            <div class="champ1_modal">
                <input type="text" name="title" id="edit_expense_title" placeholder="Titre de dépense" required>
            </div>
            <div class="champ1_modal">
                <input type="number" step="0.01" name="amount" id="edit_expense_amount" placeholder="Montant Précis"
                    required>
            </div>
            <div class="champ1_modal">
                <input type="date" name="date" id="edit_expense_date" required>
            </div>
            <div class="btn_champ1_modal">
                <input type="submit" value="Enregistrer">
            </div>
            <button type="button" class="btn_fermer"
                onclick="this.closest('.modal_ajout_colocation').style.display='none'">X</button>
        </form>
    </div>

    <!-- Category Details Modal -->
    <div class="modal_ajout_colocation modal_details_cat" id="modal_details_cat" style="display:none;">
        <form action="#">
            <div class="titre_form">Détails de Catégorie</div>
            <div class="cat_infos">
                <div class="row1">
                    <div>
                        <p>Titre : </p><span id="dcat_name"></span>
                    </div>
                    <div>
                        <p>Description : </p><span id="dcat_description"></span>
                    </div>
                    <div>
                        <p>Créé en : </p><span id="dcat_date"></span>
                    </div>
                    <div>
                        <p>Membres : </p>
                        <div class="membres_imgs" id="dcat_membres">
                            @foreach($collocation->users as $m)
                                <img src="{{ asset('imgs/profile.jpg') }}" alt="{{ $m->name }}" title="{{ $m->name }}">
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="row2">
                    <div class="flex">
                        <p>Liste de Dépenses :</p>
                        <button type="button" id="add_depense" onclick="openNewDepense()">+</button>
                    </div>
                    <ul id="dcat_expenses">
                    </ul>
                </div>
            </div>
            <button type="button" class="btn_fermer"
                onclick="document.getElementById('modal_details_cat').style.display='none'">X</button>
        </form>
    </div>
</main>
@endsection

@section('scripts')
    @php
        $categoriesJson = $collocation->categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'description' => $cat->description ?? '',
                'created_at' => $cat->created_at->format('d/m/Y'),
                'expenses' => $cat->expenses->sortByDesc('date')->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'title' => $e->title,
                        'amount' => $e->amount,
                        'date' => $e->date,
                        'user_id' => $e->user_id,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    @endphp
    <script>
        const categoriesData = @json($categoriesJson);

        let currentCategoryId = null;

        function showCategoryDetails(id) {
            const cat = categoriesData.find(c => c.id === id);
            if (!cat) return;

            currentCategoryId = id;

            document.getElementById('dcat_name').textContent = cat.name;
            document.getElementById('dcat_description').textContent = cat.description || '—';
            document.getElementById('dcat_date').textContent = cat.created_at;

            const ul = document.getElementById('dcat_expenses');
            ul.innerHTML = '';
            if (cat.expenses.length === 0) {
                ul.innerHTML = '<li style="opacity:.6;">Aucune dépense pour cette catégorie.</li>';
            } else {
                cat.expenses.forEach(e => {
                    const li = document.createElement('li');
                    li.className = "flex justify-between items-center w-full py-2 border-b border-gray-100 last:border-0";
                    li.innerHTML = `
                                        <div>
                                            <span class="font-medium text-gray-800">${e.title}</span> 
                                            <span class="text-sm text-gray-500 block">${e.date || ''}</span>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span class="font-bold text-gray-900">${parseFloat(e.amount).toFixed(2)} DH</span>
                                            ${window.Laravel.userId === e.user_id ? `
                                                <button type="button" onclick="editExpense(${e.id}, '${e.title.replace(/'/g, "\\'")}', ${e.amount}, '${e.date}')" class="text-blue-500 hover:text-blue-700 ml-2">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <button type="button" onclick="deleteExpense(${e.id})" class="text-red-500 hover:text-red-700 ml-2">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            ` : ''}
                                        </div>
                                    `;
                    ul.appendChild(li);
                });
            }

            document.getElementById('modal_details_cat').style.display = 'flex';
        }

        function deleteExpense(expenseId) {
            if (!confirm("Êtes-vous sûr de vouloir supprimer cette dépense ? La balance sera recalculée automatiquement.")) return;

            fetch(`/api/expenses/${expenseId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (window.showToast) window.showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        if (window.showToast) window.showToast(data.message, 'error');
                        else alert(data.message);
                    }
                });
        }

        function openNewDepense() {
            document.getElementById('modal_details_cat').style.display = 'none';
            document.getElementById('expense_category_id').value = currentCategoryId;
            document.getElementById('nouvelle_depense').style.display = 'flex';
        }

        function editExpense(id, title, amount, date) {
            document.getElementById('modal_details_cat').style.display = 'none';
            document.getElementById('edit_expense_form').action = `/api/expenses/${id}`;
            document.getElementById('edit_expense_title').value = title;
            document.getElementById('edit_expense_amount').value = parseFloat(amount).toFixed(2);
            document.getElementById('edit_expense_date').value = date;
            document.getElementById('edit_depense').style.display = 'flex';
        }

        function kickMember(collocationId, userId, memberName, hasDebt) {
            let msg = `Êtes-vous sûr de vouloir retirer ${memberName} ?`;
            if (hasDebt < 0) {
                msg += `\n\nATTENTION : Ce membre a une dette de ${Math.abs(hasDebt).toFixed(2)} DH. En le retirant, vous (en tant que propriétaire) assumerez cette dette !`;
            }

            if (!confirm(msg)) return;

            fetch(`/api/collocations/${collocationId}/members/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (window.showToast) window.showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        if (window.showToast) window.showToast(data.message, 'error');
                        else alert(data.message);
                    }
                });
        }
    </script>
@endsection
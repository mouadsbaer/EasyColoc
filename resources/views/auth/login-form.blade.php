<form id="login-form" action="{{ route('login.post') }}" method="post">
    @csrf
    <div class="titre_form">Se Connecter</div>
    <div class="champ1_register">
        <input type="email" name="email" placeholder="Your Email" required>
    </div>
    <div class="champ1_register">
        <input type="password" name="password" placeholder="Your Password" required>
    </div>
    <div class="lien_connexion">
        <p>C'est ma première fois ! <button type="button" id="creation_depuis_connection">Créer un compte</button></p>
        <p class="aide"><a href="{{ route('password.request') }}">Mot de passe oublié ?</a></p>
    </div>
    <div class="btn_register">
        <input type="submit" value="Se Connecter">
    </div>
</form>
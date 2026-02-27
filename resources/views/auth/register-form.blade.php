<form id="register-form" action="{{ route('register') }}" method="post">
    @csrf
    <div class="titre_form">Nouveaux Compte</div>
    <div class="champ1_register">
        <input type="text" name="name" placeholder="Your Name" required>
    </div>
    <div class="champ1_register">
        <input type="email" name="email" placeholder="Your Email" required>
    </div>
    <div class="champ1_register">
        <input type="text" name="phone" placeholder="Your Phone">
    </div>
    <div class="champ1_register">
        <input type="password" name="password" placeholder="Your Password" required>
    </div>
    <div class="lien_connexion">
        <p>J'ai déjà un compte ! <button type="button" id="connection_depuis_creation">Se connecter</button></p>
        <p class="aide"><a href="{{ route('help') }}">Aide</a></p>
    </div>
    <div class="btn_register">
        <input type="submit" value="SIGN UP">
    </div>
</form>
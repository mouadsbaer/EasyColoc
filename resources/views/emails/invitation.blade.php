<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: #78080E;
            color: #ffffff;
            text-align: center;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
        }

        .content {
            padding: 30px;
            line-height: 1.6;
            font-size: 16px;
        }

        .message-box {
            background: #f9f9f9;
            border-left: 4px solid #f5a623;
            padding: 15px;
            margin: 20px 0;
            font-style: italic;
            color: #555;
        }

        .btn-container {
            text-align: center;
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            margin: 0 10px;
            font-size: 16px;
            font-weight: bold;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .btn-accept {
            background-color: #28a745;
        }

        .btn-accept:hover {
            background-color: #218838;
        }

        .btn-decline {
            background-color: #dc3545;
        }

        .btn-decline:hover {
            background-color: #c82333;
        }

        .footer {
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #777;
            background-color: #eeeeee;
        }

        .footer span {
            color: #78080E;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            Invitation à rejoindre EasyColoc
        </div>
        <div class="content">
            <p>Bonjour,</p>
            <p><strong>{{ $sender->name }}</strong> vous invite à rejoindre sa colocation
                <strong>"{{ $collocation->name }}"</strong> sur l'application EasyColoc !</p>

            @if(!empty($customMessage))
                <div class="message-box">
                    "{{ $customMessage }}"
                </div>
            @endif

            <p>Gérez vos dépenses communes, organisez votre espace de vie et suivez tout ce qui se passe dans votre
                colocation de manière simple et transparente.</p>

            <div class="btn-container">
                <a href="{{ route('invitations.accept', ['token' => $token]) }}" class="btn btn-accept"
                    style="color:white !important;">Accepter</a>
                <a href="{{ route('invitations.decline', ['token' => $token]) }}" class="btn btn-decline"
                    style="color:white !important;">Refuser</a>
            </div>
        </div>
        <div class="footer">
            <p>À bientôt sur <span>EasyColoc</span> !</p>
        </div>
    </div>
</body>

</html>
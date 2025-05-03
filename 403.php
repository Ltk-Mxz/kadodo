<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Accès Interdit</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #0a0b1e;
            color: #ffffff;
            overflow: hidden;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .grid {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(#ff3a1e10 1px, transparent 1px),
                linear-gradient(90deg, #ff3a1e10 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: 1;
            perspective: 1000px;
            transform-style: preserve-3d;
            animation: grid-move 20s linear infinite;
        }

        @keyframes grid-move {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 40px 40px;
            }
        }

        .container {
            text-align: center;
            z-index: 10;
            max-width: 800px;
            padding: 2rem;
            background-color: rgba(10, 11, 30, 0.7);
            border-radius: 20px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 58, 30, 0.3);
            box-shadow: 0 0 30px rgba(255, 58, 30, 0.2);
        }

        .error-code {
            font-size: 10rem;
            font-weight: 700;
            background: linear-gradient(90deg, #ff3a1e, #ff7857);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 0 15px rgba(255, 58, 30, 0.3);
            margin-bottom: 1rem;
            position: relative;
            animation: pulse 4s ease-in-out infinite;
        }

        .error-code::after {
            content: "403";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            filter: blur(8px);
            color: #ff3a1e;
            z-index: -1;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .error-title {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: #ffffff;
            font-weight: 300;
        }

        .error-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #ffb3a9;
            line-height: 1.6;
        }

        .home-button {
            display: inline-block;
            padding: 0.8rem 2.5rem;
            background: linear-gradient(90deg, #ff3a1e, #ff7857);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(255, 58, 30, 0.4);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .home-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 58, 30, 0.6);
        }

        .home-button:active {
            transform: translateY(1px);
        }

        .home-button::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s;
        }

        .home-button:hover::before {
            left: 100%;
        }

        .security-shield {
            width: 150px;
            height: 150px;
            margin: 0 auto 2rem;
            position: relative;
        }

        .shield {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #ff3a1e 0%, #ff7857 100%);
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            position: relative;
            animation: shield-pulse 3s ease-in-out infinite;
            box-shadow: 0 0 15px rgba(255, 58, 30, 0.5);
        }

        @keyframes shield-pulse {

            0%,
            100% {
                filter: brightness(1);
            }

            50% {
                filter: brightness(1.2);
            }
        }

        .lock {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background-color: rgba(10, 11, 30, 0.9);
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 0 10px rgba(10, 11, 30, 0.5);
        }

        .lock::before {
            content: "";
            position: absolute;
            top: -25px;
            width: 30px;
            height: 25px;
            border-radius: 15px 15px 0 0;
            border: 4px solid white;
            border-bottom: none;
        }

        .laser-beam {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .laser {
            position: absolute;
            height: 1px;
            width: 100%;
            background: linear-gradient(90deg, transparent, #ff3a1e, transparent);
            animation: laser-scan 6s linear infinite;
            box-shadow: 0 0 8px rgba(255, 58, 30, 0.8);
        }

        @keyframes laser-scan {
            0% {
                top: -10%;
            }

            48% {
                top: 110%;
                opacity: 1;
            }

            49% {
                opacity: 0;
            }

            50% {
                top: -10%;
                opacity: 0;
            }

            51% {
                opacity: 1;
            }

            100% {
                top: 110%;
            }
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .error-code {
                font-size: 8rem;
            }

            .error-title {
                font-size: 2rem;
            }

            .error-message {
                font-size: 1rem;
            }

            .security-shield {
                width: 120px;
                height: 120px;
            }
        }

        @media (max-width: 480px) {
            .error-code {
                font-size: 6rem;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .container {
                padding: 1.5rem;
            }

            .security-shield {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>

<body>
    <div class="grid"></div>
    <div class="laser-beam">
        <div class="laser"></div>
    </div>

    <div class="container">
        <div class="security-shield">
            <div class="shield"></div>
            <div class="lock"></div>
        </div>
        <div class="error-code">403</div>
        <h1 class="error-title">Accès Interdit</h1>
        <p class="error-message">Vous n'avez pas les autorisations nécessaires pour accéder à cette page. Veuillez contacter l'administrateur de l'intranet si vous pensez qu'il s'agit d'une erreur.</p>
        <a href="/myschoolface/" class="home-button">Retour à l'Accueil</a>
    </div>

    <script>
        // Create multiple laser beams
        const laserBeamContainer = document.querySelector('.laser-beam');
        const laserCount = 3;

        for (let i = 0; i < laserCount; i++) {
            const laser = document.createElement('div');
            laser.classList.add('laser');

            // Random delay for each laser
            const delay = i * 2;
            laser.style.animationDelay = `${delay}s`;

            laserBeamContainer.appendChild(laser);
        }
    </script>
</body>

</html>
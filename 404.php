<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Non Trouvée</title>
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
            background-image: linear-gradient(#1e66ff10 1px, transparent 1px),
                linear-gradient(90deg, #1e66ff10 1px, transparent 1px);
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
            border: 1px solid rgba(30, 102, 255, 0.3);
            box-shadow: 0 0 30px rgba(30, 102, 255, 0.2);
        }

        .error-code {
            font-size: 10rem;
            font-weight: 700;
            background: linear-gradient(90deg, #1e66ff, #57a3ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 0 15px rgba(30, 102, 255, 0.3);
            margin-bottom: 1rem;
            position: relative;
            animation: float 4s ease-in-out infinite;
        }

        .error-code::after {
            content: "404";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            filter: blur(8px);
            color: #1e66ff;
            z-index: -1;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
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
            color: #b3c7ff;
            line-height: 1.6;
        }

        .home-button {
            display: inline-block;
            padding: 0.8rem 2.5rem;
            background: linear-gradient(90deg, #1e66ff, #57a3ff);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(30, 102, 255, 0.4);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .home-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(30, 102, 255, 0.6);
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

        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background-color: #1e66ff;
            border-radius: 50%;
            opacity: 0;
            animation: particle-animation 8s ease infinite;
        }

        @keyframes particle-animation {
            0% {
                opacity: 0;
                transform: translateY(0) translateX(0);
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                transform: translateY(-1000%) translateX(var(--x));
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
        }
    </style>
</head>

<body>
    <div class="grid"></div>
    <div class="particles" id="particles"></div>

    <div class="container">
        <div class="error-code">404</div>
        <h1 class="error-title">Page Non Trouvée</h1>
        <p class="error-message">Oups ! La page que vous recherchez semble avoir disparu dans le cyberespace. Vérifiez l'URL ou retournez à la page d'accueil.</p>
        <a href="/myschoolface/" class="home-button">Retour à l'Accueil</a>
    </div>

    <script>
        // Create particles
        const particlesContainer = document.getElementById('particles');
        const particleCount = 50;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');

            // Random position
            const posX = Math.random() * 100;
            const posY = Math.random() * 100;
            particle.style.left = `${posX}%`;
            particle.style.top = `${posY}%`;

            // Random size
            const size = Math.random() * 4 + 1;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;

            // Random direction
            const xDirection = Math.random() * 200 - 100;
            particle.style.setProperty('--x', `${xDirection}%`);

            // Random delay
            const delay = Math.random() * 5;
            particle.style.animationDelay = `${delay}s`;

            particlesContainer.appendChild(particle);
        }
    </script>
</body>

</html>
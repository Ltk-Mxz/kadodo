<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page En Développement</title>
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
            background-image: linear-gradient(#19cc6510 1px, transparent 1px),
                linear-gradient(90deg, #19cc6510 1px, transparent 1px);
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
            border: 1px solid rgba(25, 204, 101, 0.3);
            box-shadow: 0 0 30px rgba(25, 204, 101, 0.2);
        }

        .title {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(90deg, #19cc65, #65e198);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 0 15px rgba(25, 204, 101, 0.3);
            margin-bottom: 1.5rem;
            position: relative;
        }

        .subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: #ffffff;
            font-weight: 300;
        }

        .message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #b3ffce;
            line-height: 1.6;
        }

        .home-button {
            display: inline-block;
            padding: 0.8rem 2.5rem;
            background: linear-gradient(90deg, #19cc65, #65e198);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(25, 204, 101, 0.4);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .home-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(25, 204, 101, 0.6);
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

        .progress-container {
            margin: 2rem auto;
            width: 60%;
            height: 30px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 0 10px rgba(25, 204, 101, 0.2) inset;
        }

        .progress-bar {
            width: 75%;
            height: 100%;
            background: linear-gradient(90deg, #19cc65, #65e198);
            border-radius: 15px;
            position: relative;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        .percentage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: 600;
            color: white;
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }

        .dev-animation {
            position: relative;
            height: 120px;
            width: 120px;
            margin: 0 auto 2rem;
        }

        .code-block {
            position: absolute;
            width: 20px;
            height: 6px;
            background-color: #19cc65;
            border-radius: 3px;
            opacity: 0;
            animation: code-animation 2s linear infinite;
        }

        .gear {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px dashed #19cc65;
            animation: rotate 10s linear infinite;
        }

        .gear-inner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 4px dashed #65e198;
            animation: rotate 5s linear infinite reverse;
        }

        @keyframes rotate {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        @keyframes code-animation {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }

            20% {
                opacity: 1;
            }

            80% {
                opacity: 1;
            }

            100% {
                transform: translateY(var(--y)) translateX(var(--x));
                opacity: 0;
            }
        }

        .date-info {
            margin-top: 2rem;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .title {
                font-size: 2.5rem;
            }

            .subtitle {
                font-size: 1.3rem;
            }

            .message {
                font-size: 1rem;
            }

            .progress-container {
                width: 80%;
            }
        }

        @media (max-width: 480px) {
            .title {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1.2rem;
            }

            .container {
                padding: 1.5rem;
            }

            .progress-container {
                width: 100%;
            }

            .dev-animation {
                height: 100px;
                width: 100px;
            }
        }
    </style>
</head>

<body>
    <div class="grid"></div>

    <div class="container">
        <div class="dev-animation">
            <div class="gear"></div>
            <div class="gear-inner"></div>
            <div id="code-blocks"></div>
        </div>
        <h1 class="title">Page En Développement</h1>
        <h2 class="subtitle">Notre équipe travaille activement sur cette fonctionnalité</h2>
        <p class="message">Nous sommes en train de créer quelque chose d'extraordinaire pour améliorer votre expérience sur l'intranet. Revenez bientôt pour découvrir cette nouvelle fonctionnalité !</p>

        <div class="progress-container">
            <div class="progress-bar"></div>
            <div class="percentage">75%</div>
        </div>

        <a href="/myschoolface/" class="home-button">Retour à l'Accueil</a>

        <p class="date-info">Date de mise en ligne prévue : <strong>10 Avril 2025</strong></p>
    </div>

    <script>
        // Create animated code blocks
        const codeBlocksContainer = document.getElementById('code-blocks');
        const blockCount = 15;

        for (let i = 0; i < blockCount; i++) {
            const block = document.createElement('div');
            block.classList.add('code-block');

            // Random position
            const centerX = 60;
            const centerY = 60;
            const angle = Math.random() * Math.PI * 2;
            const distance = 20 + Math.random() * 40;

            const posX = centerX + Math.cos(angle) * distance;
            const posY = centerY + Math.sin(angle) * distance;

            block.style.left = `${posX}px`;
            block.style.top = `${posY}px`;

            // Random size
            const width = 10 + Math.random() * 20;
            block.style.width = `${width}px`;

            // Random direction
            const xMove = (Math.random() - 0.5) * 100;
            const yMove = (Math.random() - 0.5) * 100;
            block.style.setProperty('--x', `${xMove}px`);
            block.style.setProperty('--y', `${yMove}px`);

            // Random delay
            const delay = Math.random() * 2;
            block.style.animationDelay = `${delay}s`;

            codeBlocksContainer.appendChild(block);
        }
    </script>
</body>

</html>
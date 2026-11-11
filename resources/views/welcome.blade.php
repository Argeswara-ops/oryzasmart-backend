<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oryza Smart - Under Development</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: radial-gradient(circle at 50% 20%, #1e293b 0%, #0f172a 60%, #020617 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
            position: relative;
        }
        .bg-glow {
            position: absolute;
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, rgba(14, 165, 233, 0.08) 50%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(40px);
            z-index: 1;
        }
        .card {
            position: relative;
            z-index: 2;
            max-width: 480px;
            width: 100%;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: 40px 28px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .gif-container {
            width: 180px;
            height: 180px;
            margin: 0 auto 28px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid rgba(16, 185, 129, 0.35);
            box-shadow: 0 0 35px rgba(16, 185, 129, 0.25);
            background: #020617;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gif-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #34d399;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }
        h1 {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #ffffff;
            margin-bottom: 12px;
            line-height: 1.25;
        }
        p {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #94a3b8;
            margin-bottom: 28px;
        }
        .footer-note {
            font-size: 0.8rem;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>
    
    <div class="card">
        <div class="gif-container">
            <img src="{{ asset('images/tenor.gif') }}" alt="Sedang Dikembangkan" onerror="this.onerror=null; this.src='https://media1.tenor.com/m/wilYo_7wGKYAAAAC/new-game-ahagon-umiko-programming.gif';">
        </div>
        
        <div class="badge">
            <span class="badge-dot"></span>
            Sedang Dalam Pengembangan
        </div>

        <h1>Segera Hadir</h1>
        
        <p>
            Platform pemantauan pertanian cerdas dan ekosistem IoT Oryza sedang dipersiapkan. Kami sedang menyempurnakan fitur terbaik untuk Anda.
        </p>

        <div class="footer-note">
            <span>&copy; {{ date('Y') }} Oryza Smart Farming. All rights reserved.</span>
        </div>
    </div>
</body>
</html>

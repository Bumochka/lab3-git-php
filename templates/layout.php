<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная работа 3</title>
    <style>
        body {
            background-color: <?= htmlspecialchars($bgColor ?? '#1b2838') ?>;
            color: <?= htmlspecialchars($fontColor ?? '#ffffff') ?>;
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 90vh;
            margin: 0;
        }
        .card {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 24px;
            border-radius: 8px;
            width: 320px;
        }
        input[type="text"], input[type="password"], input[type="color"] {
            width: 100%;
            padding: 8px;
            margin: 6px 0 14px 0;
            box-sizing: border-box;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input[type="color"] { height: 40px; padding: 2px; }
        button {
            width: 100%;
            padding: 10px;
            background: #2b3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover { background: #3b536b; }
        .footer-debug {
            margin-top: 15px;
            font-family: monospace;
            font-size: 11px;
            border-top: 1px dashed rgba(255, 255, 255, 0.2);
            padding-top: 8px;
            opacity: 0.8;
            word-break: break-all;
        }
        .error { color: #ff6b6b; margin-bottom: 10px; }
        .success { color: #51cf66; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="card">
        <?php if (!empty($flashError)): ?>
            <div class="error"><?= htmlspecialchars($flashError) ?></div>
        <?php endif; ?>
        <?php if (!empty($flashSuccess)): ?>
            <div class="success"><?= htmlspecialchars($flashSuccess) ?></div>
        <?php endif; ?>

        <?php require $contentFile; ?>

        <div class="footer-debug">
            cookie: bg_color=<?= htmlspecialchars($_COOKIE['bg_color'] ?? 'не задан') ?>; font_color=<?= htmlspecialchars($_COOKIE['font_color'] ?? 'не задан') ?>
        </div>
    </div>
</body>
</html>
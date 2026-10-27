<h3>Привет, <?= htmlspecialchars($user['login']) ?>!</h3>
<p style="font-size: 13px; opacity: 0.8;">Настройки хранятся в БД и применяются через cookie-параметры</p>

<form method="POST" action="?action=update_settings">
    <label>Цвет фона страницы</label>
    <input type="color" name="bg_color" value="<?= htmlspecialchars($bgColor) ?>">

    <label>Цвет шрифта</label>
    <input type="color" name="font_color" value="<?= htmlspecialchars($fontColor) ?>">

    <button type="submit" style="margin-bottom: 10px;">Сохранить настройки</button>
</form>

<form method="POST" action="?action=logout">
    <button type="submit" style="background: #c92a2a;">Выйти</button>
</form>
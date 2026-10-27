<h2>Регистрация</h2>
<p style="font-size: 13px; opacity: 0.8;">Настройки оформления сохраняются в БД и передаются через cookie</p>
<form method="POST" action="?action=register">
    <label>Логин</label>
    <input type="text" name="login" required>

    <label>Пароль</label>
    <input type="password" name="password" required>

    <label>Повторите пароль</label>
    <input type="password" name="password_confirm" required>

    <label>Цвет фона страницы</label>
    <input type="color" name="bg_color" value="#1c2d37">

    <label>Цвет шрифта</label>
    <input type="color" name="font_color" value="#f6c25a">

    <button type="submit">Создать аккаунт</button>
</form>
<p style="font-size: 13px; margin-top: 15px;">
    Уже есть аккаунт? <a href="?action=login" style="color: inherit;">Войти</a>
</p>
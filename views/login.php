<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Iniciar Sesión</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Merriweather:wght@300;400;700;900&display=swap" rel="stylesheet">

<style>
*,
*::before,
*::after{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

:root{
    --accent:#C8A96E;
    --accent-dark:#A8894E;
    --bg-overlay:rgba(10,10,15,.72);
    --card-bg:rgba(255,255,255,.04);
    --card-border:rgba(255,255,255,.12);
    --input-bg:rgba(255,255,255,.07);
    --input-border:rgba(255,255,255,.15);
    --input-focus:rgba(200,169,110,.45);
    --text-primary:#F0EDE8;
    --text-muted:rgba(240,237,232,.55);
    --error-bg:rgba(220,80,60,.15);
    --error-border:rgba(220,80,60,.4);
    --error-text:#F5A49A;
    --success-bg:rgba(60,180,100,.15);
    --success-border:rgba(60,180,100,.4);
    --success-text:#7DDBA0;
}

body{
    font-family:'DM Sans',sans-serif;
    min-height:100vh;
    background:url('../assets/img/fnd.jpg') no-repeat center center/cover;
    display:flex;
    justify-content:center;
    align-items:center;
    overflow:hidden;
    position:relative;
}

body::before{
    content:"";
    position:fixed;
    inset:0;
    background:var(--bg-overlay);
    backdrop-filter:blur(2px);
}

body::after{
    content:"";
    position:fixed;
    width:500px;
    height:500px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(200,169,110,.12) 0%,transparent 70%);
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
}

.wrapper{
    width:100%;
    max-width:420px;
    padding:20px;
    position:relative;
    z-index:2;
    animation:fadeUp .6s ease;
}

@keyframes fadeUp{
    from{
        opacity:0;
        transform:translateY(20px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

.card{
    background:var(--card-bg);
    border:1px solid var(--card-border);
    border-radius:22px;
    padding:42px 38px;
    backdrop-filter:blur(22px);
    box-shadow:
    0 30px 70px rgba(0,0,0,.45),
    inset 0 1px 0 rgba(255,255,255,.08);
}

.logo-area{
    text-align:center;
    margin-bottom:30px;
}

.logo-frame{
    width:72px;
    height:72px;
    margin:auto;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(200,169,110,.08);
    border:1px solid rgba(200,169,110,.35);
    margin-bottom:18px;
}
.card-footer a:hover {
    text-decoration: underline;
}

.logo-frame img{
    width:44px;
    height:44px;
    object-fit:contain;
}

.logo-area h1{
    font-family:'Merriweather',serif;
    color:var(--text-primary);
    font-size:27px;
}

.logo-area p{
    margin-top:7px;
    color:var(--text-muted);
    font-size:13px;
}

.divider{
    height:1px;
    background:linear-gradient(90deg,transparent,var(--card-border),transparent);
    margin-bottom:28px;
}

.alert{
    padding:12px 14px;
    border-radius:12px;
    background:var(--error-bg);
    border:1px solid var(--error-border);
    color:var(--error-text);
    font-size:13px;
    margin-bottom:18px;
}

.field{
    margin-bottom:16px;
}

label{
    display:block;
    font-size:12px;
    color:var(--text-muted);
    margin-bottom:8px;
    font-weight:600;
    letter-spacing:.6px;
    text-transform:uppercase;
}

.input-wrap{
    position:relative;
}

.icon-left{
    position:absolute;
    left:14px;
    top:50%;
    transform:translateY(-50%);
    opacity:.55;
    pointer-events:none;
    transition:.2s;
    z-index:2;
}

.input-wrap:focus-within .icon-left{
    opacity:1;
}

.form-input{
    width:100%;
    height:50px;
    border-radius:13px;
    border:1px solid var(--input-border);
    background:var(--input-bg);
    color:var(--text-primary);
    font-size:14.5px;
    font-family:'DM Sans',sans-serif;
    padding:0 48px 0 44px;
    outline:none;
    transition:.2s;
    appearance:none;
    -webkit-appearance:none;
}

.form-input::placeholder{
    color:rgba(240,237,232,.32);
}

.form-input:focus{
    border-color:var(--accent);
    background:rgba(255,255,255,.09);
    box-shadow:0 0 0 3px var(--input-focus);
}

/* IMPORTANTE:
   mismo diseño aunque cambie password a text */
input[type="text"].form-input,
input[type="password"].form-input,
input[type="email"].form-input{
    height:50px;
    border-radius:13px;
    border:1px solid var(--input-border);
    background:var(--input-bg);
    color:var(--text-primary);
    padding:0 48px 0 44px;
    font-size:14.5px;
}

.toggle-pass{
    position:absolute;
    right:10px;
    top:50%;
    transform:translateY(-50%);
    width:34px;
    height:34px;
    border:none;
    border-radius:10px;
    background:transparent;
    color:var(--text-muted);
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:.2s;
}

.toggle-pass:hover{
    color:var(--text-primary);
    background:rgba(255,255,255,.06);
}

.btn-submit{
    width:100%;
    height:50px;
    margin-top:10px;
    border:none;
    border-radius:13px;
    cursor:pointer;
    font-size:15px;
    font-weight:700;
    background:linear-gradient(135deg,var(--accent),var(--accent-dark));
    color:#1a120a;
    box-shadow:0 8px 22px rgba(200,169,110,.28);
    transition:.2s;
}

.btn-submit:hover{
    transform:translateY(-1px);
    box-shadow:0 12px 28px rgba(200,169,110,.35);
}

.card-footer{
    margin-top:24px;
    text-align:center;
    color:var(--text-muted);
    font-size:12px;
}
</style>
</head>
<body>

<div class="wrapper">
<div class="card">

<div class="logo-area">
    <div class="logo-frame">
        <img src="../assets/img/icon.png" alt="Logo">
    </div>
    <h1>Bienvenido</h1>
    <p>Ingresa tus credenciales para continuar</p>
</div>

<div class="divider"></div>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert" style="background:var(--success-bg);border-color:var(--success-border);color:var(--success-text);">¡Cuenta creada exitosamente! Inicia sesión.</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <?php if ($_GET['error'] == 1): ?>
        <div class="alert">Usuario no encontrado. Verifica tu correo.</div>
    <?php elseif ($_GET['error'] == 2): ?>
        <div class="alert">Contraseña incorrecta. Inténtalo de nuevo.</div>
    <?php endif; ?>
<?php endif; ?>

<form action="../controllers/login.php" method="POST" autocomplete="off">

<div class="field">
    <label for="correo">Correo electrónico</label>
    <div class="input-wrap">
        <svg class="icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="2">
            <rect x="2" y="4" width="20" height="16" rx="2"/>
            <path d="M22 7L12 14L2 7"/>
        </svg>

        <input
            type="email"
            id="correo"
            name="correo"
            class="form-input"
            value="admin@test.com"
            placeholder="tu@correo.com"
            required
        >
    </div>
</div>

<div class="field">
    <label for="pass">Contraseña</label>
    <div class="input-wrap">

        <svg class="icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>

        <input
            type="password"
            id="pass"
            name="pass"
            class="form-input"
            value="123456"
            placeholder="••••••••"
            required
        >

        <button type="button" class="toggle-pass" onclick="togglePass()">
            <svg id="eye-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </button>

    </div>
</div>

<button type="submit" class="btn-submit">Ingresar</button>
<div class="card-footer" style="margin-top:18px;">
    ¿No tienes cuenta? <a href="register.php" style="color:var(--accent);text-decoration:none;font-weight:600;">Regístrate aquí</a>
</div>

</form>

<div class="card-footer">
Sistema protegido — acceso restringido
</div>

</div>
</div>

<script>
function togglePass(){
    const input = document.getElementById("pass");
    const icon = document.getElementById("eye-icon");

    if(input.type === "password"){
        input.type = "text";
        icon.innerHTML = `
            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C5 20 1 12 1 12a21.8 21.8 0 0 1 5.06-5.94"/>
            <path d="M9.9 4.24A10.6 10.6 0 0 1 12 4c7 0 11 8 11 8a22.3 22.3 0 0 1-2.17 3.19"/>
            <line x1="1" y1="1" x2="23" y2="23"/>
        `;
    }else{
        input.type = "password";
        icon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
        `;
    }
}
</script>

</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registrarse</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Merriweather:wght@300;400;700;900&display=swap" rel="stylesheet">

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

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
    overflow-y:auto;
    position:relative;
    padding:20px 0;
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
    width:500px;height:500px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(200,169,110,.12) 0%,transparent 70%);
    top:50%;left:50%;
    transform:translate(-50%,-50%);
}

.wrapper{
    width:100%;
    max-width:460px;
    padding:20px;
    position:relative;
    z-index:2;
    animation:fadeUp .6s ease;
}

@keyframes fadeUp{
    from{opacity:0;transform:translateY(20px);}
    to{opacity:1;transform:translateY(0);}
}

.card{
    background:var(--card-bg);
    border:1px solid var(--card-border);
    border-radius:22px;
    padding:42px 38px;
    backdrop-filter:blur(22px);
    box-shadow:0 30px 70px rgba(0,0,0,.45),inset 0 1px 0 rgba(255,255,255,.08);
}

.logo-area{text-align:center;margin-bottom:30px;}

.logo-frame{
    width:72px;height:72px;
    margin:auto;
    border-radius:18px;
    display:flex;align-items:center;justify-content:center;
    background:rgba(200,169,110,.08);
    border:1px solid rgba(200,169,110,.35);
    margin-bottom:18px;
}

.logo-frame img{width:44px;height:44px;object-fit:contain;}

.logo-area h1{font-family:'Merriweather',serif;color:var(--text-primary);font-size:27px;}
.logo-area p{margin-top:7px;color:var(--text-muted);font-size:13px;}

.divider{
    height:1px;
    background:linear-gradient(90deg,transparent,var(--card-border),transparent);
    margin-bottom:28px;
}

.alert{
    padding:12px 14px;border-radius:12px;
    font-size:13px;margin-bottom:18px;
}
.alert.error{background:var(--error-bg);border:1px solid var(--error-border);color:var(--error-text);}
.alert.success{background:var(--success-bg);border:1px solid var(--success-border);color:var(--success-text);}

/* Grid de dos columnas */
.fields-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:0 16px;
}
.field{margin-bottom:16px;}
.field.full{grid-column:1/-1;}

label{
    display:block;font-size:12px;
    color:var(--text-muted);margin-bottom:8px;
    font-weight:600;letter-spacing:.6px;text-transform:uppercase;
}

.input-wrap{position:relative;}

.icon-left{
    position:absolute;left:14px;top:50%;
    transform:translateY(-50%);
    opacity:.55;pointer-events:none;transition:.2s;z-index:2;
}
.input-wrap:focus-within .icon-left{opacity:1;}

.form-input,
select.form-input{
    width:100%;height:50px;
    border-radius:13px;
    border:1px solid var(--input-border);
    background:var(--input-bg);
    color:var(--text-primary);
    font-size:14.5px;
    font-family:'DM Sans',sans-serif;
    padding:0 14px 0 44px;
    outline:none;transition:.2s;
    appearance:none;-webkit-appearance:none;
}

.form-input::placeholder{color:rgba(240,237,232,.32);}

.form-input:focus,
select.form-input:focus{
    border-color:var(--accent);
    background:rgba(255,255,255,.09);
    box-shadow:0 0 0 3px var(--input-focus);
}

select.form-input{
    cursor:pointer;
    padding-right:36px;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23C8A96E' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 14px center;
}

select.form-input option{background:#1a1510;color:var(--text-primary);}

.has-toggle .form-input{padding-right:48px;}

.toggle-pass{
    position:absolute;right:10px;top:50%;transform:translateY(-50%);
    width:34px;height:34px;border:none;border-radius:10px;
    background:transparent;color:var(--text-muted);
    cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s;
}
.toggle-pass:hover{color:var(--text-primary);background:rgba(255,255,255,.06);}

.btn-submit{
    width:100%;height:50px;margin-top:10px;
    border:none;border-radius:13px;cursor:pointer;
    font-size:15px;font-weight:700;
    background:linear-gradient(135deg,var(--accent),var(--accent-dark));
    color:#1a120a;
    box-shadow:0 8px 22px rgba(200,169,110,.28);
    transition:.2s;
}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 12px 28px rgba(200,169,110,.35);}

.card-footer{
    margin-top:24px;text-align:center;
    color:var(--text-muted);font-size:12px;
}
.card-footer a{color:var(--accent);text-decoration:none;font-weight:600;}
.card-footer a:hover{text-decoration:underline;}

/* Responsive: una columna en pantallas pequeñas */
@media(max-width:480px){
    .card{padding:32px 22px;}
    .fields-grid{grid-template-columns:1fr;}
    .field.full{grid-column:1;}
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
        <h1>Crear Cuenta</h1>
        <p>Completa el formulario para registrarte</p>
    </div>

    <div class="divider"></div>

    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] == 1): ?>
            <div class="alert error">El correo ya está registrado. Usa uno diferente.</div>
        <?php elseif ($_GET['error'] == 2): ?>
            <div class="alert error">Las contraseñas no coinciden. Inténtalo de nuevo.</div>
        <?php elseif ($_GET['error'] == 3): ?>
            <div class="alert error">Todos los campos obligatorios deben completarse.</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert success">¡Cuenta creada exitosamente! Ya puedes iniciar sesión.</div>
    <?php endif; ?>

    <form action="../controllers/register.php" method="POST" autocomplete="off">

        <div class="fields-grid">

            <!-- Nombre completo -->
            <div class="field full">
                <label for="nombre">Nombre completo</label>
                <div class="input-wrap">
                    <svg class="icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input type="text" id="nombre" name="nombre" class="form-input"
                           placeholder="Juan Pérez" required>
                </div>
            </div>

            <!-- Correo -->
            <div class="field full">
                <label for="correo">Correo electrónico</label>
                <div class="input-wrap">
                    <svg class="icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M22 7L12 14L2 7"/>
                    </svg>
                    <input type="email" id="correo" name="correo" class="form-input"
                           placeholder="tu@correo.com" required>
                </div>
            </div>

            <!-- Teléfono -->
<div class="field full">
    <label for="telefono">Teléfono</label>
                <div class="input-wrap">
                    <svg class="icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.76a16 16 0 0 0 6.29 6.29l.94-.94a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    <input type="text" id="telefono" name="telefono" class="form-input"
                           placeholder="987654321" maxlength="9"
                           oninput="this.value=this.value.replace(/\D/g,'')">
                </div>
            </div>
            <!-- Dirección -->
            <div class="field full">
                <label for="direccion">Dirección <span style="opacity:.45;font-weight:400;text-transform:none;letter-spacing:0">(opcional)</span></label>
                <div class="input-wrap">
                    <svg class="icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <input type="text" id="direccion" name="direccion" class="form-input"
                           placeholder="Av. Lima 123, Lima">
                </div>
            </div>

            <!-- Contraseña -->
            <div class="field">
                <label for="pass">Contraseña</label>
                <div class="input-wrap has-toggle">
                    <svg class="icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <input type="password" id="pass" name="pass" class="form-input"
                           placeholder="••••••••" required minlength="6">
                    <button type="button" class="toggle-pass" onclick="togglePass('pass','eye1')">
                        <svg id="eye1" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Confirmar contraseña -->
            <div class="field">
                <label for="pass_confirm">Confirmar</label>
                <div class="input-wrap has-toggle">
                    <svg class="icon-left" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C8A96E" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <input type="password" id="pass_confirm" name="pass_confirm" class="form-input"
                           placeholder="••••••••" required minlength="6">
                    <button type="button" class="toggle-pass" onclick="togglePass('pass_confirm','eye2')">
                        <svg id="eye2" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

        </div><!-- /fields-grid -->

        <button type="submit" class="btn-submit">Crear Cuenta</button>

    </form>

    <div class="card-footer">
        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
    </div>

</div>
</div>

<script>
const eyeOpen  = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
const eyeClosed= `<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C5 20 1 12 1 12a21.8 21.8 0 0 1 5.06-5.94"/><path d="M9.9 4.24A10.6 10.6 0 0 1 12 4c7 0 11 8 11 8a22.3 22.3 0 0 1-2.17 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;

function togglePass(inputId, iconId){
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if(input.type === "password"){
        input.type = "text";
        icon.innerHTML = eyeClosed;
    } else {
        input.type = "password";
        icon.innerHTML = eyeOpen;
    }
}

// Validación cliente: contraseñas coinciden
document.querySelector("form").addEventListener("submit", function(e){
    const p1 = document.getElementById("pass").value;
    const p2 = document.getElementById("pass_confirm").value;
    if(p1 !== p2){
        e.preventDefault();
        const existing = document.querySelector(".alert.error");
        if(existing) existing.remove();
        const alert = document.createElement("div");
        alert.className = "alert error";
        alert.textContent = "Las contraseñas no coinciden. Inténtalo de nuevo.";
        document.querySelector(".divider").after(alert);
        document.getElementById("pass").focus();
    }
});
</script>

</body>
</html>
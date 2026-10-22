<?php
$titulo_pestana = "Acerca de";
include 'includes/header.php';
?>

<section class="hero">
  <div class="hero-texto">
    <h1>Bienvenido a Clínica Salud+</h1>
    <p>Más de 10 años brindando atención médica cercana, humana y de calidad
       a nuestra comunidad. Tu salud es nuestra prioridad.</p>
  </div>
  <div class="hero-icono">
    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M20 8V22C20 27.5228 24.4772 32 30 32C35.5228 32 40 27.5228 40 22V8"
            stroke="#8da9c4" stroke-width="3" stroke-linecap="round"/>
      <line x1="20" y1="8" x2="20" y2="4" stroke="#8da9c4" stroke-width="3" stroke-linecap="round"/>
      <line x1="40" y1="8" x2="40" y2="4" stroke="#8da9c4" stroke-width="3" stroke-linecap="round"/>
      <path d="M30 32V42C30 48 34.5 52 40 52C45.5 52 50 48 50 42V38"
            stroke="#8da9c4" stroke-width="3" stroke-linecap="round"/>
      <circle cx="50" cy="38" r="5" stroke="#8da9c4" stroke-width="3"/>
      <circle cx="50" cy="38" r="1.6" fill="#8da9c4"/>
    </svg>
  </div>
</section>

<div class="tarjetas">
  <div class="tarjeta">
    <div class="icono">🩺</div>
    <h3>Nuestra misión</h3>
    <p>Brindar servicios médicos accesibles y de alta calidad, con calidez humana en cada consulta.</p>
  </div>
  <div class="tarjeta">
    <div class="icono">👨‍⚕️</div>
    <h3>Nuestro equipo</h3>
    <p>Contamos con médicos especialistas certificados en distintas áreas de la salud.</p>
  </div>
  <div class="tarjeta">
    <div class="icono">🏥</div>
    <h3>Instalaciones</h3>
    <p>Espacios modernos y equipados para brindar diagnósticos y tratamientos oportunos.</p>
  </div>
  <div class="tarjeta">
    <div class="icono">📋</div>
    <h3>Historial digital</h3>
    <p>Gestionamos el registro de pacientes de forma organizada, rápida y segura.</p>
  </div>
</div>

<div class="info-clinica">
  <div>
    <h4>Dirección</h4>
    <p>Av. Ricardo J. Alfaro de la Guardia, Ciudad</p>
  </div>
  <div>
    <h4>Horario de atención</h4>
    <p>Lunes a viernes: 7:00 am – 6:00 pm<br>Sábados: 8:00 am – 1:00 pm</p>
  </div>
  <div>
    <h4>Teléfono</h4>
    <p>+507 306-222-456</p>
  </div>
  <div>
    <h4>Correo</h4>
    <p>contactoinfo@clinicasaludmas.com</p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

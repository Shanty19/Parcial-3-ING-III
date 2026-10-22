// Menú lateral desplegable en tabletas y celulares (solo interacción visual)
(function () {
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('overlay');
  var boton = document.getElementById('btnMenu');

  function abrir() { sidebar.classList.add('abierto'); overlay.classList.add('visible'); }
  function cerrar() { sidebar.classList.remove('abierto'); overlay.classList.remove('visible'); }

  boton.addEventListener('click', function () {
    sidebar.classList.contains('abierto') ? cerrar() : abrir();
  });
  overlay.addEventListener('click', cerrar);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrar(); });
  window.addEventListener('resize', function () { if (window.innerWidth > 900) cerrar(); });
})();
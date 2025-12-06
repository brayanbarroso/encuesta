// Verificar que el usuario está autenticado
(function () {
  const IDLE_LIMIT_MS = 5 * 60 * 1000; // 5 minutos
  let idleTimer = null;

  // Llama al logout en el servidor y redirige al login
  async function doLogout() {
    try {
      await fetch("../server/logout.php");
    } catch (err) {
      console.error("Error llamando logout:", err);
    } finally {
      window.location.href = "../login";
    }
  }

  // Reiniciar el timer de inactividad
  function resetIdleTimer() {
    if (idleTimer) clearTimeout(idleTimer);
    idleTimer = setTimeout(() => {
      // Inactividad detectada
      doLogout();
    }, IDLE_LIMIT_MS);
  }

  // Registrar eventos que cuentan como actividad
  ["mousemove", "mousedown", "keydown", "touchstart", "click"].forEach(
    (evt) => {
      window.addEventListener(evt, resetIdleTimer, { passive: true });
    }
  );

  // Comprobación inicial del servidor (sigue existiendo)
  (async function () {
    try {
      const res = await fetch("../server/check_session.php");
      if (!res.ok) {
        // No autenticado o sesión expirada
        window.location.href = "../login";
        return;
      }
      // Si está ok, iniciar el timer de inactividad
      resetIdleTimer();
    } catch (err) {
      console.error("Error verificando sesión:", err);
      window.location.href = "../login";
    }
  })();
})();

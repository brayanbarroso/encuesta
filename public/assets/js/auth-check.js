// Verificar que el usuario está autenticado
(async function () {
  try {
    const res = await fetch("../server/check_session.php");
    if (!res.ok) {
      // No autenticado o sesión expirada
      window.location.href = "login.html";
    }
  } catch (err) {
    console.error("Error verificando sesión:", err);
    window.location.href = "login.html";
  }
})();

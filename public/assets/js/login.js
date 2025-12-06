document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const user = document.getElementById("user").value.trim();
  const pass = document.getElementById("pass").value.trim();
  const alertEl = document.getElementById("alert");
  const loadingEl = document.getElementById("loading");

  if (user === "" || pass === "") {
    alertEl.textContent = "Por favor completa todos los campos";
    alertEl.classList.add("show");
    return;
  }

  loadingEl.style.display = "block";
  alertEl.classList.remove("show");

  try {
    const res = await fetch("./server/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ user, pass }),
    });

    const data = await res.json();

    if (data.success) {
      // Redirigir a admin
      window.location.href = "./public/admin";
    } else {
      alertEl.textContent = data.message || "Error en autenticación";
      alertEl.classList.add("show");
    }
  } catch (err) {
    console.error("Error:", err);
    alertEl.textContent = "Error de conexión: " + err.message;
    alertEl.classList.add("show");
  } finally {
    loadingEl.style.display = "none";
  }
});

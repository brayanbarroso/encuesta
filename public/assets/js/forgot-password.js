// public/assets/js/forgot-password.js
document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search);
  const token = urlParams.get("token");

  if (token) {
    // Validar el token y mostrar Step 2
    validateTokenAndShowResetForm(token);
  }

  // Step 1: Request reset
  const requestForm = document.getElementById("requestForm");
  if (requestForm) {
    requestForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      await handleRequestReset();
    });
  }

  // Step 2: Reset password
  const resetForm = document.getElementById("resetForm");
  if (resetForm) {
    resetForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      await handleResetPassword();
    });
  }
});

async function validateTokenAndShowResetForm(token) {
  try {
    const response = await fetch(
      `./server/validate_reset_token.php?token=${encodeURIComponent(token)}`
    );
    const data = await response.json();

    if (data.valid) {
      // Ocultar Step 1, mostrar Step 2
      document.getElementById("step1").style.display = "none";
      document.getElementById("step2").style.display = "block";
      document.getElementById("resetToken").value = token;
    } else {
      showError("Enlace inválido o expirado", "reset-alert");
      setTimeout(() => {
        window.location.href = "./login";
      }, 3000);
    }
  } catch (error) {
    console.error("Error validando token:", error);
    showError("Error al validar el enlace", "reset-alert");
  }
}

async function handleRequestReset() {
  const identifier = document.getElementById("identifier").value.trim();
  const alertEl = document.getElementById("alert");
  const successEl = document.getElementById("success-alert");
  const loadingEl = document.getElementById("loading");
  const btn = document.querySelector("#requestForm button");

  if (identifier === "") {
    showError("Ingresa tu usuario o correo", "alert");
    return;
  }

  btn.disabled = true;
  loadingEl.style.display = "block";
  alertEl.style.display = "none";
  successEl.style.display = "none";

  try {
    const response = await fetch("./server/request_password_reset.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ identifier }),
    });

    const data = await response.json();

    if (data.success) {
      successEl.textContent = data.message;
      successEl.style.display = "block";
      document.getElementById("requestForm").reset();

      // Si existe debug_link (modo desarrollo), mostrar con opciones de copiar/abrir
      if (data.debug_link) {
        const debugContainer = document.getElementById("debugLinkContainer");
        const debugInput = document.getElementById("debugLinkInput");
        const copyBtn = document.getElementById("copyDebugLink");
        const openBtn = document.getElementById("openDebugLink");

        debugInput.value = data.debug_link;
        openBtn.href = data.debug_link;
        debugContainer.style.display = "block";

        // Botón copiar con feedback visual
        copyBtn.addEventListener("click", () => {
          debugInput.select();
          document.execCommand("copy");
          const originalText = copyBtn.textContent;
          copyBtn.textContent = "✓ Copiado";
          copyBtn.classList.remove("btn-outline-info");
          copyBtn.classList.add("btn-success");
          setTimeout(() => {
            copyBtn.textContent = originalText;
            copyBtn.classList.add("btn-outline-info");
            copyBtn.classList.remove("btn-success");
          }, 2000);
        });
      }
    } else {
      showError(data.message || "Error en la solicitud", "alert");
    }
  } catch (error) {
    console.error("Error:", error);
    showError("Error al procesar tu solicitud", "alert");
  } finally {
    btn.disabled = false;
    loadingEl.style.display = "none";
  }
}

async function handleResetPassword() {
  const token = document.getElementById("resetToken").value;
  const newPassword = document.getElementById("newPassword").value.trim();
  const confirmPassword = document
    .getElementById("confirmPassword")
    .value.trim();
  const alertEl = document.getElementById("reset-alert");
  const loadingEl = document.getElementById("reset-loading");
  const btn = document.querySelector("#resetForm button");

  if (newPassword === "" || confirmPassword === "") {
    showError("Ambas contraseñas son requeridas", "reset-alert");
    return;
  }

  if (newPassword !== confirmPassword) {
    showError("Las contraseñas no coinciden", "reset-alert");
    return;
  }

  if (newPassword.length < 6) {
    showError("La contraseña debe tener al menos 6 caracteres", "reset-alert");
    return;
  }

  btn.disabled = true;
  loadingEl.style.display = "block";
  alertEl.style.display = "none";

  try {
    const response = await fetch("./server/reset_password.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        token,
        password: newPassword,
        confirm_password: confirmPassword,
      }),
    });

    const data = await response.json();

    if (data.success) {
      // Mostrar mensaje de éxito y redirigir a login
      const successEl = document.createElement("div");
      successEl.className = "alert alert-success";
      successEl.textContent = data.message;
      document
        .getElementById("resetForm")
        .parentNode.insertBefore(
          successEl,
          document.getElementById("resetForm")
        );

      setTimeout(() => {
        window.location.href = "./login";
      }, 2000);
    } else {
      showError(
        data.message || "Error al restablecer la contraseña",
        "reset-alert"
      );
    }
  } catch (error) {
    console.error("Error:", error);
    showError("Error al restablecer la contraseña", "reset-alert");
  } finally {
    btn.disabled = false;
    loadingEl.style.display = "none";
  }
}

function showError(message, alertId) {
  const alertEl = document.getElementById(alertId);
  alertEl.textContent = message;
  alertEl.style.display = "block";
}

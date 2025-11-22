document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('surveyForm');
    const identInput = document.getElementById('identificacion');
    const userCheckText = document.getElementById('userCheck');
    const successMsg = document.getElementById('successMsg');
    const submitBtn = document.getElementById('submitBtn');
  
    // helper para enlazar toggles: cuando pregunta X == 'NO' => mostrar elemento
    function toggleSub(questionName, elementId, showIfValue = 'NO') {
      const radios = document.getElementsByName(questionName);
      radios.forEach(radio => {
        radio.addEventListener('change', () => {
          const selected = document.querySelector(`input[name="${questionName}"]:checked`);
          if (selected && selected.value === showIfValue) {
            document.getElementById(elementId).classList.remove('d-none');
          } else {
            document.getElementById(elementId).classList.add('d-none');
          }
        });
      });
    }
  
    // En este formulario: q1, q2, q4, q5, q6 tienen subpreguntas si responden NO
    toggleSub('q2', 'q2_sub_no', 'NO');
    toggleSub('q3', 'q3_sub_no', 'NO');
    toggleSub('q4', 'q4_sub_no', 'NO');
    toggleSub('q5', 'q5_sub_no', 'NO');
    toggleSub('q7', 'q7_sub_no', 'NO');

     // Validación de pregunta 6 (al menos una opción)
    const checkboxesP6 = document.querySelectorAll('input[name="q6"]');
    const ningunoCheckbox = document.getElementById('q6_ninguno');
        
    ningunoCheckbox.addEventListener("change", function () {
      if (this.checked) {
        checkboxesP6.forEach((checkbox) => {
          if (checkbox !== this) checkbox.checked = false;
        });
      }
    });

    checkboxesP6.forEach((checkbox) => {
      if (checkbox !== ningunoCheckbox) {
        checkbox.addEventListener("change", function () {
          if (this.checked) {
            ningunoCheckbox.checked = false;
          }
        });
      }
    });
  
    // chequeo existencia usuario (POST JSON)
    async function checkUserExists(ident) {
      if (!ident) return false;
    
      try {
        const res = await fetch('./server/check_user.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({identificacion: ident})
        });
        const json = await res.json();
        return json.exists === true;
      } catch (e) {
        console.error('Error check user', e);
        return false;
      }
    }
  
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      userCheckText.classList.add('d-none');
      successMsg.classList.add('d-none');
  
      // HTML5 validation
      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }
  
      const ident = identInput.value.trim();
      submitBtn.disabled = true;
      submitBtn.textContent = 'Validando...';
  
      const exists = await checkUserExists(ident);
      if (!exists) {
        Swal.fire({
          icon: 'error',
          title: 'Asociado no encontrado',
          text: 'El asociado no está registrado y no puede responder la encuesta.',
          confirmButtonColor: '#d33'
      }).then(() => {
          // 🟩 PONER FOCO EN EL CAMPO IDENTIFICACIÓN
          const input = document.getElementById('identificacion');

    // Hacer scroll animado hacia el input
    input.scrollIntoView({
        behavior: "smooth",
        block: "center" // o "start"
    });

    // Asegurar focus después del scroll
    setTimeout(() => {
        input.focus();
    }, 400); // tiempo suficiente para el smooth scroll
      });

        form.reset();
        // submitBtn.disabled = false;
        // submitBtn.textContent = 'Enviar encuesta';
        return;
      }
  
      // Construir objeto de respuestas (mapea cada campo)
      const answers = {
        q1: document.querySelector('input[name="q1"]:checked')?.value || '',
        q2: document.querySelector('input[name="q2"]:checked')?.value || '',
        q2_no_comment: document.getElementById('q2_no_comment')?.value?.trim() || '',
        q3: document.querySelector('input[name="q3"]:checked')?.value || '',
        q3_no_comment: document.getElementById('q3_no_comment')?.value.trim() || '',
        q4: document.querySelector('input[name="q4"]:checked')?.value || '',
        q4_no_comment: document.getElementById('q4_no_comment')?.value?.trim() || '',
        q5: document.querySelector('input[name="q5"]:checked')?.value || '',
        q5_no_comment: document.getElementById('q5_no_comment')?.value?.trim() || '',
        // q6: document.querySelector('input[name="q6"]:checked')?.value || '',
        q6: Array.from(document.querySelectorAll('input[name="q6"]:checked')).map(cb => cb.value),
        q7: document.querySelector('input[name="q7"]:checked')?.value || '',
        q7_no_comment: document.getElementById('q7_no_comment')?.value?.trim() || '',
        fortalezas: document.getElementById('fortalezas')?.value?.trim() || '',
        oportunidades: document.getElementById('oportunidades')?.value?.trim() || '',
        debilidades: document.getElementById('debilidades')?.value?.trim() || '',
        amenazas: document.getElementById('amenazas')?.value?.trim() || '',
        autorizado: document.getElementById('autorizo')?.checked ? 'SI' : 'NO'
      };
  
      submitBtn.textContent = 'Enviando...';
  
      try {
        const res = await fetch('./server/save_response.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({identificacion: ident, answers})
        });
        const json = await res.json();
        if (json.success) {
          Swal.fire({
            icon: 'success',
            title: '¡Encuesta enviada!',
             html: `
                    <h4>Gracias por tu participación</h4>
                    <p><strong>Número de rifa:</strong>${String(json.id).padStart(3, '0')}</p>
    `,
            confirmButtonColor: '#2F9E44'
        });

        form.reset();
        form.classList.remove('was-validated');
      } else {
          if (json.message.includes("Ya has respondido")) {
            Swal.fire({
              icon: 'warning',
              title: 'Encuesta ya respondida',
    html: `
        <p>Ya completaste esta encuesta anteriormente.</p>
        <p><strong>Su número de rifa es:</strong>${String(json.id).padStart(3, '0')}</p>
    `,
              confirmButtonColor: '#F6C343'
          });

          form.reset();
          form.classList.remove('was-validated');
          } else {
              alert('Error al guardar: ' + (json.message || 'Desconocido'));
          }
      }
      } catch (err) {
        alert('Error de red: ' + err.message);
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Enviar encuesta';
      }
    });
  });
  
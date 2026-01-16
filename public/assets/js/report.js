(async function () {
  const res = await fetch("../server/get_report_data.php");
  if (!res.ok) {
    document.getElementById("summary").textContent =
      "Error cargando reporte: HTTP " + res.status;
    return;
  }
  const data = await res.json();

  // Guardar datos para el generador de PDF
  if (window.setReportData) {
    window.setReportData(data);
  }

  document.getElementById(
    "summary"
  ).textContent = `Respuestas totales: ${data.total_responses}`;

  // Mapeo de preguntas con sus nombres
  const questionLabels = {
    q1: "Conoce usted el portafolio de servicios que brinda COOEDUCORD",
    q2: "El portafolio de servicios, está acorde con sus necesidades",
    q3: "cree usted que las actividades realizadas, satisfacen las necesidades de los asociados",
    q4: "Consulta usted las plataformas virtuales (página web y redes sociales) para enterarse de noticias, eventos y gestión del portafolio de servicios",
    q5: "Estaría usted dispuesto a utilizar el servicio de oficina virtual para gestionar los servicios ofrecidos por la Cooperativa",
    q7: "Recomendaría usted los servicios de la Cooperativa a los nuevos docentes vinculados al sector del magisterio para afiliarse",
  };

  // Yes/No: crear un gráfico por pregunta
  const yesno = data.yesno || {};
  const yesnoContainer = document.getElementById("yesno-charts");
  const yesnoCharts = [];
  Object.keys(yesno)
    .sort()
    .filter((q) => q !== "q6") // Excluir q6 (no es sí/no)
    .forEach((q) => {
      const info = yesno[q];
      const title = questionLabels[q] || q.toUpperCase();

      const col = document.createElement("div");
      col.className = "yesno-chart-col";
      const card = document.createElement("div");
      card.className = "card p-3";

      // Título
      const h = document.createElement("h5");
      h.textContent = title;
      h.className = "mb-3";
      card.appendChild(h);

      // Conteo de respuestas (Sí/No)
      const countRow = document.createElement("div");
      countRow.className = "row mb-3";

      const countYesCol = document.createElement("div");
      countYesCol.className = "col-6";
      const countYesCard = document.createElement("div");
      countYesCard.className =
        "bg-success bg-opacity-10 p-2 rounded text-center";
      countYesCard.innerHTML = `<strong>Sí</strong><br><span style="font-size: 1.5rem; color: #28a745;">${info.counts.yes}</span>`;
      countYesCol.appendChild(countYesCard);
      countRow.appendChild(countYesCol);

      const countNoCol = document.createElement("div");
      countNoCol.className = "col-6";
      const countNoCard = document.createElement("div");
      countNoCard.className = "bg-danger bg-opacity-10 p-2 rounded text-center";
      countNoCard.innerHTML = `<strong>No</strong><br><span style="font-size: 1.5rem; color: #dc3545;">${info.counts.no}</span>`;
      countNoCol.appendChild(countNoCard);
      countRow.appendChild(countNoCol);

      card.appendChild(countRow);

      // Gráfico
      const canvas = document.createElement("canvas");
      // tag the canvas with the question key so PDF mapping is robust
      canvas.id = `yesno-${q}`;
      canvas.dataset.qkey = q;
      card.appendChild(canvas);

      // Tabla de porcentajes (después del gráfico)
      const percentTableWrapper = document.createElement("div");
      percentTableWrapper.style.width = "fit-content";
      percentTableWrapper.style.margin = "0 auto";

      const percentTable = document.createElement("table");
      percentTable.className = "table table-sm table-bordered mb-0";
      percentTable.style.fontSize = "0.875rem";
      percentTable.style.width = "250px";
      const thead = document.createElement("thead");
      thead.innerHTML = `
        <tr style="background-color: #f8f9fa;">
          <th>Respuesta</th>
          <th style="text-align: right;">Porcentaje</th>
        </tr>
      `;
      percentTable.appendChild(thead);
      const tbody = document.createElement("tbody");
      tbody.innerHTML = `
        <tr>
          <td><strong>Sí</strong></td>
          <td style="text-align: right; color: #28a745;"><strong>${
            info.percent.yes
          }%</strong></td>
        </tr>
        <tr>
          <td><strong>No</strong></td>
          <td style="text-align: right; color: #dc3545;"><strong>${
            info.percent.no
          }%</strong></td>
        </tr>
        ${
          info.percent.other > 0
            ? `
        <tr>
          <td><strong>Otro</strong></td>
          <td style="text-align: right; color: #6c757d;"><strong>${info.percent.other}%</strong></td>
        </tr>
        `
            : ""
        }
      `;
      percentTable.appendChild(tbody);
      percentTableWrapper.appendChild(percentTable);
      card.appendChild(percentTableWrapper);

      col.appendChild(card);
      yesnoContainer.appendChild(col);

      const labels = ["Sí", "No"];
      const values = [info.percent.yes || 0, info.percent.no || 0];

      const ch = new Chart(canvas.getContext("2d"), {
        type: "doughnut",
        data: {
          labels,
          datasets: [
            {
              data: values,
              backgroundColor: ["#28a745", "#dc3545"],
            },
          ],
        },
        options: {
          plugins: { legend: { position: "bottom" } },
          responsive: true,
        },
      });
      yesnoCharts.push(ch);
    });

  // FODA: mostrar top mentions lists
  const foda = data.foda || {};
  const fodaContainer = document.getElementById("foda");
  ["fortalezas", "oportunidades", "debilidades", "amenazas"].forEach((k) => {
    const col = document.createElement("div");
    col.className = "col-md-6";
    const card = document.createElement("div");
    card.className = "card p-3";
    const h = document.createElement("h6");
    h.textContent = k.charAt(0).toUpperCase() + k.slice(1);
    card.appendChild(h);

    const ul = document.createElement("ol");
    const items = foda[k] || {};
    let i = 0;
    for (const [text, count] of Object.entries(items)) {
      if (i >= 10) break;
      const li = document.createElement("li");
      li.textContent = `${text} — ${count}`;
      ul.appendChild(li);
      i++;
    }
    if (i === 0) {
      const p = document.createElement("p");
      p.className = "text-muted";
      p.textContent = "No hay menciones";
      card.appendChild(p);
    } else card.appendChild(ul);

    col.appendChild(card);
    fodaContainer.appendChild(col);
  });
  // Services chart
  const services = data.services || {};
  const svcLabels = Object.keys(services).slice(0, 20);
  const svcValues = svcLabels.map((l) => services[l]);

  const svcCtx = document.getElementById("servicesChart").getContext("2d");
  const servicesChart = new Chart(svcCtx, {
    type: "bar",
    data: {
      labels: svcLabels,
      datasets: [
        { label: "Veces", data: svcValues, backgroundColor: "#007bff" },
      ],
    },
    options: {
      indexAxis: "y",
      responsive: true,
      plugins: { legend: { display: false } },
    },
  });
})();

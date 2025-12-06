(function () {
  const tbody = document.querySelector("#responsesTable tbody");
  const paginationEl = document.getElementById("pagination");
  const pageSizeSelect = document.getElementById("pageSizeSelect");
  const infoEl = document.getElementById("tableInfo");

  let responses = [];
  let currentPage = 1;

  const safe = (v) =>
    Array.isArray(v)
      ? v.filter(Boolean).join(", ")
      : v === null || v === undefined
      ? ""
      : v;

  async function fetchResponses() {
    try {
      const res = await fetch("../server/get_responses_table.php");
      if (!res.ok) throw new Error("HTTP " + res.status);
      const data = await res.json();
      responses = Array.isArray(data) ? data : [];
      renderPage(1);
    } catch (err) {
      console.error("Error loading responses", err);
      tbody.innerHTML = `<tr><td colspan="19" class="text-danger">Error cargando respuestas: ${err.message}</td></tr>`;
      infoEl.textContent = "Error cargando datos";
    }
  }

  function renderPage(page) {
    const total = responses.length;
    const pageSize = parseInt(pageSizeSelect.value, 10) || 10;
    const totalPages = Math.max(1, Math.ceil(total / pageSize));

    if (page < 1) page = 1;
    if (page > totalPages) page = totalPages;
    currentPage = page;

    const start = (currentPage - 1) * pageSize;
    const pageItems = responses.slice(start, start + pageSize);

    renderTableRows(pageItems);
    renderPagination(totalPages);

    if (total === 0) {
      infoEl.textContent = "No hay respuestas";
    } else {
      infoEl.textContent = `Mostrando ${start + 1} - ${Math.min(
        start + pageSize,
        total
      )} de ${total}`;
    }
  }

  function renderTableRows(items) {
    tbody.innerHTML = "";
    if (!items || items.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="19" class="text-muted">No hay respuestas</td></tr>';
      return;
    }

    items.forEach((item) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${safe(item.id)}</td>
        <td>${safe(item.created_at)}</td>
        <td>${safe(item.identificacion)}</td>
        <td>${safe(item.nombre)}</td>

        <td>${safe(item.q1)}</td>

        <td>${safe(item.q2)}</td>
        <td>${safe(item.q2_no_comment)}</td>

        <td>${safe(item.q3)}</td>
        <td>${safe(item.q3_no_comment)}</td>

        <td>${safe(item.q4)}</td>
        <td>${safe(item.q4_no_comment)}</td>

        <td>${safe(item.q5)}</td>
        <td>${safe(item.q5_no_comment)}</td>

        <td>${safe(item.q6)}</td>

        <td>${safe(item.q7)}</td>
        <td>${safe(item.q7_no_comment)}</td>

        <td>${safe(item.fortalezas)}</td>
        <td>${safe(item.oportunidades)}</td>
        <td>${safe(item.debilidades)}</td>
        <td>${safe(item.amenazas)}</td>

        <td>${safe(item.autorizado)}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  // Logout handler

  // paginacion
  function renderPagination(totalPages) {
    paginationEl.innerHTML = "";

    const createPageItem = (label, page, disabled = false, active = false) => {
      const li = document.createElement("li");
      li.className =
        "page-item" + (disabled ? " disabled" : "") + (active ? " active" : "");
      const a = document.createElement("a");
      a.className = "page-link";
      a.href = "#";
      a.dataset.page = page;
      a.textContent = label;
      li.appendChild(a);
      return li;
    };

    // Prev
    paginationEl.appendChild(
      createPageItem("«", currentPage - 1, currentPage === 1)
    );

    // Simple range: show up to 9 page buttons centered
    const maxButtons = 9;
    let start = 1;
    let end = totalPages;
    if (totalPages > maxButtons) {
      const half = Math.floor(maxButtons / 2);
      start = Math.max(1, currentPage - half);
      end = start + maxButtons - 1;
      if (end > totalPages) {
        end = totalPages;
        start = end - maxButtons + 1;
      }
    }

    for (let i = start; i <= end; i++) {
      paginationEl.appendChild(createPageItem(i, i, false, i === currentPage));
    }

    // Next
    paginationEl.appendChild(
      createPageItem("»", currentPage + 1, currentPage === totalPages)
    );
  }

  // Click delegation
  paginationEl.addEventListener("click", (e) => {
    e.preventDefault();
    const a = e.target.closest("a.page-link");
    if (!a) return;
    const page = parseInt(a.dataset.page, 10);
    if (!isNaN(page)) renderPage(page);
  });

  pageSizeSelect.addEventListener("change", () => renderPage(1));

  // Init
  fetchResponses();
})();


// Variable global para almacenar los datos del reporte
let reportData = null;

// Función para guardar los datos del reporte
window.setReportData = function(data) {
  reportData = data;
};

// Esperamos a que el botón esté disponible
window.addEventListener("load", () => {
  const downloadBtn = document.getElementById("downloadPdfBtn");
  if (downloadBtn) {
    downloadBtn.addEventListener("click", generatePDF);
  }
});

async function generatePDF() {
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF("p", "mm", "a4");
  const pageWidth = pdf.internal.pageSize.getWidth();
  const pageHeight = pdf.internal.pageSize.getHeight();
  const margin = 15;
  let yPosition = margin;

  // Deshabilitar botón y mostrar mensaje
  const downloadBtn = document.getElementById("downloadPdfBtn");
  const originalText = downloadBtn.innerHTML;
  downloadBtn.disabled = true;
  downloadBtn.innerHTML = "Generando PDF...";

  try {
    // Esperar un momento para asegurar que todos los gráficos estén renderizados
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // Verificar que existan gráficos para procesar
    const yesnoChartElements = document.querySelectorAll('[id^="yesno-"]');
    if (yesnoChartElements.length === 0) {
      throw new Error("No se encontraron gráficos para generar el PDF. Por favor espere a que carguen los datos.");
    }
    
    // Título del documento
    pdf.setFontSize(18);
    pdf.setFont(undefined, "bold");
    pdf.text("Análisis de Encuesta Evaluación Plan Estratégico 2021 - 2025", pageWidth / 2, yPosition, { align: "center" });
    pdf.text("e insumos para el periodo 2026 - 2030", pageWidth / 2, yPosition + 7, { align: "center" });
    yPosition += 20; // Espacio suficiente después del título de dos líneas

    // Resumen
    const summaryText = document.getElementById("summary").textContent;
    pdf.setFontSize(12);
    pdf.setFont(undefined, "normal");
    pdf.text(summaryText, margin, yPosition);
    yPosition += 10;

    // Mapeo de preguntas con sus nombres
    const questionLabels = {
      q1: "Conoce usted el portafolio de servicios que brinda COOEDUCORD",
      q2: "El portafolio de servicios, está acorde con sus necesidades",
      q3: "Cree usted que las actividades realizadas, satisfacen las necesidades de los asociados",
      q4: "Consulta usted las plataformas virtuales (página web y redes sociales) para enterarse de noticias, eventos y gestión del portafolio de servicios",
      q5: "Estaría usted dispuesto a utilizar el servicio de oficina virtual para gestionar los servicios ofrecidos por la Cooperativa",
      q7: "Recomendaría usted los servicios de la Cooperativa a los nuevos docentes vinculados al sector del magisterio para afiliarse",
    };

    // Procesar cada pregunta de Sí/No
    const yesnoCharts = document.querySelectorAll('[id^="yesno-"]');
    for (let i = 0; i < yesnoCharts.length; i++) {
      const canvas = yesnoCharts[i];
      const qkey = canvas.dataset.qkey || canvas.id.replace('yesno-', '');
      const title = questionLabels[qkey] || (qkey ? qkey.toUpperCase() : `Pregunta ${i + 1}`);

      // Verificar si necesitamos una nueva página
      if (yPosition > pageHeight - 100) {
        pdf.addPage();
        yPosition = margin;
      }

      // Título de la pregunta
      pdf.setFontSize(11);
      pdf.setFont(undefined, "bold");
      const splitTitle = pdf.splitTextToSize(title, pageWidth - 2 * margin);
      pdf.text(splitTitle, margin, yPosition);
      yPosition += splitTitle.length * 5 + 3;

      // Obtener datos desde reportData (más confiable que leer el DOM)
      let yesCount = "0";
      let noCount = "0";
      let yesPercent = "0%";
      let noPercent = "0%";
      
      if (reportData && reportData.yesno && reportData.yesno[qkey]) {
        const questionData = reportData.yesno[qkey];
        yesCount = String(questionData.counts.yes || 0);
        noCount = String(questionData.counts.no || 0);
        yesPercent = String(questionData.percent.yes || 0) + "%";
        noPercent = String(questionData.percent.no || 0) + "%";
      } else {
        // Fallback: intentar leer del DOM si no hay datos guardados
        const card = canvas.closest(".card");
        
        if (!card) {
          console.warn(`No se encontró la tarjeta ni datos para ${qkey}`);
          continue;
        }
        
        // Corregir selectores para que coincidan con la estructura HTML
        const yesSpan = card.querySelector(".bg-success span");
        const noSpan = card.querySelector(".bg-danger span");
        
        if (yesSpan) yesCount = yesSpan.textContent.trim();
        if (noSpan) noCount = noSpan.textContent.trim();
        
        // Obtener porcentajes de la tabla existente
        const percentTable = card.querySelector("table tbody");
        const percentRows = percentTable ? percentTable.querySelectorAll("tr") : [];
        
        percentRows.forEach(row => {
          const cells = row.querySelectorAll("td");
          if (cells.length >= 2) {
            const label = cells[0].textContent.trim();
            const value = cells[1].textContent.trim();
            if (label === "Sí") yesPercent = value;
            if (label === "No") noPercent = value;
          }
        });
      }

      // Capturar el gráfico como imagen
      const chartImage = canvas.toDataURL("image/png");
      const chartWidth = 50;
      const chartHeight = 50;
      const chartX = margin;

      pdf.addImage(chartImage, "PNG", chartX, yPosition, chartWidth, chartHeight);

      // Crear tabla de datos al lado del gráfico
      const tableX = chartX + chartWidth + 10;
      const tableY = yPosition;
      
      // Calcular ancho necesario para los encabezados (más compacto)
      pdf.setFontSize(10);
      pdf.setFont(undefined, "bold");
      const headerWidthRespuesta = pdf.getTextWidth("Respuesta");
      const headerWidthCantidad = pdf.getTextWidth("Cantidad");
      const headerWidthPorcentaje = pdf.getTextWidth("Porcentaje");
      
      // Calcular ancho necesario para los datos (usando fuente normal)
      pdf.setFont(undefined, "normal");
      const dataWidthYes = pdf.getTextWidth(yesCount);
      const dataWidthNo = pdf.getTextWidth(noCount);
      const dataWidthYesPct = pdf.getTextWidth(yesPercent);
      const dataWidthNoPct = pdf.getTextWidth(noPercent);
      
      // Ancho de cada columna: máximo entre encabezado y datos
      const col1Width = Math.max(headerWidthRespuesta, Math.max(pdf.getTextWidth("Sí"), pdf.getTextWidth("No"))) + 4; // +4 para padding 2px cada lado
      const col2Width = Math.max(headerWidthCantidad, Math.max(dataWidthYes, dataWidthNo)) + 4;
      const col3Width = Math.max(headerWidthPorcentaje, Math.max(dataWidthYesPct, dataWidthNoPct)) + 4;
      
      // Ancho total de la tabla (suma de columnas)
      const tableWidth = col1Width + col2Width + col3Width;
      const rowHeight = 8;
      
      // Posiciones de las columnas
      const col1X = tableX;
      const col2X = tableX + col1Width;
      const col3X = tableX + col1Width + col2Width;

      // Calcular centros de cada columna para centrar el contenido
      const col1Center = col1X + col1Width / 2;
      const col2Center = col2X + col2Width / 2;
      const col3Center = col3X + col3Width / 2;

      // Encabezados de tabla
      pdf.setFont(undefined, "bold");
      pdf.setFillColor(248, 249, 250);
      pdf.rect(tableX, tableY, tableWidth, rowHeight, "F");
      pdf.setTextColor(0, 0, 0);
      pdf.text("Respuesta", col1Center, tableY + 5.5, { align: "center" });
      pdf.text("Cantidad", col2Center, tableY + 5.5, { align: "center" });
      pdf.text("Porcentaje", col3Center, tableY + 5.5, { align: "center" });

      // Fila Sí
      pdf.setFont(undefined, "normal");
      pdf.setTextColor(40, 167, 69); // Verde
      pdf.text("Sí", col1Center, tableY + rowHeight + 5.5, { align: "center" });
      pdf.setTextColor(0, 0, 0);
      pdf.text(yesCount, col2Center, tableY + rowHeight + 5.5, { align: "center" });
      pdf.setTextColor(40, 167, 69);
      pdf.text(yesPercent, col3Center, tableY + rowHeight + 5.5, { align: "center" });

      // Fila No
      pdf.setTextColor(220, 53, 69); // Rojo
      pdf.text("No", col1Center, tableY + rowHeight * 2 + 5.5, { align: "center" });
      pdf.setTextColor(0, 0, 0);
      pdf.text(noCount, col2Center, tableY + rowHeight * 2 + 5.5, { align: "center" });
      pdf.setTextColor(220, 53, 69);
      pdf.text(noPercent, col3Center, tableY + rowHeight * 2 + 5.5, { align: "center" });

      // Líneas de la tabla
      pdf.setTextColor(0, 0, 0);
      pdf.setDrawColor(200, 200, 200);
      pdf.rect(tableX, tableY, tableWidth, rowHeight * 3);
      pdf.line(tableX, tableY + rowHeight, tableX + tableWidth, tableY + rowHeight);
      pdf.line(tableX, tableY + rowHeight * 2, tableX + tableWidth, tableY + rowHeight * 2);
      pdf.line(col2X, tableY, col2X, tableY + rowHeight * 3);
      pdf.line(col3X, tableY, col3X, tableY + rowHeight * 3);

      yPosition += Math.max(chartHeight, rowHeight * 3) + 10;
    }

    // Nueva página para FODA
    pdf.addPage();
    yPosition = margin;

    pdf.setFontSize(14);
    pdf.setFont(undefined, "bold");
    pdf.text("FODA - Principales menciones", margin, yPosition);
    yPosition += 10;

    // Procesar sección FODA
    const fodaSections = ["fortalezas", "oportunidades", "debilidades", "amenazas"];
    const fodaContainer = document.getElementById("foda");
    const fodaCards = fodaContainer.querySelectorAll(".card");

    fodaSections.forEach((section, index) => {
      if (index === 2 && yPosition > pageHeight / 2) {
        // Nueva página para Debilidades y Amenazas
        pdf.addPage();
        yPosition = margin;
      }

      pdf.setFontSize(12);
      pdf.setFont(undefined, "bold");
      pdf.text(section.charAt(0).toUpperCase() + section.slice(1), margin, yPosition);
      yPosition += 7;

      pdf.setFontSize(10);
      pdf.setFont(undefined, "normal");

      const card = fodaCards[index];
      if (card) {
        const items = card.querySelectorAll("ol li");
        if (items.length > 0) {
          items.forEach((item, idx) => {
            if (yPosition > pageHeight - margin - 10) {
              pdf.addPage();
              yPosition = margin;
            }
            const text = `${idx + 1}. ${item.textContent}`;
            const splitText = pdf.splitTextToSize(text, pageWidth - 2 * margin - 5);
            pdf.text(splitText, margin + 5, yPosition);
            yPosition += splitText.length * 5;
          });
        } else {
          pdf.setTextColor(128, 128, 128);
          pdf.text("No hay menciones", margin + 5, yPosition);
          pdf.setTextColor(0, 0, 0);
          yPosition += 5;
        }
      }
      yPosition += 5;
    });

    // Nueva página para gráfico de servicios
    pdf.addPage();
    yPosition = margin;

    pdf.setFontSize(14);
    pdf.setFont(undefined, "bold");
    pdf.text("Servicios más utilizados (sede recreacional)", margin, yPosition);
    yPosition += 10;

    // Capturar gráfico de servicios
    const servicesCanvas = document.getElementById("servicesChart");
    if (servicesCanvas) {
      const servicesImage = servicesCanvas.toDataURL("image/png");
      const imgWidth = pageWidth - 2 * margin;
      const imgHeight = 120;
      pdf.addImage(servicesImage, "PNG", margin, yPosition, imgWidth, imgHeight);
    }

    // Pie de página en todas las páginas
    const totalPages = pdf.internal.pages.length - 1; // -1 porque el array incluye un elemento vacío
    for (let i = 1; i <= totalPages; i++) {
      pdf.setPage(i);
      pdf.setFontSize(8);
      pdf.setTextColor(128, 128, 128);
      pdf.text(
        `© 2025 Cooeducord - Página ${i} de ${totalPages}`,
        pageWidth / 2,
        pageHeight - 10,
        { align: "center" }
      );
    }

    // Guardar PDF
    const fecha = new Date().toISOString().split("T")[0];
    pdf.save(`Reporte_Encuesta_${fecha}.pdf`);
  } catch (error) {
    console.error("Error al generar PDF:", error);
    alert("Hubo un error al generar el PDF. Por favor, intente nuevamente.");
  } finally {
    // Restaurar botón
    downloadBtn.disabled = false;
    downloadBtn.innerHTML = originalText;
  }
}


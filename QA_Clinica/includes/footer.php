  </main>

  <footer>
    &copy; QA Clínica Salud+ · Sistema de aseguramiento de calidad para Clínica Salud+
  </footer>
</div>

<script src="js/dashboard.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
(function () {
    function sanitizarTexto(valor) {
        return String(valor ?? '').replace(/\s+/g, ' ').trim();
    }

    function agregarTexto(doc, texto, x, y, anchoMaximo, tamanio, interlineado) {
        const limpio = sanitizarTexto(texto);
        if (!limpio) return y;

        const lineas = doc.splitTextToSize(limpio, anchoMaximo);
        doc.setFontSize(tamanio);

        lineas.forEach(function (linea) {
            if (y > 760) {
                doc.addPage();
                y = 52;
            }
            doc.text(linea, x, y);
            y += interlineado;
        });

        return y;
    }

    function exportarPdfDetalle() {
        const payload = window.pdfData;
        if (!payload || !payload.title) {
            alert('No hay información disponible para exportar el PDF.');
            return;
        }

        if (!window.jspdf || !window.jspdf.jsPDF) {
            alert('La librería de exportación PDF no está disponible en este momento.');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
        const anchoPagina = doc.internal.pageSize.getWidth();
        const margen = 46;
        const anchoLinea = anchoPagina - (margen * 2);
        let y = 52;

        doc.setFillColor(15, 23, 42);
        doc.roundedRect(margen, 18, 170, 24, 6, 6, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(16);
        doc.text('QA Clínica Salud+', margen + 16, 35);

        doc.setTextColor(15, 23, 42);
        doc.setFontSize(18);
        doc.setFont(undefined, 'bold');
        y = agregarTexto(doc, payload.title, margen, 72, anchoLinea, 18, 22);

        if (payload.subtitle) {
            doc.setFont(undefined, 'normal');
            y = agregarTexto(doc, payload.subtitle, margen, y + 8, anchoLinea, 10, 16);
        }

        doc.setDrawColor(203, 213, 225);
        doc.line(margen, y + 10, anchoPagina - margen, y + 10);
        y += 26;

        (payload.sections || []).forEach(function (seccion) {
            if (!seccion || !seccion.items || !seccion.items.length) return;

            if (seccion.title) {
                doc.setFont(undefined, 'bold');
                doc.setFontSize(12);
                doc.text(seccion.title, margen, y);
                y += 18;
            }

            doc.setFont(undefined, 'normal');
            doc.setFontSize(10);

            seccion.items.forEach(function (item) {
                const valor = sanitizarTexto(item.value) || 'Sin información';
                const texto = (item.label ? item.label + ': ' : '') + valor;
                const lineas = doc.splitTextToSize(texto, anchoLinea);

                if (y > 740) {
                    doc.addPage();
                    y = 52;
                }

                doc.text(lineas, margen, y);
                y += Math.max(14, lineas.length * 12);
            });

            y += 12;
        });

        const nombreArchivo = payload.fileName || 'detalle_qa_clinica.pdf';
        doc.save(nombreArchivo);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-export-pdf]').forEach(function (boton) {
            boton.addEventListener('click', exportarPdfDetalle);
        });
    });

    window.exportarPdfDetalle = exportarPdfDetalle;
})();
</script>
</body>
</html>
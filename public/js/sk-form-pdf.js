(function () {
    const pdfjs = window.pdfjsLib;
    const wrap = document.querySelector('[data-sk-pdf]');
    if (!wrap) return;

    const canvas = wrap.querySelector('canvas');
    const message = wrap.querySelector('[data-pdf-error]');
    const fields = wrap.querySelector('.sk-paper-fields');
    const pageSelect = document.querySelector('[data-pdf-page]');
    const pageSelectors = Array.from(document.querySelectorAll('[data-pdf-page-select]'));
    if (!pdfjs || !canvas || !message || !fields) {
        if (message) {
            message.hidden = false;
            message.textContent = 'ไม่สามารถเตรียมตัวแสดงแบบฟอร์มได้ กรุณาโหลดหน้าใหม่หรือติดต่อผู้ดูแลระบบ';
        }
        return;
    }

    pdfjs.GlobalWorkerOptions.workerSrc = wrap.dataset.pdfWorker;

    pdfjs.getDocument(wrap.dataset.pdfUrl).promise.then(function (pdf) {
        window.dispatchEvent(new CustomEvent('skpdf:loaded', { detail: { pdf: pdf } }));
        let renderTask = null;
        let renderVersion = 0;
        let page = null;
        const pages = Array.from({ length: pdf.numPages }, function (_, index) {
            return { value: String(index + 1), label: 'หน้า ' + (index + 1) };
        });

        [pageSelect].concat(pageSelectors).filter(Boolean).forEach(function (select) {
            select.replaceChildren();
            pages.forEach(function (item) {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.label;
                select.appendChild(option);
            });
        });
        const pageFields = Array.from(fields.querySelectorAll('[data-field]'));
        const automaticFields = pageFields.filter(function (field) {
            return field.dataset.autoPosition === '1';
        });
        const automaticPerPage = Math.ceil(automaticFields.length / pdf.numPages);
        const columns = 4;
        const automaticPageCounts = Array.from({ length: pdf.numPages }, function (_, pageIndex) {
            const start = pageIndex * automaticPerPage;
            return Math.max(0, Math.min(automaticPerPage, automaticFields.length - start));
        });
        automaticFields.forEach(function (field, index) {
            const pageIndex = Math.min(Math.floor(index / Math.max(automaticPerPage, 1)), pdf.numPages - 1);
            const localIndex = index - pageIndex * automaticPerPage;
            const pageCount = automaticPageCounts[pageIndex];
            const rows = Math.ceil(pageCount / columns);
            const rowStep = rows > 1 ? 88 / (rows - 1) : 0;
            field.dataset.page = String(pageIndex + 1);
            field.style.left = (7 + (localIndex % columns) * 23) + '%';
            field.style.top = (6 + Math.floor(localIndex / columns) * rowStep) + '%';
            const key = field.dataset.field;
            const leftInput = fields.querySelector('[data-position="' + key + '-left"]');
            const topInput = fields.querySelector('[data-position="' + key + '-top"]');
            const pageInput = fields.querySelector('[data-position="' + key + '-page"]');
            if (leftInput) leftInput.value = field.style.left.slice(0, -1);
            if (topInput) topInput.value = field.style.top.slice(0, -1);
            if (pageInput) pageInput.value = pageIndex + 1;
        });
        pageFields.forEach(function (field) {
            const pageNumber = Number(field.dataset.page);
            if (!Number.isInteger(pageNumber) || pageNumber < 1 || pageNumber > pdf.numPages) {
                field.dataset.page = '1';
                const pageInput = fields.querySelector('[data-position="' + field.dataset.field + '-page"]');
                if (pageInput) pageInput.value = '1';
            }
        });
        pageSelectors.forEach(function (select) {
            const selectedField = fields.querySelector('.sk-position-field.is-selected');
            if (selectedField) select.value = selectedField.dataset.page || '1';
        });
        if (pageSelect) {
            const selectedField = fields.querySelector('.sk-position-field.is-selected');
            if (selectedField) pageSelect.value = selectedField.dataset.page || '1';
        }

        function syncFieldsToPage(pageNumber) {
            fields.querySelectorAll('[data-field]').forEach(function (field) {
                field.hidden = Number(field.dataset.page) !== pageNumber;
            });
        }

        async function renderPage(pageNumber) {
            const width = wrap.clientWidth;
            if (!width) return;
            const version = ++renderVersion;
            const previousRenderTask = renderTask;
            if (previousRenderTask) previousRenderTask.cancel();
            syncFieldsToPage(pageNumber);
            try {
                const selectedPage = await pdf.getPage(pageNumber);
                if (previousRenderTask) {
                    try {
                        await previousRenderTask.promise;
                    } catch (error) {
                        if (!error || error.name !== 'RenderingCancelledException') throw error;
                    }
                }
                if (version !== renderVersion) return;
                page = selectedPage;
                const viewport = page.getViewport({ scale: width / page.getViewport({ scale: 1 }).width });
                const pixelRatio = window.devicePixelRatio || 1;
                const context = canvas.getContext('2d');
                canvas.width = Math.ceil(viewport.width * pixelRatio);
                canvas.height = Math.ceil(viewport.height * pixelRatio);
                canvas.style.width = viewport.width + 'px';
                canvas.style.height = viewport.height + 'px';

                renderTask = page.render({
                    canvasContext: context,
                    viewport: viewport,
                    transform: pixelRatio === 1 ? null : [pixelRatio, 0, 0, pixelRatio, 0, 0]
                });
                await renderTask.promise;
                if (version !== renderVersion) return;
                wrap.style.aspectRatio = viewport.width + ' / ' + viewport.height;
                fields.hidden = false;
                message.hidden = true;
            } catch (error) {
                if (error && error.name === 'RenderingCancelledException') return;
                console.error('Unable to render source PDF page.', error);
                message.hidden = false;
                message.textContent = 'แสดงเอกสารต้นฉบับไม่สำเร็จ กรุณาโหลดหน้าใหม่หรือติดต่อผู้ดูแลระบบ';
            }
        }

        if (pageSelect) {
            pageSelect.addEventListener('change', function () {
                renderPage(Number(pageSelect.value));
            });
        }
        renderPage(pageSelect ? Number(pageSelect.value) : 1);
        if (window.ResizeObserver) {
            new ResizeObserver(function () {
                renderPage(pageSelect ? Number(pageSelect.value) : 1);
            }).observe(wrap);
        } else {
            window.addEventListener('resize', function () {
                renderPage(pageSelect ? Number(pageSelect.value) : 1);
            });
        }
    }).catch(function () {
        message.hidden = false;
        message.textContent = 'โหลดเอกสารต้นฉบับไม่สำเร็จ กรุณาลองใหม่อีกครั้งหรือติดต่อผู้ดูแลระบบ';
    });
})();

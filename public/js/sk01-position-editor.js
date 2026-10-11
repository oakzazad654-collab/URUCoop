(function () {
    const wrap = document.querySelector('[data-sk-pdf]');
    const form = document.getElementById('positionForm');
    if (!wrap || !form) return;

    const select = document.getElementById('fieldSelect');
    const controls = {
        left: document.getElementById('positionLeft'),
        top: document.getElementById('positionTop'),
        width: document.getElementById('positionWidth'),
        row_step: document.getElementById('positionRowStep'),
        page: document.getElementById('positionPage')
    };
    const previewPage = document.getElementById('previewPage');
    const autoPositionButton = document.getElementById('autoPositionButton');
    const autoPositionStatus = document.getElementById('autoPositionStatus');
    const fields = Array.from(form.querySelectorAll('.sk-position-field'));
    let selectedKey = select.value;
    let dragState = null;

    function hiddenInput(key, dimension) {
        return form.querySelector('[data-position="' + key + '-' + dimension + '"]');
    }

    function readPosition(key) {
        return {
            left: Number(hiddenInput(key, 'left').value),
            top: Number(hiddenInput(key, 'top').value),
            width: Number(hiddenInput(key, 'width').value),
            row_step: Number(hiddenInput(key, 'row_step').value) || 0,
            page: Number(hiddenInput(key, 'page').value) || 1
        };
    }

    function layoutField(field, position) {
        if (!field.classList.contains('sk-position-radio')) {
            field.style.width = position.width + '%';
            return;
        }

        const count = Number(field.dataset.count);
        const horizontal = field.dataset.layout !== 'vertical';
        const span = Math.max(position.width * Math.max(count - 1, 0), 1.8);
        const visualWidth = horizontal ? span : 2;
        const visualHeight = horizontal ? 2 : Math.max(span, 2);
        field.style.width = visualWidth + '%';
        field.style.height = visualHeight + '%';
        Array.from(field.querySelectorAll('span')).forEach(function (marker, index) {
            marker.style.left = horizontal && span > 0 ? (position.width * index / visualWidth * 100) + '%' : '50%';
            marker.style.top = !horizontal && span > 0 ? (position.width * index / visualHeight * 100) + '%' : '50%';
        });
    }

    function writePosition(key, position) {
        const field = fields.find(function (item) { return item.dataset.field === key; });
        const count = Number(field.dataset.count);
        const horizontalRadio = field.classList.contains('sk-position-radio') && field.dataset.layout !== 'vertical';
        const verticalRadio = field.classList.contains('sk-position-radio') && field.dataset.layout === 'vertical';
        const maxLeft = horizontalRadio ? 100 - position.width * Math.max(count - 1, 0) : 100 - position.width;
        const maxTop = verticalRadio
            ? 100 - position.width * Math.max(count - 1, 0)
            : (field.classList.contains('sk-position-radio') ? 99 : 97.8);
        const bounded = {
            left: Math.max(0, Math.min(maxLeft, position.left)),
            top: Math.max(0, Math.min(maxTop, position.top)),
            width: Math.max(0.1, Math.min(100, position.width)),
            row_step: Math.max(0, Math.min(20, position.row_step || 0)),
            page: position.page
        };
        field.dataset.page = bounded.page;
        field.style.left = bounded.left + '%';
        field.style.top = bounded.top + '%';
        layoutField(field, bounded);
        Object.keys(bounded).forEach(function (dimension) {
            const value = dimension === 'page' ? bounded.page : Number(bounded[dimension].toFixed(2));
            hiddenInput(key, dimension).value = value;
            if (key === selectedKey) controls[dimension].value = value;
        });
        field.hidden = bounded.page !== Number(previewPage.value);
    }

    function selectField(key) {
        selectedKey = key;
        select.value = key;
        fields.forEach(function (field) {
            field.classList.toggle('is-selected', field.dataset.field === key);
        });
        const position = readPosition(key);
        controls.left.value = position.left;
        controls.top.value = position.top;
        controls.width.value = position.width;
        controls.row_step.value = position.row_step;
        controls.page.value = position.page;
        previewPage.value = position.page;
        previewPage.dispatchEvent(new Event('change'));
    }

    fields.forEach(function (field) {
        layoutField(field, readPosition(field.dataset.field));
        field.addEventListener('pointerdown', function (event) {
            event.preventDefault();
            const key = field.dataset.field;
            selectField(key);
            dragState = {
                key: key,
                x: event.clientX,
                y: event.clientY,
                position: readPosition(key),
                width: wrap.getBoundingClientRect().width,
                height: wrap.getBoundingClientRect().height
            };
            field.setPointerCapture(event.pointerId);
        });

        field.addEventListener('pointermove', function (event) {
            if (!dragState || dragState.key !== field.dataset.field) return;
            const position = {
                left: dragState.position.left + ((event.clientX - dragState.x) / dragState.width * 100),
                top: dragState.position.top + ((event.clientY - dragState.y) / dragState.height * 100),
                width: dragState.position.width,
                row_step: dragState.position.row_step,
                page: dragState.position.page
            };
            writePosition(dragState.key, position);
        });

        ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(function (name) {
            field.addEventListener(name, function () { dragState = null; });
        });
    });

    select.addEventListener('change', function () { selectField(select.value); });
    Object.keys(controls).forEach(function (dimension) {
        controls[dimension].addEventListener(dimension === 'page' ? 'change' : 'input', function () {
            const position = readPosition(selectedKey);
            position[dimension] = Number(controls[dimension].value) || (dimension === 'page' ? 1 : 0);
            if (dimension === 'page') {
                previewPage.value = position.page;
                previewPage.dispatchEvent(new Event('change'));
            }
            writePosition(selectedKey, position);
        });
    });
    previewPage.addEventListener('change', function () {
        const page = Number(previewPage.value);
        fields.forEach(function (field) {
            field.hidden = Number(field.dataset.page) !== page;
        });
        if (Number(controls.page.value) !== page) {
            const selectedPosition = readPosition(selectedKey);
            controls.page.value = selectedPosition.page;
        }
    });

    function normalizeText(value) {
        return Array.from(value.normalize('NFKC')).filter(function (character) {
            return !/[\s\-–—:：.()]/u.test(character);
        }).join('').toLocaleLowerCase();
    }

    function makeTextLines(textContent, viewport) {
        const items = textContent.items.filter(function (item) {
            return typeof item.str === 'string' && item.str.trim() && item.transform;
        }).map(function (item) {
            const baseline = viewport.convertToViewportPoint(item.transform[4], item.transform[5]);
            const end = viewport.convertToViewportPoint(item.transform[4] + item.width, item.transform[5]);
            return {
                text: item.str,
                left: baseline[0],
                right: end[0],
                baseline: baseline[1],
                height: item.height,
                pageWidth: viewport.width,
                pageHeight: viewport.height
            };
        }).sort(function (left, right) {
            return left.baseline - right.baseline || left.left - right.left;
        });

        const lines = [];
        items.forEach(function (item) {
            let line = lines.find(function (candidate) {
                return Math.abs(candidate.baseline - item.baseline) <= 3;
            });
            if (!line) {
                line = { items: [], baseline: item.baseline };
                lines.push(line);
            }
            line.items.push(item);
        });

        return lines.map(function (line, lineIndex) {
            line.items.sort(function (left, right) { return left.left - right.left; });
            let text = '';
            const characters = [];
            line.items.forEach(function (item) {
                const itemCharacters = Array.from(item.text);
                itemCharacters.forEach(function (character, index) {
                    const normalized = normalizeText(character);
                    Array.from(normalized).forEach(function (normalizedCharacter) {
                        text += normalizedCharacter;
                        characters.push({
                            item: item
                        });
                    });
                });
            });
            return { text: text, characters: characters, items: line.items, lineIndex: lineIndex };
        });
    }

    window.addEventListener('skpdf:loaded', function (event) {
        const pdf = event.detail.pdf;
        if (!autoPositionButton || !autoPositionStatus) return;
        autoPositionButton.disabled = false;
        autoPositionStatus.textContent = 'เอกสารพร้อม ลองจัดวางช่องที่พบข้อความกำกับได้';

        autoPositionButton.addEventListener('click', async function () {
            autoPositionButton.disabled = true;
            autoPositionStatus.textContent = 'กำลังค้นหาข้อความในเอกสาร...';
            try {
                const pageLines = [];
                for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                    const page = await pdf.getPage(pageNumber);
                    const viewport = page.getViewport({ scale: 1 });
                    const content = await page.getTextContent();
                    makeTextLines(content, viewport).forEach(function (line) {
                        pageLines.push({ page: pageNumber, line: line });
                    });
                }

                const claimedMatches = new Set();
                let placedCount = 0;
                fields.forEach(function (field) {
                    const label = normalizeText(field.dataset.label || '');
                    if (!label) return;

                    let placed = false;
                    for (let lineIndex = 0; lineIndex < pageLines.length; lineIndex++) {
                        const entry = pageLines[lineIndex];
                        let searchFrom = 0;
                        let matchIndex;
                        while ((matchIndex = entry.line.text.indexOf(label, searchFrom)) !== -1) {
                            const matchKey = entry.page + ':' + entry.line.lineIndex + ':' + matchIndex;
                            searchFrom = matchIndex + label.length;
                            if (claimedMatches.has(matchKey)) continue;

                            const matchEnd = matchIndex + label.length - 1;
                            const endPosition = entry.line.characters[matchEnd];
                            if (!endPosition) continue;
                            claimedMatches.add(matchKey);

                            const textItem = endPosition.item;
                            const leftPercent = ((textItem.right + 4) / textItem.pageWidth) * 100;
                            const topPercent = ((textItem.baseline - textItem.height) / textItem.pageHeight) * 100;
                            const currentPosition = readPosition(field.dataset.field);
                            currentPosition.left = Math.max(0, Math.min(leftPercent, 99 - currentPosition.width));
                            currentPosition.top = Math.max(0, Math.min(topPercent, 97.8));
                            currentPosition.page = entry.page;
                            writePosition(field.dataset.field, currentPosition);
                            placedCount++;
                            placed = true;
                            break;
                        }
                        if (placed) break;
                    }
                });

                if (pageLines.length === 0) {
                    autoPositionStatus.textContent = 'ไม่พบข้อความใน PDF (อาจเป็นไฟล์ภาพสแกน) จึงไม่ได้เปลี่ยนตำแหน่งช่อง';
                } else if (placedCount === 0) {
                    autoPositionStatus.textContent = 'ไม่พบคำกำกับที่ตรงกับชื่อช่อง จึงไม่ได้เปลี่ยนตำแหน่งช่อง';
                } else {
                    autoPositionStatus.textContent = 'จัดวางได้ ' + placedCount + ' จาก ' + fields.length + ' ช่อง ช่องที่เหลือให้ตรวจและจัดวางเอง แล้วตรวจตำแหน่งทั้งหมดก่อนบันทึก';
                }
            } catch (error) {
                console.error('Unable to auto-position PDF fields from text.', error);
                autoPositionStatus.textContent = 'ค้นหาข้อความใน PDF ไม่สำเร็จ กรุณาลองใหม่หรือลากจัดตำแหน่งเอง';
            } finally {
                autoPositionButton.disabled = false;
            }
        }, { once: true });
    });

    selectField(selectedKey);
})();

<?php
use Core\Security;
?>

<div class="main-container book-reader-shell">
    <div class="page-header">
        <h1 class="page-title">📖 <?= Security::sanitize($course['title']) ?></h1>
        <p class="page-subtitle">Lecture page par page, adaptée à la fenêtre.</p>
    </div>

    <div class="book-reader-toolbar">
        <a href="<?= BASE_URL ?>/categorie/livres" class="btn btn-danger-outline">← Retour</a>
        <?php if (!empty($course['youtube_url'])): ?>
            <button type="button" class="btn btn-primary open-video-modal">▶ Vidéo associée</button>
        <?php endif; ?>
    </div>

    <?php if (!empty($course['file_id'])): ?>

        <div class="book-reader-stage" id="bookReaderStage" aria-live="polite">
            <div class="book-reader-track" id="bookReaderTrack"></div>

            <button type="button" class="book-reader-arrow book-reader-arrow-left" id="bookArrowLeftBtn" aria-label="Page précédente">‹</button>
            <button type="button" class="book-reader-arrow book-reader-arrow-right" id="bookArrowRightBtn" aria-label="Page suivante">›</button>

            <div class="book-reader-fullscreen-bar" id="bookReaderFullscreenBar" aria-label="Contrôles du lecteur">
                <button type="button" class="book-reader-control" id="bookZoomOutBtn" aria-label="Réduire le zoom">−</button>
                <button type="button" class="book-reader-control book-reader-control--active" id="bookZoomResetBtn" aria-label="Réinitialiser le zoom">100%</button>
                <button type="button" class="book-reader-control" id="bookZoomInBtn" aria-label="Augmenter le zoom">＋</button>
                <span class="fullscreen-page-info" id="fullscreenPageInfo">Page 1 / 1</span>
                
                <button type="button" class="btn btn-primary-small" id="bookFullscreenBtn" title="Plein écran">⛶</button>
            </div>
        </div>

        <div class="book-reader-toolbar book-reader-toolbar-bottom">
            <button type="button" class="btn btn-danger-outline" id="bookPrevBtn">← Précédente</button>
            <span class="page-subtitle" id="bookPageInfo">Page 1 / 1</span>
            
            <button type="button" class="btn btn-primary" id="bookNextBtn">Suivante →</button>
            <button type="button" class="btn btn-primary" id="bookFullscreenBtnBottom">Plein écran</button>
        </div>
    <?php else: ?>
        <div class="info-box">
            <p class="course-desc">Aucun document PDF n'est associé à ce livre.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($course['description'])): ?>
        <div class="info-box book-description-box">
            <h2 class="course-card-title">Description</h2>
            <p class="course-desc"><?= Security::sanitize($course['description']) ?></p>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($course['youtube_url'])): ?>
    <div class="video-modal-overlay" id="videoModalOverlay" aria-hidden="true">
        <div class="video-modal-content">
            <button type="button" class="video-modal-close" aria-label="Fermer">✕</button>
            <div class="video-modal-frame">
                <iframe
                    id="videoModalIframe"
                    data-embed="<?= $course['youtube_embed'] ?? '' ?>"
                    src="about:blank"
                    title="Vidéo de support"
                    allowfullscreen>
                ></iframe>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($course['file_id'])): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pdfUrl = '<?= BASE_URL ?>/files/<?= (int)$course['file_id'] ?>/preview';
            const track = document.getElementById('bookReaderTrack');
            const stage = document.getElementById('bookReaderStage');
            const pageInfo = document.getElementById('bookPageInfo');
            const prevBtn = document.getElementById('bookPrevBtn');
            const nextBtn = document.getElementById('bookNextBtn');
            const fullscreenBtn = document.getElementById('bookFullscreenBtn');
            const fullscreenBtnBottom = document.getElementById('bookFullscreenBtnBottom');
            const arrowLeftBtn = document.getElementById('bookArrowLeftBtn');
            const arrowRightBtn = document.getElementById('bookArrowRightBtn');
            const fullscreenPageInfo = document.getElementById('fullscreenPageInfo');
            const zoomInBtn = document.getElementById('bookZoomInBtn');
            const zoomOutBtn = document.getElementById('bookZoomOutBtn');
            const zoomResetBtn = document.getElementById('bookZoomResetBtn');
            const bookmarkBtn = document.getElementById('bookBookmarkBtn');
            const bookmarkBtnBottom = document.getElementById('bookBookmarkBtnBottom');
            const overlay = document.getElementById('videoModalOverlay');
            const videoIframe = document.getElementById('videoModalIframe');
            const modalClose = document.querySelector('.video-modal-close');
            const openVideoButton = document.querySelector('.open-video-modal');
            const readerLinkBar = document.getElementById('bookReaderInlineLinks');
            const rawBookLinks = <?= json_encode($course['links'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
            const bookLinks = Array.isArray(rawBookLinks) ? rawBookLinks.map(function (link) {
                return {
                    name: link.name || link.link_name || 'Lien',
                    page_number: Number(link.page_number ?? link.page ?? 1),
                    url: link.url || '#'
                };
            }) : [];

            if (!track || !stage || typeof pdfjsLib === 'undefined') {
                return;
            }

            let pdfDoc = null;
            let currentPage = 1;
            let totalPages = 1;
            let zoom = 1;
            const courseId = <?= (int)($course['id'] ?? 0) ?>;
            const bookmarkKey = 'book_reader_bookmark_' + courseId;
            const minZoom = 1;
            const maxZoom = 2.5;

            function closeVideoModal() {
                if (overlay) {
                    overlay.classList.remove('show');
                    overlay.setAttribute('aria-hidden', 'true');
                }
                if (videoIframe) {
                    videoIframe.src = 'about:blank';
                }
            }

            function extractYoutubeId(rawUrl) {
                if (!rawUrl) return null;
                const value = rawUrl.trim();
                const match = value.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/);
                return match ? match[1] : null;
            }

            function openVideoOverlay(rawUrl) {
                if (!overlay || !videoIframe) return;
                const youtubeId = extractYoutubeId(rawUrl);
                if (!youtubeId) {
                    window.open(rawUrl, '_blank', 'noopener');
                    return;
                }
                videoIframe.src = 'https://www.youtube.com/embed/' + youtubeId + '?autoplay=1';
                overlay.classList.add('show');
                overlay.setAttribute('aria-hidden', 'false');
            }

            if (openVideoButton) {
                openVideoButton.addEventListener('click', function () {
                    if (videoIframe && videoIframe.dataset.embed) {
                        openVideoOverlay('https://www.youtube.com/watch?v=' + videoIframe.dataset.embed);
                    }
                });
            }

            if (overlay && modalClose) {
                modalClose.addEventListener('click', closeVideoModal);
            }

            if (overlay) {
                overlay.addEventListener('click', function (event) {
                    if (event.target === overlay) {
                        closeVideoModal();
                    }
                });
            }

            function setPageText() {
                if (pageInfo) pageInfo.textContent = 'Page ' + currentPage + ' / ' + totalPages;
                if (fullscreenPageInfo) fullscreenPageInfo.textContent = 'Page ' + currentPage + ' / ' + totalPages;
            }

            function updateButtons() {
                if (prevBtn) prevBtn.disabled = currentPage <= 1;
                if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
                if (arrowLeftBtn) arrowLeftBtn.disabled = currentPage <= 1;
                if (arrowRightBtn) arrowRightBtn.disabled = currentPage >= totalPages;
                if (zoomOutBtn) zoomOutBtn.disabled = zoom <= minZoom || !stage.classList.contains('fullscreen');
                if (zoomInBtn) zoomInBtn.disabled = zoom >= maxZoom || !stage.classList.contains('fullscreen');
                if (zoomResetBtn) zoomResetBtn.textContent = Math.round(zoom * 100) + '%';
            }

            function updateBookmarkButtons() {
                const savedPage = Number(localStorage.getItem(bookmarkKey) || 0);
                const isMarked = savedPage === currentPage;

                if (bookmarkBtn) {
                    bookmarkBtn.classList.toggle('active', isMarked);
                    bookmarkBtn.textContent = isMarked ? '★' : '☆';
                    bookmarkBtn.setAttribute('aria-pressed', isMarked ? 'true' : 'false');
                }

                if (bookmarkBtnBottom) {
                    bookmarkBtnBottom.textContent = isMarked ? '★ Page mémorisée' : '☆ Marquer la page';
                    bookmarkBtnBottom.classList.toggle('active', isMarked);
                }
            }

            function restoreSavedBookmark() {
                const savedPage = Number(localStorage.getItem(bookmarkKey) || 0);
                if (savedPage >= 1 && savedPage <= totalPages) {
                    currentPage = savedPage;
                    return true;
                }
                return false;
            }

            function renderPage(pageNumber) {
                if (!pdfDoc) return;

                const pageLinks = (bookLinks || []).filter(function (link) {
                    return Number(link.page_number ?? link.page ?? 0) === Number(pageNumber);
                });

                if (readerLinkBar) {
                    readerLinkBar.innerHTML = '';
                    if (pageLinks.length) {
                        pageLinks.forEach(function (link) {
                            const anchor = document.createElement('a');
                            anchor.href = link.url || '#';
                            anchor.target = '_blank';
                            anchor.rel = 'noopener noreferrer';
                            anchor.className = 'book-link-action';
                            anchor.textContent = link.name || 'Lien';
                            readerLinkBar.appendChild(anchor);
                        });
                    }
                }

                pdfDoc.getPage(pageNumber).then(function (page) {
                    const baseViewport = page.getViewport({ scale: 1 });
                    const stageRect = stage.getBoundingClientRect();
                    const paddingX = stage.classList.contains('fullscreen') ? 72 : 48;
                    const paddingY = stage.classList.contains('fullscreen') ? 140 : 90;
                    const widthAvailable = Math.max(220, stageRect.width - paddingX);
                    const heightAvailable = Math.max(260, window.innerHeight - paddingY);
                    const fitScale = Math.min(widthAvailable / baseViewport.width, heightAvailable / baseViewport.height, 2.25);
                    const isMobile = window.innerWidth <= 768;

                    const pageScale = stage.classList.contains('fullscreen')
                        ? Math.min(Math.max(fitScale * zoom, 0.5), maxZoom)
                        : (isMobile ? 0.5 : 1.0);

                    const viewport = page.getViewport({ scale: pageScale, rotation: 0 });
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    canvas.style.width = viewport.width + 'px';
                    canvas.style.height = viewport.height + 'px';
                    canvas.style.display = 'block';
                    canvas.style.maxWidth = 'none';

                    const pageWrapper = document.createElement('div');
                    pageWrapper.className = 'book-reader-page';
                    pageWrapper.style.width = Math.min(viewport.width, Math.max(220, stage.clientWidth - 24)) + 'px';
                    pageWrapper.style.height = viewport.height + 'px';
                    pageWrapper.style.maxWidth = '100%';
                    pageWrapper.style.overflow = 'visible';
                    pageWrapper.style.margin = '0 auto';
                    pageWrapper.appendChild(canvas);

                    track.innerHTML = '';
                    track.appendChild(pageWrapper);

                    page.render({ canvasContext: context, viewport: viewport }).promise.then(function () {
                        if (pageLinks.length) {
                            const floatingBar = document.createElement('div');
                            floatingBar.className = 'book-link-actions';
                            pageLinks.forEach(function (link) {
                                const anchor = document.createElement('a');
                                anchor.href = link.url || '#';
                                anchor.target = '_blank';
                                anchor.rel = 'noopener noreferrer';
                                anchor.className = 'book-link-action';
                                anchor.textContent = link.name || 'Lien';
                                floatingBar.appendChild(anchor);
                            });
                            pageWrapper.appendChild(floatingBar);
                        }
                    });
                });
            }

            function goToPage(pageNumber) {
                currentPage = Math.min(Math.max(pageNumber, 1), totalPages);
                renderPage(currentPage);
                setPageText();
                updateButtons();
                updateBookmarkButtons();
            }

            function applyZoom(nextZoom) {
                if (!stage.classList.contains('fullscreen')) return;
                zoom = Math.min(Math.max(nextZoom, minZoom), maxZoom);
                renderPage(currentPage);
                updateButtons();
            }

            function toggleFullscreen() {
                const isFull = stage.classList.toggle('fullscreen');
                document.body.style.overflow = isFull ? 'hidden' : '';
                setPageText();
                updateButtons();
                renderPage(currentPage);
            }

            function toggleBookmark() {
                const savedPage = Number(localStorage.getItem(bookmarkKey) || 0);
                if (savedPage === currentPage) {
                    localStorage.removeItem(bookmarkKey);
                } else {
                    localStorage.setItem(bookmarkKey, String(currentPage));
                }
                updateBookmarkButtons();
            }

            if (prevBtn) prevBtn.addEventListener('click', function () { if (currentPage > 1) goToPage(currentPage - 1); });
            if (nextBtn) nextBtn.addEventListener('click', function () { if (currentPage < totalPages) goToPage(currentPage + 1); });
            if (arrowLeftBtn) arrowLeftBtn.addEventListener('click', function () { if (currentPage > 1) goToPage(currentPage - 1); });
            if (arrowRightBtn) arrowRightBtn.addEventListener('click', function () { if (currentPage < totalPages) goToPage(currentPage + 1); });
            if (zoomInBtn) zoomInBtn.addEventListener('click', function () { applyZoom(zoom + 0.1); });
            if (zoomOutBtn) zoomOutBtn.addEventListener('click', function () { applyZoom(zoom - 0.1); });
            if (zoomResetBtn) zoomResetBtn.addEventListener('click', function () { applyZoom(1); });
            if (bookmarkBtn) bookmarkBtn.addEventListener('click', toggleBookmark);
            if (bookmarkBtnBottom) bookmarkBtnBottom.addEventListener('click', toggleBookmark);
            if (fullscreenBtn) fullscreenBtn.addEventListener('click', toggleFullscreen);
            if (fullscreenBtnBottom) fullscreenBtnBottom.addEventListener('click', toggleFullscreen);
            window.addEventListener('resize', function () {
                if (pdfDoc) {
                    renderPage(currentPage);
                }
            });

            window.addEventListener('keydown', function (event) {
                if (overlay && overlay.classList.contains('show')) {
                    if (event.key === 'Escape') closeVideoModal();
                    return;
                }

                if (event.key === 'ArrowLeft' && currentPage > 1) goToPage(currentPage - 1);
                if (event.key === 'ArrowRight' && currentPage < totalPages) goToPage(currentPage + 1);
                if ((event.key === '+' || event.key === '=') && stage.classList.contains('fullscreen')) applyZoom(zoom + 0.1);
                if ((event.key === '-' || event.key === '_') && stage.classList.contains('fullscreen')) applyZoom(zoom - 0.1);
                if (event.key === 'Escape' && stage.classList.contains('fullscreen')) {
                    stage.classList.remove('fullscreen');
                    document.body.style.overflow = '';
                    setPageText();
                    updateButtons();
                    renderPage(currentPage);
                }
            });

            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            pdfjsLib.getDocument(pdfUrl).promise.then(function (pdf) {
                pdfDoc = pdf;
                totalPages = pdf.numPages;
                const restored = restoreSavedBookmark();
                if (restored) currentPage = Number(localStorage.getItem(bookmarkKey));
                goToPage(currentPage);
                updateBookmarkButtons();
            }).catch(function () {
                track.innerHTML = '<div class="book-reader-page empty">Le PDF ne peut pas être ouvert pour le moment.</div>';
            });
        });
    </script>
<?php endif; ?>

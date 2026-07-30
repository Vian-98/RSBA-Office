import './bootstrap';
// import './filamentfixtallstack';

// print area v1
// window.printArea = function (elementId) {
//     let printContents = document.getElementById(elementId).innerHTML;
//     let originalContents = document.body.innerHTML;

//     document.body.innerHTML = printContents; // Replace page content with print content
//     window.print(); // Show print dialog
//     document.body.innerHTML = originalContents; // Restore original content after printing

//     location.reload(); // Reload to restore Livewire functionality
// };

// Versi 3
// window.printArea = function (elementId) {
//     const printElement = document.getElementById(elementId);

//     console.log('Has content:', printElement.innerHTML.length);

//     // Remove hidden class temporarily to let Livewire render
//     printElement.classList.remove('absolute', '-left-[9999px]', 'print-hidden');
//     printElement.classList.add('print-active');

//     // Add print mode
//     document.body.classList.add('print-mode');

//     // Wait a moment for content to render
//     setTimeout(() => {
//         window.print();

//         // Restore after print
//         document.body.classList.remove('print-mode');
//         printElement.classList.remove('print-active');
//         printElement.classList.add('absolute', '-left-[9999px]');
//     }, 300);
// };


// Print Area
window.printArea = function (elementId, title = 'Print') {
    // Get the content
    const printElement = document.getElementById(elementId);
    let printContents = printElement.innerHTML;

    // Convert relative URLs to absolute URLs
    const baseUrl = window.location.origin;

    // Fix image src attributes
    printContents = printContents.replace(/src="(?!http|data:)([^"]*)"/g, (match, url) => {
        if (url.startsWith('/')) {
            return `src="${baseUrl}${url}"`;
        }
        return `src="${baseUrl}/${url}"`;
    });

    // Fix background images in style attributes
    printContents = printContents.replace(/url\(['"']?(?!http|data:)([^'")\s]+)['"']?\)/g, (match, url) => {
        if (url.startsWith('/')) {
            return `url('${baseUrl}${url}')`;
        }
        return `url('${baseUrl}/${url}')`;
    });

    // Create iframe
    const iframe = document.createElement('iframe');
    iframe.style.position = 'absolute';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = 'none';

    document.body.appendChild(iframe);

    const iframeWindow = iframe.contentWindow;
    const iframeDoc = iframeWindow.document;

    // Get all stylesheets from parent page
    let styles = '';
    for (let i = 0; i < document.styleSheets.length; i++) {
        try {
            const styleSheet = document.styleSheets[i];
            if (styleSheet.href) {
                // External stylesheet
                styles += `<link rel="stylesheet" href="${styleSheet.href}">`;
            } else if (styleSheet.cssRules) {
                // Inline styles
                styles += '<style>';
                for (let j = 0; j < styleSheet.cssRules.length; j++) {
                    styles += styleSheet.cssRules[j].cssText;
                }
                styles += '</style>';
            }
        } catch (e) {
            console.warn('Could not access stylesheet:', e);
        }
    }

    // Write to iframe
    iframeDoc.open();
    iframeDoc.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>${title}</title>
            <base href="${baseUrl}/">
            ${styles}
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                }
            </style>
        </head>
        <body>
            ${printContents}
        </body>
        </html>
    `);
    iframeDoc.close();

    // Wait for images and styles to load before printing
    setTimeout(() => {
        // Wait for all images to load
        const images = iframeDoc.getElementsByTagName('img');
        const imagePromises = Array.from(images).map(img => {
            if (img.complete) {
                return Promise.resolve();
            }
            return new Promise((resolve) => {
                img.onload = resolve;
                img.onerror = resolve; // Resolve even on error to not block
            });
        });

        Promise.all(imagePromises).then(() => {
            // Simpan title asli dan set ke nomor surat sebelum print
            const originalTitle = document.title;
            document.title = title;

            // Restore title setelah dialog print benar-benar ditutup
            const restoreAndCleanup = () => {
                document.title = originalTitle;
                iframeWindow.removeEventListener('afterprint', restoreAndCleanup);
                window.removeEventListener('afterprint', restoreAndCleanup);
                setTimeout(() => {
                    if (document.body.contains(iframe)) {
                        document.body.removeChild(iframe);
                    }
                }, 100);
            };

            // Listen di kedua window (iframe & parent) agar tidak terlewat
            iframeWindow.addEventListener('afterprint', restoreAndCleanup);
            window.addEventListener('afterprint', restoreAndCleanup);

            iframeWindow.focus();
            iframeWindow.print();
        });
    }, 500);
};
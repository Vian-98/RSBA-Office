document.addEventListener('alpine:init', () => {
    // Override x-trap to allow focus on teleported elements (e.g. TallStackUI select dropdown)
    let originalTrap = Alpine.directive.bind(null, 'trap');
    window.__filamentModalFocusFix = true;
});

// Intercept x-trap's focusin handler at the document level
document.addEventListener('focusin', function (e) {
    // Allow focus if the target is inside a floating/teleported dropdown
    if (
        e.target.tagName === 'INPUT' &&
        !document.querySelector('[x-trap]')?.contains(e.target)
    ) {
        e.stopImmediatePropagation();
    }
}, true);
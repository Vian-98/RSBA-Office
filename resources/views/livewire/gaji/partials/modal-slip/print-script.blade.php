<!-- Custom Print Script -->
<script>
    function printSalarySlip() {
        var printContents = document.getElementById('salary-slip-print').innerHTML;

        var printWindow = window.open('', '', 'height=700,width=850');
        printWindow.document.write('<html><head><title>Cetak Slip Gaji</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: sans-serif; font-size: 13px; color: #1e293b; line-height: 1.4; padding: 20px; margin: 0; }');
        printWindow.document.write('.flex { display: flex; }');
        printWindow.document.write('.flex-col { flex-direction: column; }');
        printWindow.document.write('.items-center { align-items: center; }');
        printWindow.document.write('.justify-center { justify-content: center; }');
        printWindow.document.write('.pb-4 { padding-bottom: 12px; }');
        printWindow.document.write('.mb-4 { margin-bottom: 16px; }');
        printWindow.document.write('.border-b-2 { border-bottom: 2px solid #0f172a; }');
        printWindow.document.write('.border-slate-900 { border-color: #0f172a; }');
        printWindow.document.write('.text-base { font-size: 14px; }');
        printWindow.document.write('.font-black { font-weight: 800; }');
        printWindow.document.write('.tracking-widest { letter-spacing: 0.1em; }');
        printWindow.document.write('.uppercase { text-transform: uppercase; }');
        printWindow.document.write('.leading-none { line-height: 1; }');
        printWindow.document.write('.w-full { width: 100%; }');
        printWindow.document.write('.text-xs { font-size: 11px; }');
        printWindow.document.write('.font-semibold { font-weight: 600; }');
        printWindow.document.write('.border-collapse { border-collapse: collapse; }');
        printWindow.document.write('.border-t { border-top: 1px solid #cbd5e1; }');
        printWindow.document.write('.border-b { border-bottom: 1px solid #cbd5e1; }');
        printWindow.document.write('.border-slate-300 { border-color: #cbd5e1; }');
        printWindow.document.write('.py-1\\.5 { padding-top: 6px; padding-bottom: 6px; }');
        printWindow.document.write('.font-bold { font-weight: bold; }');
        printWindow.document.write('.border { border: 1px solid #1e293b; }');
        printWindow.document.write('.border-slate-800 { border-color: #1e293b; }');
        printWindow.document.write('.border-b-4 { border-bottom-width: 4px; }');
        printWindow.document.write('.border-double { border-bottom-style: double !important; border-color: #1e293b !important; }');
        printWindow.document.write('.border-r { border-right: 1px solid #cbd5e1; }');
        printWindow.document.write('.px-3 { padding-left: 12px; padding-right: 12px; }');
        printWindow.document.write('.font-medium { font-weight: 500; }');
        printWindow.document.write('.text-center { text-align: center; }');
        printWindow.document.write('.text-right { text-align: right; }');
        printWindow.document.write('.bg-slate-50\\/50 { background-color: #f8fafc; }');
        printWindow.document.write('.h-4 { height: 16px; }');
        printWindow.document.write('.bg-slate-50\\/20 { background-color: #f8fafc; }');
        printWindow.document.write('.py-2 { padding-top: 8px; padding-bottom: 8px; }');
        printWindow.document.write('.text-indigo-800 { color: #3730a3; }');
        printWindow.document.write('.text-sm { font-size: 13px; }');
        printWindow.document.write('.mt-6 { margin-top: 24px; }');
        printWindow.document.write('.pl-6 { padding-left: 24px; }');
        printWindow.document.write('.text-slate-600 { color: #475569; }');
        
        printWindow.document.write('@media print {');
        printWindow.document.write('  body { -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 10px; margin: 0; }');
        printWindow.document.write('  table { border-collapse: collapse !important; border: 1px solid #1e293b !important; }');
        printWindow.document.write('  td { border-bottom: 1px solid #cbd5e1 !important; border-right: 1px solid #cbd5e1 !important; }');
        printWindow.document.write('  tr.border-double td { border-bottom: 4px double #1e293b !important; }');
        printWindow.document.write('  tr:last-child td { border-bottom: none !important; }');
        printWindow.document.write('  td:last-child { border-right: none !important; }');
        printWindow.document.write('  .bg-indigo-50\\/60 { background-color: #f0f9ff !important; color: #075985 !important; }');
        printWindow.document.write('  .bg-slate-50\\/50 { background-color: #f8fafc !important; }');
        printWindow.document.write('}');
        printWindow.document.write('<\/style>');
        printWindow.document.write('<\/head><body>');
        printWindow.document.write(printContents);
        printWindow.document.write('<\/body><\/html>');
        printWindow.document.close();

        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 250);
    }
</script>

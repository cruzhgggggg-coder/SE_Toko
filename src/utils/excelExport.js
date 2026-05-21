/**
 * Direct Excel Export Utility
 * Generates formatted CSV files prepended with a UTF-8 Byte Order Mark (BOM)
 * to ensure Microsoft Excel opens them with correct character encoding and formatting.
 */

// Helper to escape CSV cell values
function escapeCSVCell(value) {
  if (value === undefined || value === null) return '';
  const str = String(value);
  // If the string contains double quotes, commas, semicolons or newlines, wrap in quotes
  if (str.includes('"') || str.includes(',') || str.includes(';') || str.includes('\n') || str.includes('\r')) {
    return `"${str.replace(/"/g, '""')}"`;
  }
  return str;
}

/**
 * Downloads a structured CSV file
 * @param {Array<Array<any>>} rows - 2D array of cells
 * @param {string} filename - Output file name
 */
export function downloadCSV(rows, filename) {
  // UTF-8 BOM
  const BOM = '\uFEFF';
  
  // Join cells with semicolon (Excel's preferred delimiter in local regional settings)
  const csvContent = BOM + rows.map(row => row.map(escapeCSVCell).join(';')).join('\r\n');
  
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  link.style.visibility = 'hidden';
  
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

/**
 * Export transactions list
 * @param {Array} transactions 
 * @param {string} dateRangeStr 
 */
export function exportTransactionsExcel(transactions, dateRangeStr = '') {
  const headers = ['ID TRANSAKSI', 'TANGGAL & WAKTU', 'KETERANGAN', 'PELANGGAN', 'METODE', 'NOMINAL'];
  
  const rows = [
    ['LAPORAN TRANSAKSI PENJUALAN'],
    [dateRangeStr ? `Periode: ${dateRangeStr}` : 'Semua Periode'],
    [],
    headers
  ];
  
  transactions.forEach(tx => {
    const date = new Date(tx.transaction_date);
    const dateFormatted = date.toLocaleDateString('id-ID') + ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    const typeStr = tx.payment_method === 'debt' ? 'Kasbon (Hutang)' : 'Penjualan';
    const customerName = tx.customer?.name || 'Customer Umum';
    
    rows.push([
      `#TXN-${tx.id}`,
      dateFormatted,
      typeStr,
      customerName,
      tx.payment_method.toUpperCase(),
      tx.total_amount
    ]);
  });
  
  const filename = `laporan_transaksi_${new Date().toISOString().split('T')[0]}.csv`;
  downloadCSV(rows, filename);
}

/**
 * Export daily financial report
 * @param {Object} report 
 */
export function exportDailyReportExcel(report) {
  const rows = [
    ['LAPORAN KEUANGAN HARIAN (PERMANEN)'],
    [`Tanggal Laporan: ${report.report_date}`],
    [],
    ['METRIK', 'NOMINAL / JUMLAH'],
    ['Tanggal', report.report_date],
    ['Total Transaksi', report.total_transactions],
    ['Total Pemasukan (Omset)', report.total_revenue],
    ['Pelunasan Piutang (Bayar Kasbon)', report.total_debt_payments || 0],
    ['Hutang Baru Terbentuk', report.new_debt_amount || 0],
    ['Pengeluaran Operasional', report.expense_amount || 0],
    ['Laba Bersih', report.net_income || report.total_profit]
  ];
  
  const filename = `laporan_harian_${report.report_date}.csv`;
  downloadCSV(rows, filename);
}

/**
 * Export detailed sales report (by product, category, payment method)
 * @param {Object} report 
 * @param {string} dateRangeStr 
 */
export function exportDetailedReportExcel(report, dateRangeStr = '') {
  const rows = [
    ['LAPORAN DETAIL PENJUALAN BARANG'],
    [dateRangeStr ? `Periode: ${dateRangeStr}` : 'Semua Periode'],
    [],
    ['1. PENJUALAN PER PRODUK'],
    ['NAMA PRODUK', 'KUANTITAS TERJUAL', 'TOTAL REVENUE (OMSET)'],
  ];
  
  if (report.sales_by_product && report.sales_by_product.length > 0) {
    report.sales_by_product.forEach(item => {
      rows.push([item.name, item.total_qty, item.total_revenue]);
    });
  } else {
    rows.push(['Tidak ada data', 0, 0]);
  }
  
  rows.push([], ['2. PENJUALAN PER KATEGORI'], ['NAMA KATEGORI', 'TOTAL REVENUE (OMSET)']);
  
  if (report.sales_by_category && report.sales_by_category.length > 0) {
    report.sales_by_category.forEach(item => {
      rows.push([item.category_name, item.total_revenue]);
    });
  } else {
    rows.push(['Tidak ada data', 0]);
  }
  
  rows.push([], ['3. PENJUALAN BERDASARKAN METODE PEMBAYARAN'], ['METODE PEMBAYARAN', 'JUMLAH TRANSAKSI', 'TOTAL REVENUE (OMSET)']);
  
  if (report.sales_by_payment && report.sales_by_payment.length > 0) {
    report.sales_by_payment.forEach(item => {
      rows.push([item.payment_method.toUpperCase(), item.transaction_count, item.total_revenue]);
    });
  } else {
    rows.push(['Tidak ada data', 0, 0]);
  }
  
  const filename = `laporan_detail_penjualan_${new Date().toISOString().split('T')[0]}.csv`;
  downloadCSV(rows, filename);
}

<?php
/**
 * Generate encrypted financial report for Flag 9
 * Encryption: XOR with key from config/secret.php
 */

$key = 'nac_internal_api_2024';

$content = "============================================\n";
$content .= "  LAPORAN KEUANGAN RAHASIA NAC CAFE\n";
$content .= "============================================\n";
$content .= "Periode    : Januari 2019 - Desember 2019\n";
$content .= "Disusun    : Divisi Keuangan Internal\n";
$content .= "Klasifikasi: SANGAT RAHASIA\n";
$content .= "--------------------------------------------\n";
$content .= "Investasi Awal  : Rp 50.000.000\n";
$content .= "Sumber Dana     : Tabungan Pribadi Pendiri\n";
$content .= "Nomor Rekening  : 7281-9304-5567 (BCA)\n";
$content .= "Tanggal Transfer: 15 Januari 2019\n";
$content .= "--------------------------------------------\n";
$content .= "Kode Internal   : modal_awal_50juta_tahun_2019\n";
$content .= "--------------------------------------------\n";
$content .= "CATATAN:\n";
$content .= "Dokumen ini bersifat sangat rahasia.\n";
$content .= "Tidak untuk disebarluaskan ke publik.\n";
$content .= "Pelanggaran akan ditindak sesuai hukum.\n";
$content .= "============================================\n";

// XOR encryption
$encrypted = '';
$keyLen = strlen($key);
for ($i = 0; $i < strlen($content); $i++) {
    $encrypted .= chr(ord($content[$i]) ^ ord($key[$i % $keyLen]));
}

$output_dir = '/var/www/html/admin/data';
if (!is_dir($output_dir)) {
    mkdir($output_dir, 0755, true);
}

$output = "--- NAC CAFE ENCRYPTED BACKUP ---\n";
$output .= "Algorithm: XOR\n";
$output .= "Format: Base64\n";
$output .= "--- BEGIN ENCRYPTED DATA ---\n";
$output .= base64_encode($encrypted) . "\n";
$output .= "--- END ENCRYPTED DATA ---\n";

file_put_contents($output_dir . '/financial_report.enc', $output);
echo "[+] Encrypted financial report generated.\n";

<?php
require_once __DIR__.'/includes/auth.php';
require_perm('laporan.read');
$f = ['penempatan_id' => (int)($_GET['penempatan_id'] ?? 0), 'status_aktivitas_id' => (int)($_GET['status_aktivitas_id'] ?? 0), 'pendidikan_id' => (int)($_GET['pendidikan_id'] ?? 0)];
$rows = cari_dosen($f);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_dosen_'.date('Ymd').'.csv"');
echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'w');
fputcsv($out, array_merge(['No'], array_values(KOLOM)), ',', '"', '\\');
foreach ($rows as $i => $r) {
  $line = [$i + 1];
  foreach (KOLOM as $k => $_) $line[] = $r[$k];
  fputcsv($out, $line, ',', '"', '\\');
}
fclose($out);

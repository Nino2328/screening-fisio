<?php
// Pembersihan HANYA data uji berawalan QA_TEST_ (+ akun qa_test_user@example.com)
// dan file foto uji di public/uploads/athletes (2 foto lama aplikasi dipertahankan).
// Pakai: php bersihkan-qa.php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$FOTO_LAMA = [
    '1773847515_whatsapp-image-2025-01-10-at-19-20250110075854.jpeg',
    '1790878768_WhatsApp Image 2026-10-02 at 01.18.57.jpeg',
];

$idsAtlet = DB::table('athletes')->where('name', 'like', 'QA_TEST_%')->pluck('id')->all();

$idsTes = DB::table('test_results')
    ->where(function ($q) use ($idsAtlet) {
        $q->whereIn('athlete_id', $idsAtlet)->orWhere('name', 'like', 'QA_TEST_%');
    })->pluck('id')->all();

$idsMental = DB::table('mental_assessments')
    ->where(function ($q) use ($idsAtlet) {
        $q->whereIn('athlete_id', $idsAtlet)->orWhere('name', 'like', 'QA_TEST_%');
    })->pluck('id')->all();

$idsTaktik = DB::table('taktik_assessments')
    ->where(function ($q) use ($idsAtlet) {
        $q->whereIn('athlete_id', $idsAtlet)->orWhere('name', 'like', 'QA_TEST_%');
    })->pluck('id')->all();

$idsUser = DB::table('users')->where('email', 'qa_test_user@example.com')->pluck('id')->all();

echo "Dihapus (hard delete, hanya baris QA_TEST_):\n";
echo '  test_results        : ' . count($idsTes) . "\n";
echo '  mental_assessments  : ' . count($idsMental) . "\n";
echo '  taktik_assessments  : ' . count($idsTaktik) . "\n";
echo '  athletes            : ' . count($idsAtlet) . "\n";
echo '  users (qa_test_user): ' . count($idsUser) . "\n";

DB::table('test_results')->whereIn('id', $idsTes)->delete();
DB::table('mental_assessments')->whereIn('id', $idsMental)->delete();
DB::table('taktik_assessments')->whereIn('id', $idsTaktik)->delete();
DB::table('athletes')->whereIn('id', $idsAtlet)->delete();
DB::table('users')->whereIn('id', $idsUser)->delete();

$berkas = glob(__DIR__ . '/../public/uploads/athletes/*') ?: [];
$hapusBerkas = 0;
foreach ($berkas as $f) {
    if (!in_array(basename($f), $FOTO_LAMA, true)) {
        @unlink($f);
        $hapusBerkas++;
        echo '  berkas dihapus: ' . basename($f) . "\n";
    }
}
echo '  berkas foto dihapus : ' . $hapusBerkas . "\n\n";

// Verifikasi sisa
$sisa = [
    'athletes' => DB::table('athletes')->where('name', 'like', 'QA_TEST_%')->count(),
    'test_results' => DB::table('test_results')->where('name', 'like', 'QA_TEST_%')->count(),
    'mental' => DB::table('mental_assessments')->where('name', 'like', 'QA_TEST_%')->count(),
    'taktik' => DB::table('taktik_assessments')->where('name', 'like', 'QA_TEST_%')->count(),
    'users' => DB::table('users')->where('email', 'qa_test_user@example.com')->count(),
];
echo "Sisa QA_TEST_ per tabel: " . json_encode($sisa) . "\n";
echo 'Total atlet aktif: ' . DB::table('athletes')->whereNull('deleted_at')->count()
    . ' | hasil tes aktif: ' . DB::table('test_results')->whereNull('deleted_at')->count()
    . ' | user: ' . DB::table('users')->count() . "\n";
echo "Isi public/uploads/athletes:\n";
foreach ((glob(__DIR__ . '/../public/uploads/athletes/*') ?: []) as $f) {
    echo '  ' . basename($f) . "\n";
}

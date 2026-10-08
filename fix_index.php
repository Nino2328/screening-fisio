<?php
$c = file_get_contents('resources/views/athletes/index.blade.php');

// Tambah @if sebelum edit button
$old = '<button onclick="editAthlete({{ $athlete->id }})"';
$new = '@if(auth()->user()->role !== \'pelatih\')' . PHP_EOL . '                                         <button onclick="editAthlete({{ $athlete->id }})"';
$c = str_replace($old, $new, $c);

// Tambah @endif setelah hapus button
$old2 = 'Hapus' . PHP_EOL . '                                         </button>' . PHP_EOL . '                                     </div>';
$new2 = 'Hapus' . PHP_EOL . '                                         </button>' . PHP_EOL . '                                         @endif' . PHP_EOL . '                                     </div';
$c = str_replace($old2, $new2, $c);

file_put_contents('resources/views/athletes/index.blade.php', $c);
echo 'Done';
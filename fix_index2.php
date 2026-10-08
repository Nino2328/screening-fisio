<?php
$c = file_get_contents('resources/views/athletes/index.blade.php');

// Tambah @endif sebelum </div>
$old = '</button>' . PHP_EOL . '                                     </div>';
$new = '</button>' . PHP_EOL . '                                         @endif' . PHP_EOL . '                                     </div';
$c = str_replace($old, $new, $c);

file_put_contents('resources/views/athletes/index.blade.php', $c);
echo 'Done';
<?php
file_put_contents(__DIR__ . "/test_output.txt", "Test run at " . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
echo "Done";

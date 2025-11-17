<?php
for ($i = 1; $i <= 100; $i++) {

    if ($i % 15 == 0) {
        echo "FooBar";
    } elseif ($i % 3 == 0) {
        echo "Foo";
    } elseif ($i % 5 == 0) {
        echo "Bar";
    } else {
        echo $i;
    }

    echo "<br>";
}
?>

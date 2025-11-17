<?php
function doubleBoucle($n) {
    $result = "";

    for ($i = 1; $i <= $n; $i++) {
        for ($j = 1; $j <= $i; $j++) {
            $result .= $i;
        }
        $result .= "\n";
    }

    return $result;
}   


?>

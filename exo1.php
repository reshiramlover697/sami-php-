<?php
function niveauScolaire($age) {
    if ($age < 3) {
        return "creche";
    } elseif ($age < 6) {
        return "maternelle";
    } elseif ($age < 11) {
        return "primaire";
    } elseif ($age < 16) {
        return "college";
    } elseif ($age < 18) {
        return "lycée";
    } else {
        return "";
    }
}


echo niveauScolaire(4); 
echo "\n";
echo niveauScolaire(12); 
echo "\n";
echo niveauScolaire(20); 
?>
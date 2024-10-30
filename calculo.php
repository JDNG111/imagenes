<?php
header('Content-Type: application/json');

// Establecer mayor precisión para cálculos numéricos
ini_set('precision', 16); // Incrementa la precisión a 16 dígitos

// Obtener los datos del POST
$data = json_decode(file_get_contents("php://input"), true);

$coefA = $data['coefA'];
$coefB = $data['coefB'];
$coefC = $data['coefC'];
$coefD = $data['coefD'];
$coefE = $data['coefE'];
$coefF = $data['coefF'];
$coefG = $data['coefG']; // Nuevo coeficiente G
$x1 = $data['x1'];
$x2 = $data['x2'];

function ecuacion($x, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG) {
    return (
        $coefA * pow($x, 4) * sin($coefB * $x) +
        $coefC * cos($coefD * $x) +
        $coefE * pow(abs($x), 3) * sin($coefF * $x) +
        $coefG * sqrt(abs($x))
    ) / (1 + abs($coefB * $x));

}

// Método Regla del Trapecio
function reglaTrapecio($x1, $x2, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG) {
    $intervalos = 10000;  // Usar 10000 intervalos para mayor precisión
    $longIntervalo = ($x2 - $x1) / $intervalos;
    $acumula = 0.5 * (ecuacion($x1, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG) + 
                      ecuacion($x2, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG));
    
    for ($cont = 1; $cont < $intervalos; $cont++) {
        $valX = $x1 + $cont * $longIntervalo;
        $acumula += ecuacion($valX, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG);
    }

    return abs($acumula * $longIntervalo);
}

// Método Suma de Riemann
function sumaRiemann($x1, $x2, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG) {
    $intervalos = 10000; 
    $longIntervalo = ($x2 - $x1) / $intervalos;
    $acumula = 0.0;
    
    for ($cont = 0; $cont < $intervalos; $cont++) {
        $valX = $x1 + $cont * $longIntervalo;
        $acumula += ecuacion($valX, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG) * $longIntervalo;
    }
    
    return abs($acumula);
}

// Método Regla de Simpson
function reglaSimpson($x1, $x2, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG) {
    $intervalos = 10000;
    if ($intervalos % 2 != 0) $intervalos++; // Asegurarse de que sea par
    $longIntervalo = ($x2 - $x1) / $intervalos;
    $acumula = ecuacion($x1, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG) + 
               ecuacion($x2, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG);
    
    for ($cont = 1; $cont < $intervalos; $cont++) {
        $valX = $x1 + $cont * $longIntervalo;
        $acumula += ($cont % 2 == 0 ? 2 : 4) * ecuacion($valX, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG);
    }
    
    return abs(($longIntervalo / 3) * $acumula);
}

// Método Cuadratura de Gauss-Legendre
function cuadraturaGaussLegendre($x1, $x2, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG) {
    $x = [-1.0 / sqrt(3), 1.0 / sqrt(3)];
    $w = [1.0, 1.0];
    $c1 = ($x2 - $x1) / 2.0;
    $c2 = ($x2 + $x1) / 2.0;
    $integral = 0.0;

    for ($i = 0; $i < count($x); $i++) {
        $integral += $w[$i] * ecuacion($c1 * $x[$i] + $c2, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG);
    }

    return abs($c1 * $integral);
}

// Calcular las áreas utilizando diferentes métodos
$areaTrapecio = reglaTrapecio($x1, $x2, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG);
$areaRiemann = sumaRiemann($x1, $x2, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG);
$areaSimpson = reglaSimpson($x1, $x2, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG);
$areaGauss = cuadraturaGaussLegendre($x1, $x2, $coefA, $coefB, $coefC, $coefD, $coefE, $coefF, $coefG);

// Promediar las áreas para obtener un cálculo más preciso
$areaPromedio = ($areaTrapecio + $areaRiemann + $areaSimpson + $areaGauss) / 4;

// Retornar el resultado en formato JSON
echo json_encode(["area" => $areaPromedio]);
?>
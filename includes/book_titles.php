<?php
/**
 * Mapa de traducción de títulos de libros.
 * Clave = título en BD (idioma original con el que se registró el libro),
 * Valor = traducción al idioma actual.
 * Si un título no está en el mapa, se muestra tal cual (igual que en genres.php).
 *
 * NOTA: a diferencia del género (un catálogo cerrado de ~25 valores), el título
 * es texto libre que puede llegar por el buscador de Google Books en cualquier
 * idioma. Este mapa cubre los libros de ejemplo del seed; agrega aquí cada
 * título nuevo que quieras traducir, con la misma clave exacta que tiene en BD.
 */
function getBookTitleTranslations(string $lang): array {
    $maps = [
        'es' => [], // español es el idioma base, no necesita traducción
        'en' => [
            'Cien años de soledad' => 'One Hundred Years of Solitude',
            'El Principito'        => 'The Little Prince',
            'Rayuela'               => 'Hopscotch',
            'Alicia en el País de las Maravillas' => "Alice's Adventures in Wonderland",
            'Crimen y Castigo'      => 'Crime and Punishment',
            'Cumbres Borrascosas'   => 'Wuthering Heights',
        ],
        'fr' => [
            'Cien años de soledad' => 'Cent ans de solitude',
            '1984'                  => '1984',
            'El Principito'        => 'Le Petit Prince',
            'Rayuela'               => 'Marelle',
            'Alicia en el País de las Maravillas' => 'Alice au pays des merveilles',
            'Crimen y Castigo'      => 'Crime et Châtiment',
            'Cumbres Borrascosas'   => 'Les Hauts de Hurlevent',
        ],
        'pt' => [
            'Cien años de soledad' => 'Cem Anos de Solidão',
            'El Principito'        => 'O Pequeno Príncipe',
            'Rayuela'               => 'O Jogo da Amarelinha',
            'Alicia en el País de las Maravillas' => 'Alice no País das Maravilhas',
            'Crimen y Castigo'      => 'Crime e Castigo',
            'Cumbres Borrascosas'   => 'O Morro dos Ventos Uivantes',
        ],
        'de' => [
            'Cien años de soledad' => 'Hundert Jahre Einsamkeit',
            'El Principito'        => 'Der kleine Prinz',
            'Rayuela'               => 'Himmel und Hölle',
            'Alicia en el País de las Maravillas' => 'Alice im Wunderland',
            'Crimen y Castigo'      => 'Schuld und Sühne',
            'Cumbres Borrascosas'   => 'Sturmhöhe',
        ],
    ];
    return $maps[$lang] ?? [];
}

function translateBookTitle(string $titulo, string $lang): string {
    if ($lang === 'es') return $titulo;
    $map = getBookTitleTranslations($lang);
    return $map[$titulo] ?? $titulo; // si no hay traducción, muestra el original
}

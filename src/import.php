<?php
// Transforme une recette collée en texte (par ex. depuis Claude) en champs du formulaire.
//
// Format attendu (souple : majuscules, accents, gras Markdown et « ## » sont tolérés) :
//
//   Titre : Tarte aux pommes
//   Type : Dessert
//   Temps : 1 h 15
//   Personnes : 6
//   Auteur : Claude
//   Ingrédients :
//   - 200 g de farine
//   Étapes :
//   1. Préchauffer le four.

require_once __DIR__ . '/db.php';

// Minuscules sans accents ni ponctuation Markdown, pour reconnaître les intitulés.
function normaliser(string $texte): string
{
    $texte = strtr(trim($texte, " \t*_#:"), [
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a',
        'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ù' => 'u', 'û' => 'u', 'ç' => 'c',
        'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'À' => 'a', 'Î' => 'i', 'Ô' => 'o', 'Ç' => 'c',
    ]);
    return trim(strtolower($texte), " \t*_#"); // strtolower ne touche qu'aux lettres sans accent
}

// Intitulé tel qu'écrit (normalisé) => champ du formulaire.
const INTITULES = [
    'titre' => 'titre', 'nom' => 'titre',
    'type' => 'categorie', 'categorie' => 'categorie',
    'temps' => 'temps', 'temps total' => 'temps', 'temps de preparation' => 'temps', 'duree' => 'temps',
    'personnes' => 'personnes', 'nombre de personnes' => 'personnes', 'pour' => 'personnes',
    'portions' => 'personnes',
    'auteur' => 'auteur', 'source' => 'auteur',
    'ingredients' => 'ingredients',
    'etapes' => 'etapes', 'preparation' => 'etapes', 'instructions' => 'etapes',
];

// Retire la puce ou le numéro en début de ligne (« - », « • », « 1. », « 2) »…).
function sans_puce(string $ligne): string
{
    return trim(preg_replace('/^\s*(?:[-*•–]|\d+\s*[.)])\s+/u', '', $ligne));
}

// « 1 h 30 », « 1h30 », « 45 min », « 90 » => minutes (ou null).
function en_minutes(string $texte): ?int
{
    if (preg_match('/(\d+)\s*h\s*(\d+)?/i', $texte, $m)) {
        return (int)$m[1] * 60 + (int)($m[2] ?? 0);
    }
    return preg_match('/\d+/', $texte, $m) ? (int)$m[0] : null;
}

function analyser_recette(string $texte): array
{
    $r = ['titre' => '', 'categorie' => 'Plat', 'temps' => null, 'personnes' => null,
          'ingredients' => '', 'etapes' => '', 'auteur' => ''];
    $listes = ['ingredients' => [], 'etapes' => []];
    $section = null; // liste en cours de lecture (ingrédients ou étapes)
    $premiere_ligne = null;

    foreach (preg_split('/\R/u', $texte) as $ligne) {
        $ligne = trim(str_replace(['**', '__'], '', $ligne));
        if ($ligne === '') continue;
        $premiere_ligne ??= trim($ligne, " \t#");

        // Une ligne d'intitulé : « Clé : valeur » ou « ## Clé » (jamais une puce).
        $est_puce = sans_puce($ligne) !== $ligne;
        [$cle, $valeur] = array_pad(explode(':', $ligne, 2), 2, '');
        $champ = $est_puce ? null : (INTITULES[normaliser($cle)] ?? null);

        if ($champ === null) {
            if ($section) $listes[$section][] = sans_puce($ligne);
            continue;
        }

        $valeur = trim($valeur);
        if (isset($listes[$champ])) {
            $section = $champ;
            if ($valeur !== '') $listes[$section][] = sans_puce($valeur);
            continue;
        }
        $section = null;
        switch ($champ) {
            case 'temps':     $r['temps'] = en_minutes($valeur); break;
            case 'personnes': $r['personnes'] = preg_match('/\d+/', $valeur, $m) ? (int)$m[0] : null; break;
            case 'categorie':
                foreach (CATEGORIES as $c) {
                    if (normaliser($c) === normaliser($valeur)) $r['categorie'] = $c;
                }
                break;
            default: $r[$champ] = $valeur;
        }
    }

    // Pas de ligne « Titre : » ? On prend la première ligne du texte.
    if ($r['titre'] === '' && $premiere_ligne !== null && !isset(INTITULES[normaliser(explode(':', $premiere_ligne)[0])])) {
        $r['titre'] = $premiere_ligne;
    }
    $r['ingredients'] = implode("\n", $listes['ingredients']);
    $r['etapes'] = implode("\n", $listes['etapes']);
    return $r;
}

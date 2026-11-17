<?php
// potato drawings

const POTATO = 'M-95 5 C-100 -55 -45 -78 5 -74 C60 -72 102 -45 98 2 C95 55 50 78 -2 76 C-55 74 -92 50 -95 5 Z';
const STAR = 'M0 -9 Q1.6 -1.6 9 0 Q1.6 1.6 0 9 Q-1.6 1.6 -9 0 Q-1.6 -1.6 0 -9 Z';

// gold coin
function primospud() {
    return '<svg width="18" height="15" viewBox="-104 -84 208 168">
        <path d="' . POTATO . '" fill="#E8C66A" stroke="#3A2F22" stroke-width="12"/>
        <path d="' . STAR . '" fill="#F6FBF7" transform="translate(35 -20) scale(3.5)"/>
    </svg>';
}

// potato wearing stuff
function potato($items = [], $width = 90, $extra = '') {
    $height = round($width * 1.065);
    $svg = "<svg $extra width='$width' height='$height' viewBox='-115 -150 230 245'>";

    // the cape goes behind everything
    if (in_array('Tuber Cape', $items)) {
        $svg .= "<path d='M-72 -28 L72 -28 L108 90 L-108 90 Z'
                       fill='#B5473A' stroke='#3A2F22' stroke-width='5'/>";
    }

    // the leaf, unless a hat is covering it
    if (!array_intersect($items, ['Beanie', 'Top Hat', "Chef's Hat"])) {
        $svg .= "<path d='M8 -72 C2 -104 32 -124 64 -114 C58 -88 36 -72 8 -72 Z'
                       fill='#6FBF8E' stroke='#2F5A3D' stroke-width='5'/>";
        $svg .= "<line x1='10' y1='-74' x2='50' y2='-108' stroke='#2F5A3D' stroke-width='4'/>";
    }

    // body, spots and eyes
    $svg .= "<path d='" . POTATO . "' fill='#EED9B0' stroke='#3A2F22' stroke-width='6'/>";
    $svg .= "<circle cx='55' cy='36' r='5' fill='#D9BF8E'/>";
    $svg .= "<circle cx='-60' cy='40' r='4' fill='#D9BF8E'/>";
    $svg .= "<circle cx='-20' cy='-45' r='3' fill='#D9BF8E'/>";
    $svg .= "<circle cx='-32' cy='-6' r='8.5' fill='#1F2421'/>";
    $svg .= "<circle cx='32' cy='-6' r='8.5' fill='#1F2421'/>";

    foreach ($items as $item) {
        $svg .= accessory($item);
    }
    return $svg . '</svg>';
}

// accessories
function accessory($name) {
    $line = "stroke='#3A2F22' stroke-width='4'";

    $drawings = [
        'Beanie' => "
            <path d='M-64 -50 Q0 -128 64 -50 Z' fill='#4E9E73' $line/>
            <rect x='-68' y='-60' width='136' height='18' fill='#2F7A52' $line/>
            <circle cx='0' cy='-100' r='12' fill='#F6FBF7' $line/>",
        'Top Hat' => "
            <rect x='-42' y='-134' width='84' height='64' fill='#1F2421'/>
            <rect x='-42' y='-86' width='84' height='10' fill='#B5473A'/>
            <rect x='-72' y='-74' width='144' height='10' fill='#1F2421'/>",
        "Chef's Hat" => "
            <circle cx='-30' cy='-100' r='28' fill='#F6FBF7' $line/>
            <circle cx='30' cy='-100' r='28' fill='#F6FBF7' $line/>
            <circle cx='0' cy='-114' r='32' fill='#F6FBF7' $line/>
            <rect x='-46' y='-86' width='92' height='22' fill='#F6FBF7' $line/>",
        'Tiger Ears' => "
            <polygon points='-62,-48 -70,-100 -22,-70' fill='#E8893A' $line/>
            <polygon points='62,-48 70,-100 22,-70' fill='#E8893A' $line/>",
        'Round Glasses' => "
            <circle cx='-32' cy='-6' r='20' fill='none' stroke='#1F2421' stroke-width='4'/>
            <circle cx='32' cy='-6' r='20' fill='none' stroke='#1F2421' stroke-width='4'/>
            <line x1='-12' y1='-8' x2='12' y2='-8' stroke='#1F2421' stroke-width='4'/>",
        'Square Shades' => "
            <rect x='-54' y='-20' width='44' height='26' fill='#1F2421'/>
            <rect x='10' y='-20' width='44' height='26' fill='#1F2421'/>
            <line x1='-10' y1='-12' x2='10' y2='-12' stroke='#1F2421' stroke-width='6'/>",
        'Bow Tie' => "
            <polygon points='0,48 -30,33 -30,63' fill='#B5473A' $line/>
            <polygon points='0,48 30,33 30,63' fill='#B5473A' $line/>",
        'Sprout Crown' => crown(),
    ];

    return $drawings[$name] ?? '';
}

// the crown
function crown() {
    $rows = [
        'g....g....g',
        'y...yyy...y',
        'yy.yyyyy.yy',
        'yyyyyyyyyyy',
        'yyyyymyyyyy',
        'ddddddddddd',
    ];
    $colors = ['g' => '#6FBF8E', 'y' => '#E2B53E', 'm' => '#7FE0B2', 'd' => '#B8862A'];

    $svg = '';
    foreach ($rows as $row => $pixels) {
        foreach (str_split($pixels) as $col => $pixel) {
            if ($pixel != '.') {
                $x = -47 + $col * 8.5;
                $y = -113 + $row * 8.5;
                $svg .= "<rect x='$x' y='$y' width='9' height='9' fill='$colors[$pixel]'/>";
            }
        }
    }
    return $svg;
}

// stuff someone has equipped
function equipped($userId) {
    $items = query('SELECT e.slot, i.name, i.grade_id, g.name AS grade
                    FROM equipped e
                    JOIN items i ON i.item_id = e.item_id
                    LEFT JOIN grades g ON g.grade_id = i.grade_id
                    WHERE e.user_id = ?', $userId);
    return array_column($items, null, 'slot');
}
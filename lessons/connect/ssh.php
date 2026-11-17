<?php
// RIT specific SSH lesson setup
// lesson.php will set the $os the user chooses ('windows' or 'mac') and $prompt (the laptop's prompt) before including
// ^ all user chosen in the site btw
//   prompt  stuff shown before cursor
//   answer  what they need to type ('*' means anything, like passwords)
//   output  printed - when correct
//   wrong   printed - when wrong (btw $cmd is the first token they typed)
//   hint    if they get it wrong show under terminal this

$app = $os == 'mac' ? 'Terminal' : 'PowerShell';
$notFound = $os == 'mac'
    ? 'zsh: command not found: $cmd'
    : "\$cmd : The term '\$cmd' is not recognized as a command.";

return [
    [
        'name' => "Open $app",
        'text' => $os == 'mac'
            ? 'Press <b>Cmd + Space</b>, type <b>Terminal</b> and press Enter.'
            : 'Press the <b>Windows</b> key, type <b>PowerShell</b> and press Enter.',
        'more' => "That window is a shell. You type a command, press Enter, and it answers.
                   Try one here first: type <code>whoami</code> to ask who you are.",
        'terminal' => [
            'lines' => [
                [
                    'prompt' => $prompt,
                    'answer' => 'whoami',
                    'output' => 'spud',
                    'wrong'  => $notFound,
                    'hint'   => 'Type whoami and press Enter.',
                ],
            ],
            'end' => $prompt,
        ],
        'tip' => "$app comes with your computer. Nothing to install.",
    ],
    [
        'name' => 'Run the ssh command',
        'text' => "In $app, type the command below, then press Enter.
                   Swap <code>abc1234</code> for your own RIT username.",
        'more' => '<code>ssh abc1234@banjo.rit.edu</code>',
        'terminal' => [
            'lines' => [
                [
                    'prompt' => $prompt,
                    'answer' => 'ssh abc1234@banjo.rit.edu',
                    'output' => "The authenticity of host 'banjo.rit.edu' can't be established.",
                    'wrong'  => 'ssh: Could not resolve hostname: Name or service not known',
                    'hint'   => 'Check the spelling: your username, an @, then banjo.rit.edu',
                ],
                [
                    'prompt' => 'Are you sure you want to continue connecting (yes/no/[fingerprint])?',
                    'answer' => 'yes',
                    'output' => "Warning: Permanently added 'banjo.rit.edu' to the list of known hosts.",
                    'wrong'  => "Please type 'yes', 'no' or the fingerprint:",
                    'hint'   => 'Type yes. It only asks this the first time.',
                ],
            ],
            'end' => "abc1234@banjo.rit.edu's password:",
        ],
        'tip' => "banjo.rit.edu is for your people.rit.edu site (older guides call it gibson.rit.edu).
                  For this class you'll use web01.gccis.rit.edu.",
    ],
    [
        'name' => 'Type your password',
        'text' => 'Now it asks for your RIT password. Type it and press Enter.',
        'more' => "This is just practice, so type anything. Never put your real password into a website.",
        'terminal' => [
            'history' => "$prompt ssh abc1234@banjo.rit.edu",
            'lines' => [
                [
                    'prompt' => "abc1234@banjo.rit.edu's password:",
                    'answer' => '*',
                    'output' => 'Welcome to banjo.rit.edu',
                ],
            ],
            'end' => 'abc1234@banjo:~$',
        ],
        'tip' => "Your password won't show up as you type, not even dots. That's normal.",
    ],
    [
        'name' => "You're in",
        'text' => 'When the prompt changes to <code>abc1234@banjo:~$</code>, you\'re on the server.
                   Everything you type now runs there, not on your laptop.',
        'more' => 'Type <code>exit</code> to come back home.',
        'terminal' => [
            'history' => 'Welcome to banjo.rit.edu',
            'lines' => [
                [
                    'prompt' => 'abc1234@banjo:~$',
                    'answer' => 'exit',
                    'output' => "logout\nConnection to banjo.rit.edu closed.",
                    'wrong'  => '-bash: $cmd: command not found',
                    'hint'   => 'Type exit to leave the server.',
                ],
            ],
            'end' => $prompt,
        ],
        'tip' => 'Closing the window works too, but exit is the polite way out.',
    ],
];
// fake terminal checker
var script = JSON.parse(document.getElementById('script').textContent);
var screen = document.querySelector('.terminal pre');
var prompt = document.querySelector('.terminal .prompt');
var input = document.querySelector('.terminal input');
var hint = document.querySelector('.hint');
var line = 0;

// strip and check for rit username format
function clean(text) {
  text = text.trim().replace(/\s+/g, ' ');
  return text.replace(/\b[a-z]{2,3}\d{4}\b/g, 'abc1234');
}

function print(text) {
  screen.textContent += text + '\n';
}

function showPrompt() {
  var current = script.lines[line];

  if (current) {
    prompt.textContent = current.prompt;
    // no see pass
    input.classList.toggle('hidden', current.answer == '*');
  } else {
    // unlock next button on finish
    prompt.textContent = script.end;
    input.disabled = true;
    document.getElementById('next').classList.remove('locked');
  }
}

input.addEventListener('keydown', function (event) {
  if (event.key != 'Enter') {
    return;
  }

  var current = script.lines[line];
  var typed = clean(input.value);
  var isPassword = current.answer == '*';

  // echo the line no pass
  if (isPassword) {
    print(current.prompt);
  } else {
    print(current.prompt + ' ' + input.value);
  }
  input.value = '';

  if (typed == '') {
    return;
  }

  if (typed == current.answer || isPassword) {
    print(current.output);
    hint.textContent = '';
    line++;
    showPrompt();
  } else {
    var firstWord = typed.split(' ')[0];
    print(current.wrong.replace(/\$cmd/g, firstWord));
    hint.textContent = '✕ ' + current.hint;
  }
});

if (script.history) {
  print(script.history);
}
showPrompt();
input.focus();

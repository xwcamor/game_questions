(() => {
  const root = document.getElementById('game-app');
  if (!root) return;

  const cfg = root.dataset;
  const stage = document.getElementById('game-stage');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const host = cfg.isHost === '1';

  let state = null;
  let busy = false;
  let poller = null;
  let timer = null;
  let lastStateKey = '';
  let seconds = null;
  let sound = true;
  let audio = null;
  let selectedMultiple = new Set();
  let answerSubmitted = false;
  let feedbackText = '';
  let feedbackCorrect = null;
  let finishedCelebrated = false;

  const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[char]));

  async function post(url, data = {}) {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf
      },
      body: JSON.stringify(data)
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(payload.message || 'No se pudo completar la acción.');
    return payload;
  }

  function tone(frequency, duration = .12, type = 'sine') {
    if (!sound) return;
    try {
      audio ||= new (window.AudioContext || window.webkitAudioContext)();
      if (audio.state === 'suspended') audio.resume();
      const oscillator = audio.createOscillator();
      const gain = audio.createGain();
      oscillator.type = type;
      oscillator.frequency.value = frequency;
      gain.gain.setValueAtTime(.05, audio.currentTime);
      gain.gain.exponentialRampToValueAtTime(.001, audio.currentTime + duration);
      oscillator.connect(gain);
      gain.connect(audio.destination);
      oscillator.start();
      oscillator.stop(audio.currentTime + duration);
    } catch (_) { /* Audio puede estar bloqueado hasta la primera interacción. */ }
  }

  function sfx(kind) {
    if (kind === 'correct') {
      tone(660); setTimeout(() => tone(880), 140);
    } else if (kind === 'wrong') {
      tone(260, .18, 'triangle'); setTimeout(() => tone(190, .2, 'triangle'), 150);
    } else if (kind === 'start') {
      [523, 659, 784].forEach((note, index) => setTimeout(() => tone(note, .15), index * 130));
    } else if (kind === 'tick') {
      tone(750, .07, 'square');
    } else if (kind === 'reveal') {
      [440, 554, 659].forEach((note, index) => setTimeout(() => tone(note, .18), index * 120));
    } else if (kind === 'finish') {
      [523, 659, 784, 1047].forEach((note, index) => setTimeout(() => tone(note, .23), index * 130));
    }
  }

  function confetti() {
    const layer = document.createElement('div');
    layer.className = 'confetti-layer';
    for (let index = 0; index < 90; index++) {
      const piece = document.createElement('i');
      piece.className = 'confetti-piece';
      piece.style.left = Math.random() * 100 + '%';
      piece.style.animationDelay = Math.random() + 's';
      piece.style.setProperty('--hue', Math.random() * 360);
      layer.appendChild(piece);
    }
    document.body.appendChild(layer);
    setTimeout(() => layer.remove(), 4200);
  }

  function leaderboard(items) {
    if (!items?.length) return '<p class="muted">Todavía no hay participantes.</p>';
    return '<div class="leaderboard-list">' + items.map(item =>
      '<div class="leaderboard-item"><b>' + item.position + '</b><span>' + esc(item.avatar) +
      '</span><strong>' + esc(item.name) + '</strong><em>' +
      Number(item.score || 0).toLocaleString('es-PE') + '</em></div>'
    ).join('') + '</div>';
  }

  function side() {
    return '<aside class="game-panel game-side"><h3>🏆 Clasificación</h3>' +
      leaderboard(state.leaderboard || []) +
      '<hr><h3>👥 Participantes</h3><p class="muted">' +
      (state.participants?.length || 0) + ' conectados</p></aside>';
  }

  function postForm(url, label, cssClass = 'btn btn-primary') {
    return '<form data-action="' + url + '"><button class="' + cssClass + '">' + label + '</button></form>';
  }

  function renderLobby() {
    stage.innerHTML = '<div class="lobby-grid"><section class="game-panel">' +
      '<div class="room-hero"><span>✦ SALA ABIERTA</span><h2>¡Que comience la diversión!</h2>' +
      '<p>Comparte este código con tus participantes.</p><div class="big-code">' + esc(state.code) +
      '</div></div><h3>Participantes conectados</h3><div class="participant-grid">' +
      (state.participants || []).map(participant =>
        '<div class="participant-chip"><span>' + esc(participant.avatar) + '</span>' + esc(participant.name) + '</div>'
      ).join('') + '</div>' +
      (host
        ? '<div class="form-actions" style="margin-top:22px">' +
          postForm(cfg.startUrl, '🚀 Iniciar partida') +
          postForm(cfg.finishUrl, 'Finalizar', 'btn btn-danger-ghost') + '</div>'
        : '<p class="notice notice-info">Esperando que el anfitrión inicie la partida...</p>') +
      '</section>' + side() + '</div>';
  }

  async function submitAnswer(optionIds) {
    if (answerSubmitted || host) return;
    answerSubmitted = true;
    try {
      const result = await post(cfg.answerUrl, { option_ids: optionIds });
      feedbackCorrect = Boolean(result.is_correct);
      feedbackText = result.is_correct
        ? '✅ ¡Correcto! +' + Number(result.points_awarded).toLocaleString('es-PE') + ' puntos'
        : '❌ No es correcto. ¡Sigue atento!';
      sfx(result.is_correct ? 'correct' : 'wrong');
    } catch (error) {
      answerSubmitted = false;
      feedbackCorrect = false;
      feedbackText = error.message;
      selectedMultiple.clear();
    }
    await refresh();
  }

  function renderQuestion() {
    const question = state.question;
    if (!question) return renderLobby();

    const answered = host || state.my_answer || answerSubmitted;
    const started = state.started_at ? new Date(state.started_at).getTime() : Date.now();
    const elapsed = Math.max(0, (Date.now() - started) / 1000);
    const limit = Number(question.time_limit || 20);
    const remaining = Math.max(0, Math.ceil(limit - elapsed));
    const isMultiple = question.type === 'multiple_choice';

    const choices = (question.options || []).map((option, index) => {
      const isSelected = selectedMultiple.has(Number(option.id));
      return '<button class="game-answer ' + (isSelected ? 'selected' : '') +
        '" data-option="' + option.id + '" aria-pressed="' + isSelected + '"' +
        (answered ? ' disabled' : '') + '><span class="answer-letter">' +
        String.fromCharCode(65 + index) + '</span>' + esc(option.text) + '</button>';
    }).join('');

    stage.innerHTML = '<div class="game-grid"><section class="game-panel">' +
      '<div style="display:flex;justify-content:space-between;align-items:center">' +
      '<span class="pill pill-purple">PREGUNTA ' + (state.current_index + 1) + ' / ' + state.total_questions + '</span>' +
      '<span class="muted">' + esc(question.category || 'General') + '</span></div>' +
      '<div class="timer-ring" style="--timer-progress:' + Math.min(100, remaining / limit * 100) + '%"><span>' +
      remaining + '</span></div><h2 class="question-title">' + esc(question.text) + '</h2>' +
      (isMultiple ? '<p class="helper-text">Selecciona todas las alternativas correctas y confirma tu respuesta.</p>' : '') +
      '<div class="answer-grid">' + choices + '</div>' +
      (isMultiple && !answered
        ? '<button class="btn btn-primary btn-block" type="button" data-submit-multiple style="margin-top:14px">Confirmar respuestas</button>'
        : '') +
      '<p id="answer-feedback" class="muted" style="margin-top:15px">' +
      (state.my_answer || answerSubmitted
        ? (feedbackText || '✓ Respuesta registrada. ¡Buena suerte!')
        : (host ? 'Vista del anfitrión. Controla el avance de la partida.' : 'Selecciona tu respuesta antes de que termine el tiempo.')) +
      '</p>' +
      (host ? '<div class="form-actions" style="margin-top:18px">' + postForm(cfg.revealUrl, 'Revelar respuesta') + '</div>' : '') +
      '</section>' + side() + '</div>';

    const ring = stage.querySelector('.timer-ring span');
    clearInterval(timer);
    timer = setInterval(() => {
      if (!ring) return;
      const left = Math.max(0, Math.ceil(limit - (Date.now() - started) / 1000));
      ring.textContent = left;
      if (left <= 5 && left > 0 && seconds !== left) {
        seconds = left;
        sfx('tick');
      }
      if (left === 0) clearInterval(timer);
    }, 200);
  }

  function renderReveal() {
    const question = state.question;
    if (!question) return renderLobby();
    const correct = new Set((question.correct_option_ids || []).map(Number));
    const options = question.options || [];

    stage.innerHTML = '<div class="game-grid"><section class="game-panel">' +
      '<span class="pill pill-green">RESPUESTA REVELADA</span><h2 class="question-title">' +
      esc(question.text) + '</h2><div class="answer-grid">' +
      options.map((option, index) => {
        const isCorrect = correct.has(Number(option.id));
        return '<div class="game-answer ' + (isCorrect ? 'correct-answer' : 'wrong-answer') + '">' +
          '<span class="answer-letter">' + String.fromCharCode(65 + index) + '</span>' +
          esc(option.text) + (isCorrect ? ' ✓' : '') + '</div>';
      }).join('') + '</div><div class="reveal-banner">✓ ' +
      (question.answer_summary?.filter(answer => answer.correct).length || 0) +
      ' respuestas correctas</div>' +
      (question.explanation ? '<p>' + esc(question.explanation) + '</p>' : '') +
      '<h3>Respuestas de los participantes</h3><div class="summary-list">' +
      (question.answer_summary || []).map(answer =>
        '<div class="summary-row"><strong>' + (answer.correct ? '✅' : '❌') + ' ' +
        esc(answer.name) + '</strong><span>' + Number(answer.points || 0).toLocaleString('es-PE') +
        ' pts · ' + (Number(answer.time_ms || 0) / 1000).toFixed(2) + ' s</span></div>'
      ).join('') + '</div>' +
      (host
        ? '<div class="form-actions" style="margin-top:18px">' + postForm(cfg.nextUrl, 'Siguiente pregunta →') + '</div>'
        : '<p class="notice notice-info">El anfitrión está preparando la siguiente pregunta.</p>') +
      '</section>' + side() + '</div>';
  }

  function renderFinished() {
    const top = (state.leaderboard || []).slice(0, 3);
    stage.innerHTML = '<section class="game-panel" style="text-align:center"><span class="eyebrow">PARTIDA FINALIZADA</span>' +
      '<h1 style="font:800 38px Outfit,sans-serif">¡Tenemos ganadores!</h1>' +
      '<p class="muted">Gracias por participar en PlayLab.</p><div class="podium">' +
      [top[1], top[0], top[2]].map((participant, index) => participant
        ? '<div class="podium-place ' + (index === 1 ? 'first' : '') + '"><div style="font-size:30px">' +
          (index === 1 ? '🏆' : index === 0 ? '🥈' : '🥉') + '</div><strong>' +
          esc(participant.name) + '</strong><small>' +
          Number(participant.score || 0).toLocaleString('es-PE') + ' puntos</small></div>'
        : '<div></div>').join('') +
      '</div><h3>Ranking final</h3>' + leaderboard(state.leaderboard || []) +
      '<a class="btn btn-outline" href="/">Volver al inicio</a></section>';

    if (!finishedCelebrated) {
      finishedCelebrated = true;
      sfx('finish');
      confetti();
    }
  }

  async function refresh() {
    if (busy) return;
    busy = true;
    try {
      const response = await fetch(cfg.stateUrl, { headers: { 'Accept': 'application/json' } });
      if (!response.ok) throw new Error('No se pudo sincronizar la sala.');
      const next = await response.json();
      const nextKey = next.status + ':' + next.phase + ':' + next.current_index;

      if (nextKey !== lastStateKey) {
        clearInterval(timer);
        seconds = null;
        selectedMultiple.clear();
        answerSubmitted = Boolean(next.my_answer);
        feedbackText = '';
        feedbackCorrect = null;
        if (next.status === 'running' && next.phase === 'question') sfx('start');
        if (next.phase === 'reveal') sfx('reveal');
        if (next.status === 'finished') sfx('finish');
        lastStateKey = nextKey;
      } else if (next.my_answer) {
        answerSubmitted = true;
      }

      state = next;
      if (next.status === 'finished' || next.phase === 'finished') renderFinished();
      else if (next.status === 'lobby') renderLobby();
      else if (next.phase === 'reveal') renderReveal();
      else renderQuestion();
    } catch (error) {
      stage.innerHTML = '<div class="notice notice-info">' + esc(error.message) +
        ' <button class="btn btn-outline btn-sm" onclick="location.reload()">Reintentar</button></div>';
    } finally {
      busy = false;
    }
  }

  stage.addEventListener('click', async event => {
    const answer = event.target.closest('[data-option]');
    if (answer) {
      if (!state || state.phase !== 'question' || host || state.my_answer || answerSubmitted) return;
      const optionId = Number(answer.dataset.option);
      if (state.question?.type === 'multiple_choice') {
        if (selectedMultiple.has(optionId)) selectedMultiple.delete(optionId);
        else selectedMultiple.add(optionId);
        answer.classList.toggle('selected', selectedMultiple.has(optionId));
        answer.setAttribute('aria-pressed', String(selectedMultiple.has(optionId)));
      } else {
        await submitAnswer([optionId]);
      }
      return;
    }

    if (event.target.closest('[data-submit-multiple]')) {
      if (selectedMultiple.size === 0) {
        alert('Selecciona al menos una alternativa.');
        return;
      }
      await submitAnswer([...selectedMultiple]);
      return;
    }

    const form = event.target.closest('form[data-action]');
    if (form) {
      event.preventDefault();
      const button = form.querySelector('button');
      button.disabled = true;
      try {
        await post(form.dataset.action);
        await refresh();
      } catch (error) {
        alert(error.message);
        button.disabled = false;
      }
    }
  });

  document.getElementById('sound-toggle')?.addEventListener('click', event => {
    sound = !sound;
    event.currentTarget.textContent = sound ? '🔊 Sonido' : '🔇 Silenciado';
    if (sound) sfx('start');
  });

  refresh();
  poller = setInterval(refresh, 1100);
  window.addEventListener('beforeunload', () => {
    clearInterval(poller);
    clearInterval(timer);
  });
})();

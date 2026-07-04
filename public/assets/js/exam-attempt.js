(function () {
  const config = window.examAttemptConfig;
  if (!config) return;

  const cards = Array.from(document.querySelectorAll('.exam-question-card'));
  const nav = document.getElementById('question-nav');
  const timerEl = document.getElementById('exam-timer');
  let current = 0;

  cards.forEach((card, index) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-secondary btn-sm';
    btn.textContent = index + 1;
    btn.addEventListener('click', () => showQuestion(index));
    nav.appendChild(btn);
  });

  function showQuestion(index) {
    current = Math.max(0, Math.min(index, cards.length - 1));
    cards.forEach((c, i) => { c.style.display = i === current ? 'block' : 'none'; });
    Array.from(nav.children).forEach((b, i) => b.classList.toggle('btn-primary', i === current));
  }

  document.getElementById('prev-question')?.addEventListener('click', () => showQuestion(current - 1));
  document.getElementById('next-question')?.addEventListener('click', () => showQuestion(current + 1));
  showQuestion(0);

  document.querySelectorAll('.essay-answer').forEach((el) => {
    el.addEventListener('copy', (e) => e.preventDefault());
    el.addEventListener('cut', (e) => e.preventDefault());
    el.addEventListener('paste', (e) => e.preventDefault());
    el.addEventListener('drop', (e) => e.preventDefault());
  });

  function collectAnswers() {
    const form = document.getElementById('exam-attempt-form');
    const fd = new FormData(form);
    const map = {};
    for (const [key, value] of fd.entries()) {
      const m = key.match(/^answers\[(\d+)\]\[(.+?)\](\[\])?$/);
      if (!m) continue;
      const idx = m[1];
      const field = m[2];
      map[idx] = map[idx] || { exam_quiz_question_id: null };
      if (field === 'exam_quiz_question_id') map[idx].exam_quiz_question_id = parseInt(value, 10);
      if (field === 'answer_text') map[idx].answer_text = value;
      if (field === 'selected_choice_id') map[idx].selected_choice_id = parseInt(value, 10);
      if (field === 'selected_choice_ids') {
        map[idx].selected_choice_ids = map[idx].selected_choice_ids || [];
        map[idx].selected_choice_ids.push(parseInt(value, 10));
      }
    }
    return Object.values(map);
  }

  async function autosave() {
    try {
      await fetch(config.autosaveUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': config.csrf,
        },
        body: JSON.stringify({ answers: collectAnswers() }),
      });
    } catch (e) {
      console.warn('Autosave failed', e);
    }
  }

  setInterval(autosave, config.autosaveInterval);

  const expires = new Date(config.expiresAt).getTime();
  const timer = setInterval(() => {
    const diff = expires - Date.now();
    if (diff <= 0) {
      clearInterval(timer);
      timerEl.textContent = 'انتهى الوقت';
      document.getElementById('exam-attempt-form')?.submit();
      return;
    }
    const m = Math.floor(diff / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    timerEl.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
  }, 1000);
})();

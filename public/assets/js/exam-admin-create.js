(function () {
  const config = window.examAdminCreateConfig;
  if (!config) return;

  const sectionSelect = document.getElementById('class-section-id');
  const contextPanel = document.getElementById('section-context-panel');
  const contextSummary = document.getElementById('context-summary');
  const bankStatsEl = document.getElementById('bank-stats');
  const categoryBlock = document.getElementById('category-filter-block');
  const categorySelect = document.getElementById('category-ids');
  const questionCountInput = document.getElementById('question-count');
  const questionCountHint = document.getElementById('question-count-hint');
  const startsAtInput = document.getElementById('starts-at');
  const endsAtInput = document.getElementById('ends-at');
  const durationInput = document.getElementById('duration-minutes');
  const typeCheckboxes = Array.from(document.querySelectorAll('.question-type-filter'));

  let currentContext = null;

  function formatDatetimeLocal(date) {
    const pad = (n) => String(n).padStart(2, '0');
    return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate())
      + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
  }

  function suggestEndsAt() {
    if (!startsAtInput.value) return;
    const start = new Date(startsAtInput.value);
    const duration = parseInt(durationInput.value || '60', 10);
    if (Number.isNaN(duration)) return;
    const end = new Date(start.getTime() + duration * 60000);
    endsAtInput.value = formatDatetimeLocal(end);
  }

  function selectedTypes() {
    return typeCheckboxes.filter((cb) => cb.checked).map((cb) => cb.value);
  }

  function selectedCategoryIds() {
    return Array.from(categorySelect.selectedOptions).map((o) => parseInt(o.value, 10));
  }

  function filterStats(ctx) {
    if (!ctx) return { total: 0, by_type: {} };
    const types = selectedTypes();
    const categoryIds = selectedCategoryIds();
    let total = 0;
    const byType = {};

    Object.keys(config.questionTypes).forEach((type) => {
      byType[type] = types.includes(type) ? (ctx.bank_stats.by_type[type] || 0) : 0;
    });

    if (categoryIds.length === 0) {
      types.forEach((type) => { total += byType[type] || 0; });
      return { total, by_type: byType };
    }

    total = 0;
    ctx.categories.forEach((cat) => {
      if (!categoryIds.includes(cat.id)) return;
      total += cat.questions_count;
    });

    return { total, by_type: byType };
  }

  function renderStats(ctx) {
    const stats = filterStats(ctx);
    const typeLines = Object.keys(config.questionTypes).map((type) => {
      return config.questionTypes[type] + ': ' + (stats.by_type[type] || 0);
    }).join(' | ');

    bankStatsEl.innerHTML = '<strong>الأسئلة المتاحة في البنك:</strong> ' + stats.total
      + '<div style="margin-top:4px;">' + typeLines + '</div>';

    const requested = parseInt(questionCountInput.value || '0', 10);
    if (stats.total < requested) {
      questionCountHint.textContent = 'تحذير: طلبت ' + requested + ' سؤالاً لكن المتاح ' + stats.total + ' فقط للمادة والفلاتر المحددة.';
      questionCountHint.style.color = '#c53030';
    } else {
      questionCountHint.textContent = 'الدرجة الكاملة 100 وتُوزَّع تلقائياً. المتاح حالياً: ' + stats.total + ' سؤال.';
      questionCountHint.style.color = '';
    }
  }

  function renderCategories(ctx) {
    categorySelect.innerHTML = '';
    ctx.categories.forEach((cat) => {
      const opt = document.createElement('option');
      opt.value = cat.id;
      opt.textContent = cat.name + ' (' + cat.questions_count + ' سؤال)';
      if (config.oldCategoryIds.map(String).includes(String(cat.id))) {
        opt.selected = true;
      }
      categorySelect.appendChild(opt);
    });
    categoryBlock.style.display = ctx.categories.length ? 'block' : 'none';
  }

  async function loadSectionContext(sectionId) {
    if (!sectionId) {
      contextPanel.style.display = 'none';
      currentContext = null;
      return;
    }

    try {
      const res = await fetch(config.sectionContextUrl + '/' + sectionId, {
        headers: { Accept: 'application/json' },
      });
      if (!res.ok) throw new Error('failed');
      const ctx = await res.json();
      currentContext = ctx;

      contextPanel.style.display = 'block';
      contextSummary.innerHTML = [
        '<div><strong>المادة:</strong> ' + (ctx.subject_name || '—') + '</div>',
        '<div><strong>الدكتور:</strong> ' + (ctx.doctor_name || '—') + '</div>',
        '<div><strong>الشعبة:</strong> ' + (ctx.section_name || '—') + '</div>',
        '<div><strong>الفصل:</strong> ' + (ctx.semester_name || '—') + '</div>',
        '<div><strong>الطلاب المسجلون:</strong> ' + (ctx.students_count || 0) + '</div>',
      ].join('');

      renderCategories(ctx);
      renderStats(ctx);
    } catch (e) {
      contextPanel.style.display = 'none';
      currentContext = null;
    }
  }

  sectionSelect.addEventListener('change', () => loadSectionContext(sectionSelect.value));
  categorySelect.addEventListener('change', () => renderStats(currentContext));
  typeCheckboxes.forEach((cb) => cb.addEventListener('change', () => renderStats(currentContext)));
  questionCountInput.addEventListener('input', () => renderStats(currentContext));
  startsAtInput.addEventListener('change', suggestEndsAt);
  durationInput.addEventListener('change', suggestEndsAt);

  if (config.oldSectionId) {
    loadSectionContext(config.oldSectionId);
  }
})();

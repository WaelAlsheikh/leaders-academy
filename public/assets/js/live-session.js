(function () {
  const config = window.liveSessionPageConfig;
  if (!config) {
    return;
  }

  const state = {
    role: config.role,
    api: null,
    isModerator: false,
    recordingOn: false,
    joinedConference: false,
    localLeft: false,
    initializingEmbed: false,
    passwordApplied: false,
    tileViewApplied: false,
    canEmbed: typeof config.initialState?.canEmbed === 'undefined' ? !!config.embedEnabled : !!config.initialState.canEmbed,
    doctorReady: !!config.initialState?.doctorReady,
    commentsEnabled: !!config.initialState?.commentsEnabled,
    commentsBlocked: !!config.initialState?.commentsBlocked,
    audioModerationEnabled: !!config.initialState?.audioModerationEnabled,
    videoModerationEnabled: !!config.initialState?.videoModerationEnabled,
    ended: !!config.initialState?.ended || config.initialState?.statusCode === 'ended',
    entryClosed: !!config.initialState?.entryClosed || config.initialState?.statusCode === 'entry_closed',
  };

  const standaloneWindow = !!config.jitsiStandaloneWindow;
  let studentStandaloneHeartbeatStarted = false;

  const els = {
    jitsiContainer: document.getElementById('jitsi-meeting-container'),
    statusBanner: document.getElementById('live-session-status-banner'),
    commentsList: document.getElementById('comments-list'),
    commentsMeta: document.getElementById('comments-meta'),
    studentCommentForm: document.getElementById('student-comment-form'),
    studentCommentBody: document.getElementById('student-comment-body'),
    studentCommentsDisabledNote: document.getElementById('student-comments-disabled-note'),
    doctorCommentForm: document.getElementById('doctor-comment-form'),
    doctorCommentBody: document.getElementById('doctor-comment-body'),
    attendanceSummary: document.getElementById('attendance-summary'),
    attendanceTableBody: document.getElementById('attendance-table-body'),
    blockSelectedBtn: document.getElementById('block-selected-comments-btn'),
    unblockSelectedBtn: document.getElementById('unblock-selected-comments-btn'),
    toggleCommentsBtn: document.getElementById('doctor-toggle-comments-btn'),
    toggleAudioBtn: document.getElementById('doctor-toggle-audio-moderation-btn'),
    toggleVideoBtn: document.getElementById('doctor-toggle-video-moderation-btn'),
    recordingBtn: document.getElementById('doctor-recording-btn'),
    moderatorNote: document.getElementById('doctor-moderator-note'),
    openDirectJitsiBtn: document.getElementById('doctor-open-direct-jitsi-btn'),
    standaloneHostReadyBtn: document.getElementById('doctor-standalone-host-ready-btn'),
    screenShareNote: document.getElementById('doctor-screen-share-note'),
  };

  const request = async (method, url, data) => {
    const response = await window.axios({
      method,
      url,
      data,
      headers: {
        Accept: 'application/json',
      },
    });

    return response.data;
  };

  const escapeHtml = (value) => {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
  };

  const showBanner = (message) => {
    if (!els.statusBanner) return;
    if (!message) {
      els.statusBanner.style.display = 'none';
      els.statusBanner.textContent = '';
      return;
    }

    els.statusBanner.textContent = message;
    els.statusBanner.style.display = 'block';
  };

  const showDeviceHelpMessage = (fallbackMessage) => {
    showBanner(
      fallbackMessage ||
      'تعذر تجهيز الكاميرا أو الميكروفون داخل نافذة المحاضرة. اسمح بالأذونات لنطاق meet.jit.si أو افتح الجلسة في نافذة مستقلة ثم فعّل الكاميرا/الصوت من هناك.'
    );
  };

  const setScreenShareNoteVisibility = (visible) => {
    if (!els.screenShareNote) return;
    els.screenShareNote.style.display = visible ? 'block' : 'none';
  };

  const setStudentCommentAvailability = () => {
    if (state.role !== 'student') return;

    const disabled = state.ended || !state.commentsEnabled || state.commentsBlocked;

    if (els.studentCommentForm) {
      els.studentCommentForm.style.display = disabled ? 'none' : 'flex';
    }

    if (els.studentCommentsDisabledNote) {
      if (disabled) {
        let text = 'التعليقات متوقفة حالياً.';
        if (state.commentsBlocked) {
          text = 'تم إيقاف إمكانية التعليق لحسابك في هذه الجلسة.';
        } else if (state.ended) {
          text = 'انتهت الجلسة ولا يمكن إضافة تعليقات جديدة.';
        }

        els.studentCommentsDisabledNote.textContent = text;
        els.studentCommentsDisabledNote.style.display = 'block';
      } else {
        els.studentCommentsDisabledNote.style.display = 'none';
      }
    }

    if (els.commentsMeta) {
      els.commentsMeta.textContent = state.commentsEnabled ? 'مفعّلة' : 'متوقفة';
    }
  };

  const updateDoctorActionLabels = () => {
    if (state.role !== 'doctor') return;

    if (els.toggleCommentsBtn) {
      els.toggleCommentsBtn.textContent = state.commentsEnabled ? 'إيقاف التعليقات' : 'السماح بالتعليقات';
    }
    if (els.toggleAudioBtn) {
      els.toggleAudioBtn.textContent = state.audioModerationEnabled ? 'فتح صوت الطلاب' : 'تقييد صوت الطلاب';
    }
    if (els.toggleVideoBtn) {
      els.toggleVideoBtn.textContent = state.videoModerationEnabled ? 'فتح فيديو الطلاب' : 'تقييد فيديو الطلاب';
    }
    if (els.recordingBtn) {
      els.recordingBtn.textContent = state.recordingOn ? 'إيقاف التسجيل المحلي' : 'بدء تسجيل محلي';
    }
    if (els.commentsMeta) {
      els.commentsMeta.textContent = state.commentsEnabled ? 'مفعّلة' : 'متوقفة';
    }
    if (els.moderatorNote) {
      els.moderatorNote.style.display = state.isModerator ? 'none' : 'block';
    }
  };

  const renderLeadersPlaceholder = (message, subtext) => {
    if (!els.jitsiContainer) return;

    const logoUrl = config.branding?.logoUrl || '';
    const title = config.branding?.title || 'Leaders';
    const safeMessage = escapeHtml(message || 'انتهت الجلسة الحالية.');
    const safeSubtext = escapeHtml(subtext || 'يمكنك العودة إلى المنصة ومتابعة الجلسات الأخرى.');

    els.jitsiContainer.innerHTML = `
      <div class="live-session-placeholder">
        <div class="live-session-placeholder-brand">
          ${logoUrl ? `<img src="${logoUrl}" alt="${escapeHtml(title)}">` : ''}
          <div class="live-session-placeholder-title">${escapeHtml(title)}</div>
        </div>
        <div class="live-session-placeholder-message">${safeMessage}</div>
        <div class="live-session-placeholder-subtext">${safeSubtext}</div>
      </div>
    `;
  };

  const renderStudentWaitingPlaceholder = () => {
    if (state.role !== 'student' || state.ended || state.localLeft) {
      return;
    }

    if (state.entryClosed) {
      renderLeadersPlaceholder(
        'توقفت إمكانية الدخول لهذه الجلسة.',
        'أغلق الدكتور باب الدخول حالياً. إذا أعاد فتحه أو ما زالت الجلسة مستمرة يمكنك التحديث والمحاولة مجددًا.'
      );
      return;
    }

    if (!state.doctorReady) {
      renderLeadersPlaceholder(
        'بانتظار اعتماد الدكتور للجلسة.',
        standaloneWindow
          ? 'سيظهر هنا زر الدخول إلى القاعة بمجرد أن يفتح الدكتور المحاضرة في نافذة كاملة ويُفعّل السماح لطلابه بالحضور من صفحته.'
          : 'سيظهر الفيديو تلقائيًا هنا فور أن يبدأ الدكتور الاستضافة ويضغط "أنا المضيف".'
      );
      return;
    }

    if (!state.canEmbed) {
      renderLeadersPlaceholder(
        'المحاضرة بدأت لكن الدخول غير متاح بعد.',
        standaloneWindow
          ? 'انتظر أن يفعِّل الدكتور «السماح للطلاب بالدخول» من لوحته أو يعيد فتح باب الدخول.'
          : 'يرجى الانتظار قليلًا، وسيتم فتح نافذة الفيديو تلقائيًا عند جاهزية الجلسة.'
      );
    }
  };

  const syncDoctorPresence = async (isReady, useKeepalive = false) => {
    if (state.role !== 'doctor' || !config.endpoints?.hostPresence) {
      return;
    }

    if (useKeepalive) {
      try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        await fetch(config.endpoints.hostPresence, {
          method: 'POST',
          credentials: 'same-origin',
          keepalive: true,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token || '',
          },
          body: JSON.stringify({ is_ready: !!isReady }),
        });
      } catch (error) {
        console.warn('Unable to sync doctor host presence with keepalive', error);
      }

      return;
    }

    try {
      await request('post', config.endpoints.hostPresence, {
        is_ready: !!isReady,
      });
    } catch (error) {
      console.warn('Unable to sync doctor host presence', error);
    }
  };

  const ensureStudentStandaloneHeartbeat = () => {
    if (state.role !== 'student' || studentStandaloneHeartbeatStarted || state.ended || state.localLeft) {
      return;
    }

    studentStandaloneHeartbeatStarted = true;
    heartbeat(null);
    const heartbeatInterval = Math.max(Number(config.timers?.heartbeatIntervalSeconds || 12), 8) * 1000;
    window.setInterval(() => heartbeat(), heartbeatInterval);
  };

  const renderStudentStandaloneJoinCard = () => {
    if (!els.jitsiContainer) return;

    const url = config.embedPayload?.meetLaunchUrl || config.embedPayload?.meetingUrl;
    const safeSubject = escapeHtml(config.embedPayload?.subject || 'قاعة المحاضرة');

    els.jitsiContainer.innerHTML = `
      <div class="live-session-placeholder student-standalone-room-card">
        <div class="live-session-placeholder-message">الجلسة جارية</div>
        <div class="live-session-placeholder-subtext">${safeSubject}<br/><span style="opacity:.85;font-size:.93em;display:inline-block;margin-top:10px;line-height:1.6;">مع الخادم العام لـ Jitsi لا يمكن إبقاء الفيديو مضمّناً هنا لفترات طويلة دون قطع؛ لذلك ادخل إلى القاعة من الزر أدناه في نافذة متصفّح كاملة.</span></div>
        <button type="button" class="btn btn-primary" id="student-open-jitsi-standalone-btn" ${url ? '' : 'disabled'}>
          فتح المحاضرة في نافذة جديدة
        </button>
      </div>
    `;

    if (!url) {
      return;
    }

    const btn = document.getElementById('student-open-jitsi-standalone-btn');
    btn?.addEventListener('click', () => {
      const opened = window.open(url, '_blank', 'noopener,noreferrer');
      ensureStudentStandaloneHeartbeat();
      if (!opened) {
        showBanner('لم تُفتَح النافذة؛ اسمح بالنوافذ المنبثقة لهذا الموقع ثم حاول مجدداً.');
      } else if (els.statusBanner) {
        els.statusBanner.style.display = 'none';
      }
    });
  };

  const maybeInitStudentEmbed = () => {
    if (state.role !== 'student') {
      return;
    }

    if (!config.embedEnabled || !config.embedPayload || !els.jitsiContainer) {
      return;
    }

    if (state.api || state.initializingEmbed || state.localLeft || state.ended) {
      return;
    }

    if (!state.canEmbed || !state.doctorReady) {
      renderStudentWaitingPlaceholder();
      return;
    }

    if (standaloneWindow) {
      renderStudentStandaloneJoinCard();
      return;
    }

    state.initializingEmbed = true;
    renderLeadersPlaceholder('جارٍ فتح قاعة المحاضرة...', 'يرجى الانتظار لثوانٍ قليلة حتى يتم تجهيز الفيديو.');
    initJitsi().finally(() => {
      state.initializingEmbed = false;
    });
  };

  const disposeConference = (message, subtext) => {
    state.localLeft = true;
    state.joinedConference = false;
    if (state.api) {
      try {
        state.api.dispose();
      } catch (error) {
        console.warn('Failed to dispose Jitsi conference', error);
      }
      state.api = null;
    }

    if (els.jitsiContainer) {
      renderLeadersPlaceholder(
        message || 'الجلسة غير متاحة حالياً.',
        subtext || 'يمكنك انتظار إعادة فتحها أو العودة إلى الصفحة السابقة.'
      );
    }
  };

  const handleSessionFlags = (session) => {
    if (!session) return;

    state.ended = !!session.ended || session.status_code === 'ended' || session.code === 'ended';
    state.entryClosed = !!session.entry_closed || session.status_code === 'entry_closed' || session.code === 'entry_closed';
    if (typeof session.can_embed !== 'undefined') {
      state.canEmbed = !!session.can_embed;
    }
    if (typeof session.doctor_ready !== 'undefined') {
      state.doctorReady = !!session.doctor_ready;
    }
    if (typeof session.comments_enabled !== 'undefined') {
      state.commentsEnabled = !!session.comments_enabled;
    }
    if (typeof session.comments_blocked !== 'undefined') {
      state.commentsBlocked = !!session.comments_blocked;
    }
    if (typeof session.audio_moderation_enabled !== 'undefined') {
      state.audioModerationEnabled = !!session.audio_moderation_enabled;
    }
    if (typeof session.video_moderation_enabled !== 'undefined') {
      state.videoModerationEnabled = !!session.video_moderation_enabled;
    }

    const label = session.status_label || session.label;
    if (label) {
      showBanner(state.ended || state.entryClosed ? label : '');
    }

    if (state.ended) {
      disposeConference('تم إنهاء الجلسة.', 'أغلق المحاضر هذه الجلسة. يمكنك العودة إلى منصة ليدرز.');
    }

    if (state.role === 'student' && !state.ended && !state.api) {
      maybeInitStudentEmbed();
    }

    setStudentCommentAvailability();
    updateDoctorActionLabels();
  };

  const renderComments = (comments) => {
    if (!els.commentsList) return;

    if (!comments || comments.length === 0) {
      els.commentsList.innerHTML = '<div class="doctor-portal-empty doctor-portal-empty-inline">لا توجد تعليقات حتى الآن.</div>';
      return;
    }

    const html = comments.map((comment) => {
      const hiddenClass = comment.is_hidden ? ' live-session-comment-hidden' : '';
      const hideButton = state.role === 'doctor' && !comment.is_hidden
        ? `<button type="button" class="btn btn-secondary live-session-comment-hide-btn" data-comment-id="${comment.id}">إخفاء</button>`
        : '';

      return `
        <div class="live-session-comment${hiddenClass}">
          <div class="live-session-comment-head">
            <strong>${escapeHtml(comment.author_name)}</strong>
            <span>${escapeHtml(comment.created_at || '')}</span>
          </div>
          <div class="live-session-comment-body">${escapeHtml(comment.body)}</div>
          <div class="live-session-comment-actions">
            ${comment.is_hidden ? '<span class="doctor-inline-note">تم إخفاء هذا التعليق</span>' : ''}
            ${hideButton}
          </div>
        </div>
      `;
    }).join('');

    els.commentsList.innerHTML = html;
    els.commentsList.scrollTop = els.commentsList.scrollHeight;
  };

  const fetchComments = async () => {
    if (!config.endpoints?.comments) return;

    try {
      const data = await request('get', config.endpoints.comments);
      renderComments(data.comments || []);
      handleSessionFlags(data.session);
    } catch (error) {
      console.warn('Unable to fetch comments', error);
    }
  };

  const fetchAttendance = async () => {
    if (state.role !== 'doctor' || !config.endpoints?.attendance) return;

    try {
      const data = await request('get', config.endpoints.attendance);
      handleSessionFlags(data.session);

      if (els.attendanceSummary) {
        els.attendanceSummary.textContent = `إجمالي الطلاب: ${data.summary.total_students} — دخلوا: ${data.summary.joined_students} — حاضرون الآن: ${data.summary.present_students}`;
      }

      if (!els.attendanceTableBody) return;

      if (!data.students || data.students.length === 0) {
        els.attendanceTableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;">لا يوجد طلاب في هذه الشعبة.</td></tr>';
        return;
      }

      els.attendanceTableBody.innerHTML = data.students.map((student) => `
        <tr>
          <td><input type="checkbox" class="attendance-student-checkbox" value="${student.id}"></td>
          <td>${escapeHtml(student.full_name)}</td>
          <td>${escapeHtml(student.username)}</td>
          <td>
            ${student.is_present
              ? '<span class="live-attendance-badge live-attendance-badge-present">حاضر الآن</span>'
              : (student.has_joined
                  ? '<span class="live-attendance-badge live-attendance-badge-left">غادر</span>'
                  : '<span class="live-attendance-badge live-attendance-badge-pending">لم يدخل</span>')}
          </td>
          <td>${escapeHtml(student.joined || '—')}</td>
          <td>${escapeHtml(student.last_seen || '—')}</td>
          <td>${student.comments_blocked ? 'محظورة' : 'مسموحة'}</td>
        </tr>
      `).join('');
    } catch (error) {
      console.warn('Unable to fetch attendance', error);
    }
  };

  const getSelectedStudentIds = () => {
    return Array.from(document.querySelectorAll('.attendance-student-checkbox:checked'))
      .map((checkbox) => Number.parseInt(checkbox.value, 10))
      .filter(Boolean);
  };

  const updateCommentBlocks = async (action) => {
    const studentIds = getSelectedStudentIds();
    if (studentIds.length === 0) {
      alert('يرجى اختيار طالب واحد على الأقل.');
      return;
    }

    try {
      await request('post', config.endpoints.commentBlocks, {
        action,
        student_ids: studentIds,
      });
      fetchAttendance();
    } catch (error) {
      alert(error?.response?.data?.message || 'تعذر تحديث حالة التعليقات للطلاب المحددين.');
    }
  };

  const hideComment = async (commentId) => {
    try {
      await request('post', `${config.endpoints.hideCommentBase}/${commentId}/hide`);
      fetchComments();
    } catch (error) {
      alert('تعذر إخفاء التعليق.');
    }
  };

  const applyRoomPassword = () => {
    if (!state.api || !state.isModerator || state.passwordApplied || !config.embedPayload?.roomPassword) {
      return;
    }

    try {
      state.api.executeCommand('password', config.embedPayload.roomPassword);
      state.passwordApplied = true;
    } catch (error) {
      console.warn('Unable to apply room password', error);
    }
  };

  const applyModerationSettings = () => {
    if (!state.api || !state.isModerator || state.role !== 'doctor') {
      return;
    }

    try {
      state.api.executeCommand('toggleModeration', state.audioModerationEnabled, 'audio');
      if (state.audioModerationEnabled) {
        state.api.executeCommand('muteEveryone', 'audio');
      }
    } catch (error) {
      console.warn('Unable to apply audio moderation', error);
    }

    try {
      state.api.executeCommand('toggleModeration', state.videoModerationEnabled, 'video');
    } catch (error) {
      console.warn('Unable to apply video moderation', error);
    }
  };

  const applyConferenceViewPreferences = () => {
    if (!state.api) {
      return;
    }

    if (!state.tileViewApplied) {
      try {
        state.api.executeCommand('toggleTileView');
        state.tileViewApplied = true;
      } catch (error) {
        console.warn('Unable to enable tile view by default', error);
      }
    }

    try {
      state.api.executeCommand('setNoiseSuppressionEnabled', true);
    } catch (error) {
      console.warn('Unable to enable noise suppression', error);
    }
  };

  const updateModeration = async (payload) => {
    try {
      const data = await request('post', config.endpoints.moderation, payload);
      handleSessionFlags(data.session);
      applyModerationSettings();
    } catch (error) {
      alert(error?.response?.data?.message || 'تعذر تحديث إعدادات الجلسة.');
    }
  };

  const sendComment = async (form, bodyField) => {
    const body = bodyField?.value?.trim();
    if (!body) {
      return;
    }

    try {
      await request('post', config.endpoints.storeComment, { body });
      bodyField.value = '';
      fetchComments();
    } catch (error) {
      alert(error?.response?.data?.message || 'تعذر إرسال التعليق.');
    }
  };

  const heartbeat = async (participantId) => {
    if (state.role !== 'student' || !config.endpoints?.heartbeat || state.ended || state.localLeft) {
      return;
    }

    try {
      const data = await request('post', config.endpoints.heartbeat, {
        participant_id: participantId || undefined,
      });
      handleSessionFlags(data.session);
    } catch (error) {
      const session = error?.response?.data?.session;
      if (session) {
        handleSessionFlags(session);
      }
      if (error?.response?.status === 403) {
        showBanner(error?.response?.data?.message || 'توقفت إمكانية الدخول لهذه الجلسة.');
        if (!state.joinedConference) {
          disposeConference();
        }
      }
    }
  };

  const initPolling = () => {
    fetchComments();

    const commentsPoll = Math.max(Number(config.timers?.commentsPollSeconds || 5), 3) * 1000;
    window.setInterval(fetchComments, commentsPoll);

    if (state.role === 'doctor') {
      fetchAttendance();
      const attendancePoll = Math.max(Number(config.timers?.attendancePollSeconds || 10), 5) * 1000;
      window.setInterval(fetchAttendance, attendancePoll);
    }
  };

  const loadJitsiScript = (domain) => {
    return new Promise((resolve, reject) => {
      if (window.JitsiMeetExternalAPI) {
        resolve();
        return;
      }

      const existing = document.querySelector('script[data-jitsi-external-api="1"]');
      if (existing) {
        existing.addEventListener('load', resolve, { once: true });
        existing.addEventListener('error', reject, { once: true });
        return;
      }

      const script = document.createElement('script');
      script.src = `https://${domain}/external_api.js`;
      script.async = true;
      script.dataset.jitsiExternalApi = '1';
      script.addEventListener('load', resolve, { once: true });
      script.addEventListener('error', reject, { once: true });
      document.head.appendChild(script);
    });
  };

  const applyLocalDisplayName = () => {
    const displayName = config.embedPayload?.userInfo?.displayName;
    if (!displayName || !state.api) {
      return;
    }
    try {
      state.api.executeCommand('displayName', displayName);
    } catch (error) {
      console.warn('Unable to set display name in Jitsi', error);
    }
  };

  const scheduleDisplayNameRetries = () => {
    applyLocalDisplayName();
    [120, 350].forEach((ms) => {
      window.setTimeout(() => applyLocalDisplayName(), ms);
    });
  };

  const initJitsi = async () => {
    if (!config.embedEnabled || !config.embedPayload || !els.jitsiContainer) {
      return;
    }

    els.jitsiContainer.innerHTML = '';

    try {
      await loadJitsiScript(config.embedPayload.domain);
    } catch (error) {
      console.error('Unable to load Jitsi external API', error);
      showBanner('تعذر تحميل منصة الفيديو.');
      return;
    }

    const options = {
      roomName: config.embedPayload.roomName,
      parentNode: els.jitsiContainer,
      width: '100%',
      height: '100%',
      userInfo: config.embedPayload.userInfo || {},
      configOverwrite: config.embedPayload.configOverwrite || {},
      interfaceConfigOverwrite: config.embedPayload.interfaceConfigOverwrite || {},
      lang: 'ar',
    };

    if (config.embedPayload.jwt) {
      options.jwt = config.embedPayload.jwt;
    }

    try {
      state.api = new window.JitsiMeetExternalAPI(config.embedPayload.domain, options);
    } catch (error) {
      console.error('Unable to initialize Jitsi conference', error);
      showBanner('تعذر تشغيل منصة الفيديو.');
      return;
    }

    try {
      const iframe = state.api.getIFrame?.();
      if (iframe) {
        iframe.setAttribute(
          'allow',
          'camera; microphone; display-capture; fullscreen; autoplay; clipboard-read; clipboard-write'
        );
        iframe.setAttribute('allowfullscreen', 'true');
      }
    } catch (error) {
      console.warn('Unable to adjust Jitsi iframe permissions', error);
    }

    state.api.addEventListener('participantRoleChanged', (event) => {
      state.isModerator = event.role === 'moderator';
      updateDoctorActionLabels();

       if (state.role === 'doctor') {
        syncDoctorPresence(state.isModerator);
      }

      if (state.isModerator) {
        applyRoomPassword();
        applyModerationSettings();
        try {
          if (config.embedPayload.subject) {
            state.api.executeCommand('subject', config.embedPayload.subject);
          }
        } catch (error) {
          console.warn('Unable to set conference subject', error);
        }
      }
    });

    state.api.addEventListener('passwordRequired', () => {
      if (config.embedPayload.roomPassword) {
        try {
          state.api.executeCommand('password', config.embedPayload.roomPassword);
        } catch (error) {
          console.warn('Unable to submit room password', error);
        }
      }
    });

    state.api.addEventListener('videoConferenceJoined', (event) => {
      state.joinedConference = true;
      state.localLeft = false;
      scheduleDisplayNameRetries();
      applyConferenceViewPreferences();
      if (state.role === 'student') {
        heartbeat(event.id || null);
        const heartbeatInterval = Math.max(Number(config.timers?.heartbeatIntervalSeconds || 12), 8) * 1000;
        window.setInterval(() => heartbeat(), heartbeatInterval);
      } else {
        updateDoctorActionLabels();
        applyRoomPassword();
        applyModerationSettings();
      }
    });

    state.api.addEventListener('readyToClose', () => {
      showBanner('تم إغلاق نافذة المحاضرة.');
      if (state.role === 'doctor') {
        syncDoctorPresence(false);
      }
      disposeConference('تم إنهاء الجلسة.', 'شكرًا لحضورك. يمكنك الآن العودة إلى منصة ليدرز.');
    });

    state.api.addEventListener('toolbarButtonClicked', (event) => {
      if (event?.key !== 'hangup' || event?.preventExecution !== true) {
        return;
      }

      showBanner('جارٍ مغادرة الجلسة...');
      if (state.role === 'doctor') {
        syncDoctorPresence(false);
      }
      disposeConference(
        'تمت مغادرة الجلسة.',
        state.role === 'doctor'
          ? 'يمكنك العودة إلى المحاضرات الحية أو تحديث الصفحة إذا أردت الدخول مجددًا.'
          : 'يمكنك العودة إلى الجدول أو تحديث الصفحة إذا أردت الدخول مجددًا ما دامت الجلسة ما تزال مفتوحة.'
      );
    });

    state.api.addEventListener('recordingStatusChanged', (event) => {
      state.recordingOn = !!(event.on || event.status === 'on');
      updateDoctorActionLabels();
    });

    state.api.addEventListener('screenSharingStatusChanged', (event) => {
      setScreenShareNoteVisibility(!!event?.on);
    });

    state.api.addEventListener('cameraError', (event) => {
      console.warn('Jitsi camera error', event);
      showDeviceHelpMessage(event?.message);
    });

    state.api.addEventListener('micError', (event) => {
      console.warn('Jitsi microphone error', event);
      showDeviceHelpMessage(event?.message);
    });

    state.api.addEventListener('audioAvailabilityChanged', (event) => {
      if (event?.available === false) {
        showDeviceHelpMessage('الميكروفون غير متاح داخل نافذة المحاضرة حالياً. جرّب فتح الجلسة في نافذة مستقلة أو اسمح للمتصفح بالوصول إلى الميكروفون.');
      }
    });

    state.api.addEventListener('videoAvailabilityChanged', (event) => {
      if (event?.available === false) {
        showDeviceHelpMessage('الكاميرا غير متاحة داخل نافذة المحاضرة حالياً. جرّب فتح الجلسة في نافذة مستقلة أو اسمح للمتصفح بالوصول إلى الكاميرا.');
      }
    });

    state.api.addEventListener('browserSupport', (event) => {
      if (event?.supported === false) {
        showBanner('هذا المتصفح أو هذا الوضع الأمني لا يدعم تشغيل Jitsi بشكل كامل داخل الصفحة.');
      }
    });
  };

  const bindEvents = () => {
    if (els.studentCommentForm) {
      els.studentCommentForm.addEventListener('submit', (event) => {
        event.preventDefault();
        sendComment(els.studentCommentForm, els.studentCommentBody);
      });
    }

    if (els.doctorCommentForm) {
      els.doctorCommentForm.addEventListener('submit', (event) => {
        event.preventDefault();
        sendComment(els.doctorCommentForm, els.doctorCommentBody);
      });
    }

    if (els.commentsList && state.role === 'doctor') {
      els.commentsList.addEventListener('click', (event) => {
        const button = event.target.closest('.live-session-comment-hide-btn');
        if (!button) return;

        const commentId = Number.parseInt(button.dataset.commentId, 10);
        if (!commentId) return;

        hideComment(commentId);
      });
    }

    if (els.blockSelectedBtn) {
      els.blockSelectedBtn.addEventListener('click', () => updateCommentBlocks('block'));
    }

    if (els.unblockSelectedBtn) {
      els.unblockSelectedBtn.addEventListener('click', () => updateCommentBlocks('unblock'));
    }

    if (els.toggleCommentsBtn) {
      els.toggleCommentsBtn.addEventListener('click', () => {
        updateModeration({ comments_enabled: !state.commentsEnabled });
      });
    }

    if (els.toggleAudioBtn) {
      els.toggleAudioBtn.addEventListener('click', () => {
        updateModeration({ audio_moderation_enabled: !state.audioModerationEnabled });
      });
    }

    if (els.toggleVideoBtn) {
      els.toggleVideoBtn.addEventListener('click', () => {
        updateModeration({ video_moderation_enabled: !state.videoModerationEnabled });
      });
    }

    if (els.recordingBtn) {
      els.recordingBtn.addEventListener('click', () => {
        if (!state.api) {
          alert('منصة الفيديو غير جاهزة بعد.');
          return;
        }

        try {
          if (state.recordingOn) {
            state.api.executeCommand('stopRecording', { mode: 'local' });
          } else {
            state.api.executeCommand('startRecording', { mode: 'local' });
          }
          state.recordingOn = !state.recordingOn;
          updateDoctorActionLabels();
        } catch (error) {
          alert('التسجيل المحلي غير متاح حالياً في هذا المتصفح أو هذا المزود.');
        }
      });
    }

    if (els.openDirectJitsiBtn) {
      els.openDirectJitsiBtn.addEventListener('click', () => {
        if (!config.embedPayload?.meetLaunchUrl && !config.embedPayload?.meetingUrl) {
          alert('رابط الجلسة المباشر غير متاح حالياً.');
          return;
        }

        window.open(config.embedPayload.meetLaunchUrl || config.embedPayload.meetingUrl, '_blank', 'noopener,noreferrer');
      });
    }

    if (els.standaloneHostReadyBtn) {
      els.standaloneHostReadyBtn.addEventListener('click', () => {
        syncDoctorPresence(true);
        showBanner('تم تمكين الطلاب لرؤية زر الدخول إلى القاعة في لوحتهم بعد أن تكون قد ظهر لك الغرفة في النافذة المنفصلة.');
      });
    }

    document.querySelectorAll('[data-end-session-form="1"]').forEach((form) => {
      form.addEventListener('submit', (event) => {
        if (state.role !== 'doctor' || state.ended) {
          return;
        }

        syncDoctorPresence(false);

        if (standaloneWindow) {
          showBanner('');
          return;
        }

        if (!state.api || !state.isModerator) {
          return;
        }

        renderLeadersPlaceholder('جارٍ إنهاء الجلسة...', 'سيتم إغلاق قاعة Jitsi وإظهار واجهة ليدرز بدلاً منها.');

        try {
          state.api.executeCommand('endConference');
        } catch (error) {
          console.warn('Unable to end conference from Jitsi API', error);
        }
      });
    });

    if (state.role === 'doctor') {
      window.addEventListener('beforeunload', () => {
        if (state.isModerator || state.joinedConference) {
          syncDoctorPresence(false, true);
        }
      });
    }
  };

  setStudentCommentAvailability();
  updateDoctorActionLabels();
  bindEvents();
  initPolling();
  if (state.role === 'doctor') {
    if (!standaloneWindow) {
      initJitsi();
    }
  } else {
    maybeInitStudentEmbed();
  }
})();

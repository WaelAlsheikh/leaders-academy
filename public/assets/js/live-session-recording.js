(function () {
  const STORAGE_DB = 'leaders_live_session';
  const STORAGE_STORE = 'recording_prefs';
  const DIR_HANDLE_KEY = 'lastDirectoryHandle';

  const recordingState = {
    config: null,
    directoryHandle: null,
    recordedBlob: null,
    recordedChunks: [],
    mediaRecorder: null,
    captureStream: null,
    displayStream: null,
    micStream: null,
    audioContext: null,
    audioSources: [],
    ffmpegInstance: null,
    pendingEndForm: null,
    sessionHadRecording: false,
    autoStartAttempted: false,
    isRecording: false,
    hasTabAudio: false,
    hasMicAudio: false,
    endConferenceHook: null,
    onRecordingStateChange: null,
  };

  const els = {};

  const supportsFileSystemAccess = () => typeof window.showDirectoryPicker === 'function';

  const readAutoRecordingPreference = () => {
    const storageKey = recordingState.config?.storageKey || 'leaders.doctor.autoLocalRecording';
    const serverDefault = recordingState.config?.autoStartDefault !== false;

    try {
      const stored = window.localStorage.getItem(storageKey);
      if (stored === null) {
        return serverDefault;
      }

      return stored === '1' || stored === 'true';
    } catch (error) {
      return serverDefault;
    }
  };

  const writeAutoRecordingPreference = (enabled) => {
    const storageKey = recordingState.config?.storageKey || 'leaders.doctor.autoLocalRecording';

    try {
      window.localStorage.setItem(storageKey, enabled ? '1' : '0');
    } catch (error) {
      console.warn('Unable to persist auto-recording preference', error);
    }
  };

  const bindElements = () => {
    els.modalBackdrop = document.getElementById('recording-save-modal-backdrop');
    els.pickFolderBtn = document.getElementById('recording-pick-folder-btn');
    els.skipBtn = document.getElementById('recording-save-skip-btn');
    els.folderLabel = document.getElementById('recording-folder-label');
    els.progressWrap = document.getElementById('recording-save-progress-wrap');
    els.progressBar = document.getElementById('recording-save-progress-bar');
    els.progressText = document.getElementById('recording-save-progress-text');
    els.statusNote = document.getElementById('recording-save-status-note');
    els.autoToggle = document.getElementById('doctor-auto-recording-toggle');
    els.activationPrompt = document.getElementById('recording-activation-prompt');
    els.activationBtn = document.getElementById('recording-activation-btn');
    els.audioStatusBanner = document.getElementById('recording-audio-status');
  };

  const notifyRecordingStateChange = () => {
    if (typeof recordingState.onRecordingStateChange === 'function') {
      recordingState.onRecordingStateChange(recordingState.isRecording);
    }
  };

  const setProgress = (percent, text) => {
    if (!els.progressWrap) {
      return;
    }

    els.progressWrap.style.display = 'block';
    if (els.progressBar) {
      els.progressBar.style.width = `${Math.max(0, Math.min(100, percent))}%`;
    }
    if (els.progressText) {
      els.progressText.textContent = text || '';
    }
  };

  const hideProgress = () => {
    if (els.progressWrap) {
      els.progressWrap.style.display = 'none';
    }
    if (els.progressBar) {
      els.progressBar.style.width = '0%';
    }
    if (els.progressText) {
      els.progressText.textContent = '';
    }
  };

  const updateModalLabels = () => {
    if (els.folderLabel) {
      els.folderLabel.textContent = recordingState.directoryHandle
        ? `المجلد: ${recordingState.directoryHandle.name}`
        : 'لم يُختر مجلد بعد';
    }
  };

  const showActivationPrompt = (message) => {
    if (!els.activationPrompt) {
      return;
    }

    if (message && els.activationPrompt.querySelector('.recording-activation-text')) {
      els.activationPrompt.querySelector('.recording-activation-text').textContent = message;
    }

    els.activationPrompt.style.display = 'block';
  };

  const hideActivationPrompt = () => {
    if (els.activationPrompt) {
      els.activationPrompt.style.display = 'none';
    }
  };

  const showAudioStatus = (message, isWarning = false) => {
    if (!els.audioStatusBanner) {
      return;
    }

    els.audioStatusBanner.textContent = message;
    els.audioStatusBanner.classList.toggle('recording-audio-status-warning', isWarning);
    els.audioStatusBanner.style.display = 'block';
  };

  const hideAudioStatus = () => {
    if (els.audioStatusBanner) {
      els.audioStatusBanner.style.display = 'none';
    }
  };

  const openModal = () => {
    if (!els.modalBackdrop) {
      return;
    }

    recordingState.directoryHandle = null;
    hideProgress();

    if (els.statusNote) {
      const subject = recordingState.config?.subjectTitle || 'المحاضرة';
      els.statusNote.textContent = supportsFileSystemAccess()
        ? `تم إيقاف تسجيل «${subject}». اختر مجلد الحفظ ليتم حفظ الفيديو مباشرةً فيه.`
        : `تم إيقاف تسجيل «${subject}». سيتم تنزيل النسخة المضغوطة بعد المعالجة.`;
    }

    updateModalLabels();
    els.modalBackdrop.style.display = 'flex';
    document.body.classList.add('recording-save-modal-open');
  };

  const closeModal = () => {
    if (!els.modalBackdrop) {
      return;
    }

    els.modalBackdrop.style.display = 'none';
    document.body.classList.remove('recording-save-modal-open');
    hideProgress();
  };

  const openIndexedDb = () => new Promise((resolve, reject) => {
    const request = window.indexedDB.open(STORAGE_DB, 1);
    request.onupgradeneeded = () => {
      const db = request.result;
      if (!db.objectStoreNames.contains(STORAGE_STORE)) {
        db.createObjectStore(STORAGE_STORE);
      }
    };
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });

  const persistDirectoryHandle = async (handle) => {
    if (!handle) {
      return;
    }

    try {
      const db = await openIndexedDb();
      await new Promise((resolve, reject) => {
        const tx = db.transaction(STORAGE_STORE, 'readwrite');
        tx.objectStore(STORAGE_STORE).put(handle, DIR_HANDLE_KEY);
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
      });
    } catch (error) {
      console.warn('Unable to persist directory handle', error);
    }
  };

  const restoreDirectoryHandle = async () => {
    try {
      const db = await openIndexedDb();
      const handle = await new Promise((resolve, reject) => {
        const tx = db.transaction(STORAGE_STORE, 'readonly');
        const request = tx.objectStore(STORAGE_STORE).get(DIR_HANDLE_KEY);
        request.onsuccess = () => resolve(request.result || null);
        request.onerror = () => reject(request.error);
      });

      if (handle && typeof handle.queryPermission === 'function') {
        const permission = await handle.queryPermission({ mode: 'readwrite' });
        if (permission === 'granted') {
          recordingState.directoryHandle = handle;
        }
      }
    } catch (error) {
      console.warn('Unable to restore directory handle', error);
    }
  };

  const getRecorderMimeType = () => {
    const candidates = [
      'video/webm;codecs=vp9,opus',
      'video/webm;codecs=vp8,opus',
      'video/webm',
    ];

    return candidates.find((type) => {
      try {
        return typeof MediaRecorder !== 'undefined' && MediaRecorder.isTypeSupported(type);
      } catch (error) {
        return false;
      }
    }) || '';
  };

  const cleanupCaptureStream = () => {
    [recordingState.captureStream, recordingState.displayStream, recordingState.micStream].forEach((stream) => {
      if (!stream) {
        return;
      }

      stream.getTracks().forEach((track) => track.stop());
    });

    recordingState.captureStream = null;
    recordingState.displayStream = null;
    recordingState.micStream = null;

    recordingState.audioSources.forEach((source) => {
      try {
        source.disconnect();
      } catch (error) {
        console.warn('Unable to disconnect audio source', error);
      }
    });
    recordingState.audioSources = [];

    if (recordingState.audioContext) {
      recordingState.audioContext.close().catch(() => {});
      recordingState.audioContext = null;
    }

    recordingState.hasTabAudio = false;
    recordingState.hasMicAudio = false;
    hideAudioStatus();
  };

  const requestDisplayStream = async () => {
    return navigator.mediaDevices.getDisplayMedia({
      video: {
        displaySurface: 'browser',
        width: { ideal: 1280 },
        height: { ideal: 720 },
        frameRate: { ideal: 24, max: 30 },
      },
      audio: {
        echoCancellation: false,
        noiseSuppression: false,
        autoGainControl: false,
        suppressLocalAudioPlayback: false,
      },
      preferCurrentTab: true,
      selfBrowserSurface: 'include',
      systemAudio: 'include',
    });
  };

  const requestMicrophoneStream = async () => {
    return navigator.mediaDevices.getUserMedia({
      audio: {
        echoCancellation: true,
        noiseSuppression: true,
        autoGainControl: true,
        channelCount: { ideal: 1 },
      },
      video: false,
    });
  };

  const buildCompositeRecordingStream = async (displayStream, micStream) => {
    const videoTrack = displayStream.getVideoTracks()[0] || null;
    const displayAudioTracks = displayStream.getAudioTracks();
    const micAudioTracks = micStream?.getAudioTracks?.() || [];

    recordingState.hasTabAudio = displayAudioTracks.length > 0;
    recordingState.hasMicAudio = micAudioTracks.length > 0;

    const compositeStream = new MediaStream();
    if (videoTrack) {
      compositeStream.addTrack(videoTrack);
    }

    if (!recordingState.hasTabAudio && !recordingState.hasMicAudio) {
      return compositeStream;
    }

    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) {
      const fallbackAudio = displayAudioTracks[0] || micAudioTracks[0];
      if (fallbackAudio) {
        compositeStream.addTrack(fallbackAudio);
      }
      return compositeStream;
    }

    const audioContext = new AudioContextClass();
    recordingState.audioContext = audioContext;

    if (audioContext.state === 'suspended') {
      await audioContext.resume();
    }

    const destination = audioContext.createMediaStreamDestination();
    const tabGain = audioContext.createGain();
    const micGain = audioContext.createGain();

    tabGain.gain.value = 1;
    micGain.gain.value = recordingState.hasTabAudio ? 0.85 : 1;

    tabGain.connect(destination);
    micGain.connect(destination);

    if (recordingState.hasTabAudio) {
      const tabSource = audioContext.createMediaStreamSource(new MediaStream(displayAudioTracks));
      tabSource.connect(tabGain);
      recordingState.audioSources.push(tabSource);
    }

    if (recordingState.hasMicAudio) {
      const micSource = audioContext.createMediaStreamSource(new MediaStream(micAudioTracks));
      micSource.connect(micGain);
      recordingState.audioSources.push(micSource);
    }

    destination.stream.getAudioTracks().forEach((track) => {
      compositeStream.addTrack(track);
    });

    return compositeStream;
  };

  const describeAudioCaptureStatus = () => {
    if (recordingState.hasTabAudio && recordingState.hasMicAudio) {
      return {
        message: 'التسجيل نشط: صوت التبويب + الميكروفون.',
        warning: false,
      };
    }

    if (recordingState.hasMicAudio) {
      return {
        message: 'التسجيل نشط: الميكروفون فقط. لتسجيل صوت الطلاب أيضاً، أعد بدء التسجيل وفعّل «مشاركة صوت التبويب».',
        warning: true,
      };
    }

    if (recordingState.hasTabAudio) {
      return {
        message: 'التسجيل نشط: صوت التبويب فقط.',
        warning: false,
      };
    }

    return {
      message: 'تحذير: لا يوجد صوت في التسجيل. أعد المحاولة وفعّل الميكروفون و«مشاركة صوت التبويب».',
      warning: true,
    };
  };

  const buildRecordedBlob = () => {
    if (!recordingState.recordedChunks.length) {
      return null;
    }

    const mimeType = recordingState.mediaRecorder?.mimeType || 'video/webm';
    return new Blob(recordingState.recordedChunks, { type: mimeType });
  };

  const startCustomRecording = async () => {
    if (recordingState.isRecording) {
      return true;
    }

    if (typeof MediaRecorder === 'undefined') {
      alert('المتصفح لا يدعم تسجيل الفيديو محلياً.');
      return false;
    }

    cleanupCaptureStream();
    recordingState.recordedChunks = [];

    try {
      const displayStream = await requestDisplayStream();
      recordingState.displayStream = displayStream;

      let micStream = null;
      try {
        micStream = await requestMicrophoneStream();
        recordingState.micStream = micStream;
      } catch (error) {
        console.warn('Microphone unavailable for lecture recording', error);
      }

      const stream = await buildCompositeRecordingStream(displayStream, micStream);
      recordingState.captureStream = stream;

      displayStream.getVideoTracks().forEach((track) => {
        track.addEventListener('ended', () => {
          if (recordingState.isRecording) {
            stopCustomRecording().catch((warn) => {
              console.warn('Recording stopped because screen share ended', warn);
            });
          }
        });
      });

      if (!stream.getAudioTracks().length) {
        cleanupCaptureStream();
        alert(
          'لم يتم التقاط أي صوت.\n\n'
          + 'عند بدء التسجيل:\n'
          + '1) اختر «هذا التبويب»\n'
          + '2) فعّل «مشاركة صوت التبويب» أو Share tab audio\n'
          + '3) اسمح للمتصفح باستخدام الميكروفون'
        );
        return false;
      }

      const mimeType = getRecorderMimeType();
      const videoKbps = Number(recordingState.config?.videoKbps || 100);
      const audioKbps = Number(recordingState.config?.audioKbps || 64);
      const options = {
        videoBitsPerSecond: videoKbps * 1000,
        audioBitsPerSecond: audioKbps * 1000,
      };

      if (mimeType) {
        options.mimeType = mimeType;
      }

      const mediaRecorder = new MediaRecorder(stream, options);
      recordingState.mediaRecorder = mediaRecorder;

      mediaRecorder.addEventListener('dataavailable', (event) => {
        if (event.data && event.data.size > 0) {
          recordingState.recordedChunks.push(event.data);
        }
      });

      mediaRecorder.start(4000);
      recordingState.isRecording = true;
      recordingState.sessionHadRecording = true;
      hideActivationPrompt();

      const audioStatus = describeAudioCaptureStatus();
      showAudioStatus(audioStatus.message, audioStatus.warning);
      notifyRecordingStateChange();

      return true;
    } catch (error) {
      if (error?.name === 'NotAllowedError') {
        showActivationPrompt(
          'لتفعيل التسجيل: اختر «هذا التبويب»، فعّل «مشاركة صوت التبويب»، واسمح بالميكروفون.'
        );
      } else {
        console.warn('Unable to start custom recording', error);
        alert('تعذر بدء التسجيل المحلي. تأكد من السماح بمشاركة التبويب والميكروفون.');
      }

      cleanupCaptureStream();
      return false;
    }
  };

  const stopCustomRecording = async () => {
    if (!recordingState.isRecording || !recordingState.mediaRecorder) {
      recordingState.recordedBlob = buildRecordedBlob();
      return recordingState.recordedBlob;
    }

    const mediaRecorder = recordingState.mediaRecorder;

    await new Promise((resolve) => {
      const finalize = () => {
        mediaRecorder.removeEventListener('stop', finalize);
        resolve();
      };

      mediaRecorder.addEventListener('stop', finalize);

      try {
        if (mediaRecorder.state !== 'inactive') {
          mediaRecorder.stop();
        } else {
          resolve();
        }
      } catch (error) {
        console.warn('Unable to stop media recorder', error);
        resolve();
      }
    });

    recordingState.isRecording = false;
    recordingState.mediaRecorder = null;
    cleanupCaptureStream();
    recordingState.recordedBlob = buildRecordedBlob();
    notifyRecordingStateChange();

    return recordingState.recordedBlob;
  };

  const loadFfmpeg = async () => {
    if (recordingState.ffmpegInstance) {
      return recordingState.ffmpegInstance;
    }

    setProgress(5, 'جارٍ تحميل أداة ضغط الفيديو...');

    const { FFmpeg } = await import('https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.12.10/+esm');
    const { fetchFile, toBlobURL } = await import('https://cdn.jsdelivr.net/npm/@ffmpeg/util@0.12.1/+esm');

    const ffmpeg = new FFmpeg();

    ffmpeg.on('progress', ({ progress }) => {
      const pct = 20 + Math.round((progress || 0) * 70);
      setProgress(pct, 'جارٍ ضغط الفيديو...');
    });

    const baseUrl = 'https://cdn.jsdelivr.net/npm/@ffmpeg/core@0.12.6/dist/esm';
    await ffmpeg.load({
      coreURL: await toBlobURL(`${baseUrl}/ffmpeg-core.js`, 'text/javascript'),
      wasmURL: await toBlobURL(`${baseUrl}/ffmpeg-core.wasm`, 'application/wasm'),
    });

    recordingState.ffmpegInstance = { ffmpeg, fetchFile };

    return recordingState.ffmpegInstance;
  };

  const buildOutputFilename = () => {
    const base = recordingState.config?.suggestedFilename || `lecture_${Date.now()}`;
    return `${base}.mp4`.replace(/[^\w\u0600-\u06FF.-]+/g, '-');
  };

  const compressVideo = async (sourceBlob) => {
    const videoKbps = Number(recordingState.config?.videoKbps || 100);
    const audioKbps = Number(recordingState.config?.audioKbps || 32);
    const outputHeight = Number(recordingState.config?.outputHeight || 480);
    const inputName = 'input.webm';
    const outputName = 'output.mp4';

    try {
      const { ffmpeg, fetchFile } = await loadFfmpeg();
      setProgress(15, 'جارٍ تجهيز الملف للضغط...');

      await ffmpeg.writeFile(inputName, await fetchFile(sourceBlob));

      await ffmpeg.exec([
        '-i', inputName,
        '-map', '0:v:0',
        '-map', '0:a:0?',
        '-vf', `scale=-2:${outputHeight}`,
        '-c:v', 'libx264',
        '-b:v', `${videoKbps}k`,
        '-maxrate', `${videoKbps}k`,
        '-bufsize', `${videoKbps * 2}k`,
        '-c:a', 'aac',
        '-b:a', `${audioKbps}k`,
        '-ar', '44100',
        '-ac', '2',
        '-movflags', '+faststart',
        '-preset', 'veryfast',
        '-shortest',
        outputName,
      ]);

      const data = await ffmpeg.readFile(outputName);
      return new Blob([data.buffer], { type: 'video/mp4' });
    } catch (error) {
      console.warn('ffmpeg compression failed, falling back to original file', error);
      return sourceBlob;
    }
  };

  const writeBlobToDirectory = async (blob, filename) => {
    if (!recordingState.directoryHandle) {
      throw new Error('No directory selected');
    }

    const fileHandle = await recordingState.directoryHandle.getFileHandle(filename, { create: true });
    const writable = await fileHandle.createWritable();
    await writable.write(blob);
    await writable.close();
  };

  const downloadBlob = (blob, filename) => {
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = filename;
    anchor.click();
    window.setTimeout(() => URL.revokeObjectURL(url), 1000);
  };

  const saveRecordingToSelectedFolder = async () => {
    const sourceBlob = recordingState.recordedBlob;

    if (!sourceBlob || sourceBlob.size === 0) {
      alert('لا يوجد تسجيل محفوظ لهذه الجلسة.');
      return false;
    }

    if (!recordingState.directoryHandle && supportsFileSystemAccess()) {
      alert('يرجى اختيار مجلد الحفظ أولاً.');
      return false;
    }

    if (els.pickFolderBtn) {
      els.pickFolderBtn.disabled = true;
    }
    if (els.skipBtn) {
      els.skipBtn.disabled = true;
    }

    try {
      setProgress(10, 'جارٍ ضغط الفيديو...');
      const outputFilename = buildOutputFilename();
      const outputBlob = await compressVideo(sourceBlob);
      setProgress(92, 'جارٍ حفظ الملف...');

      if (recordingState.directoryHandle && supportsFileSystemAccess()) {
        await writeBlobToDirectory(outputBlob, outputFilename);
        setProgress(100, `تم الحفظ: ${outputFilename}`);
      } else {
        downloadBlob(outputBlob, outputFilename);
        setProgress(100, 'تم تنزيل الملف المضغوط.');
      }

      if (els.statusNote) {
        const sizeMb = (outputBlob.size / (1024 * 1024)).toFixed(1);
        els.statusNote.textContent = `اكتمل الحفظ (${sizeMb} MB).`;
      }

      await new Promise((resolve) => window.setTimeout(resolve, 700));
      return true;
    } catch (error) {
      console.error('Unable to save recording', error);
      alert('تعذر حفظ التسجيل. يمكنك تخطي هذه الخطوة والمحاولة لاحقاً.');
      return false;
    } finally {
      if (els.pickFolderBtn) {
        els.pickFolderBtn.disabled = false;
      }
      if (els.skipBtn) {
        els.skipBtn.disabled = false;
      }
    }
  };

  const pickFolderAndSave = async () => {
    if (!recordingState.recordedBlob || recordingState.recordedBlob.size === 0) {
      alert('لا يوجد تسجيل محفوظ لهذه الجلسة.');
      return false;
    }

    if (!supportsFileSystemAccess()) {
      return saveRecordingToSelectedFolder();
    }

    try {
      const handle = await window.showDirectoryPicker({ mode: 'readwrite' });
      recordingState.directoryHandle = handle;
      await persistDirectoryHandle(handle);
      updateModalLabels();
      return saveRecordingToSelectedFolder();
    } catch (error) {
      if (error?.name !== 'AbortError') {
        alert('تعذر اختيار مجلد الحفظ.');
      }
      return false;
    }
  };

  const submitPendingEndForm = () => {
    const form = recordingState.pendingEndForm;
    recordingState.pendingEndForm = null;

    if (typeof recordingState.endConferenceHook === 'function') {
      recordingState.endConferenceHook();
    }

    if (!form) {
      return;
    }

    form.dataset.recordingSaveCompleted = '1';
    form.submit();
  };

  const finishEndFlow = () => {
    closeModal();
    submitPendingEndForm();
  };

  const showSaveModalForEnd = () => new Promise((resolve) => {
    if (!recordingState.sessionHadRecording || !recordingState.recordedBlob || !els.modalBackdrop) {
      resolve(true);
      return;
    }

    openModal();

    const onSkipHandler = () => {
      els.skipBtn?.removeEventListener('click', onSkipHandler);
      els.pickFolderBtn?.removeEventListener('click', onPickFolderHandler);
      finishEndFlow();
      resolve(true);
    };

    const onPickFolderHandler = async () => {
      const ok = await pickFolderAndSave();
      if (!ok) {
        return;
      }

      els.skipBtn?.removeEventListener('click', onSkipHandler);
      els.pickFolderBtn?.removeEventListener('click', onPickFolderHandler);
      finishEndFlow();
      resolve(true);
    };

    els.skipBtn?.addEventListener('click', onSkipHandler);
    els.pickFolderBtn?.addEventListener('click', onPickFolderHandler);
  });

  const bindModalStaticEvents = () => {
    els.activationBtn?.addEventListener('click', () => {
      startCustomRecording();
    });
  };

  const bindAutoToggle = () => {
    if (!els.autoToggle) {
      return;
    }

    els.autoToggle.checked = readAutoRecordingPreference();
    els.autoToggle.addEventListener('change', () => {
      writeAutoRecordingPreference(!!els.autoToggle.checked);
    });
  };

  const tryAutoStartRecording = () => {
    if (recordingState.autoStartAttempted) {
      return;
    }

    if (!readAutoRecordingPreference() || recordingState.isRecording) {
      return;
    }

    recordingState.autoStartAttempted = true;

    window.setTimeout(async () => {
      const started = await startCustomRecording();
      if (!started && readAutoRecordingPreference()) {
        showActivationPrompt('التسجيل التلقائي مفعّل. اضغط الزر أدناه، اختر «هذا التبويب»، وفعّل «مشاركة صوت التبويب».');
      }
    }, 1200);
  };

  const toggleCustomRecording = async () => {
    if (recordingState.isRecording) {
      await stopCustomRecording();
      return false;
    }

    const started = await startCustomRecording();
    return started;
  };

  const stopRecordingIfActive = async () => {
    if (!recordingState.isRecording && !recordingState.recordedChunks.length) {
      return recordingState.recordedBlob;
    }

    return stopCustomRecording();
  };

  const handleEndSessionRequest = async (form, liveState, hooks) => {
    if (liveState.role !== 'doctor' || liveState.ended) {
      return;
    }

    if (form?.dataset?.recordingSaveCompleted === '1') {
      return;
    }

    recordingState.pendingEndForm = form;

    if (typeof hooks?.beforeEnd === 'function') {
      hooks.beforeEnd();
    }

    await stopRecordingIfActive();

    if (recordingState.sessionHadRecording && recordingState.recordedBlob) {
      await showSaveModalForEnd();
      return;
    }

    submitPendingEndForm();
  };

  const init = (pageConfig, hooks) => {
    if (pageConfig?.role !== 'doctor' || !pageConfig?.recording) {
      return null;
    }

    recordingState.config = pageConfig.recording;
    recordingState.endConferenceHook = hooks?.endConference || null;
    recordingState.onRecordingStateChange = hooks?.onRecordingStateChange || null;

    bindElements();
    bindModalStaticEvents();
    bindAutoToggle();
    restoreDirectoryHandle();

    return {
      tryAutoStartRecording,
      toggleCustomRecording,
      stopRecordingIfActive,
      handleEndSessionRequest,
      readAutoRecordingPreference,
      isRecording: () => recordingState.isRecording,
    };
  };

  window.LiveSessionRecording = { init };
})();

<!DOCTYPE html>
<html>
<head>
  <title>JsSIP WebSocket Call Test</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/jssip/dist/jssip.min.js"></script>
</head>
<body>
  <div class="container mt-4">
    <h1>JsSIP 通話テスト</h1>
    <div id="status" class="alert alert-info"></div>

    <div class="mb-3">
      <label for="extension" class="form-label">発信先内線番号:</label>
      <input type="text" id="extension" class="form-control" placeholder="内線番号を入力">
    </div>

    <div class="mb-3">
      <button id="callButton" class="btn btn-primary">発信</button>
      <button id="hangupButton" class="btn btn-danger" disabled>切断</button>
    </div>

    <div class="mt-3">
      <audio id="remoteAudio" autoplay playsinline></audio>
      <audio id="localAudio" controls muted autoplay></audio>
    </div>

    <a href="{{ route('pjsip.index') }}" class="btn btn-secondary mt-3">戻る</a>
  </div>

  <script>
    // JsSIPの設定
    const socket = new JsSIP.WebSocketInterface('ws://asterisk-dev-kijima.ringing.co.jp:8088/ws');
    
    // 認証情報の準備
    const username = '6000';
    const realm = 'asterisk-dev-kijima.ringing.co.jp';
    const password = 'your_password';
    
    const configuration = {
      sockets: [socket],
      uri: `sip:${username}@${realm}`,
      password: password,
      realm: realm,
      authorization_user: username,
      register_expires: 30,
      connection_recovery_min_interval: 1,
      connection_recovery_max_interval: 15,
      no_answer_timeout: 60,
      use_preloaded_route: true,
      session_timers: false,  // セッションタイマーを無効化
      pcConfig: {
        iceServers: [
          { urls: ['stun:stun.l.google.com:19302'] }
        ]
      },
      mediaConstraints: {
        audio: true,
        video: false
      }
    };

    let ua;
    let session = null;

    // RTCSessionの設定を最適化
    const callOptions = {
      mediaConstraints: {
        audio: {
          echoCancellation: { ideal: true },
          noiseSuppression: { ideal: true },
          autoGainControl: { ideal: true },
          latency: { ideal: 0.01 }
        },
        video: false
      },
      pcConfig: {
        iceServers: [
          { urls: ['stun:stun.l.google.com:19302'] }
        ],
        bundlePolicy: 'max-bundle',
        rtcpMuxPolicy: 'require',
        iceTransportPolicy: 'all',
        iceCandidatePoolSize: 1
      },
      rtcOfferConstraints: {
        offerToReceiveAudio: true,
        offerToReceiveVideo: false,
        iceRestart: false,
        voiceActivityDetection: false
      },
      sessionTimerExpires: 90,
      iceGatheringTimeout: 2000
    };

    // メディアストリームを事前に取得
    let localStream = null;
    async function initializeMedia() {
      try {
        const constraints = {
          audio: {
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true
          },
          video: false
        };
        
        localStream = await navigator.mediaDevices.getUserMedia(constraints);
        const localAudio = document.getElementById('localAudio');
        if (localAudio) {
          localAudio.srcObject = localStream;
        }
        return true;
      } catch (error) {
        console.error('メディアストリーム取得エラー:', error);
        return false;
      }
    }

    // UAの初期化
    ua = new JsSIP.UA(configuration);

    // デバッグログの有効化
    JsSIP.debug.enable('JsSIP:*');

    // 詳細なイベントログ
    ua.on('connecting', () => {
      console.log('WebSocket接続中...');
    });

    ua.on('connected', () => {
      console.log('WebSocket接続成功');
    });

    ua.on('disconnected', () => {
      console.log('WebSocket切断');
      setTimeout(() => {
        ua.start();  // 自動再接続
      }, 3000);
    });

    ua.on('registered', () => {
      console.log('SIP登録成功');
    });

    ua.on('unregistered', () => {
      console.log('SIP登録解除');
    });

    ua.on('registrationFailed', (e) => {
      console.error('SIP登録失敗:', e);
    });

    ua.on('newRTCSession', (data) => {
      session = data.session;
      
      session.on('confirmed', () => {
        updateStatus('通話中');
        if (typeof playAudio === 'function') {
          playAudio().catch(err => console.error('音声再生エラー:', err));
        }
      });
    });

    // 発信ボタンのイベント
    document.getElementById('callButton').addEventListener('click', async () => {
      const extension = document.getElementById('extension').value;
      if (!extension) {
        alert('内線番号を入力してください');
        return;
      }

      // メディアストリームが未取得の場合は取得
      if (!localStream) {
        const result = await initializeMedia();
        if (!result) {
          alert('マイクの初期化に失敗しました');
          return;
        }
      }

      // 発信オプションにメディアストリームを設定
      callOptions.mediaStream = localStream;

      const target = `sip:${extension}@asterisk-dev-kijima.ringing.co.jp`;
      try {
        session = ua.call(target, callOptions);
        setCallSessionHandlers();
      } catch (error) {
        console.error('発信エラー:', error);
        alert('発信に失敗しました');
      }
    });

    // 切断ボタンのイベント
    document.getElementById('hangupButton').addEventListener('click', () => {
      if (session) {
        session.terminate();
      }
    });

    // セッションハンドラーの設定
    function setCallSessionHandlers() {
      session.on('connecting', () => {
        updateStatus('発信中...');
      });

      session.on('progress', () => {
        updateStatus('呼び出し中...');
      });

      session.on('accepted', async () => {
        updateStatus('通話中');
        document.getElementById('hangupButton').disabled = false;
        
        const remoteAudio = document.getElementById('remoteAudio');
        remoteAudio.style.display = 'block';
        
        try {
          const stream = new MediaStream();
          session.connection.getReceivers().forEach(receiver => {
            if (receiver.track.kind === 'audio') {
              stream.addTrack(receiver.track);
            }
          });
          remoteAudio.srcObject = stream;
          await remoteAudio.play().catch(e => console.warn('自動再生エラー:', e));
        } catch (error) {
          console.error('音声ストリーム設定エラー:', error);
        }
      });

      session.on('ended', () => {
        updateStatus('通話終了');
        resetCallState();
      });

      session.on('failed', (e) => {
        updateStatus('通話失敗: ' + e.cause);
        resetCallState();
      });
    }

    // ステータス更新関数
    function updateStatus(message) {
      document.getElementById('status').textContent = message;
    }

    // 通話状態のリセット
    function resetCallState() {
      document.getElementById('hangupButton').disabled = true;
      document.getElementById('callButton').disabled = false;
      session = null;
    }

    // 発信ボタンの有効化
    function enableCallButton() {
      document.getElementById('callButton').disabled = false;
    }

    // 発信ボタンの無効化
    function disableCallButton(disabled) {
      document.getElementById('callButton').disabled = disabled;
    }

    // UA開始
    ua.start();

    // 通話オプションの設定
    const options = {
      mediaConstraints: {
        audio: true,
        video: false
      },
      pcConfig: {
        iceServers: [
          { urls: ['stun:stun.l.google.com:19302'] }
        ]
      },
      rtcOfferConstraints: {
        offerToReceiveAudio: 1,
        offerToReceiveVideo: 0
      }
    };

    const playAudio = async () => {
      try {
        const remoteAudio = document.getElementById('remoteAudio');
        await remoteAudio.play();
      } catch (err) {
        console.error('音声再生エラー:', err);
      }
    };
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
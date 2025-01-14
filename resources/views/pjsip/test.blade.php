<!DOCTYPE html>
<html>
<head>
  <title>JsSIP WebSocket Connection Test</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/jssip/dist/jssip.min.js"></script>
</head>
<body>
  <h1>JsSIP WebSocket Test</h1>
  <div id="status"></div>

  <script>
    // JsSIPを初期化
    const socket = new JsSIP.WebSocketInterface('wss://asterisk-dev-kijima.ringing.co.jp:8089/ws');
    const configuration = {
      sockets: [socket],
      uri: 'sip:6000@asterisk-dev-kijima.ringing.co.jp', // 例：6000はSIPエンドポイント
      password: 'your_password', // SIPエンドポイントのパスワード
    };

    const ua = new JsSIP.UA(configuration);

    // イベントリスナーを設定
    ua.on('connected', () => {
      document.getElementById('status').innerText = 'WebSocket 接続成功！';
      console.log('WebSocket 接続成功');
    });

    ua.on('disconnected', () => {
      document.getElementById('status').innerText = 'WebSocket 接続失敗';
      console.log('WebSocket 接続失敗');
    });

    ua.on('registrationFailed', (e) => {
      document.getElementById('status').innerText = '登録失敗: ' + e.cause;
      console.error('登録失敗:', e.cause);
    });

    ua.on('registered', () => {
      document.getElementById('status').innerText = 'SIP登録成功！';
      console.log('SIP登録成功');
    });

    ua.on('unregistered', () => {
      document.getElementById('status').innerText = 'SIP登録解除';
      console.log('SIP登録解除');
    });

    ua.on('registrationExpiring', () => {
      console.log('SIP登録が期限切れ');
      ua.register();
    });

    // 接続開始
    ua.start();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

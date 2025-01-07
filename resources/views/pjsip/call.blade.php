<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>通話</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>通話画面</h1>

        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <p class="card-text">発信元内線番号: {{ $user->extension }}</p>
                    <label for="extensionInput" class="form-label">発信先内線番号</label>
                    <input type="text" class="form-control" id="extensionInput" 
                           pattern="[6][0-9]{3}" 
                           title="6000台の4桁の数字を入力してください"
                           placeholder="例: 6001">
                    <div class="form-text">6000台の4桁の数字で入力してください</div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button id="callButton" class="btn btn-success">通話開始</button>
            <button id="hangupButton" class="btn btn-danger" disabled>通話終了</button>
            <a href="{{ route('pjsip.index') }}" class="btn btn-secondary">戻る</a>
        </div>

        <div id="status" class="alert alert-info" style="display: none;"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jssip@3.10.0/dist/jssip.min.js"></script>
    <script>
        // ステータス表示用の関数
        function showStatus(message, type = 'info') {
            const statusDiv = document.getElementById('status');
            statusDiv.textContent = message;
            statusDiv.className = `alert alert-${type}`;
            statusDiv.style.display = 'block';
        }

        // JsSIP設定
        const socket = new JsSIP.WebSocketInterface('wss://your-asterisk-server:8089/ws');
        const configuration = {
            sockets: [socket],
            uri: 'sip:{{ $user->extension }}@your-asterisk-server',
            password: 'your_password',
            realm: 'your_realm'
        };

        const ua = new JsSIP.UA(configuration);
        let session = null;

        // UAイベントハンドラ
        ua.on('connected', () => {
            showStatus('SIPサーバーに接続しました', 'success');
        });

        ua.on('disconnected', () => {
            showStatus('SIPサーバーから切断されました', 'warning');
            document.getElementById('callButton').disabled = false;
            document.getElementById('hangupButton').disabled = true;
        });

        ua.on('registered', () => {
            showStatus('SIPサーバーに登録されました', 'success');
            document.getElementById('callButton').disabled = false;
        });

        // 通話開始ボタンのイベントハンドラ
        document.getElementById('callButton').addEventListener('click', () => {
            const extensionInput = document.getElementById('extensionInput');
            const extension = extensionInput.value.trim();

            // 入力値の検証
            if (!extension.match(/^[6][0-9]{3}$/)) {
                showStatus('有効な内線番号を入力してください（6000台の4桁）', 'danger');
                return;
            }

            // 自分自身への発信を防止
            if (extension === '{{ $user->extension }}') {
                showStatus('自分自身への発信はできません', 'danger');
                return;
            }

            const options = {
                mediaConstraints: { audio: true, video: false }
            };
            
            session = ua.call(`sip:${extension}@your-asterisk-server`, options);
            
            session.on('connecting', () => {
                showStatus(`${extension}に発信中...`, 'info');
                document.getElementById('callButton').disabled = true;
                document.getElementById('hangupButton').disabled = false;
            });

            session.on('accepted', () => {
                showStatus(`${extension}と通話中`, 'success');
            });

            session.on('ended', () => {
                showStatus('通話が終了しました', 'info');
                document.getElementById('callButton').disabled = false;
                document.getElementById('hangupButton').disabled = true;
                session = null;
            });

            session.on('failed', (e) => {
                showStatus('通話に失敗しました: ' + e.cause, 'danger');
                document.getElementById('callButton').disabled = false;
                document.getElementById('hangupButton').disabled = true;
                session = null;
            });
        });

        // 通話終了ボタンのイベントハンドラ
        document.getElementById('hangupButton').addEventListener('click', () => {
            if (session) {
                session.terminate();
            }
        });

        // UAの開始
        ua.start();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
import JsSIP from 'jssip';

class SipClient {
    constructor() {
        this.socket = null;
        this.ua = null;
        this.session = null;
        this.callOptions = {
            mediaConstraints: {
                audio: true,
                video: false
            }
        };
    }

    init(extension, password) {
        // WebSocketの設定を修正
        const socket = new JsSIP.WebSocketInterface('wss://160.16.73.142:8089/ws');
        
        const configuration = {
            sockets: [socket],
            uri: `sip:${extension}@160.16.73.142`,
            password: password,
            realm: '160.16.73.142',
            register: true,
            register_expires: 60
        };

        this.ua = new JsSIP.UA(configuration);

        // UAイベントハンドラの設定
        this.ua.on('connected', () => {
            this.updateStatus('WebSocket接続完了');
        });

        this.ua.on('disconnected', () => {
            this.updateStatus('WebSocket切断');
        });

        this.ua.on('registered', () => {
            this.updateStatus('SIP登録完了');
        });

        this.ua.on('registrationFailed', (e) => {
            this.updateStatus('SIP登録失敗');
            console.error('登録失敗の詳細:', e);
        });

        this.ua.start();
    }

    call(extension) {
        const uri = `sip:${extension}@160.16.73.142`;
        this.session = this.ua.call(uri, this.callOptions);
        this.setSessionHandlers();
    }

    hangup() {
        if (this.session) {
            this.session.terminate();
        }
    }

    setSessionHandlers() {
        this.session.on('connecting', () => {
            this.updateStatus('発信中...');
        });

        this.session.on('progress', () => {
            this.updateStatus('呼び出し中...');
        });

        this.session.on('accepted', () => {
            this.updateStatus('通話中');
            document.getElementById('hangupButton').disabled = false;
        });

        this.session.on('ended', () => {
            this.updateStatus('通話終了');
            document.getElementById('hangupButton').disabled = true;
        });

        this.session.on('failed', () => {
            this.updateStatus('通話失敗');
            document.getElementById('hangupButton').disabled = true;
        });
    }

    updateStatus(status) {
        const statusDiv = document.getElementById('status');
        statusDiv.textContent = status;
        statusDiv.style.display = 'block';
    }
}

window.SipClient = new SipClient(); 
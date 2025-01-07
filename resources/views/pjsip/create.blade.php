<!DOCTYPE html>
<html>
<head>
    <title>内線新規作成 - PJSIP内線管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>内線新規作成</h1>
        
        <!-- 成功メッセージ表示 -->
        @if (session('success'))
            <div id="successMessage" class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- エラーメッセージ表示 -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- フォーム -->
        <form id="createForm" action="{{ route('pjsip.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="extension" class="form-label">内線番号</label>
                <input type="text" class="form-control" id="extension" name="extension" required pattern="[6][0-9]{3}" title="6000台の4桁の数字を入力してください">
                <div class="form-text">6000台の4桁の数字で入力してください（例：6001）</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">パスワード</label>
                <input type="text" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">名前</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label for="department" class="form-label">部署</label>
                <input type="text" class="form-control" id="department" name="department" value="{{ old('department') }}" required>
            </div>
            <div class="mb-3">
                <label for="position" class="form-label">役職</label>
                <input type="text" class="form-control" id="position" name="position" value="{{ old('position') }}" required>
            </div>
            <button type="submit" class="btn btn-primary">作成</button>
            <a href="{{ route('pjsip.index') }}" class="btn btn-secondary">戻る</a>
        </form>

    </>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 成功メッセージを数秒後に非表示
        setTimeout(() => {
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
        }, 3000); // 3秒後に非表示

        // フォームをクリア
        setTimeout(() => {
            document.getElementById("createForm").reset();
        }, 3000); // 3秒後にリセット
    </script>
</body>
</html>

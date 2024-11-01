<!DOCTYPE html>
<html>
<head>
    <title>内線情報編集 - PJSIP内線管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>内線情報編集</h1>

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
        <form action="{{ route('pjsip.update', $user->extension) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="extension" class="form-label">内線番号</label>
                <input type="text" class="form-control" id="extension" name="extension" value="{{ $user->extension }}" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">名前</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
            </div>
            <div class="mb-3">
                <label for="department" class="form-label">部署</label>
                <input type="text" class="form-control" id="department" name="department" value="{{ $user->department }}" required>
            </div>
            <div class="mb-3">
                <label for="position" class="form-label">役職</label>
                <input type="text" class="form-control" id="position" name="position" value="{{ $user->position }}" required>
            </div>
            <button type="submit" class="btn btn-primary">更新</button>
            <a href="{{ route('pjsip.index') }}" class="btn btn-secondary">戻る</a>
        </form>
        <form action="{{ route('pjsip.destroy', $user->extension) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('本当に削除しますか？')">削除</button>
        </form>
    </div>
</body>
</html>

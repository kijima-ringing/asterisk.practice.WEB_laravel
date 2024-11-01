<!DOCTYPE html>
<html>
<head>
    <title>内線一覧 - PJSIP内線管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>内線一覧</h1>
        <!-- 成功メッセージ表示 -->
        @if (session('success'))
            <div id="successMessage" class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <!-- 検索フォーム -->
        <form action="{{ route('pjsip.index') }}" method="GET" class="mb-3">
            <input type="text" name="search" class="form-control" placeholder="内線番号、名前、部署、役職で検索">
            <button type="submit" class="btn btn-primary mt-2">検索</button>
        </form>
        <!-- 内線情報のテーブル -->
        <table class="table">
            <thead>
                <tr>
                    <th>内線番号</th>
                    <th>名前</th>
                    <th>部署</th>
                    <th>役職</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->extension }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->department }}</td>
                        <td>{{ $user->position }}</td>
                        <td>
                            <a href="{{ route('pjsip.edit', $user->extension) }}" class="btn btn-warning btn-sm">編集</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('pjsip.create') }}" class="btn btn-primary">新規作成</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

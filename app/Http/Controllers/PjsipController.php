<?php

namespace App\Http\Controllers;

use App\Models\PjsipEndpoint;
use App\Models\PjsipAuth;
use App\Models\PjsipAor;
use Illuminate\Http\Request;

class PjsipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \DB::table('users')->select('extension', 'name', 'department', 'position');

        // 検索機能の実装
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('extension', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('department', 'LIKE', "%{$search}%")
                  ->orWhere('position', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->get();

        return view('pjsip.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pjsip.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'extension' => 'required|unique:ps_endpoints,id|digits:4',
            'password' => 'required',
            'name' => 'required|string|max:100',
            'department' => 'required|string|max:50',
            'position' => 'required|string|max:50',
        ]);

        \DB::transaction(function () use ($request) {
            $now = now();

            // エンドポイントの作成
            PjsipEndpoint::create([
                'id' => $request->extension,
                'context' => 'from-internal',
                'disallow' => 'all',
                'allow' => 'ulaw',
                'auth' => $request->extension,
                'aors' => $request->extension,
                'rewrite_contact' => 'yes',
                'mailboxes' => $request->extension . '@default',
                'transport' => 'transport-udp'
            ]);

            // 認証の作成
            PjsipAuth::create([
                'id' => $request->extension,
                'auth_type' => 'userpass',
                'password' => $request->password,
                'username' => $request->extension
            ]);

            // AORの作成
            PjsipAor::create([
                'id' => $request->extension,
                'type' => 'aor',
                'max_contacts' => 10
            ]);

            // ユーザー情報の保存
            \DB::table('users')->insert([
                'extension' => $request->extension,
                'name' => $request->name,
                'department' => $request->department,
                'position' => $request->position,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return redirect()->route('pjsip.create')
            ->with('success', "内線番号 {$request->extension} を作成しました");
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = \DB::table('users')->where('extension', $id)->first();
        
        return view('pjsip.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // 現在の内線番号を取得
        $currentExtension = $id;
        $newExtension = $request->extension;

        // バリデーション
        $request->validate([
            'name' => 'required|string|max:100',
            'department' => 'required|string|max:50',
            'position' => 'required|string|max:50',
            'extension' => 'required|string|max:4|unique:users,extension,' . $currentExtension . ',extension' .
                           '|unique:ps_endpoints,id,' . $currentExtension . ',id' .
                           '|unique:ps_auths,id,' . $currentExtension . ',id' .
                           '|unique:ps_aors,id,' . $currentExtension . ',id',
        ]);

        // usersテーブルの更新
        \DB::table('users')->where('extension', $currentExtension)->update([
            'name' => $request->name,
            'department' => $request->department,
            'position' => $request->position,
            'extension' => $newExtension,
            'updated_at' => now(),
        ]);

        // ps_endpointsテーブルの更新
        \DB::table('ps_endpoints')->where('id', $currentExtension)->update([
            'id' => $newExtension,
            'auth' => $newExtension,
            'aors' => $newExtension,
            'mailboxes' => $newExtension . '@default',
        ]);

        // ps_authsテーブルの更新
        \DB::table('ps_auths')->where('id', $currentExtension)->update([
            'id' => $newExtension,
            'username' => $newExtension,
        ]);

        // ps_aorsテーブルの更新
        \DB::table('ps_aors')->where('id', $currentExtension)->update([
            'id' => $newExtension,
        ]);

        // 更新メッセージをリダイレクト
        return redirect()->route('pjsip.index')
            ->with('success', "内線番号 {$currentExtension} の情報を更新しました");
    }
  
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // トランザクションを使用して、関連するすべてのレコードを削除
        \DB::transaction(function () use ($id) {
            // usersテーブルの削除
            \DB::table('users')->where('extension', $id)->delete();

            // ps_endpointsテーブルの削除
            \DB::table('ps_endpoints')->where('id', $id)->delete();

            // ps_authsテーブルの削除
            \DB::table('ps_auths')->where('id', $id)->delete();

            // ps_aorsテーブルの削除
            \DB::table('ps_aors')->where('id', $id)->delete();
        });

        return redirect()->route('pjsip.index')
            ->with('success', "内線番号 {$id} の情報を削除しました");
    }

    public function call($id)
    {
        $user = \DB::table('users')->where('extension', $id)->first();
        return view('pjsip.call', compact('user'));
    }
}

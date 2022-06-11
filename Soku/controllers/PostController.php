<?php


class PostController extends Controller
{

    public function helloAction()
    {
        // viewファイルの読み込み
        return $this->render();
    }

    public function homeAction()
    {
        // viewファイルの読み込み
        return $this->render();
    }

    // 投稿先一覧を取得する
    public function postsAction()
    {
        // postをDBから取得
        $posts = $this->db_manager
                        ->get('Post')
                        ->fetchAllPosts();

        // 取得したpostをparameterとしてviewに渡す
        return $this->render(
            [
                'posts' => $posts
            ]
        );
    }


    // DBへ投稿する
    public function postAction()
    {
        // POSTでのアクションでなければ404を返す
        if(!$this->request->isPost())
        {
            $this->forward404();
        }

        // posts画面にリダイレクトさせる為、投稿先一覧を取得する
        $posts = $this->db_manager
            ->get('Post')
            ->fetchAllPosts();

        // バリデーション処理
        $errors = [];

        $comment = $this->request->getPost('comment');
        $name = $this->request->getPost('name');

        if (!strlen($comment)) {
            // commentが入力されていない場合
            $errors[] = 'ひとことを入力してください';
        } else if (mb_strlen($comment) > 200) {
            // commentが200文字以上で入力されている場合
            $errors[] = 'ひとことは200 文字以内で入力してください';
        }

        if (!strlen($name)) {
            // nameが入力されていない場合
            $errors[] = '名前を入力して下さい';
        }

        // エラーがない場合
        if (count($errors) === 0) {
            // Postテーブルにデータを挿入
            $this->db_manager->get('Post')->insert($name, $comment);
            // /postsにリダイレクト
            return $this->redirect('/posts');
        }

        // エラーがある場合
        // 投稿画面を表示
        return $this->render([
            'errors'   => $errors,
            'comment'  => $comment,
            'name'  => $name,
            'posts' => $posts
            // 明示的にpostsを入れないと、テンプレート名は$actionName(post)になる
            ],'posts');
    }
}
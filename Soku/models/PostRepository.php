<?php


class PostRepository extends DbRepository
{
    /*
     * 投稿を全て取得する
     */
    public function fetchAllPosts()
    {
        $sql = "
         SELECT * FROM post;   
        ";
        return $this->fetchAll($sql);
    }

    /**
     *
     * 新規投稿を行う
     *
     * @param $name
     * @param $comment
     */
    public function insert($name, $comment)
    {
        // insert処理を行う時間を取得
        $now = new DateTime();


        // 実行するSQL
        $sql = "
            INSERT INTO post(name, comment, created_at)
                VALUES(:name, :comment, :created_at)
        ";

        // SQLを実行した結果を取得
        $stmt = $this->execute($sql, [
            ':name'    => $name,
            ':comment'       => $comment,
            ':created_at' => $now->format('Y-m-d H:i:s'),
        ]);
    }
}

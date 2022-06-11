<?php
/**
 * Created by PhpStorm.
 * User: endu
 * Date: 2019/03/25
 * Time: 14:56
 */

class View
{
    protected $base_dir;
    protected $defaults;
    protected $layout_variables = [];

    /**
     *
     * View constructor.
     *
     * @param $base_dir vireファイルが置かれておりディレクトリ
     * @param $defaults Viewに渡すdefaultの値を設定できる
     */
    public function __construct($base_dir, $defaults)
    {
        $this->base_dir = $base_dir;
        $this->defaults = $defaults;
    }

    // viewファイルでの変数の設定
    /**
     *
     * 変数layout.phpに$nameをキーとした$valueを値としいて格納する
     *
     * @param $name
     * @param $value
     */
    public function setLayoutVar($name,$value)
    {
        $this->layout_variables[$name] = $value;
    }

    /**
     * ビューファイルをレンダリング
     *
     * @param string $_path viewファイルのパス
     * @param array $_variables 表示する値
     * @param mixed $_layout
     * @return string
     */
    public function renderView($_path, $_variables = [ ], $_layout = false)
    {
        // viewファイルの特定
        $_file = $this->base_dir . '/' . $_path . '.php';

        // 変数をインポート
        extract(array_merge($this->defaults, $_variables));

        // アウトプットバッファリグ開始
        ob_start();
        // バッファを超えた場合に自動でフラッシュする設定をOFFにする
        ob_implicit_flush(0);

        // viewファイル読み込む
        require $_file;

        // 出力した内容を$contentに格納
        $content = ob_get_clean();

        // viewsディレクトリ直下のファイルを読み込む
        if ($_layout) {
            $content = $this->renderView($_layout,
                array_merge($this->layout_variables, array(
                        '_content' => $content,
                    )
                ));
        }

        // 読み込んだファイルの内容を返す
        return $content;
    }

    // 文字列をescapeする
    public function escape($string)
    {
        return htmlspecialchars($string,ENT_QUOTES,'UTF-8');
    }



}
<?php

class ClassLoader
{
    protected $dir;

    // autoload処理を行う関数を指定する
    /**
     *
     * PHPにオートローダクラスを指定する
     *
     */
    public function register()
    {
        // ClassLoaderのloadClass関数をautoloadを行う関数として指定
        spl_autoload_register([$this,'loadClass']);
    }

    // Classの読み込み先のディレクトリを指定する
    /**
     *
     * 特定のディレクトリからクラスを読むこむようにします。
     *
     * @param $dir ディレクトリ名
     */
    public function registerDir($dir)
    {
        $this->dir[ ] = $dir;
    }

    /**
     *
     * $dirを取得する
     *
     * @return $dir
     */
    public function getDir()
    {
        return $this->dir;
    }

    // 指定したディレクトリからClassファイルを読み込む
    /**
     *
     * クラスが指定したディレクトリに存在し、存在すればそのクラスをrequireしてくる
     *
     * @param $class クラス名
     */
    public function loadClass($class)
    {
        foreach ($this->dir as $dir){
            $file = $dir . '/' . $class.'.php';
            // Tells whether a file exists and is readable
            if(is_readable($file)){
                require $file;

                return;
            }
        }
    }
}
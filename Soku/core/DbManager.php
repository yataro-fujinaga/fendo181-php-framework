<?php

class DbManager
{
    protected $connections = [];
    protected $repository_connection_map = [];
    protected $repositories = [];

    /**
     *
     * PDOオブジェクトを使ってDBへの接続情報を管理する
     *
     * @param $name
     * @param $params
     */
    public function connect($name,$params)
    {
        $params = array_merge([
           'dsn' => null,
           'user' => '',
           'password' => '',
           'options' => [],
        ],$params);

        try {
            $con = new PDO(
                $params['dsn'],
                $params['user'],
                $params['password'],
                $params['options']
            );
        }catch (PDOException $e){
            echo 'Connection failed: ' . $e->getMessage();
        }


        $con->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        $this->connections[$name] = $con;
    }

    /**
     *
     * 指定がなければ最初のPDOクラスのインスタンスを返して、$nameがあれば新しいPDOインスタンスを返します
     *
     * @param null $name
     * @return PDOインスタンス
     */
    public function getConnection($name = null)
    {
        // 接続先の指定がなければ、最初のPDOインスタンスを返す
        if(is_null($name)){
            return current($this->connections);
        }

        // 接続先の指定があれば、そのPDOインスタンスを返す
        return $this->connections[$name];
    }

    /**
     *
     * Repositoryクラスでどの接続を扱うかを管理する
     * repository_connection_mapプロパティにテーブルごとのRepositoryクラスと接続名の対応を格納する
     *
     * @param $repository_name
     * @param $name
     */
    public function setRepositoryConnectionMap($repository_name, $name)
    {
        $this->repository_connection_map[$repository_name] = $name;
    }

    /**
     *
     * Repositoryクラスに対応する接続を取得しようとした際に、既にrepository_connection_mapに設定されていたら、その設定を使う
     * そうでなければ、最初に作成したものを取得する
     *
     * @param $repository_name
     * @return PDOインスタンス
     */
    public function getConnectionForRepository($repository_name)
    {
        // 指定したRepositoryに対応するTableが存在するか調べる
        if(isset($this->repository_connection_map[$repository_name])){
            // 存在したら、その対応に沿ってDBに接続する
            $name = $this->repository_connection_map[$repository_name];
            $con = $this->getConnection($name);
        }else{
            // 存在しなかったら、新規にDBに接続する
            $con = $this->getConnection();
        }

        return $con;
    }

    /**
     *
     * 一度作成したRepositoryクラスのインスタンスを取得する
     *
     * @param $repository_name
     * @return Repositoryクラスのインスタンスを返す
     */
    public function get($repository_name)
    {
        // 指定したRepositoryが作成されているか確認
        if(!isset($this->repositories[$repository_name])){
            // 作成されている場合
            $repository_class = $repository_name.'Repository';
            // 指定したRepositoryに対応したDBの接続情報を返す
            $con = $this->getConnectionForRepository($repository_name);

            // 指定したRepositoryをInstance化
            $repository = new $repository_class($con);

            // Instance化したRepositoryを格納
            $this->repositories[$repository_name] = $repository;
        }

        // 作成されていない場合
        return $this->repositories[$repository_name];
    }

    /**
     *
     * インスタンスが破棄されたタイミングでデータベースの接続状態を解除する
     *
     */
    public function __destruct()
    {
        foreach ($this->repositories as $repository){
            unset($repository);
        }

        foreach ($this->connections as $con){
            unset($con);
        }
    }
}
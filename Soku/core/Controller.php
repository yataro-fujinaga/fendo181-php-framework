<?php

abstract class Controller
{
    protected $controller_name;
    protected $action_name;
    protected $application;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
    protected $auth_actions = [];

    public function __construct($application)
    {
        // Ccontrollerが10文字なので、後ろの10文字を取り除いて、クラス名を小文字にする
        $this->controller_name = strtolower(substr(get_class($this), 0, -10));

        $this->application = $application;
        $this->request = $application->getRequest();
        $this->response = $application->getResponse();
        $this->session = $application->getSession();
        $this->db_manager = $application->getDbManager();
    }


    /**
     *
     * Applicationクラスから呼び出されて実際に実行するメソッド
     *
     * @param $action
     * @param array $params
     * @return mixed
     */
    public function run($action, $params = [])
    {
        $this->action_name = $action;

        $action_methods = $action . 'Action';

        // Controllerに指定したAction名があるか確認
        if (!(method_exists($this, $action_methods))) {
            // ないなら例外を投げる
            $this->forward404();
        }

        // ログイン判定処理
        // ログインが必要なActionでログインしているかを確認
        if($this->needsAuthentication($action) && !$this->session->isAuthenticated()){
            // ログインしていない例外を投げる
            throw new UnauthorizedActionException();
        }

        // 可変関数で動的にメソッドを変えるようにする
        // 指定したControllerのActionにparameterを渡して実行
        $content = $this->$action_methods($params);

        // Actionの戻り値を返す
        return $content;
    }

    /**
     *
     * ログインが必要か判定を行う
     *
     * @param $action
     * @return bool|void
     */
    public function needsAuthentication($action)
    {
        // 認証が必要なactionとして設定されているかを確認
        if($this->auth_actions === true || (is_array($this->auth_actions) && in_array($action, $this->auth_actions))){
          return true;
        }

        return false;
    }



    /**
     *
     *  ビューファイルの読み込み処理をラッピングしたメソッド
     *
     * @param array $variables
     * @param null $template
     * @param string $layout
     * @return string
     */
    protected function render($variables = [], $template = null, $layout = 'layout')
    {
        $defaults = [
            // RequestクラスのInstanceを取得
            'request' => $this->request,
            // 実行されるscriptのURI
            'base_url' => $this->request->getbaseURl(),
            // SessionクラスのInstanceを取得
            'session' => $this->session,

        ];

        // ViewのInstanceを生成する
        $view = new View($this->application->getViewDir(), $defaults);

        // テンプレート名を指定してなかったら、アクション名がテンプレート名になる
        if (is_null($template)) {
            $template = $this->action_name;
        }

        // viewファイルのパスを取得
        // （例）UseControllerだったら、user/$templateとなる
        $path = $this->controller_name . '/' . $template;

        // Viewクラスのrenderメソッド
        // viewファイルの内容を返す
        return $view->renderView($path, $variables, $layout);
    }

    /**
     *
     * 404画面に遷移させるメソッド
     *
     * @throws HttpNotFoundException
     */
    protected function forward404()
    {
        throw new HttpNotFoundException('Forwarded 404 page from' . $this->controller_name . '/' . $this->action_name);
    }

    /**
     *
     * URLを引数として受け取り、Responseオブジェクトにリダイレクトさせるように設定する
     *
     * @param $url
     */
    protected function redirect($url)
    {
        // urlがhttp,httpsで始まっていない場合
        if (!preg_match('#https?://#', $url)) {
            // httpsかhttpかを取得
            $protocol = $this->request->isSSl() ? 'https//' : 'http';
            // hostを取得
            $host = $this->request->getHost();
            // base urlを取得
            $base_url = $this->request->getBaseUrl();

            // urlを取得
            $url = $protocol . $host . $url;
        }

        // Status Codeを設定する
        $this->response->setStatusCode(302, 'Found');
        // HttpHeaderの設定
        $this->response->setHttpHeader('Location', $url);
    }

    // CSRF対策

    /**
     *
     * CSRF対策の為、トークンを生成し、サーバ上に保存するためにセッションに格納を行う
     *
     * @param $form_name
     * @return csrf_tpken
     */
    protected function generateCsrfToken($form_name)
    {
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $this->session->get($key, []);
        if (count($tokens) > 10) {
            array_shift($tokens);
        }

        $token = sha1($form_name . session_id() . microtime());
        $tokens[] = $token;

        $this->session->set($key, $tokens);

        return $token;
    }

    /**
     *
     * セッション上に格納されたCSRFトークンを確認し一度破棄してから再度生成します
     *
     * @param $form_name
     * @param $token
     * @return bool
     */
    protected function checkCsrfTokens($form_name, $token)
    {
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $this->session->get($key,[]);

        $pos = array_search($token, $tokens, true);

        if($pos !== false){
            unset($tokens[$pos]);
            $this->session->set($key,$tokens);

            return true;
        }

        return false;
    }
}
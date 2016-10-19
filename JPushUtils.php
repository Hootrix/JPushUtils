<?php

/**
 * JPush操作类
 *
 * Created by PhpStorm.
 * User: Pang
 * Date: 2016/10/18
 * Time: 21:29
 */

class JPushUtils
{
    private $APP_KEY = '';
    private $MASTER_SECRET = '';
    const ALLOW_DATA = array('all', 'ios', 'android');

    private $client;
    private $push_payload;

    //链式操作中判断方法是否调用
    private $_platform = null;
    private $_subscribers = null;
    private $_notification = null;//推送内容


    public function __construct()
    {
        $this->client = new JPush\Client($this->APP_KEY, $this->MASTER_SECRET);
        $this->push_payload = $this->client->push();
    }

    /**
     * @param string $APP_KEY
     */
    public function setAPPKEY($APP_KEY)
    {
        $this->APP_KEY = $APP_KEY;
    }

    /**
     * @param string $MASTER_SECRET
     */
    public function setMASTERSECRET($MASTER_SECRET)
    {
        $this->MASTER_SECRET = $MASTER_SECRET;
    }


    /**
     * 设置推送的平台
     * 参数遵循sdk中的配置
     *
     * @return JPush
     */
    public function setPlatform($platform = 'all')
    {
//        ['ios', 'android']
        $this->_platform = $platform;
        $this->push_payload->setPlatform($platform);
        return $this;
    }

    /**
     * 设置订阅者
     * ['tag' => ['tag1', 'tag2'], 'alias' => 'Alias1','registrationid'=>'aaaa'];
     * @param string $subscribers
     * @return $this
     */
    public function setSubscribers($subscribers = 'all')
    {
        $this->_subscribers = $subscribers;
        if (is_array($subscribers)) {
            foreach ($subscribers as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $itemChild) {
                        $action = $this->checkSubscribersAction($itemChild);
                        if ($action) $this->push_payload->{$action}($itemChild);
                    }
                } elseif (is_string($v)) {
                    $action = $this->checkSubscribersAction($k);
                    if ($action) $this->push_payload->{$action}($v);
                } else {
//                    throw new Exception('请勿传入非String,Array型数据');
                }
            }

        } else {
            $this->push_payload->addAllAudience();
        }
        return $this;
    }

    /**
     * 检测推送给订阅者的对象名称,然后返回给调用方的操作方法名
     * @param $name
     * @return bool|string
     */
    private function checkSubscribersAction($name)
    {
        $name = strtolower($name);
        switch ($name) {
            case 'tag'://标签
                return 'addTag';
                break;
            case 'alias'://别名
                return 'addAlias';
                break;
            case 'registrationid'://注册的设备id
                return 'addRegistrationId';
                break;
        }
        return false;
    }

    /**
     * 设置推送的内容
     *
     *  参数二遵循sdk中的配置
     * $platformIndividualSettings = [
     * 'android' => [
     * 'extras' => ['00000000000' => 'a'],
     * 'title' => '标题'],
     * 'ios' => [
     * 'sound' => 'sound',
     * 'badge' => '+1',
     * 'extras' => [
     * 'key' => 'value'
     * ]]];
     *
     * @param $content
     * @param string $platformIndividualSettings 单独设置需要推送的平台的配置
     * @return JPush
     */
    public function setNotification($content, $platformIndividualSettings = 'all')
    {
        if (!$content) {
            throw  new  InvalidArgumentException('必须设置setNotification参数一。');
        }
        $this->_notification = $content;
        if (is_string($platformIndividualSettings) && 'all' == $platformIndividualSettings) {
            $this->push_payload->setNotificationAlert($content);
        } else if (is_array($platformIndividualSettings)) {
            foreach ($platformIndividualSettings as $k => $v) {
                if (in_array($k, ['ios', 'android'])) {
                    switch ($k) {
                        case 'ios':
                            $this->push_payload->iosNotification($content, $v);
                            break;
                        case 'android':
                            $this->push_payload->androidNotification($content, $v);
                            break;
                    }
                }
            }
        } else {
            throw  new InvalidArgumentException('参数2 非法。');
        }
        return $this;
    }

    /**
     * 操作完成执行发送动作
     *
     * @return JPush
     */
    public function send()
    {
        if (!$this->_platform) {
            $this->setPlatform();
        }
        if (!$this->_subscribers) {
            $this->setSubscribers();
        }
        if (!$this->_notification) {
            $this->setNotification('jPush test data：' . date("m-d H:i:s", time()));
        }


        try {
            $response = $this->push_payload->send();
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            // try something here
            print $e;
        } catch (\JPush\Exceptions\APIRequestException $e) {
            // try something here
            print $e;
        }
//        print_r($response);
        return $response;
    }
}

//
//require __DIR__ . '/../autoload.php';
//
//$o = new JPushUtils();
//$o->setPlatform()->setSubscribers()->setNotification('content',['android' => ['title' => 'title']])->send();
#END PANG

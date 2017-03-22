<?php
/**
 * Created by PhpStorm.
 * User: Lenovo
 * Date: 2017/3/2
 * Time: 21:05
 */

namespace dungang\webuploader\actions;

use yii\base\Action;
use yii\helpers\Json;
use dungang\storage\Driver;
use dungang\storage\StorageEvent;

class DelAction extends Action
{

    use ActionTrait;

    public function run(){
        $result = [
            'jsonrpc'=>'2.0',
        ];
        if ($post = \Yii::$app->request->post()) {
            unset($post[\Yii::$app->request->csrfParam]);

            $this->instanceDriver($post);

            if(isset($post['fileObj'])) {
                $delObj = $post['fileObj'];
                unset($post['fileObj']);
                $event = new StorageEvent();
                $this->driverInstance->trigger(Driver::EVENT_BEFORE_DELETE_FILE, $event);
                if ($this->driverInstance->deleteFile($delObj)) {
                    $result['result'] = $delObj;
                    $event->file = $delObj;
                    $this->driverInstance->trigger(Driver::EVENT_AFTER_DELETE_FILE,$event);
                } else {
                    $result['error'] = [
                        'code'=> '110',
                        'message' => '文件删除失败',
                    ];
                }
            }
            $result['id'] = $this->driverInstance->id;
        } else {
            $result['error'] = [
                'code'=> '400',
                'message' => '请求不合法',
            ];
        }
        return Json::encode($result);
    }
}
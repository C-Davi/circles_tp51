<?php

namespace app\common\controller;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use app\lib\exception\BaseException;

class AliSMSController
{
    static public function sendSMS($phone,$code)
    {
        AlibabaCloud::accessKeyClient(config('api.aliSMS.accessKeyId'), config('api.aliSMS.accessSecret'))
            ->regionId(config('api.aliSMS.regionId'))->asDefaultClient();
        try {
            $options = [
                'query' => [
                    'RegionId' => config('api.aliSMS.regionId'),
                    'PhoneNumbers' => $phone,
                    'SignName' => config('api.aliSMS.SignName'),
                    'TemplateCode' => config('api.aliSMS.TemplateCode'),
                    'TemplateParam' => '{"code":'.$code.'}',
                ],
            ];
            $result = AlibabaCloud::rpc()
                ->product(config('api.aliSMS.product'))
                // ->scheme('https') // https | http
                ->version(config('api.aliSMS.version'))
                ->action('SendSms')
                ->method('GET')
                ->host('dysmsapi.aliyuncs.com')
                ->options($options)
                ->request();
            return $result->toArray();
        } catch (ClientException $e) {
            throw new BaseException(['code'=>200,'msg'=>$e->getErrorMessage(),'errorCode'=>30000]);
        } catch (ServerException $e) {
            throw new BaseException(['code'=>200,'msg'=>$e->getErrorMessage(),'errorCode'=>30000]);
        }
    }
}

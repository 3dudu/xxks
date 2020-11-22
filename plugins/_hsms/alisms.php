<?php
/**

 */

 /**

 */

require VENDOR_PATH . '/vendor/autoload.php';

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class alisms extends hsmsBase
{


	/**
	 * @brief 获取config用户配置
	 * @return array
	 */
	private function getConfig()
	{
		//如果后台没有设置的话，这里手动配置也可以
		$siteConfigObj = new Config("site_config");

        AlibabaCloud::accessKeyClient($siteConfigObj->sms_userid, $siteConfigObj->sms_pwd)
                        ->regionId('cn-hangzhou') // replace regionId as you need
                        ->asDefaultClient();
        return array(
            'accessKeyId'   => $siteConfigObj->sms_userid,
            'SignName' => $siteConfigObj->sms_username,
            'accessSecret'  => $siteConfigObj->sms_pwd,
        );
	}

	/**
	 * @brief 发送短信
	 * @param string $mobile
	 * @param string $content
	 * @return
	 */
	public function send($mobile,$content)
	{
        $config = self::getConfig();

        try {
            $result = AlibabaCloud::rpc()
                                  ->product('Dysmsapi')
                                  // ->scheme('https') // https | http
                                  ->version('2017-05-25')
                                  ->action('SendSms')
                                  ->method('POST')
                                  ->options([
                                                'query' => [
                                                  'RegionId' => "cn-hangzhou",
                                                  'PhoneNumbers' => $mobile,
                                                  'SignName' => $config['SignName'],
                                                  'TemplateCode' => $content['TemplateCode'],
                                                  'TemplateParam' => $content['TemplateParam'],
                                                ],
                                            ])
                                  ->request();
            return $this->response($result);
        } catch (ClientException $e) {
			return 'fail';
        } catch (ServerException $e) {
			return 'fail';
        }
	}

	/**
	 * @brief 解析结果
	 * @param $result 发送结果
	 * @return string success or fail
	 */
	public function response($result)
	{
		if($result['Message']=='OK')
		{
			return 'success';
		}
		else
		{
			return 'fail';
		}
	}

	/**
	 * @brief 配置文件
	 */
	public function getParam()
	{
		return array(
			"sms_userid"   => "AccessKey",
			"sms_username" => "签名",
			"sms_pwd"      => "AccessKeySecret",
		);
	}
}
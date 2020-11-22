<?php
/**
 * @brief 用户接口
 * @class Ucenter
 * 接口 访问路径
 */
class Order extends IApiController implements userAuthorization
{
	public function init()
	{
		$this->cacheAble = true;
		$this->cacheActions = array(
		//	'pointBand'=>60,
		);
		$this->versionActions = array(
		//	'pointBand'=>array('1.0.12','1.0.9'),
		);
		//$this->disableAction = array("pointBand");
    }

    public function suborder(){
        $user_id   = IFilter::act($this->user['user_id'],'int');
        $poolid = IFilter::act( IReq::get('poolid'),'int');
        $order_type = IFilter::act( IReq::get('order_type'),'int');
        $product_code = IFilter::act( IReq::get('product_code'));
        $amount = IFilter::act( IReq::get('amount'),'float');
        $orderinfo = '';
        $hasorder = false;
        $orderObj       = new IModel('order');
     
        //支付三十分钟
        $order = $orderObj->getObj('user_id='.$user_id.' and (status=0 or status=1) and ((order_type='.$order_type.' and product_id='.$poolid.') or (order_type=3 and curdate()>=start_date and curdate()<=end_date ))');
        if(!$poolid){
            $order_type=3;
        }
        if($order){
            if($order['status']==1){
                $hasorder = true;
            }else{
                $orderinfo = $this->alipay_data($order['orderno'],$order['product_id'],$order['amount']);
            }
            $this->setRenderData(array('hasorder'=>$hasorder,'order'=>$order,'orderinfo'=>$orderinfo));
        }else{
            $orderno = ITime::getDateTime('YmdHis').IHash::random(8);
            $order = array(
                'user_id'=>$user_id,
                'orderno'=>$orderno,
                'product_id'=>$poolid,
                'product_code'=>$product_code,
                'amount'=>$amount,
                'order_type'=>$order_type,
                'status'=>0,
                'create_time'=>ITime::getDateTime()
            );
            if($order_type==3){
                $order['start_date'] = ITime::getDateTime('Y-m-d');
                if($product_code=='q_pool_year'){
                    $order['end_date'] = ITime::getDateTime('Y-m-d',strtotime("+1 year"));
                }else{
                    $order['end_date'] = ITime::getDateTime('Y-m-d',strtotime("+1 month"));
                }
            }
            $orderinfo = $this->alipay_data($order['orderno'],$order['product_id'],$order['amount']);
            $orderObj->setData($order);
            $orderObj->add();
            $this->setRenderData(array('hasorder'=>$hasorder,'order'=>$order,'orderinfo'=>$orderinfo));
        }

    }

    public function alipay_data($orderno,$poolid,$amount){
        $payment['M_OrderNO'] = $orderno;
        $payment['M_Remark'] = '内容：'.$poolid;
        $payment['R_Name']  = '内容：'.$poolid;
        $payment['M_Amount']  = $amount;
        $payment['client']  = 'app';
        $paymentInstance = Payment::createPaymentInstance(18);
        $sendData = $paymentInstance->getSendData(Payment::getXXPayinfo(18,'order',$payment));
        return $sendData;
    }

    public function updateorder()
    {
        $user_id   = IFilter::act($this->user['user_id'],'int');
        $orderno = IFilter::act( IReq::get('orderno'));
        $orderObj       = new IModel('order');
        $order = $orderObj->getObj('orderno="'.$orderno.'" and user_id='.$user_id);
        if($order){
            if($order['status']==0){
                $orderObj->setData(array('status'=>1,'pay_time'=>ITime::getDateTime()));
                $order['status'] = 1;
                $orderObj->update('orderno="'.$orderno.'"');
            }
            $this->setRenderData(array('hasorder'=>true,'order'=>$order));
        }else{
            IError::show(403,"订单不存在");
        }
    }

    public function orderlist()
    {
        $user_id   = IFilter::act($this->user['user_id'],'int');
        $orderObj       = new IModel('order as o , exam_pool p');
        $orderlist = $orderObj->query('o.user_id='.$user_id.' and status=1 and o.product_id=p.id','p.*,o.product_code,o.amount,o.orderno,o.create_time,o.status');
        if($orderlist){
            foreach($orderlist as &$val){
                if(strpos($val['product_code'],'year')!==false){
                    $val['title']="包年VIP";
                    $val['price']=$val['amount'];
                }else if(strpos($val['product_code'],'month')!==false){
                    $val['title']="包月VIP";
                    $val['price']=$val['amount'];
                }
            }
            $this->setRenderData(array('orderlist'=>$orderlist));
        }else{
            $this->setRenderData(array('orderlist'=>array()));
        }
    }

    public function restoreOrder(){
        $user_id   = IFilter::act($this->user['user_id'],'int');
        $userObj = new IModel('user');
        $curuser =  $userObj->getObj('id='.$user_id);
        if($curuser){
            if($curuser['uuid'] && strlen($curuser['uuid'])==36){
                $uuser = $userObj->query('id<>'.$user_id.' and uuid="'.$curuser['uuid'].'"');
                if($uuser){
                    foreach($uuser as $val){
                        $orderObj       = new IModel('order');
                        $orderObj->setData(array("user_id"=>$user_id));
                        $orderObj->update("user_id=".$val['id']);
                    }
                }
            }
        }
    }
}
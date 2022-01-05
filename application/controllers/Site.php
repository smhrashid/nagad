<?php
//error_reporting(0);

        if (! defined('BASEPATH')) exit('No direct script access allowed');
        class Site extends CI_Controller {	

            public function index(){
                $this->home();
                }
                public function home() {
                   // if ($_SERVER['HTTP_HOST']=='192.168.1.73'){
                            $this->load->model('policyinfo_model');
                             if((count((array)$this->policyinfo_model->get_user()))==1){
                                 echo  $this->policyinfo_model->get_premcal();
                             }
                             else{
                                 echo 'invalid user/pass';
                             }
                       // }
                     }
             public function nagadcomf() {
               //  if ($_SERVER['HTTP_HOST']=='192.168.1.73'){
                    file_put_contents('log/'.(date("d-m-Y", time())).'-1payload.log',PHP_EOL .'------------------------------------------', FILE_APPEND);
                    file_put_contents('log/'.(date("d-m-Y", time())).'-1payload.log',PHP_EOL .file_get_contents('php://input'), FILE_APPEND);
                    file_put_contents('log/'.(date("d-m-Y", time())).'-1awssns.log',PHP_EOL .'-------------------------------------------', FILE_APPEND);
                    foreach($_SERVER as $key => $value){
                        file_put_contents('log/'.(date("d-m-Y", time())).'-1awssns.log',PHP_EOL .'$_SERVER["'.$key.'"] = '.$value, FILE_APPEND);
                    }
               // }
                    $this->load->model('policyinfo_model');
                             if((count((array)$this->policyinfo_model->get_user()))==1){
                                        $jnagdata=PHP_EOL .file_get_contents('php://input');
                                                  $asd=json_decode($jnagdata);
                                                  $query_from = "select * from ipl.collection_web_new where ID_NO='$asd->orderid'";
                                                  $q_from  = $this->db->query($query_from);
                                                  foreach ($q_from ->result() as $r_from){
                                                              $pol_num=$r_from->POLICY;
                                                              $orderID=$r_from->ID_NO;
                                                              $email_add=$r_from->USER_ID;
                                                              $amount=$r_from->NET_RECIV;
                                                              $recdate=$r_from->RCPT_DATE;
                                                  }
                                                  echo (int)$amount.'='.(int)$asd->nagad_txn_amount;
                                                  if ((date_format(date_create($recdate),"d-M-Y")==date_format(date_create($asd->nagad_txn_time),"d-M-Y"))&&($orderID==$asd->orderid)&&((int)$amount==(int)$asd->nagad_txn_amount)){
                                                     foreach($asd as $key=>$value){
                                                         $query= "insert into ipl.web_mar_success (POLICY,ORDER_ID,USER_ID,MAR_STAT,MAR_INFO,BCODE) VALUES ('$pol_num','$orderID','$email_add','$key','$value','167')";
                                                            $this->db->query($query);
                                                      }
                                                      $query_vpc_up = "update ipl.collection_web_new set CR_NO= 'MF'||lpad(IPL.MRNUMBER.NEXTVAL,8,0),STATUS='SUCCESS' where ID_NO ='$orderID'";
                                                      $this->db->query($query_vpc_up);
                                                  }
                             }
                             else{
                                 echo 'invalid user/pass';
                             }
             }
       }


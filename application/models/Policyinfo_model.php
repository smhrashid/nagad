<?php
class Policyinfo_model extends CI_Model {
    public function get_user() { 
    
        $marid=$_SERVER["HTTP_MARID"];
        $marpass=$_SERVER["HTTP_MARPASS"];
        if (isset($marid)&&isset($marpass)){
            $query_user = "select *  from ipl.users_bank  where USERNAME='$marid' and PASSWORD='$marpass'";
            $qu_find = $this->db->query($query_user); 
            foreach ($qu_find->result() as $r_user){
                $us[]= $r_user;
            }
            if (isset($us)){
                return $us;
            }
        }
        else {
            return 0;
        }
    }
    public function get_premcal() { 
        $policy_number=$_SERVER["HTTP_POLNUM"];
        if (isset($policy_number)){
            $policy= $policy_number;
                        
            $query_pol ="select POLICY,totprem ,premno,paymode, pnextpay,NAME,datcom, DOB,SUSPENSE,STATUS,matdate+1 mm, MONTHS_BETWEEN(SYSDATE,PNEXTPAY) ss,
                        IPL.LATE_FEE(POLICY,PRJ_CODE,PLAN,TERM,PAYMODE,TOTPREM,PNEXTPAY,
                        case when paymode='5' then floor(months_between(sysdate,pnextpay))
                             when paymode='1' then floor(floor(months_between(sysdate,pnextpay))/12)
                             when paymode='2' then floor(floor(months_between(sysdate,pnextpay))/6)
                             when paymode='4' then floor(floor(months_between(sysdate,pnextpay))/3)
                        end,SYSDATE,0) LATEFEE,
                        'plil'||lpad(VPC_MERCHTXNREF_SEQ.nextval+1,8,0) orderid,(plan||'-'||term) PLAN,SUMASS,PHONE,PRJ_CODE,(oc1 || '/' ||oc2|| '/' ||oc3|| '/' ||oc4|| '/' ||oc5||'/'||oc6||'/'||oc7||'/'||oc8||'/'||oc9||'/'||oc10)as org_setup,TOTPAID,DECODE(PRJ_CODE,'05','p','24','p','i') PRJ_TYPE
                        from  IPL.ALL_POLICY where policy= '$policy' OR policy= '$policy'";
            $q_pol = $this->db->query($query_pol);
            foreach ($q_pol->result() as $r_pol):
                $pol[]=$r_pol;
            endforeach;
    
            $polcount=0;
            if(isset($pol)){
                $polcount= count((array)$pol);
            }
            if ($polcount==1){
                $status= $pol[0]->STATUS;
                $mm= $pol[0]->MM;
                $pnextpay= $pol[0]->PNEXTPAY;
                $ss=$pol[0]->SS;
                if ($status == 'M') {
                    $p_st=0;	
                    $pol_stat="Policy Alrrady Matured";
                }
                elseif ($status == 'D') {
                    $p_st=0;
                    $pol_stat="Date claim Intimated";
                }	
                elseif ($status == 'S') {
                        $p_st=0;
                        $pol_stat="Policy alrrady Surrenderad";
                }
                elseif ($status == 'C') {
                    $p_st=0;
                    $pol_stat="Policy is Cancelled";
                }
                elseif ($pnextpay == $mm) {
                    $p_st=0;
                    $pol_stat="Alrrady Matured";
                }
                elseif ($ss>60) {
                    $p_st=0;
                    $pol_stat="Special Revival Requerd";
                }
                elseif ($ss>3 && $ss<60){
                    $p_st=1;
                    $pol_stat="DGH Requerd";
                }
                else {
                    $p_st=1;
                    $pol_stat="Policy Inforce";
                }
                if ($p_st==1){
                    
                  $policy= $pol[0]->POLICY;
	$datcom= $pol[0]->DATCOM;
	$pnextpay= $pol[0]->PNEXTPAY;
	$plan= $pol[0]->PLAN;
	$sumass= $pol[0]->SUMASS;
	$phone= $pol[0]->PHONE;
	$prj_code= $pol[0]->PRJ_CODE;
	$org_setup= $pol[0]->ORG_SETUP;
	$premno= $pol[0]->PREMNO;
	$totpaid= $pol[0]->TOTPAID;
	$status= $pol[0]->STATUS;
	$mm= $pol[0]->MM;
	$ss= $pol[0]->SS;
                    $orderid= $pol[0]->ORDERID;
                    $paymode= $pol[0]->PAYMODE;
                    $name=$pol[0]->NAME;
                    $latefee=$pol[0]->LATEFEE;
                    $totprem= $pol[0]->TOTPREM;
                    $suspense= $pol[0]->SUSPENSE;
                    if ($paymode == '1') { $term_month ='12'; }
                    elseif ($paymode == '2') { $term_month ='6';} 
                    elseif ($paymode == '4') { $term_month ='3'; }
                    elseif ($paymode == '5') { $term_month ='1';  }
                    $n_du=(ceil($ss/$term_month));
                    if ($n_du<=0) {$num_due=1;} 
                    else {$num_due=$n_du;}
                    $prem_amnt=$totprem*$num_due;
                    $net_reciv=$prem_amnt+$latefee-$suspense;
      
                    $m_add=($num_due)*$term_month;
                    $tot_payable=$net_reciv;
                    $up_pol_premno=$num_due+$premno;
                    $up_pol_totpayed=$totpaid+$net_reciv;
                    $query="INSERT INTO ipl.collection_web_new 
	(POLICY,NAME,DATCOM,PAYMODE,PNEXTPAY,PLAN,SUMASS,TOTPREM,PHONE,PRJ_CODE,ORG_SETUP,PREMNO,TOTPAID,UP_POL_PREMNO,UP_POL_TOTPAYED,UP_SUSPANSE,UP_PNEXTPAY,RCPT_DATE,NOS_PREMNO,PREM_AMNT,LATE_FEES,TOT_PAYABLE,DISCOUNT,SUSPANSE_AMT,NET_RECIV,ID_NO,USER_ID,PRJ_TYPE,BCODE,POL_STATUS)  
                   VALUES ('$policy',"
                            . "'$name',"
                            . "'$datcom',"
                            . "'$paymode',"
                            . "'$pnextpay',"
                            . "'$plan',"
                            . "'$sumass',"
                            . "'$totprem',"
                            . "'$phone',"
                            . "'$prj_code',"
                            . "'$org_setup',"
                            . "'$premno',"
                            . "'$totpaid',"
                            . "'$up_pol_premno',"
                            . "'$up_pol_totpayed',"
                            . "'0',"
                            . "(add_months ('$pnextpay','$m_add')),"
                            . "sysdate,"
                            . "'$num_due',"
                            . "'$prem_amnt',"
                            . "'$latefee',"
                            . "'$tot_payable',"
                            . "'0',"
                            . "'0',"
                            . "'$tot_payable',"
                            . "'$orderid',"
                            . "'111',"
                            . "'',"
                            . "'167',"
                            . "'$pol_stat')";
                    $this->db->query($query);
                    $jdata='{
                                "orderid":"'.$orderid.'",
                                "name":"'.$name.'",
                                "amount":"'.$net_reciv.'",
                                "status":"'.$pol_stat.'"
                                }';
                }
                else{
                    $jdata='{
                                "orderid":"0",
                                "name":"'.$name.'",
                                "amount":"0",
                                "status":"'.$pol_stat.'"
                                }';
                }
            }
            else {
                   $jdata='{
                                "orderid":"0",
                                "name":"xxx",
                                "amount":"0",
                                "status":"Invalid Policy"
                                }';
            }
        }
        else {
                   $jdata='{
                                "orderid":"0",
                                "name":"xxx",
                                "amount":"0",
                                "status":"Invalid Policy"
                                }';
        }
        return $jdata;
    }
}

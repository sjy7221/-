<?php

//三个人时找出下一个玩家mid
    function sweizhi($weizhi,$roomInfo){
        for($i=1;$i<3;$i++){

            if($weizhi+$i == 3) {
                $next = 0;//下一个人

            }elseif($weizhi+$i == 4){
                $next =1;//下下个人
            }else{
                $next = $weizhi+$i;
            }
        }
        $now = $roomInfo['weizhi'][$next];//取出下一个人的mid

        return $now;
    }


//返回数据格式
    function reData($route,$data){
    $dd = [
        'route'=>$route,
        'data'=>$data
    ];
    return $dd;
}
    //单
    function dan($pai)
    {

        if(count($pai) == 1){
         return ['type'=>1,'len'=>1,'zhu'=>$pai[0]] ;
        }else{
            return false;
        }
    }
    //顺子 传入数组【41，51,61】
    function shun($pai,$shoupai)
    {
        //排序
         sort($pai);
        $u = 1;
  
        //顺子5起步
        if(count($pai) >= 5){
            $numb =  zhuanhuan($pai);
          
                //做判断
               for($j=1;$j<count($numb);$j++){
                    if($numb[0]+$j == $numb[$j]){
                     $u +=1;
                   }

                
               }
            
         if($u == count($pai)){
           
           return ['type'=>2,'len'=>count($pai),'zhu'=>$pai[0]];
         }else{

          return sandai($pai,$shoupai);
         }

     
        }else{
             return false;
        }
    }
/// 判断打出牌在不在手牌里
    function panduan($pai,$shoupai)
    {
        $flag =1;
        foreach($pai as $va){
            if(in_array($va,$shoupai)){
                continue;
            }else{
                $flag = 0 ;
                break;
            }

        }
        if ($flag) {
            if(count($pai)== 1){

                $leix =   dan($pai);
            }elseif(count($pai) == 2){
                $leix =  duizi($pai);
            }elseif(count($pai) == 3){
                $leix =  hou3($pai,$shoupai);
            }elseif(count($pai)>=4){

                $leix =  liandui($pai,$shoupai);
            }
            return $leix;
        }else {
            return false;
        }
    }

    //发牌
    function fapai($gameInfo,$roomInfo,$userInfo)
    {
        D('fapai',$roomInfo['guize']['renshu']);
        $renshu = $roomInfo['guize']['renshu'];
        $roomid = $roomInfo['guize']['room_id'];
        $pai = [31,32,33,34,41,42,43,44,51,52,53,54,61,62,63,64,71,72,73,74,81,82,83,84,91,92,93,94,101,102,103,104,111,112,113,114,121,122,123,124,131,132,133,134,144,142,143,160];
        shuffle($pai);

        $numb = count($pai)/$renshu;
        $pais = [];


        for($i = 0;$i<$renshu;$i++){
            for($j=0;$j<$numb;$j++){
                $pais[$i][] =  array_pop($pai);
            }
            sort($pais[$i]);

        }

        $o = -1;
        $h3id = '0';
        $niaoid = '0';
        foreach ( $gameInfo['users'] as $k=>$v) {

            $o++;
            $gameInfo['users'][$k]['shoupai'] = $pais[$o];

        }

        //找出牌中黑桃三先出的mid 和鸟牌 mid
        foreach($gameInfo['users'] as $kk=>$vv){
            //黑桃三先出的mid
            if(!(array_search(31,$gameInfo['users'][$kk]['shoupai']) === false)){
                $h3id = $kk;
            }
            // 鸟牌 mid
            if(!(array_search(102,$gameInfo['users'][$kk]['shoupai']) === false)){
                $niaoid = $kk;
            }
        }

        if( $roomInfo['nowjushu'] == 1){
            $gameInfo['now'] = $h3id;
        }
        if(isset($roomInfo['guize']['suanfa'][1]) && $roomInfo['guize']['suanfa'][1]){
            $gameInfo['niaoid'] = $niaoid;
        }
        if(isset($roomInfo['guize']['suanfa'][2]) && $roomInfo['guize']['suanfa'][2]){
            $roomInfo['xianshi'] = 1;
        }
        return ['roomInfo'=>$roomInfo,'gameInfo'=>$gameInfo];

    }
    //对子
    function duizi($pai)
    {

       $numb =  zhuanhuan($pai);
       //单对，两位相等
      if($numb[0] == $numb[1]){
    
         return ['type'=>3,'len'=>2,'zhu'=>$pai[0]];
      }else{
         return false;
      }
    }
    //连对
    function liandui($pai,$shoupai)
    {
       
            $numb =  zhuanhuan($pai);
            //判断打出去的牌是不是炸弹
        if(count($numb) == 4 && $numb[0] == $numb[1] && $numb[1] == $numb[2] && $numb[2] == $numb[3]){
            return  zhadan($numb);
        }else{
             $cishu = (array_count_values($numb));
      
    

     $numb =  array_unique($numb);
  
         $a = 0;
         $b = 0;
         foreach($numb as $v){
              
           if ($cishu[$v] === 2){
            $a+=1;
           }elseif($cishu[$v] === 3){
            $b+=1;
           }
         }

            sort($numb);
        if($a == count($numb)){
             
                    $u = 1;
               for($j=1;$j<count($numb);$j++){
                
                    if($numb[0]+$j == $numb[$j]){
                     $u +=1;
                   }

                
               }

               if($u == count($numb)){
                // var_dump($numb);
          return ['type'=>4,'len'=>count($pai),'zhu'=>$pai[0]];
         }else{
            return false;
         }

        }elseif($b == 1){
           return sandai($pai,$shoupai);
       
        }elseif($b == 2){
              return  feiji($pai,$shoupai);
        }else{

            return  shun($pai,$shoupai);
        }
        }
     

    
     
       
    }
    //三带二/
    function sandai($pai,$shoupai)
    {
       
        if(count($pai) == 5){
        $numb =  zhuanhuan($pai);

      $cishu = (array_count_values($numb));
   
      
       $numb =   array_unique($numb);
        $b = 0;
        
        //做相同数计数
            $zhu = '';
         foreach($cishu as $k => $v){

            if($v == 3){
            $b+=1;
            $zhu = $k;
           }
         }
      
       if($b == 1){
       
         return ['type'=>5,'len'=>count($pai),'zhu'=>$zhu];

       }else{
         return false;
       }
        }elseif(count($pai) == 4){
          return  sand1($pai,$shoupai);
        }else{
             return false;
        }
       
     
    }
    //三带一
    function sand1($pai,$shoupai)
    {
        //自己手牌
        
        if(count($shoupai) == 4){
                $numb =  zhuanhuan($pai);
       //array_count_values 对数组中的所有值进行计数：
        $cishu = (array_count_values($numb));
       $numb =   array_unique($numb);
         $b = 0;
        
        //做相同数计数
         foreach($numb as $v){

            if($cishu[$v] === 3){
            $b+=1;
           }
         }
      
       if($b == 1){
       
        return ['type'=>6,'len'=>count($pai)];

       }else{
         return false;
       }
      
        }else{
            return false;
        }
        
    }
    //最后三张
    function hou3($pai,$shoupai)
    {
        //自己手牌
//        $shoupai = [101,111,121];
        if(count($shoupai) == 3 && $shoupai==$pai){
               $numb =  zhuanhuan($pai);
       //array_count_values 对数组中的所有值进行计数：
       $cishu = (array_count_values($numb));
       $numb =   array_unique($numb);
          $b = 0;
          
        //做相同数计数
         foreach($numb as $v){

            if($cishu[$v] === 3){
            $b+=1;
           }
         }
      
       if($b == 1){
        // var_dump($numb);
        return ['type'=>7,'len'=>count($pai)];

       }else{
       return false;
       }
       // var_dump($numb);
        }else{
            return false;
        }
    }

    //飞机传 数组为 【2个连三张在前,】
    function feiji($pai,$shoupai)
    {
        sort($pai);

         //自己手牌
      
        if(count($shoupai) >= count($pai) && count($pai) == 10 ){
            //如果手牌大于或者等于打出的牌，那么打出的牌必须为10张
         
               $numb =  zhuanhuan($pai);
             //去掉花色
           
            //相同数字做计数
           $cishu = (array_count_values($numb));
            $numb =   array_unique($numb);
            $b = 0;
           $ob = [];
            $zhu = [];
        //做相同数计数
         foreach($numb as $v){

            if($cishu[$v] === 3){
            $b+=1;
                $zhu[] = $v;
                //将相同的三位数的键存入数组
            array_push($ob,$v);
           }
         }
     
    
            //判断是否为333444连续
         if($b == 2 && $ob[0]+1 == $ob[1]){
        $zhu = $zhu[0];
         return ['type'=>8,'len'=>count($pai),'zhu'=>$zhu];

       }else{

        return false;
       }
       
        
    }elseif(count($shoupai)<10 && $shoupai === $pai){
            //去掉花色
                  $numb =  zhuanhuan($pai);
            //相同数字做计数
         $cishu = (array_count_values($numb));
            $numb =   array_unique($numb);
       $b = 0;
          
        //做相同数计数
         foreach($numb as $v){

            if($cishu[$v] === 3){
            $b+=1;
           }
         }
      
         if($b == 2){
        
         return ['type'=>9,'len'=>count($pai)];

       }else{

       return false;
       }
    }else{
       return false;
    }
    }

    //炸弹
    function zhadan($numb)
    {
         return ['type'=>10,'len'=>count($numb),'pai'=>$numb[0]];
    }

    //私有转换牌去掉花色
     function zhuanhuan($pai)
    {
             $numb = [];
             //去掉花色
             foreach($pai as $v){
             $a = substr($v,0,strlen($v)-1);
                array_push($numb,$a);
            }
            return $numb;
    }
    //传手牌 //和打出的牌 //类型
    function shoupai($pai, $dachu, $leix) {
        sort($pai);
        sort($dachu);
        $numb =  zhuanhuan($pai); //
        if (count($pai) < count($dachu)) {
            return false;
        }
        switch ($leix['type']) {
            case 1://单张
                return  type1($pai, $dachu, $numb);
            break;
            case 2: //顺子
                return  type2($pai, $dachu, $numb, $leix);
            break;
            case 3: //对子
                return  type3($pai, $dachu,$numb);
            break;
            case 4: //连对
                return  type4($pai, $dachu, $numb);
            break;
            case 5://三带二
                return  type5($pai, $dachu, $numb);
            break;
            case 8://飞机
                return  type8($pai, $dachu, $numb);
            break;
            case 10://炸弹
                return  type10($pai, $dachu, $numb);
            break;
        }
    }
    function type1($pai, $dachu, $numb) {
            D('type1.单张 tishi:',1);
        for ($i = 0;$i < count($pai);$i++) {

            for ($j = 0;$j < count($dachu);$j++) {
                if ($pai[$i] > $dachu[$j] + 10) {
                    return [$pai[$i]];
                }
            }
        }

        return  zha($numb, $pai);
    }
    function type2($pai, $dachu, $numb, $leix) {
        $u = 1;
        $tishi = []; //存牌
        $p = [];
        $snumb =  zhuanhuan($pai);
        $dnumb =  zhuanhuan($dachu);
        sort($dnumb);
        sort($snumb);
        for ($j = 1;$j < count($snumb);$j++) {
            if ($snumb[$j] > $dnumb[0]) {
                if ($snumb[$j] - 1 == $snumb[$j - 1]) {
                    $u+= 1;
                    $tishi[$j] = $pai[$j];
                    $p[] = $snumb[$j];
                    // echo $snumb[$j]; //把组成顺子的值存入数组不改变下标
                    
                }
            }
        }
        $kk = array_search($p[0] - 1, $snumb);
        $tishi[$kk] = $pai[$kk];
        sort($tishi);
        if ($u >= $leix['len']) {
            $tishi = array_slice($tishi, 0, count($dachu));
            D('type2.顺子 tishi:',$tishi);
            return $tishi; // 返回可以打出的数据
            
        } else {
            return  zha($numb, $pai);
        }
    }
    function type3($pai, $dachu,$numb) {
        sort($pai);
        $snumb =  zhuanhuan($pai);
        $dnumb =  zhuanhuan($dachu);
        sort($dnumb);
        sort($snumb);
        // var_dump($snumb);
        $tishi = []; //存牌
        $p = [];
        foreach ($snumb as $k => $v) {
            if ($v > $dnumb[0]) {
                if ($k == 0) {
                    $i = 0;
                } else {
                    if ($snumb[$k - 1] == $v) {
                        $tishi[$k] = $pai[$k]; //存牌
                        
                    }
                }
            }
        }
        foreach ($tishi as $kk => $vv) {
            $p[] = $pai[$kk - 1];
        }
        $tishi = array_merge($p, $tishi);
        $tishi = array_unique($tishi);
        sort($tishi);
        if (empty($tishi)) {
            return  zha($numb, $pai);
        } else {
            $tishi = array_slice($tishi, 0, count($dachu));
            D('type3.对子 tishi:',$tishi);
            return $tishi;
        }
    }
    function type4($pai, $dachu, $numb) {
        $snumb =  zhuanhuan($pai);
        $dnumb =  zhuanhuan($dachu);
        $cishu = (array_count_values($numb));
        $numb = array_unique($snumb);
        $n = array_unique($dnumb);
        $a = 0;
        $tishi = [];
        foreach ($numb as $k => $v) {
            if ($v > $dnumb[0]) {
                if ($cishu[$v] >= 2 && $cishu[$v] < 4) {
                    $a+= 1;
                    $tishi[] = $v;
                }
            }
        }
        $tis = [];
        $ti = [];
        if ($a >= count($n)) {
            for ($j = 1;$j < count($tishi);$j++) {
                if ($tishi[0] + $j == $tishi[$j]) {
                    $tis[$j] = $tishi[$j];
                    $tis[0] = $tishi[0];
                    $key = array_search($tishi[$j], $snumb);
                    $ks = array_search($tishi[0], $snumb);
                    $tis[$j] = $pai[$key - 1];
                    $tis[0] = $pai[$ks];
                    $ti[] = $pai[$key + 1];
                    $t[] = $pai[$key];
                }
            }
            $tishi = array_merge($tis, $ti, $t);
            $tishi = array_unique($tishi);
            sort($tishi);
            $tis =  zhuanhuan($tishi);
            // var_dump($tishi);
            $tis = array_count_values($tis);
            $jian = [];
            foreach ($tis as $key => $value) {
                if ($value == 3) {
                    $jian[] = $key;
                }
            }
            $m = '';
            foreach ($jian as $key => $value) {
                $m = array_search($value . '1', $tishi);
                if ($m) {
                    unset($tishi[$m]);
                }
            }
            if (count($tishi) >= count($dachu)) {
                $tishi = array_slice($tishi, 0, count($dachu));
                D('type4.连对 tishi:',$tishi);
                return $tishi;
            } else {
                return false;
            }
        } else {
            $numb =  zhuanhuan($pai);
            return  zha($numb, $pai);
        }
    }

    function type5($pai, $dachu, $numb)
    {
        $cishu = array_count_values($numb);
        $dnumb =  zhuanhuan($dachu);
         $dcishu = array_count_values($dnumb);
        $k = [];
        //找出手牌中3个相同的牌
       foreach ($cishu as $key => $value) {
            if($value == 3  ){
                $k[] = $key;
            }
       }
       $dk = '';
       $ob = '';
       if(empty($k) || !$k){
           return  zha($numb, $pai);
       }else{
           //找出打出牌中3个相同的牌
           foreach($dcishu as $kv => $v)
           {
               if($v == 3){
                   $dk = $kv;
               }
           }
            $ob = '';
           foreach ($k as $o => $vv) {

               if($vv > $dk){
                   $ob = $k[$o];
               }

           }
                if($ob){
                    $tishi = [];

                    foreach($numb as $n =>$b){
                        if($ob == $b){
                            $tishi[] = $pai[$n];
                        }
                    }

                    $ti =  array_diff($pai,$tishi);
                    $arr = array($ti[0],$ti[1]);
                    var_dump($arr);
                    $tishi =  array_merge($arr,$tishi);
                    D('type5.3dai2 tishi:',$tishi);
                    return $tishi;
                }else{
               return false;
                }

       }

    }
    function type8($pai, $dachu, $numb)
    {
        $cishu = (array_count_values($numb));
        $dnumb =  zhuanhuan($dachu);
        $dcishu = array_count_values($dnumb);
        //找出手牌中3个相同的牌
        $k = [];
        foreach ($cishu as $key => $value) {

            if($value == 3){
                $k[] = $key;

            }
        }
        if(empty($k) || !$k){
            return  zha($numb, $pai);
        }else{
            //找出打出牌中3个相同的牌
            $dk = '';
             $dd =[];

            foreach($dcishu as $kv => $v)
            {
                if($v == 3){
                    $dk = $kv;

                    $dd[] = $kv;
                }
            }
            $tishi = [];


              foreach ($k as $a => $b)
              {
                  //找出相邻的两个数
                  if(in_array($b+1,$k)){
                      $tishi[] = $b;
                      $tishi[] = $b+1;
                  }

              }
            echo '<br>';

            $tishi = array_slice($tishi, 0, count($dd));
            // 拼花色;
            $p1 = $tishi[0];
            $p2 = $tishi[1];
            $ttshi = [];
            for($i=1;$i<5;$i++){
                if(in_array($p1.$i,$pai) && in_array($p2.$i,$pai)){
                    $ttshi[] = $p1.$i;
                    $ttshi[] = $p2.$i;
                }
            }
            $ts = array_diff($pai,$ttshi);
            $ts = array_slice($pai, 0, 4);
           $tishi =  array_merge($ts,$ttshi);
           sort($tishi);
            D('type8.飞机 tishi:',$tishi);
           return $tishi;


        }
    }
    //有炸弹 返回炸弹
    //
    function type10($pai, $dachu, $numb)
    {
        $cishu = array_count_values($numb);
        $dnumb =  zhuanhuan($dachu);
        $dnumb = array_count_values($dnumb);
        $zd = '';
        $dc =  array_search(4,$dnumb);
        foreach ($cishu as $k=>$v){
            if($v == 4 && $k > $dc){
                $zd = $k;
            }
        }
        if(empty($zd) || !$zd){
           return false;
        }else{
            $tishi = [] ;

          //拼花色
            for($i=1;$i<5;$i++){
                if(in_array($zd.$i,$pai)){
                    $tishi[] = $zd.$i;

                }
            }
            D('type10.炸弹 tishi:',$tishi);
            return $tishi;

        }
    }
    function zha($numb, $pai) {
        $cishu = (array_count_values($numb));
        $k = array_search(4, $cishu);
        if ($k) {
            $k = array_search($k, $numb);
            D('typezha.炸弹 tishi:',2);
            return array($pai[$k], $pai[$k + 1], $pai[$k + 2], $pai[$k + 3]); //返回可以打出的豹子；
            
        } else {
            return false;
        }
    }




<?php
   
    //单
    function dan($pai)
    {
        if(count($pai) == 1){
         return ['type'=>1,'len'=>1] ; 
        }else{
            return false;
        }
    }
    //顺子 传入数组【41，51,61】
    function shun($pai)
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
           
           return ['type'=>2,'len'=>count($pai)];
         }else{

          return sandai($pai);
         }

     
        }else{
             return false;
        }
    }
    //对子
    function duizi($pai)
    {

            $numb =  zhuanhuan($pai);
       //单对，两位相等
      if($numb[0] == $numb[1]){
    
         return ['type'=>3,'len'=>2]; 
      }else{
         return false;
      }
    }
    //连对
    function liandui($pai)
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
          return ['type'=>4,'len'=>count($pai)];
         }else{
            return false;
         }

        }elseif($b == 1){
           return sandai($pai);
       
        }elseif($b == 2){
              return  feiji($pai);
        }else{

            return  shun($pai);
        }
        }
     

    
     
       
    }
    //三带二/
    function sandai($pai)
    {
       
        if(count($pai) == 5){
        $numb =  zhuanhuan($pai);

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
       
         return ['type'=>5,'len'=>count($pai)];

       }else{
         return false;
       }
        }elseif(count($pai) == 4){
          return  sand1($pai);
        }else{
             return false;
        }
       
     
    }
    //三带一
    function sand1($pai)
    {
        //自己手牌
        $arr = [31,32,33,150];
        if(count($arr) == 4){
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
    function hou3($pai)
    {
        //自己手牌
        $arr = [101,111,121];
        if(count($arr) == 3 && $arr==$pai){
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
    function feiji($pai)
    {
        sort($pai);

         //自己手牌
        $arr = [61,62,63,51,52,53,41,31,25,92];
        if(count($arr) >= count($pai) && count($pai) == 10 ){
            //如果手牌大于或者等于打出的牌，那么打出的牌必须为10张
         
               $numb =  zhuanhuan($pai);
             //去掉花色
           
            //相同数字做计数
           $cishu = (array_count_values($numb));
            $numb =   array_unique($numb);
            $b = 0;
           $ob = [];
           
        //做相同数计数
         foreach($numb as $v){

            if($cishu[$v] === 3){
            $b+=1;
            //将相同的三位数的键存入数组
            array_push($ob,$v);
           }
         }
     
    
            //判断是否为333444连续
         if($b == 2 && $ob[0]+1 == $ob[1]){
     
         return ['type'=>8,'len'=>count($pai)];

       }else{

        return false;
       }
       
        
    }elseif(count($arr)<10 && $arr === $pai){
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
        
         return ['type'=>8,'len'=>count($pai)];

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
         return ['type'=>10,'len'=>count($numb)];
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





// if(count($pai)== 1){
//     $game->dan($pai);
// }elseif(count($pai) == 2){
//     $game->duizi($pai);
// }elseif(count($pai) == 3){
//     $game->hou3($pai);
// }elseif(count($pai)>=4){
   
//          $game->liandui($pai); 



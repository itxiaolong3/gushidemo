<?php
/**
 * 童话故事模块小程序接口定义
 *
 * @author panshikj
 * @url http://www.zunyangkj.com
 */
defined('IN_IA') or exit('Access Denied');
header("Access-Control-Allow-Origin: *");

class TonghuagushiModuleWxapp extends WeModuleWxapp
{

    //接口测试
    public function doPageTest()
    {
        $file = ATTACHMENT_ROOT . 'tonghuagushi/' . 'token.txt';
        $jsonStr = file_get_contents($file);
        if ($jsonStr) {
            $reArr = json_decode($jsonStr, true);
            if ($reArr['maxTime'] > time()) {
                //大于当前时间，还未过期，直接返回
                return $reArr['access_token'];
            }
        }
        //不存在或者过期了，重新获取
        $getsiteInfo = $this->getSiteinfo();//获取配置信息
        $appid = $getsiteInfo['appid'];
        $secret = $getsiteInfo['appsecret'];
        $reArr = $this->getAccessToken($appid, $secret);
        $reArr['maxTime'] = time() + $reArr['expires_in'];
        file_put_contents($file, json_encode($reArr));
        return $reArr['access_token'];
    }
    //返回骗审标志
    public function doPageGetpian()
    {
        global $_W, $_GPC;
        $table = "story_shen";
        $arr = array();
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid";
        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $result = pdo_fetch($sql, $params);
        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '返回审核数据成功';
            $arr['data'] = $result;
            return json_encode($arr);
            exit();
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '审核数据为空';
            $arr['data'] = array();
            return json_encode($arr);
            exit();
        }
    }
    public function doPageGettoken(){
        $token=$this->getNewaccessToken();
        return $token;
    }

    //获取配置信息
    public function getSiteinfo()
    {
        global $_W;
        $table = "story_config";
        $data = array();
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `code`=:code ";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':code' => "site"
        );
        $result = pdo_fetch($sql, $params);
        if (!empty($result)) {
            $data = iunserializer($result['value']);
        }
        //echo $data['appid'];
        return $data;

    }

    //检查用户信息是否完整和是否登录
    public function doPageChecklogin()
    {
        global $_W, $_GPC;
        $table = "story_user";
        $js_code = $_GPC['code'];
        $getsiteInfo = $this->getSiteinfo();
        $appid = $getsiteInfo['appid'];
        $secret = $getsiteInfo['appsecret'];
        //exit('appid=='.$appid.'secret=='.$secret);
        $openid = $this->getOpendId($appid, $secret, $js_code);
        $data = array();
        if (!empty($openid)) {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `openId`=:openId order by u_addtime desc";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':openId' => $openid
            );
            $result = pdo_fetch($sql, $params);

            if (empty($result)) {
                $data['result'] = array();
                $data["status"] = -1;
                $data["msg"] = '没登录';
            } else {
                $errno = 1;
                $data["status"] = 1;
                $data['result'] = $result;
            }

        } else {
            $data['status'] = -1;
            $data['msg'] = 'doPageCheckinfo中无法获取openid';
        }
        echo json_encode($data);

    }

    //获取用户信息
    public function doPageGetuserinfo()
    {
        global $_W, $_GPC;
        $table = "story_user";
        $arr = array();
        $getuid = $_GPC['u_id'];
        if (empty($getuid)) {
            $arr['status'] = -1;
            $arr['message'] = "请传入用户u_id";
            $arr['result'] = array();
            echo json_encode($arr);
        }
        $select_sql = "select* from " . tablename($table) . " where `uniacid`=:uniacid and `u_id`=:u_id";
        //$getwechat_sql = "select wechat from " . tablename($table) . " where `uniacid`=:uniacid and `openId`=:openid";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':u_id' => $getuid
        );
        $result = pdo_fetch($select_sql, $params);
        if ($result) {
            $arr['status'] = 1;
            $arr['message'] = "获取用户信息成功";
            $arr['result'] = $result;
        } else {
            $arr['status'] = -1;
            $arr['message'] = "保存用户信息失败";
            $arr['result'] = array();
        }
        echo json_encode($arr);
    }
    //获取首页分享时的封面图
    public function doPageGetshareimg()
    {
        global $_W, $_GPC;
        $table = "story_config";
        $code = "site";
        $sql = "SELECT * FROM " . tablename($table) . " WHERE uniacid = :uniacid AND code = :code";
        $params = array(':uniacid' => $_W['uniacid'], ':code' => $code);
        $setting = pdo_fetch($sql, $params);
        $item = iunserializer($setting['value']);
        $item['img']=tomedia($item['img']);
      $item['title']=tomedia($item['sharetitle']);
        if ($setting) {
            $arr['status'] = 1;
            $arr['message'] = "获取分享封面的图片成功";
            $arr['result'] = $item;
        } else {
            $arr['status'] = -1;
            $arr['message'] = "获取分享封面的图片失败";
            $arr['result'] = array();
        }
        echo json_encode($arr);
    }
    //保存用户信息
    public function doPagePostuser()
    {
        global $_W, $_GPC;
        $table = "story_user";
        $data = array();
        $data['uniacid'] = $_W['uniacid'];
        $data['nickname'] = $_GPC['nickname'];
        $data['headerimg'] = $_GPC['headerimg'];
        $data['u_addtime'] = time();

        //获取opendid的参数
        $js_code = $_GPC['code'];
        $getsiteInfo = $this->getSiteinfo();
        $appid = $getsiteInfo['appid'];
        $secret = $getsiteInfo['appsecret'];
        $openid = $this->getOpendId($appid, $secret, $js_code);
        //print_r($openid);
        $select_sql = "select* from " . tablename($table) . " where `uniacid`=:uniacid and `openId`=:openid";
        //$getwechat_sql = "select wechat from " . tablename($table) . " where `uniacid`=:uniacid and `openId`=:openid";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':openid' => $openid
        );
        $result = pdo_fetch($select_sql, $params);
        //$getwechatresult = pdo_fetch($getwechat_sql, $params);
        // print_r($getvipresult);
        // $arr['wechat'] = $getwechatresult;
        if ($result) {
            //如果存在就返回前端说已经登录过，否则返回没登录状态
            $arr['status'] = 2;
            $arr['message'] = "已经登录过了";
            $arr['result'] = $result;
        } else {
            $data['openId'] = $openid;

            $arr = array();
            $res = pdo_insert($table, $data);
            //得到新增记录的id
            $getnewid = pdo_insertid();
            $arr['getid'] = $getnewid;
            if ($res) {
                $arr['status'] = 1;
                $arr['message'] = "保存用户信息成功";
                $arr['message'] = 'appenid是' . $openid;
            } else {
                $arr['status'] = -1;
                $arr['message'] = "保存用户信息失败";
            }
        }
        echo json_encode($arr);

    }

    /*更新用户资料信息*/
    public function doPageUpdateuser()
    {
        global $_W, $_GPC;
        $tablename = "story_user";
        $getbsday = $_GPC['bsday'];
        $getwechat = $_GPC['wechat'];
        $getschool = $_GPC['school'];
        $getnickname = $_GPC['nickname'];
        $getheaderimg = $_GPC['headerimg'];
        $getsex = $_GPC['sex'];
        $uid = $_GPC['u_id'];
        $getishow = $_GPC['ishow'];
        $data = array();
        $user_data = array(
            'bsday' => $getbsday,
            'wechat' => $getwechat,
            'school' => $getschool,
            'nickname' => $getnickname,
            'headerimg' => $getheaderimg,
            'sex' => $getsex,
            'ishow' => $getishow
        );
        $result = pdo_update($tablename, $user_data, array('uniacid' => $_W['uniacid'], 'u_id' => $uid));
        if (!empty($result)) {
            $data['result'] = $result;
            $data['status'] = 1;
            $data['msg'] = '更新资料成功';
        } else {
            $data['status'] = 0;
            $data['msg'] = "数据没更新";
            $data['result'] = $result;
        }

        echo json_encode($data);
    }
    //设置私密公开
    public function doPageDoisprivate()
    {
        global $_W, $_GPC;
        $table = "story_gushi";
        $table2 = "story_gushiread";
        $g_id = $_GPC['id'];
        $isread = $_GPC['isread'];
        $status = $_GPC['isstatus'];
        $data = array();
        $data['isprivate'] = $status;
        $data['g_addtime'] = time();

        $arr = array();
        //更新私密
        if ($isread){
            $result = pdo_update($table2, array('isprivate'=>$status,'r_addtime'=>time()), array('uniacid' => $_W['uniacid'], 'r_id' => $g_id));
        }else{
            $result = pdo_update($table, $data, array('uniacid' => $_W['uniacid'], 'id' => $g_id));
        }

        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '更新成功';
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '更新失败' . $result;
        }

        echo json_encode($arr);

    }
    //返回广告内容
    public function doPagegetad()
    {
        global $_W, $_GPC;
        $table = "story_adinfo";
        $arr=array();
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `aid`=:id ";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':id' =>$_GPC['id']
        );
        $result = pdo_fetch($sql, $params);
        if (!empty($result)) {
            $arr['data']=$result;
            $arr['status']=1;
            $arr['msg']='get info success';
        }else{
            $arr['data']=array();
            $arr['status']=0;
            $arr['msg']='get info fail';
        }
        //echo $data['appid'];
        echo json_encode($arr);

    }
    //返回轮播图
    public function doPageGetbanner()
    {
        global $_W, $_GPC;
        $table = "story_banner";
        $arr = array();
        $getpositon=$_GPC['position'];
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `position`=".$getpositon." order by addtime asc";

        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $result = pdo_fetchall($sql, $params);
        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '获取数据成功';
            if (is_array($result)) {
                foreach ($result as $k => $v) {
                    $result[$k]['img'] = tomedia($v['img']);
                }
                $arr["data"] = $result;
            } else {
                $result['img'] = tomedia($result['img']);
                $arr["data"] = $result;
            }
            return json_encode($arr);
            exit();
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '数据为空';
            $arr['data'] = array();
            return json_encode($arr);
            exit();
        }
    }
    //返回专辑封面和id
    public function doPageGetactivit()
    {
        global $_W, $_GPC;
        $table = "story_zhuanji";
        $arr = array();
        $sql = "select zj_id,zj_img,uniacid from " . tablename($table) . " where `uniacid`=:uniacid";
        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $result = pdo_fetchall($sql, $params);
        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '获取数据成功';
            if (is_array($result)) {
                foreach ($result as $k => $v) {
                    $result[$k]['zj_img'] = tomedia($v['zj_img']);
                }
                $arr["data"] = $result;
            } else {
                $result['zj_img'] = tomedia($result['zj_img']);
                $arr["data"] = $result;
            }
            return json_encode($arr);
            exit();
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '数据为空';
            $arr['data'] = array();
            return json_encode($arr);
            exit();
        }
    }
    //返回专辑内容
    public function doPageGetactivitdetail()
    {
        global $_W, $_GPC;
        $table = "story_zhuanji";
        $table1 = "story_gushi";
        $table2 = "story_user";
        $table3 = "story_gushiread";
        $pictext = "story_pictext";
        $gettype=$_GPC['ordertype'];
        $arr=array();

        switch ($gettype)
        {
            case 0:
                //获取专辑故事
                $zjsql = "select gs.*,u.* from " . tablename($table1).'gs,'.tablename($table2) . "  u where gs.uniacid=:uniacid and gs.zjid=:id and gs.status=1 and gs.b_id=u.u_id ORDER BY (gs.goodnum+gs.listennum) desc";
                $zjreadsql = "select gsr.*,u.* ,pt.* from " . tablename($table3).'gsr, '. tablename($pictext).'pt, '.tablename($table2) . "  u where gsr.uniacid=:uniacid and gsr.gsid=pt.p_id  and gsr.g_zjid=:id and gsr.status=1 and gsr.b_id=u.u_id ORDER BY (gsr.r_goodnum+gsr.r_listennum) desc";
                break;
            case 1:
                //A-Z，暂时没有
                $zjsql = "select gs.*,u.* from " . tablename($table1).'gs,'.tablename($table2) . "  u where gs.uniacid=:uniacid and gs.zjid=:id and gs.status=1 and gs.b_id=u.u_id";
                $zjreadsql = "select gsr.*,u.* ,pt.* from " . tablename($table3).'gsr, '. tablename($pictext).'pt, '.tablename($table2) . "  u where gsr.uniacid=:uniacid and gsr.gsid=pt.p_id  and gsr.g_zjid=:id and gsr.status=1 and gsr.b_id=u.u_id";
                break;
            case 2:
                //收听数
                $zjsql = "select gs.*,u.* from " . tablename($table1).'gs,'.tablename($table2) . "  u where gs.uniacid=:uniacid and gs.zjid=:id and gs.status=1 and gs.b_id=u.u_id ORDER BY gs.listennum desc";
                $zjreadsql = "select gsr.*,u.* ,pt.* from " . tablename($table3).'gsr, '. tablename($pictext).'pt, '.tablename($table2) . "  u where gsr.uniacid=:uniacid and gsr.gsid=pt.p_id  and gsr.g_zjid=:id and gsr.status=1 and gsr.b_id=u.u_id ORDER BY gsr.r_listennum desc";
                break;
            case 3:
                //点赞数
                $zjsql = "select gs.*,u.* from " . tablename($table1).'gs,'.tablename($table2) . "  u where gs.uniacid=:uniacid and gs.zjid=:id and gs.status=1 and gs.b_id=u.u_id ORDER BY gs.goodnum desc";
                $zjreadsql = "select gsr.*,u.* ,pt.* from " . tablename($table3).'gsr, '. tablename($pictext).'pt, '.tablename($table2) . "  u where gsr.uniacid=:uniacid and gsr.gsid=pt.p_id  and gsr.g_zjid=:id and gsr.status=1 and gsr.b_id=u.u_id ORDER BY gsr.r_goodnum desc";
                break;
            case 4:
                //创作时间
                $zjsql = "select gs.*,u.* from " . tablename($table1).'gs,'.tablename($table2) . "  u where gs.uniacid=:uniacid and gs.zjid=:id and gs.status=1 and gs.b_id=u.u_id ORDER BY gs.g_addtime desc";
                $zjreadsql = "select gsr.*,u.* ,pt.* from " . tablename($table3).'gsr, '. tablename($pictext).'pt, '.tablename($table2) . "  u where gsr.uniacid=:uniacid and gsr.gsid=pt.p_id  and gsr.g_zjid=:id and gsr.status=1 and gsr.b_id=u.u_id ORDER BY gsr.r_addtime desc";
                break;
            default:
                $zjsql = "select gs.*,u.* from " . tablename($table1).'gs,'.tablename($table2) . "  u where gs.uniacid=:uniacid and gs.zjid=:id and gs.status=1 and gs.b_id=u.u_id";
                $zjreadsql = "select gsr.*,u.* ,pt.* from " . tablename($table3).'gsr, '. tablename($pictext).'pt, '.tablename($table2) . "  u where gsr.uniacid=:uniacid and gsr.gsid=pt.p_id  and gsr.g_zjid=:id and gsr.status=1 and gsr.b_id=u.u_id";

            //随意;
        }
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `zj_id`=:id ";
        //获取专辑故事
        // $zjsql = "select gs.*,u.* from " . tablename($table1).'gs,'.tablename($table2) . "  u where gs.uniacid=:uniacid and gs.zjid=:id and gs.status=1 and gs.b_id=u.u_id";
        //获取图文专辑故事
        //$zjreadsql = "select gsr.*,u.* ,pt.* from " . tablename($table3).'gsr, '. tablename($pictext).'pt, '.tablename($table2) . "  u where gsr.uniacid=:uniacid and gsr.gsid=pt.p_id  and gsr.g_zjid=:id and gsr.status=1 and gsr.b_id=u.u_id";

        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':id' =>$_GPC['id']
        );
        $result = pdo_fetch($sql, $params);
        $allgushi = pdo_fetchall($zjsql, $params);
        foreach ($allgushi as $k=>$v){
            $allgushi[$k]['storyimg']=tomedia($v['storyimg']);
            $allgushi[$k]['g_addtime']=date('Y-m-d',$v['g_addtime']);

        }
      
        $allgushiread = pdo_fetchall($zjreadsql, $params);
        foreach ($allgushiread as $k=>$v){
            $allgushiread[$k]['p_toppic']=tomedia($v['p_toppic']);
            $allgushiread[$k]['r_addtime']=date('Y-m-d',$v['r_addtime']);
        }
        if($gettype==1){
            $allgushi=$this->chartSort($allgushi,'title');
          $allgushiread=$this->chartSort($allgushiread,'p_title');
        }
        $result['zj_img']=tomedia($result['zj_img']);

        if (!empty($result)) {
            $arr['data']=$result;
            $arr['status']=1;
            $arr['sql']=$zjreadsql;
            $arr['msg']='get info success'.$gettype;
            $arr['zjgushi']=$allgushi;
            $arr['zjgushiread']=$allgushiread;
            $arr['newarr']=$getnewarr;
          	$arr['gsrresult']=$allgushiread;
        }else{
            $arr['data']=array();
            $arr['status']=0;
            $arr['msg']='get info fail';
            $arr['zjgushi']=array();
            $arr['zjgushiread']=array();
        }
        //echo $data['appid'];
        echo json_encode($arr);

    }
    //返回背景音乐
    public function doPageGetbjmusic()
    {
        global $_W, $_GPC;
        $table = "story_bjmusic";
        $arr = array();
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid order by bj_addtime asc";

        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $result = pdo_fetchall($sql, $params);
        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '获取数据成功';
            if (is_array($result)) {
                foreach ($result as $k => $v) {
                    $result[$k]['url'] = tomedia($v['url']);
                }
                $arr["data"] = $result;
            } else {
                $result['url'] = tomedia($result['url']);
                $arr["data"] = $result;
            }
            return json_encode($arr);
            exit();
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '数据为空';
            $arr['data'] = array();
            return json_encode($arr);
            exit();
        }
    }

    //返回在线图库
    public function doPageGettuku()
    {
        global $_W, $_GPC;
        $table = "story_tuku";
        $arr = array();
        $key = $_GPC['keyword'];
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid AND `type` LIKE '%{$key}%' order by t_addtime asc";

        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $result = pdo_fetchall($sql, $params);
        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '获取数据成功';
            if (is_array($result)) {
                foreach ($result as $k => $v) {
                    $result[$k]['imgurl'] = tomedia($v['imgurl']);
                }
                $arr["data"] = $result;
            } else {
                $result['imgurl'] = tomedia($result['imgurl']);
                $arr["data"] = $result;
            }
            return json_encode($arr);
            exit();
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '数据为空';
            $arr['data'] = array();
            return json_encode($arr);
            exit();
        }
    }

    //返回在线图库分类列表
    public function doPageGetTukufenlei()
    {
        global $_W, $_GPC;
        $table = "story_tukufenlei";
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid  order by fl_addtime asc";
        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $result = pdo_fetchall($sql, $params);
        $arr = array();
        if ($result) {
            ///$this->resultinfo(1,'获取数据成功',$result);
            $arr['status'] = 1;
            $arr['msg'] = '服务返回数据成功';
            $arr['data'] = $result;
            return json_encode($arr);
            exit();
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '服务返回数据失败';
            $arr['data'] = array();
            return json_encode($arr);
            exit();
            //$this->resultinfo(0,'服务返回数据失败',$result);
        }
    }

    /*处理上传图片*/
    public function doPagePostuploadimg()

    {
        $message = "请求到服务器";

        global $_GPC, $_W;

        $uptypes = array('image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif', 'image/bmp', 'image/x-png');

        $max_file_size = 2000000; //上传文件大小限制, 单位BYTE

        $destination_folder = "../attachment/" . $_GPC['m'] . "/" . date('Ymd') . "/"; //上传文件路径

        $arr = array();

        $errno = 0;

        if (!is_uploaded_file($_FILES["file"]['tmp_name'])) //是否存在文件

        {

            $arr['status'] = 0;

            $arr['message'] = '图片不存在!';

            $message = "图片不存在!";

            print_r($message);

            $errno = 1;

            return $this->result($errno, $message, $arr);

            exit;

        }

        $file = $_FILES["file"];
        /////图片校验
//        $getaccesstoken=$this->getNewaccessToken();
//        $url = 'https://api.weixin.qq.com/wxa/img_sec_check?access_token='.$getaccesstoken;
//        $post_data['name']       = "Foo";
//        $post_data['file']       = '@' . $file;
//        //$post_data = array();
//        $res = $this->request_post($url, $post_data);
//        $file = ATTACHMENT_ROOT . 'tonghuagushi/' . 'jiaoyan.txt';
//        file_put_contents($file, json_encode($res));

        ///

        $arr['file'] = $file;

        if ($max_file_size < $file["size"]) //检查文件大小

        {

            $arr['status'] = 0;

            $arr['message'] = '文件太大';

            $message = "文件太大";

            print_r($message);

            $errno = 1;

            return $this->result($errno, $message, $arr);


            exit;

        }

        if (!in_array($file["type"], $uptypes)) //检查文件类型

        {

            $arr['status'] = 0;

            $message = "文件类型不符!" . $file["type"];

            print_r($message);

            $errno = 1;

            return $this->result($errno, $message, $arr);

            exit;

        }


        if (!file_exists($destination_folder)) {

            mkdir($destination_folder);

        }

        $filename = $file["tmp_name"];

        $pinfo = pathinfo($file["name"]);

        $ftype = $pinfo['extension'];

        $destination = $destination_folder . str_shuffle(time() . rand(111111, 999999)) . "." . $ftype;

        if (file_exists($destination)) {

            $arr['status'] = 0;


            $message = "同名文件已经存在了!" . $file["type"];

            print_r($message);

            $errno = 1;

            return $this->result($errno, $message, $arr);

            exit;

        }

        if (!move_uploaded_file($filename, $destination)) {

            $arr['status'] = 0;

            $message = "移动文件出错";

            print_r($message);

            $errno = 1;

            return $this->result($errno, $message, $arr);

            echo $arr;

            exit;

        }

        $pinfo = pathinfo($destination);

        $fname = $pinfo['basename'];

        $arr['imgname'] = "文件名：" + $fname;

        //echo $fname;

        @require_once(IA_ROOT . '/framework/function/file.func.php');

        @$filename = $fname;

        @file_remote_upload($filename);

        //https://pj.dede1.com/attachment/../attachment/zunyang_chefu188/20180515/9158665524662366.jpg

        $getdealurl = substr($destination, 14);

        $message = "生成的文件路径：" . tomedia($getdealurl);

        $arr['imgpath'] = $getdealurl;

        //print_r($message);

        // json_encode($arr);

        return $this->result($errno, $message, $arr);


    }
    //校验图片涉黄
    public function doPagePostuploadimg1()

    {


        $message = "请求到服务器";
        global $_GPC, $_W;

        $uptypes = array('image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif', 'image/bmp', 'image/x-png');

        $max_file_size = 2000000; //上传文件大小限制, 单位BYTE
        //黄图鉴定路径
        $huang= $_GPC['m'] . "/" . date('Ymd') . "/"; //上传文件路径1
        $destination_folder = "../attachment/" . $huang; //上传文件路径


        $arr = array();

        $errno = 0;

        if (!is_uploaded_file($_FILES["file"]['tmp_name'])) //是否存在文件

        {

            $arr['status'] = 0;

            $arr['message'] = '图片不存在!';

            $message = "图片不存在!";

            print_r($message);

            $errno = 1;

            return $this->result($errno, $message, $arr);

            exit;

        }

        $file = $_FILES["file"];

        $arr['file'] = $file;

        if ($max_file_size < $file["size"]) //检查文件大小

        {

            $arr['status'] = 0;

            $arr['message'] = '文件太大';

            $message = "文件太大";

            print_r($message);

            $errno = 1;

            return $this->result($errno, $message, $arr);


            exit;

        }

        if (!in_array($file["type"], $uptypes)) //检查文件类型

        {

            $arr['status'] = 0;

            $message = "文件类型不符!" . $file["type"];

            print_r($message);

            $errno = 1;

            return $this->result($errno, $message, $arr);

            exit;

        }


        if (!file_exists($destination_folder)) {

            mkdir($destination_folder);

        }

        $filename = $file["tmp_name"];

        $pinfo = pathinfo($file["name"]);

        $ftype = $pinfo['extension'];
        $timepaht=str_shuffle(time() . rand(111111, 999999)) . "." . $ftype;
        $destination = $destination_folder . $timepaht;
        //最终的涉黄图片校验图片路径
        $shepath=$huang."/".$timepaht;

        if (file_exists($destination)) {

            $arr['status'] = 0;


            $message = "同名文件已经存在了!" . $file["type"];

            print_r($message);

            $errno = 1;

            return $this->result($errno, $message, $arr);

            exit;

        }

        if (!move_uploaded_file($filename, $destination)) {

            $arr['status'] = 0;

            $message = "移动文件出错";

            print_r($message);

            $errno = 1;

            return $this->result($errno, $message, $arr);

            echo $arr;

            exit;

        }

        $pinfo = pathinfo($destination);

        $fname = $pinfo['basename'];

        $arr['imgname'] = "文件名：" + $fname;

        //echo $fname;

        @require_once(IA_ROOT . '/framework/function/file.func.php');

        @$filename = $fname;

        @file_remote_upload($filename);

        //https://pj.dede1.com/attachment/../attachment/zunyang_chefu188/20180515/9158665524662366.jpg

        /////图片校验
        $savabackinfo = ATTACHMENT_ROOT . 'tonghuagushi/' . 'jiaoyan.txt';
        $getaccesstoken=$this->getNewaccessToken();
        $url = 'https://api.weixin.qq.com/wxa/img_sec_check?access_token='.$getaccesstoken;
        $huangimgpath= $_SERVER['DOCUMENT_ROOT'].'/attachment/'.$shepath;
        $ch = curl_init($url);
        // Create a CURLFile object
        $cfile = curl_file_create($huangimgpath);
        // 需要传的数据，以数组格式
        $data = array('aaa' => $cfile);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        // 执行文件上传
        $res=curl_exec($ch);
        file_put_contents($savabackinfo, $res);
        $getstatus = file_get_contents($savabackinfo);

        $getdealurl = substr($destination, 14);
        $message = "生成的文件路径：" . tomedia($getdealurl);
        //对涉黄图片校验结果
        if ($getstatus) {
            $status = json_decode($getstatus, true);
            if ($status['errmsg'] =='ok') {
                $arr['imgpath'] = $getdealurl;
                $arr['status']=1;
            }else if($status['errcode'] =='87014'){
                //黄图
                $arr['status']=0;
                $arr['imgpath']='';
            }else{
                $arr['status']=-1;
                $arr['imgpath'] = $getdealurl;
            }
        }
        //$arr['imgpath'] = $getdealurl;

        return $this->result($errno, $message, $arr);


    }

    //处理录音上传
    public function doPagePostuploadaudio()

    {
        $message = "请求到服务器";
        global $_GPC, $_W;
        $destination_folder = "../attachment/audios/" . $_GPC['m'] . "/" . date('Ymd') . "/"; //上传文件路径

        $arr = array();

        $errno = 0;

        // 接收小程序上传录音文件
        if (!array_key_exists('file', $_FILES)) {
            $errno = 1;
            $message = '未传入文件';
            return $this->result($errno, $message, $arr);
        }
        //$record_ms=$_GPC['record_ms'];

        // 获取临时文件路径并构造新文件名称
        $file = $_FILES['file']['tmp_name'];
        $randFileName = 'weapp_audio_' . md5(uniqid("", true));
        if (!file_exists($destination_folder)) {
            mkdir($destination_folder);
        }
        // 将临时文件保存到服务器中
        $savePath = $destination_folder . $randFileName . '.mp3';
        file_put_contents($savePath, file_get_contents($file));

        //获取服务器中保存的录音文件路径并返回到小程序客户端中
        $readPath = $destination_folder . $randFileName . '.mp3';
        $getdealurl = substr($readPath, 14);
        $arr['luyinpath'] = tomedia($getdealurl);
        $arr['status'] = 1;
        echo json_encode($arr);

    }

    /*保存故事*/

    public function doPagePostgushi()

    {

        global $_W, $_GPC;
        $table = "story_gushi";
        $data = array();

        //$imgarray = array();

        $data['uniacid'] = $_W['uniacid'];

        $data['title'] = $_GPC['title'];
        //把转移符恢复htmlspecialchars_decode
        $imgarray = htmlspecialchars_decode($_GPC['storyimg']);
        //把引号替换
        $imgarray = str_replace('"', '', $imgarray);
        //剔除[ ]
        $imgarray = ltrim($imgarray, '[');
        $imgarray = substr($imgarray, 0, -1);

        $newarr = explode(",", $imgarray);
        $getroot = $_W['attachurl'];
        $rootsize = strlen($getroot);
        foreach ($newarr as $k => $v) {
            $newarr[$k] = substr($v, $rootsize);
        }

        $data['storyimg'] = implode(',', $newarr);

        $data['yuyinurl'] = $_GPC['yuyinurl'];

        $data['b_id'] = $_GPC['b_id'];
        $data['isprivate'] = $_GPC['isprivate'];
        $data['languages'] = $_GPC['languages'];
        $data['zjid'] = $_GPC['zjid'];
        $data['bgmusic'] = $_GPC['bgmusic'];

        $data['playlong'] = $_GPC['playlong'];

        $arr = array();

        if (empty($_GPC['title'])) {

            $arr['status'] = 0;

            $arr['message'] = "发布失败，故事名称不可为空";

            echo json_encode($arr);

            die();

        } else if (empty($_GPC['storyimg'])) {

            $arr['status'] = 0;

            $arr['message'] = "发布失败，故事图片不可为空";
            echo json_encode($arr);
            die();

        } else if (empty($_GPC['yuyinurl'])) {

            $arr['status'] = 0;

            $arr['message'] = "发布失败，录音不可为空";

            echo $arr;
            die();

        } else if (empty($_GPC['b_id'])) {

            $arr['status'] = 0;

            $arr['message'] = "发布失败，用户id不可为空";

            echo json_encode($arr);
            die();

        } else if (empty($_GPC['languages'])) {

            $arr['status'] = 0;

            $arr['message'] = "发布失败，请选择语言类型";
            echo json_encode($arr);
            die();
        } else if (empty($_GPC['playlong'])) {
            $arr['status'] = 0;
            $arr['message'] = "发布失败，请传入录音时长";
            echo json_encode($arr);
            die();

        } else {
            $data['g_addtime'] = time();
            $res = pdo_insert($table, $data);
            $getnewid = pdo_insertid();
            if ($res) {
                $arr['status'] = 1;
                $arr['message'] = "发布成功";
                $arr['newid'] = $getnewid;
                $arr['res'] = $res;
            } else {
                $arr['status'] = -1;
                $arr['message'] = "发布失败";
                $arr['res'] = $res;
                $arr['data'] = $data;
            }

        }
        echo json_encode($arr);

    }
    /*保存图文故事*/

    public function doPagePostreadgushi()

    {
        global $_W, $_GPC;
        $table = "story_gushiread";
        $data = array();
        //$imgarray = array();
        $data['uniacid'] = $_W['uniacid'];
        $data['r_yuyinurl'] = $_GPC['r_yuyinurl'];
        $data['b_id'] = $_GPC['b_id'];
        $data['gsid'] = $_GPC['gsid'];
        $data['g_zjid'] = $_GPC['zjid'];
        $arr = array();
        if (empty($_GPC['r_yuyinurl'])) {
            $arr['status'] = 0;
            $arr['message'] = "发布失败，录音不可为空";
            echo $arr;
            die();

        } else if (empty($_GPC['b_id'])) {
            $arr['status'] = 0;
            $arr['message'] = "发布失败，用户id不可为空";
            echo json_encode($arr);
            die();

        } else if (empty($_GPC['gsid'])) {

            $arr['status'] = 0;

            $arr['message'] = "发布失败，素材故事id不可为空";

            echo json_encode($arr);
            die();

        } else {
            $data['r_addtime'] = time();
            $res = pdo_insert($table, $data);
            $getnewid = pdo_insertid();
            if ($res) {
                $arr['status'] = 1;
                $arr['message'] = "发布成功";
                $arr['newid'] = $getnewid;
                $arr['res'] = $res;
            } else {
                $arr['status'] = -1;
                $arr['message'] = "发布失败";
                $arr['res'] = $res;
                $arr['data'] = $data;
            }

        }
        echo json_encode($arr);

    }
    //数组转xml

    public function arrayToXml($arr)

    {

        $xml = "<xml>";

        foreach ($arr as $key => $val) {

            if (is_numeric($val)) {

                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

            } else {

                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";

            }

        }

        $xml .= "</xml>";

        return $xml;

    }
    //检查故事标题是否合法
    public function doPageChecktitle(){
        global $_W, $_GPC;
        $checkcontent=$_GPC['contents'];
        $getaccesstoken=$this->getNewaccessToken();
        $checkcon=array();
        $checkcon['content']=$checkcontent;
        $checkc=json_encode($checkcon);
        $url = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token='.$getaccesstoken;
        global $_W, $_GPC;
        $savabackinfo = ATTACHMENT_ROOT . 'tonghuagushi/' . 'titlejiaoyan.txt';
        $ch = curl_init($url);
        //$cfile = curl_file_create();
        // 需要传的数据，以数组格式
        //$data = array('aaa' => $cfile);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $checkc);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        // 执行文件上传
        $res=curl_exec($ch);
        file_put_contents($savabackinfo, $res);
        $getstatus = file_get_contents($savabackinfo);
        //对涉文字校验结果
        if ($getstatus) {
            $status = json_decode($getstatus, true);
            if ($status['errmsg'] =='ok') {
                $arr['msg'] = '校验成功';
                $arr['status']=1;
            }else if($status['errcode'] =='87014'){
                //不合法
                $arr['status']=0;
                $arr['msg']='不合法标题';
            }
            echo json_encode($arr);

        }
    }
    //返回故事列表
    public function doPageGetgushilist()
    {

        global $_W, $_GPC;
        $table = "story_gushi";
        $table1 = "story_user";
        $arr = array();
        $data = array();
        $chartdata = array();
        $type = $_GPC['hot'];//判断是否为热门故事
        $key = $_GPC['keyword'];
        $gstype=$_GPC['gstype'];//故事类型，是官方还是小主播
        $ordertype=$_GPC['ordertype'];//搜索类型，如果等3表示任意
        if (empty($type)) {//
            if (!empty($key)) {
                //搜索
                $sql = "select gs.id,gs.title,gs.g_addtime,gs.storyimg,gs.goodnum,gs.listennum, u.u_id from" . tablename($table) . 'gs,'
                    . tablename($table1) . "u where gs.b_id=u.u_id and gs.`status`=1 and gs.is_delete=0 and gs.isprivate=0 and CONCAT(gs.title,u.nickname) LIKE '%{$key}%'";
            } else {
                //这里是听故事列表判断
                if ($gstype){
                    //小主播 1
                    switch ($ordertype)
                    {
                        case 0:
                            //综合
                            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and status=1 and gstype=0 and is_delete=0 and isprivate=0 ORDER BY (goodnum + listennum) desc";
                            break;
                        case 1:
                            //收听
                            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and status=1 and gstype=0  and is_delete=0 and isprivate=0 ORDER BY listennum desc";
                            break;
                        case 2:
                            //点赞
                            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and status=1 and gstype=0  and is_delete=0 and isprivate=0 ORDER BY goodnum desc";
                            break;
                        default:
                            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and status=1 and gstype=0  and is_delete=0 and isprivate=0 ORDER BY orders desc";
                        //随意;
                    }
                }else if ($gstype==0){
                    //官方(织布熊)
                    switch ($ordertype)
                    {
                        case 0:
                            //综合
                            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and status=1 and gstype=1 and is_delete=0 ORDER BY (goodnum + listennum) desc";
                            break;
                        case 1:
                            //收听
                            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and status=1 and gstype=1  and is_delete=0 ORDER BY listennum desc";
                            break;
                        case 2:
                            //点赞
                            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and status=1 and gstype=1  and is_delete=0 ORDER BY goodnum desc";
                            break;
                        default:
                            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and status=1 and gstype=1  and is_delete=0 ORDER BY orders desc";
                        //随意;
                    }
                }else{
                    //没条件
                    $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and status=1 and is_delete=0 and isprivate=0  ORDER BY g_addtime";
                }
            }

        } else {
            //综合搜索
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and status=1 and is_delete=0  and isprivate=0  ORDER BY (goodnum + listennum) desc limit 2";

        }

        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $result = pdo_fetchall($sql, $params);
        if (empty($result)) {
            $arr['status'] = 0;
            $arr['msg'] = '没故事';
            $arr['data'] = array();

        } else {
            if (is_array($result)) {
                foreach ($result as $k => $v) {
                    foreach (explode(",", $v['storyimg']) as $kk => $vv) {
                        $v['img'][] = tomedia($vv);
                    }
                    $v['g_addtime'] = date("Y-m-d H:m", $v['g_addtime']);

                    //$chartdata[]=$this->chartSort($v,$v['title']);
                    $data[] = $v;
                }
                $arr['data'] = $data;

                //$arr['chartdata'] = $chartdata;

            }
            $arr['status'] = 1;
            $arr['msg'] = '获取数据成功';


        }
        return json_encode($arr);
    }
    //返回阅读故事列表
    public function doPageGetreadgushilist()
    {
        global $_W, $_GPC;
        $table = "story_pictext";
        $table1 = "story_gushiread";
        $arr = array();
        $data = array();
        $key = $_GPC['keyword'];
        $gstype=$_GPC['gstype'];//故事类型，0-4
        $ordertype=$_GPC['ordertype'];//搜索类型，如果等3表示任意
        if (!empty($key)) {
            //搜索
            $sql = "select gs.p_id,gs.p_title,gs.p_addtime,gs.p_toppic,gs.p_content,gs.p_auther,gs.p_type from" . tablename($table) . 'gs '
                . "where CONCAT(gs.p_title) LIKE '%{$key}%'";
        } else {
            if ($gstype==0){
                //寓言故事
                switch ($ordertype)
                {
                    case 0:
                        //综合
                        $sql = "select * from " . tablename($table).'pt' . " where pt.uniacid=:uniacid and pt.p_type=0  ORDER BY (pt.p_count+(pt.p_addtime%1537000000)+(SELECT COUNT(*) FROM ims_story_gushiread where gsid=pt.p_id and status=1)) desc";
                        //echo $sql;
                        // $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=0  ORDER BY p_count desc";
                        break;
                    case 1:
                        //A-Z，暂时没有
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=0  ORDER BY p_count desc";
                        break;
                    case 2:
                        //朗读数
                        $sql = "select * from " . tablename($table).'pt' . " where pt.uniacid=:uniacid and pt.p_type=0  ORDER BY (SELECT COUNT(*) FROM ims_story_gushiread where gsid=pt.p_id and status=1) desc";
                        break;
                    case 3:
                        //创作时间
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=0  ORDER BY p_addtime desc";
                        break;
                    default:
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=0  ORDER BY p_count desc";
                    //随意;
                }
            }else if($gstype==1){
                //成语故事
                switch ($ordertype)
                {
                    case 0:
                        //综合
                        $sql = "select * from " . tablename($table).'pt' . " where pt.uniacid=:uniacid and pt.p_type=1  ORDER BY (pt.p_count+(pt.p_addtime%1537000000)+(SELECT COUNT(*) FROM ims_story_gushiread where gsid=pt.p_id and status=1)) desc";
                        // $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=0  ORDER BY p_count desc";
                        break;
                    case 1:
                        //A-Z，暂时没有
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=1  ORDER BY p_count desc";
                        break;
                    case 2:
                        //朗读数
                        $sql = "select * from " . tablename($table).'pt' . " where pt.uniacid=:uniacid and pt.p_type=1  ORDER BY (SELECT COUNT(*) FROM ims_story_gushiread where gsid=pt.p_id and status=1) desc";
                        break;
                    case 3:
                        //创作时间
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=1  ORDER BY p_addtime desc";
                        break;
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=1  ORDER BY p_count desc";
                    //随意;
                }
            }else if($gstype==2){
                //科学故事
                switch ($ordertype)
                {
                    case 0:
                        //综合
                        $sql = "select * from " . tablename($table).'pt' . " where pt.uniacid=:uniacid and pt.p_type=2  ORDER BY (pt.p_count+(pt.p_addtime%1537000000)+(SELECT COUNT(*) FROM ims_story_gushiread where gsid=pt.p_id and status=1)) desc";
                        //echo $sql;
                        // $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=2  ORDER BY p_count desc";
                        break;
                    case 1:
                        //A-Z，暂时没有
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=2  ORDER BY p_count desc";
                        break;
                    case 2:
                        //朗读数
                        $sql = "select * from " . tablename($table).'pt' . " where pt.uniacid=:uniacid and pt.p_type=2  ORDER BY (SELECT COUNT(*) FROM ims_story_gushiread where gsid=pt.p_id and status=1) desc";
                        break;
                    case 3:
                        //创作时间
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=2  ORDER BY p_addtime desc";
                        break;
                    default:
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=2  ORDER BY p_count desc";
                    //随意;
                }
            }else if($gstype==3){
                //经典名著
                switch ($ordertype)
                {
                    case 0:
                        //综合
                        $sql = "select * from " . tablename($table).'pt' . " where pt.uniacid=:uniacid and pt.p_type=3  ORDER BY (pt.p_count+(pt.p_addtime%1537000000)+(SELECT COUNT(*) FROM ims_story_gushiread where gsid=pt.p_id and status=1)) desc";
                        //echo $sql;
                        // $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=0  ORDER BY p_count desc";
                        break;
                    case 1:
                        //A-Z，暂时没有
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=0  ORDER BY p_count desc";
                        break;
                    case 2:
                        //朗读数
                        $sql = "select * from " . tablename($table).'pt' . " where pt.uniacid=:uniacid and pt.p_type=3  ORDER BY (SELECT COUNT(*) FROM ims_story_gushiread where gsid=pt.p_id and status=1) desc";
                        break;
                    case 3:
                        //创作时间
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=3  ORDER BY p_addtime desc";
                        break;
                    default:
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=3  ORDER BY p_count desc";
                    //随意;
                }
            }else{
                switch ($ordertype)
                {
                    case 0:
                        //综合
                        $sql = "select * from " . tablename($table).'pt' . " where pt.uniacid=:uniacid and pt.p_type=4  ORDER BY (pt.p_count+(pt.p_addtime%1537000000)+(SELECT COUNT(*) FROM ims_story_gushiread where gsid=pt.p_id and status=1)) desc";
                        //echo $sql;
                        // $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=0  ORDER BY p_count desc";
                        break;
                    case 1:
                        //A-Z，暂时没有
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=4  ORDER BY p_count desc";
                        break;
                    case 2:
                        //朗读数
                        $sql = "select * from " . tablename($table).'pt' . " where pt.uniacid=:uniacid and pt.p_type=4  ORDER BY (SELECT COUNT(*) FROM ims_story_gushiread where gsid=pt.p_id and status=1) desc";
                        break;
                    case 3:
                        //创作时间
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=4  ORDER BY p_addtime desc";
                        break;
                    default:
                        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and p_type=4  ORDER BY p_count desc";
                    //随意;
                }
            }
        }



        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $result = pdo_fetchall($sql, $params);
        if (empty($result)) {
            $arr['status'] = 0;
            $arr['msg'] = '没故事';
          	$arr['sql']=$sql;
            $arr['data'] = array();

        } else {
            if (is_array($result)) {
                foreach ($result as $k => $v) {
                    foreach (explode(",", $v['p_toppic']) as $kk => $vv) {
                        $v['img'][] = tomedia($vv);
                    }
                    $v['p_addtime'] = date("Y-m-d H:m", $v['p_addtime']);

                    //$chartdata[]=$this->chartSort($v,$v['title']);
                    $data[] = $v;
                    $data[$k]['counts'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($table1)."where  uniacid=".$_W['uniacid']." and status=1 and gsid=".$v['p_id']);
                }
                $arr['data'] = $data;

                //$arr['chartdata'] = $chartdata;

            }
            $arr['status'] = 1;
            $arr['msg'] = '获取数据成功';


        }
        return json_encode($arr);
    }
    //返回返回单条故事
    public function doPageGetgushi()
    {
        global $_W, $_GPC;
        $table = "story_gushi";
        $table1 = "story_user";
        $gsid = $_GPC['gushiid'];
        $arr = array();
        $sql = "select * from " . tablename($table) . 'gs,' . tablename($table1) . 'u'
            . " where gs.uniacid=:uniacid and gs.id=:id  and gs.b_id=u.u_id";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':id' => $gsid
        );
        $result = pdo_fetch($sql, $params);
        $u_id = $result['b_id'];
        $gushicount = pdo_fetch("SELECT COUNT(*) as count from ims_story_gushi WHERE is_delete=0 and status=1 and b_id=" . $u_id);
        if (empty($result)) {
            $arr['status'] = 0;
            $arr['msg'] = '没数据';
        } else {

            $thumbs = array();
            $thumbs_list = explode(",", $result['storyimg']);
            foreach ($thumbs_list as $k => $v) {
                if (!empty($v)) {
                    $thumbs[$k] = tomedia($v);
                }
            }
            $arr['addtime'] = date("Y-m-d", $result['g_addtime']);
            $arr['status'] = 1;
            $arr['msg'] = '数据请求成功';
            $arr['result'] = $result;
            $arr['imgs'] = $thumbs;
            $arr['count'] = $gushicount['count'];
        }
        echo json_encode($arr);

    }
    //返回单条阅读故事,朋友圈分享使用
    public function doPageGetgushiread()
    {
        global $_W, $_GPC;
        $table = "story_gushiread";
        $table1 = "story_user";
        $table2 = "story_pictext";
        $gsid = $_GPC['gushiid'];
        $arr = array();
        $sql = "select * from " . tablename($table) . 'gs,'. tablename($table2) . 'pt,' . tablename($table1) . 'u'
            . " where gs.uniacid=:uniacid and gs.r_id=:id  and gs.gsid=pt.p_id and gs.b_id=u.u_id";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':id' => $gsid
        );
        $result = pdo_fetch($sql, $params);
        if (empty($result)) {
            $arr['status'] = 0;
            $arr['msg'] = '没数据';
        } else {
            $result['r_addtime']= date("Y-m-d", $result['r_addtime']);
            $result['p_toppic']=tomedia($result['p_toppic']);
            $arr['addtime'] = date("Y-m-d", $result['r_addtime']);
            $arr['status'] = 1;
            $arr['msg'] = '数据请求成功';
            $arr['result'] = $result;
        }
        echo json_encode($arr);

    }
    //返回热门主播
    public function doPageGethotuser(){
        global $_W;
        $table = "story_gushi";
        $table1 = "story_user";
        $arr = array();
        $sql = "select gs.id,gs.isfrist,u.nickname,u.headerimg,u.gscount from " . tablename($table) . 'gs,' . tablename($table1) . 'u'
            . " where gs.uniacid=:uniacid and gs.b_id=u.u_id and gs.is_delete=0 and gs.status=1  and u.gscount>0 GROUP BY u.nickname ORDER BY u.gscount DESC limit 3";
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $result = pdo_fetchall($sql, $params);
        if (empty($result)) {
            $arr['status'] = 0;
            $arr['msg'] = '没数据';
        } else {
            $arr['status'] = 1;
            $arr['msg'] = '数据请求成功';
            $arr['result'] = $result;
        }
        echo json_encode($arr);
    }
    //单条热门阅读
    public function doPageGetoneread()
    {
        global $_W, $_GPC;
        $table = "story_pictext";
        $table1 = "story_gushiread";
        $table3 = "story_user";
        $gsid = $_GPC['p_id'];
        $arr = array();
        $sql = "select * from " . tablename($table) . " where uniacid=:uniacid and p_id=:gid";
        $sql1="select gs.*,u.nickname,u.headerimg,u.gscount from ". tablename($table1) . 'gs,' . tablename($table3) . 'u'. " where  gs.uniacid=:uniacid and gs.b_id=u.u_id and gs.isprivate=0 and gs.status=1 and gs.gsid=:gid ";
        //$sql1 = "select * from " . tablename($table1) . " where uniacid=:uniacid and isprivate=0 and status=1 and gsid=:gid";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':gid'=>$gsid
        );
        $result = pdo_fetch($sql, $params);
        $result['counts'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($table1)."where  uniacid=".$_W['uniacid']." and status=1 and gsid=".$gsid);
        $allgushiread = pdo_fetchall($sql1, $params);

        if (empty($result)) {
            $arr['status'] = 0;
            $arr['msg'] = '没数据';
        } else {
            $result['p_toppic']=tomedia($result['p_toppic']);
            $arr['addtime'] = date("Y-m-d", $result['g_addtime']);
            $arr['status'] = 1;
            $arr['msg'] = '数据请求成功';
            $arr['result'] = $result;
            $arr['allmusic'] = $allgushiread;
        }
        echo json_encode($arr);

    }
    //阅读故事详细
    public function doPageGetreaddetail()
    {
        global $_W, $_GPC;
        $table = "story_pictext";
        $table1 = "story_gushiread";
        $table3 = "story_user";
        $r_id = $_GPC['r_id'];//音频id
        $p_id = $_GPC['p_id'];//图文id
        $arr = array();
        $sql = "select * from " . tablename($table) . " where uniacid=:uniacid and p_id=:pid";
        $sql1="select gs.*,u.nickname,u.headerimg,u.gscount from ". tablename($table1) . 'gs,' . tablename($table3) . 'u'. " where  gs.uniacid=:uniacid and gs.b_id=u.u_id and gs.isprivate=0 and gs.r_id=:rid";
        //$sql1 = "select * from " . tablename($table1) . " where uniacid=:uniacid and isprivate=0 and status=1 and gsid=:gid";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':pid'=>$p_id
        );
        $params2 = array(
            ':uniacid' => $_W['uniacid'],
            ':rid'=>$r_id
        );
        $result = pdo_fetch($sql, $params);

        $thisreadgushi = pdo_fetch($sql1, $params2);

        if (empty($result)) {
            $arr['status'] = 0;
            $arr['msg'] = '没数据';
        } else {
            $result['p_toppic']=tomedia($result['p_toppic']);
            $arr['addtime'] = date("Y-m-d", $result['g_addtime']);
            $arr['status'] = 1;
            $arr['msg'] = '数据请求成功';
            $arr['result'] = $result;
            $arr['thisreadgushi'] = $thisreadgushi;
        }
        echo json_encode($arr);

    }
    //查询个人数据列表
    public function doPageGetpersonlist()
    {

        global $_W, $_GPC;
        $table = "story_gushi";
        $table1 = "story_user";
        $tablelinst = "story_shoutingstory";
        $tableshoucang = "story_shoucang";
        $tablepinlun = "story_pinlun";
        $arr = array();
        $data = array();
        $uid = $_GPC['uid'];
        $gsid = $_GPC['gsid'];
        $type=$_GPC['type'];
        $onegsid=$_GPC['onegsid'];
        if (empty($uid)){
            $sql = "select gs.id,gs.title,gs.g_addtime,gs.storyimg,gs.goodnum,gs.listennum, u.u_id from " . tablename($table) . 'gs,' .
                tablename($table1) . "u where gs.b_id=u.u_id and gs.`status`=1 and gs.uniacid=:uniacid and gs.is_delete=0 and gs.b_id=:uid";
            $uidsql = "select * from " . tablename($table) . " where uniacid=:uniacid and id=:id";
            $useparams = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $gsid
            );
            $uidres = pdo_fetch($uidsql, $useparams);
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':uid' => $uidres['b_id']
            );
        }else{//有传来用户的id就查询个人故事，否则直接从故事中取用户id

            switch ($type)
            {
                case 0:
                    //我的故事
                    $sql= "select * from " . tablename($table) . " where uniacid=:uniacid and is_delete=0 and b_id=:uid";
                    break;
                case 1:
                    //收听历史
                    $sql= "select * from " . tablename($tablelinst).'st,'.tablename($table) . "gs  where gs.uniacid=:uniacid AND gs.id=st.g_id and gs.is_delete=0  and st.uid=:uid";
                    break;
                case 2:
                    //我的收藏SELECT* from ims_story_shoucang sc,ims_story_gushi gs WHERE gs.id=sc.g_id and sc.uid=20
                    $sql= "select * from " . tablename($tableshoucang).'sc,'.tablename($table) . "gs  where gs.uniacid=:uniacid AND gs.id=sc.g_id and gs.is_delete=0  and sc.uid=:uid";
                    break;
                case 3:
                    //发出评论
                    $sql= "select * from " . tablename($tablepinlun).'pl,'.tablename($table1).'u,'.tablename($table) .
                        "gs  where gs.uniacid=:uniacid AND gs.id=pl.g_id and pl.uid=:uid and pl.f_uid=u.u_id";
                    break;
                case 4:
                    //收到的
                    //$sql= "select * from " . tablename($tablepinlun).'pl,'.tablename($table) . "gs  where gs.uniacid=:uniacid AND gs.id=sc.g_id and pl.f_uid=:uid";
                    $sql= "select * from " . tablename($tablepinlun).'pl,'.tablename($table1).'u,'.tablename($table) .
                        "gs  where gs.uniacid=:uniacid AND gs.id=pl.g_id and pl.f_uid=:uid and pl.uid=u.u_id";
                    break;
                case 5:
                    //发出评论中的单条所有回复
                    $sql= "select * from " . tablename($tablepinlun). "where uniacid=:uniacid AND g_id=".$onegsid." and f_uid=:uid";
                    break;
                default:
                    break;

                //随意;
            }

            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':uid' => $uid
            );
        }
        $result = pdo_fetchall($sql, $params);

        if (empty($result)) {
            $arr['status'] = 0;
            $arr['msg'] = '没故事';
            $arr['data'] = array();

        } else {
            if (is_array($result)) {
                foreach ($result as $k => $v) {
                    foreach (explode(",", $v['storyimg']) as $kk => $vv) {
                        $v['img'][] = tomedia($vv);
                    }
                    if($type==3||$type==4||$type==5){
                        $v['gp_addtime'] = date("Y-m-d", $v['gp_addtime']);
                    }else{
                        $v['g_addtime'] = date("Y-m-d", $v['g_addtime']);
                    }

                    $data[] = $v;
                }
                $arr['data'] = $data;

            }
            $arr['status'] = 1;
            $arr['msg'] = '获取数据成功';

        }
        return json_encode($arr);
    }
    //热门阅读
    public function doPageGethotread()
    {
        global $_W, $_GPC;
        $table = "story_pictext";
        $table1 = "story_gushiread";
        $arr = array();
        $sql = "select * from " . tablename($table). " where uniacid=:uniacid ORDER BY p_count DESC limit 2 ";
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $result = pdo_fetchall($sql, $params);
        foreach ($result as $k=>$vv){
            $result[$k]['p_toppic']=tomedia($vv['p_toppic']);
            $result[$k]['counts'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($table1)."where  uniacid=".$_W['uniacid']." and status=1 and gsid=".$vv['p_id']);
        }
        if (empty($result)) {
            $arr['status'] = 0;
            $arr['msg'] = '没数据';
            $arr['result']=$result;
        } else {
            $arr['status'] = 1;
            $arr['msg'] = '数据请求成功';
            $arr['result']=$result;
        }
        echo json_encode($arr);
    }
    //获取所有的阅读故事
    public function doPageGetallreadgushi()
    {
        global $_W, $_GPC;
        $table = "story_pictext";
        $table1 = "story_gushiread";
        $num=$_GPC['page'];
        $arr = array();
        $sql = "select * from " . tablename($table). " where uniacid=:uniacid ORDER BY p_count DESC";
        $sql .= " limit " . 4 * $num . ',' . 4;
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $result = pdo_fetchall($sql, $params);
        foreach ($result as $k=>$vv){
            $result[$k]['p_toppic']=tomedia($vv['p_toppic']);
            $result[$k]['counts'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($table1)."where  uniacid=".$_W['uniacid']." and status=1 and gsid=".$vv['p_id']);
        }
        if (empty($result)) {
            $arr['status'] = 0;
            $arr['msg'] = '没数据';
            $arr['result']=$result;
        } else {
            $arr['status'] = 1;
            $arr['msg'] = '数据请求成功';
            $arr['result']=$result;
        }
        echo json_encode($arr);
    }
    //获取我的阅读故事
    public function doPageGetmyreadgushi()
    {
        global $_W, $_GPC;
        $table = "story_gushiread";
        $table1 = "story_pictext";
        $getuid=$_GPC['uid'];
        $arr = array();
        $sql = "select * from " . tablename($table).'gs,'.tablename($table1). " pt where gs.uniacid=:uniacid and gs.b_id=:uid and gs.gsid=pt.p_id ORDER BY gs.r_addtime DESC";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':uid'=>$getuid
        );
        $result = pdo_fetchall($sql, $params);
        foreach ($result as $k=>$vv){
            $result[$k]['p_toppic']=tomedia($vv['p_toppic']);
        }
        if (empty($result)) {
            $arr['status'] = 0;
            $arr['msg'] = '没数据';
            $arr['result']=$result;
        } else {
            $arr['status'] = 1;
            $arr['msg'] = '数据请求成功';
            $arr['result']=$result;
        }
        echo json_encode($arr);
    }
    //删除我的故事，收听历史，我的收藏
    public function doPagePostdelete(){
        global $_W, $_GPC;
        $tablegushi = "story_gushi";
        $tablereadgushi = "story_gushiread";
        $tablepictext = "story_pictext";
        $tablelinst = "story_shoutingstory";
        $tableshoucang = "story_shoucang";
        $tablepinlun = "story_pinlun";
        $table1 = "story_user";
        $arr = array();
        $uid = $_GPC['uid'];
        $gsid=$_GPC['gsid'];
        $gsidarr=$_GPC['gsid'];
        $gsid = htmlspecialchars_decode($gsid);
        $gsidlenth=str_replace('[', '', $gsid);
        $gsidlenth = str_replace(']', '', $gsidlenth);
        $gsid = str_replace('[', '(', $gsid);
        $gsid = str_replace(']', ')', $gsid);

        $gsidarr=explode(',',$gsidlenth);
        if($gsidarr[0]==""){
            $gsidlen=0;
        }else{
            $gsidlen=count($gsidarr);
        }

        //未审核的故事
        $noshenhe=$_GPC['noshenhe'];
        $noshenhe = htmlspecialchars_decode($noshenhe);
        $shenhelenth=str_replace('[', '', $noshenhe);
        $shenhelenth = str_replace(']', '', $shenhelenth);
        $noshenhe = str_replace('[', '(', $noshenhe);
        $noshenhe = str_replace(']', ')', $noshenhe);

        $shenhearr=explode(',',$gsidlenth);
        if($shenhearr[0]==""){
            $len=0;
        }else{
            $len=count($shenhearr);
        }

        $towary=$_GPC['allarr'];
        $towary = htmlspecialchars_decode($towary);
        $towary = str_replace('[', '(', $towary);
        $towary = str_replace(']', ')', $towary);
        $type=$_GPC['type'];
        $isreadgs=$_GPC['isread'];
        switch ($type)
        {
            case 0:
                //我的故事
                //UPDATE ims_story_shoucang set uid=20 where uniacid=99 AND g_id in("43","45") and uid=23
                if ($isreadgs){
                    foreach ($gsidarr as $k=>$v){
                        $getpcountsql = "select p_count from " . tablename($tablepictext) . " where `uniacid`=:uniacid and p_id=:pid";
                        $param = array(
                            ':uniacid' => $_W['uniacid'],
                            ':pid'=>$v
                        );
                        $getpcount = pdo_fetch($getpcountsql, $param);
                        $newcont=$getpcount['p_count']-1;
                        $sql1= "UPDATE " . tablename($tablepictext). "set p_count=".$newcont." where uniacid=:uniacid"." and p_id=:pid";
                        $param2 = array(
                            ':uniacid' => $_W['uniacid'],
                            ':pid'=>$v
                        );
                        pdo_fetch($sql1, $param2);
                    }
                    $sql= "delete from ". tablename($tablereadgushi). "where uniacid=".$_W['uniacid']." AND r_id in".$gsid." and b_id=".$uid;
                }else{
                    if($len){
                        $sql= "UPDATE " . tablename($tablegushi). "set is_delete=1 where uniacid=".$_W['uniacid']." AND id in".$towary." and b_id=".$uid;
                    }else{
                        if ($gsid!="()"){
                            $sql= "UPDATE " . tablename($tablegushi). "set is_delete=1 where uniacid=".$_W['uniacid']." AND id in".$gsid." and b_id=".$uid;
                        }else{
                            $sql= "UPDATE " . tablename($tablegushi). "set is_delete=1 where uniacid=".$_W['uniacid']." AND id in".$noshenhe." and b_id=".$uid;
                        }

                    }
                }

                $selectsql = "select gscount from" . tablename($table1)." where uniacid=:uniacid and u_id=:b_id";
                $params = array(
                    ':uniacid' => $_W['uniacid'],
                    ':b_id'=>$uid
                );
                $recount = pdo_fetch($selectsql, $params);
                $gscount=$recount['gscount']-$gsidlen;
                $updatacount = pdo_update($table1, array('gscount' => $gscount), array('uniacid' => $_W['uniacid'], 'u_id' => $uid));

                $res=pdo_query($sql);
                break;
            case 1:
                //收听历史
                $sql= "delete from " . tablename($tablelinst). "where uniacid=".$_W['uniacid']." AND g_id in".$gsid." and uid=".$uid;
                $res=pdo_query($sql);
                break;
            case 2:
                //我的收藏
                $sql= "delete from " . tablename($tableshoucang). "where uniacid=".$_W['uniacid']." AND g_id in".$gsid." and uid=".$uid;
                $res=pdo_query($sql);
                break;
            case 3:
                //删除发出的评论
                $sql= "delete from " . tablename($tablepinlun). "where uniacid=".$_W['uniacid']." AND pl_id in".$gsid." and uid=".$uid;
                $res=pdo_query($sql);
                break;
            case 4:
                //删除收到的评论
                $sql= "delete from " . tablename($tablepinlun). "where uniacid=".$_W['uniacid']." AND pl_id in".$gsid." and f_uid=".$uid;
                $res=pdo_query($sql);
                break;
            default:
                break;

            //随意;
        }
        if($res){
            $arr['status']=1;
            $arr['msg']='删除成功';
            $arr['res']=$res;
            $arr['arrdata']='故事大小'.$gsidlen.'===审核大小'.$len;
            $arr['data']='故事'.json_encode($gsidlenth).'===审核大小'.json_encode($shenhelenth);

            return json_encode($arr);
        }else{
            $arr['status']=0;
            $arr['msg']='删除失败'.$gsid;
            $arr['res']=$res;
            return json_encode($arr);
        }

    }
    //保存收听历史
    public function doPagePostshouting()
    {
        global $_W, $_GPC;
        $table = "story_shoutingstory";
        $table2 = "story_gushi";
        $g_id = $_GPC['gsid'];
        $uid = $_GPC['uid'];
        $listennum = $_GPC['listennum'];
        $data = array();
        $datalisten = array();
        $datalisten['listennum'] = $listennum;
        $arr = array();
        //添加收听历史
        $time=time();
        $data['addtime'] =date('Y-m-d',$time);
        $data['g_id'] = $g_id;
        $data['uid'] = $uid;
        $data['uniacid'] = $_W['uniacid'];
        //先查询是否已经收听过了
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `g_id`=:gid and `uid`=:uid ";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':gid' => $g_id,
            ':uid' => $uid,
        );
        $result = pdo_fetch($sql, $params);
        if ($result) {
            //已经存在了，返回错误信息
            $arr['status'] = 0;
            $arr['msg'] = '该故事已在收听历史表中';
        } else {
            $result = pdo_update($table2, $datalisten, array('uniacid' => $_W['uniacid'], 'id' => $g_id));
            $res = pdo_insert($table, $data);
            if ($res) {
                //收听成功

                $arr['status'] = 1;
                $arr['msg'] = '添加收听历史成功';
            } else {
                $arr['status'] = 0;
                $arr['msg'] = '插入数据时出错，添加收听历史失败';
            }
        }


        echo json_encode($arr);
    }
    //保存阅读故事的收听历史
    public function doPagePostshoutingread()
    {
        global $_W, $_GPC;
        $table = "story_shoutingstory";
        $table2 = "story_gushiread";
        $g_id = $_GPC['gsid'];
        $uid = $_GPC['uid'];
        $listennum = $_GPC['listennum'];
        $data = array();
        $datalisten = array();
        $datalisten['r_listennum'] = $listennum;
        $arr = array();
        //添加收听历史
        $time=time();
        $data['addtime'] =date('Y-m-d',$time);
        $data['g_id'] = $g_id;
        $data['uid'] = $uid;
        $data['uniacid'] = $_W['uniacid'];
        //先查询是否已经收听过了
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `g_id`=:gid and `uid`=:uid ";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':gid' => $g_id,
            ':uid' => $uid,
        );
        $result = pdo_fetch($sql, $params);
        if ($result) {
            //已经存在了，返回错误信息
            $arr['status'] = 0;
            $arr['msg'] = '该故事已在收听历史表中';
        } else {
            $result = pdo_update($table2, $datalisten, array('uniacid' => $_W['uniacid'], 'r_id' => $g_id));
            $res = pdo_insert($table, $data);
            if ($res) {
                //收听成功

                $arr['status'] = 1;
                $arr['msg'] = '添加收听历史成功';
            } else {
                $arr['status'] = 0;
                $arr['msg'] = '插入数据时出错，添加收听历史失败';
            }
        }


        echo json_encode($arr);
    }
    //返回收藏信息
    public function doPageGetshoucang()
    {
        global $_W, $_GPC;
        $table = "story_shoucang";
        $table1 = "story_gushi";
        $getuid = $_GPC['uid'];
        $arr = array();
        $sql = "select sc.g_id from " . tablename($table) . "sc," . tablename($table1) . "gs where sc.uniacid=:uniacid and gs.id=sc.g_id and sc.uid=:uid";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':uid' => $getuid
        );
        $result = pdo_fetchall($sql, $params);
        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '获取数据成功';
            if (is_array($result)) {
                $arr["data"] = $result;
            } else {
                $arr["data"] = $result;
            }
            return json_encode($arr);
            exit();
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '数据为空';
            $arr['data'] = array();
            return json_encode($arr);
            exit();
        }
    }
//返回图文故事的收藏信息
    public function doPageGetreadshoucang()
    {
        global $_W, $_GPC;
        $table = "story_shoucang";
        $table1 = "story_gushiread";
        $getuid = $_GPC['uid'];
        $arr = array();
        $sql = "select sc.g_id from " . tablename($table) . "sc," . tablename($table1) . "gs where sc.uniacid=:uniacid and gs.r_id=sc.g_id and sc.uid=:uid";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':uid' => $getuid
        );
        $result = pdo_fetchall($sql, $params);
        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '获取数据成功';
            if (is_array($result)) {
                $arr["data"] = $result;
            } else {
                $arr["data"] = $result;
            }
            return json_encode($arr);
            exit();
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '数据为空';
            $arr['data'] = array();
            return json_encode($arr);
            exit();
        }
    }
    //修改收藏信息
    public function doPageChangeshoucang()
    {
        global $_W, $_GPC;
        $table = "story_shoucang";
        $g_id = $_GPC['gsid'];
        $uid = $_GPC['uid'];
        $changtype = $_GPC['changtype'];
        $data = array();
        $arr = array();
        if ($changtype) {
            //收藏
            $data['addtime'] = time();
            $data['g_id'] = $g_id;
            $data['uid'] = $uid;
            $data['uniacid'] = $_W['uniacid'];
            //先查询是否已经收藏过了
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `g_id`=:gid and `uid`=:uid ";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':gid' => $g_id,
                ':uid' => $uid,
            );
            $result = pdo_fetch($sql, $params);
            if ($result) {
                //已经存在了，返回错误信息
                $arr['status'] = 0;
                $arr['msg'] = '收藏表中已经存在了，收藏失败';
            } else {
                $res = pdo_insert($table, $data);
                if ($res) {
                    //收藏成功
                    $arr['status'] = 1;
                    $arr['msg'] = '收藏成功';
                } else {
                    $arr['status'] = 0;
                    $arr['msg'] = '插入数据时出错，收藏失败';
                }
            }

        } else {
            //取消收藏
            $del_result = pdo_delete($table, array('uniacid' => $_W['uniacid'], 'g_id' => $g_id, 'uid' => $uid));
            if ($del_result) {
                $arr['status'] = 1;
                $arr['msg'] = '取消收藏成功';
            } else {
                $arr['status'] = 0;
                $arr['msg'] = '插入数据时出错，收藏失败';
            }
        }
        echo json_encode($arr);
    }

    //点赞
    public function doPageDogood()
    {
        global $_W, $_GPC;
        $table = "story_gushi";
        $g_id = $_GPC['gsid'];
        $goodnum = $_GPC['goodnum'];
        $data = array();
        $data['goodnum'] = $goodnum;
        $arr = array();
        //更新点赞
        $result = pdo_update($table, $data, array('uniacid' => $_W['uniacid'], 'id' => $g_id));
        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '操作成功';
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '插操失败' . $result;
        }

        echo json_encode($arr);
    }
//阅读故事点赞
    public function doPageDoreadgood()
    {
        global $_W, $_GPC;
        $table = "story_gushiread";
        $g_id = $_GPC['gsid'];
        $goodnum = $_GPC['goodnum'];
        $data = array();
        $data['r_goodnum'] = $goodnum;
        $arr = array();
        //更新点赞
        $result = pdo_update($table, $data, array('uniacid' => $_W['uniacid'], 'r_id' => $g_id));
        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '操作成功';
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '插操失败' . $result;
        }

        echo json_encode($arr);
    }
    //留言
    public function doPagePostliuyuan()
    {
        global $_W, $_GPC;
        $table = "story_liuyan";
        $data = array();
        $data['uniacid'] = $_W['uniacid'];
        $gid=$_GPC['gid'];
        if (empty($gid)) {
            $arr['status'] = -1;
            $arr['message'] = "无法获取到投诉的故事id";
            echo json_encode($arr);
            exit();
        }
        if (empty($_GPC['title'])) {
            $arr['status'] = -1;
            $arr['message'] = "提交失败，标题不可为空";
            echo json_encode($arr);
            exit();
        }
        if (empty($_GPC['phone'])) {
            $arr['status'] = -1;
            $arr['message'] = "提交失败，手机号不可为空";
            echo json_encode($arr);
            exit();
        }
        if (empty($_GPC['content'])) {
            $arr['status'] = -1;
            $arr['message'] = "提交失败，内容不可为空";
            echo json_encode($arr);
            exit();
        }
        if (empty($_GPC['u_id'])) {
            $arr['status'] = -1;
            $arr['message'] = "提交失败，用户id不可为空";
            echo json_encode($arr);
            exit();
        }
        $data['l_title'] = $_GPC['title'];
        $data['gid'] = $gid;
        $data['phone'] = $_GPC['phone'];
        $data['content'] = $_GPC['content'];
        $data['uid'] = $_GPC['u_id'];
        $data['l_addtime'] = time();
        $res = pdo_insert($table, $data);
        $arr = array();
        if ($res) {
            $arr['status'] = 1;
            $arr['message'] = "提交成功";
        } else {
            $arr['status'] = -1;
            $arr['message'] = "提交失败";
        }
        echo json_encode($arr);
    }

    //保存评论
    public function doPagePostpinlun()
    {
        global $_W, $_GPC;
        $table = "story_pinlun";
        $data = array();
        $data['uniacid'] = $_W['uniacid'];
        $content = $_GPC['comment'];
        $uid = $_GPC['uid'];
        $f_uid = $_GPC['f_uid'];
        $headerimg = $_GPC['headerimg'];
        $nickname = $_GPC['nickname'];
        $g_id = $_GPC['gsid'];
        $ptype=$_GPC['ptype'];
        if (empty($content)) {
            $arr['status'] = 0;
            $arr['message'] = "提交失败，内容不可为空";
            echo json_encode($arr);
            exit();
        }
        if (empty($uid)) {
            $arr['status'] = 0;
            $arr['message'] = "提交失败，用户id不可为空";
            echo json_encode($arr);
            exit();
        }
        if (empty($g_id)) {
            $arr['status'] = 0;
            $arr['message'] = "提交失败，故事id不可为空";
            echo json_encode($arr);
            exit();
        }
        if (empty($f_uid)) {
            $arr['status'] = 0;
            $arr['message'] = "提交失败，主播id不可为空";
            echo json_encode($arr);
            exit();
        }
        if (empty($headerimg)) {
            $arr['status'] = 0;
            $arr['message'] = "提交失败，头像不可为空";
            echo json_encode($arr);
            exit();
        }
        if (empty($nickname)) {
            $arr['status'] = 0;
            $arr['message'] = "提交失败，昵称不可为空";
            echo json_encode($arr);
            exit();
        }
        $data['content'] = $content;
        $data['g_id'] = $g_id;
        $data['ptype'] = $ptype;
        $data['nickname'] = $nickname;
        $data['headerimg'] = $headerimg;
        $data['uid'] = $uid;
        $data['f_uid'] = $f_uid;
        $data['gp_addtime'] = time();
        $res = pdo_insert($table, $data);
        $arr = array();
        if ($res) {
            $arr['status'] = 1;
            $arr['message'] = "提交成功";
        } else {
            $arr['status'] = 0;
            $arr['message'] = "提交失败";
        }
        echo json_encode($arr);
    }

    //返回故事中的评论列表
    public function doPageGetgushipinlun()
    {
        global $_W, $_GPC;
        $table = "story_pinlun";
        $g_id = $_GPC['g_id'];
        $arr = array();
        $ptype=$_GPC['ptype'];//评论对象
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `g_id`=:gid and ptype=:ptype order by gp_addtime desc";

        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':gid' => $g_id,
            ':ptype'=>$ptype
        );
        $result = pdo_fetchall($sql, $params);
        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '获取数据成功';
            foreach ($result as $k => $v) {
                $result[$k]['addtime'] = date("Y-m-d", $v['gp_addtime']);

            }
            $arr["data"] = $result;
            return json_encode($arr);
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '数据为空';
            $arr['data'] = array();
            return json_encode($arr);
            exit();
        }
    }

    //获取小程序二维码
    public function doPageGetqrcode()
    {
        global $_W, $_GPC;
        $gsid = $_GPC['gsid'];
        $data = array(
            'scene' => intval($gsid),
            'page' => 'pages/story-detail/story-detail',
            'width' => 430,
            'auto_color' => true,
            'is_hyaline' => false,
        );
//        $data=array(
//            'path'=>'pages/index/index',
//            'width'=>430,
//        );
        //正式的
        $requestpath = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $this->getNewaccessToken();
        //测试的
        //$requestpath='https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token='.$this->getNewaccessToken();
        header("Content-type: image/jpg");
        $ewm_res = $this->api_notice_increment($requestpath, json_encode($data));
        $picture_attach = 'tonghuagushi/testqrcode_' . $_W['timestamp'] . '.png';
        file_put_contents(ATTACHMENT_ROOT . $picture_attach, $ewm_res);
//        file_put_contents(ATTACHMENT_ROOT . 'tonghuagushi/qrcodeinfo.txt', $ewm_res);
        $data = array();
        $data['img'] = tomedia($picture_attach);
        echo json_encode($data);
    }

    //获取阅读故事的小程序二维码
    public function doPageGetqrcoderead()
    {
        global $_W, $_GPC;
        $gsid = $_GPC['gsid'];
        $gspid = $_GPC['pid'];
        $data = array(
            'scene' => intval($gsid).','.intval($gspid),
            'page' => 'pages/read-voice/read-voice',
            'width' => 430,
            'auto_color' => true,
            'is_hyaline' => false,
        );
        //正式的
        $requestpath = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $this->getNewaccessToken();
        //测试的
        //$requestpath='https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token='.$this->getNewaccessToken();
        header("Content-type: image/jpg");
        $ewm_res = $this->api_notice_increment($requestpath, json_encode($data));
        $picture_attach = 'tonghuagushi/testqrcode_' . $_W['timestamp'] . '.png';
        file_put_contents(ATTACHMENT_ROOT . $picture_attach, $ewm_res);
        $data = array();
        $data['img'] = tomedia($picture_attach);
        echo json_encode($data);
    }
    //返回分享后二维码
    public function doPageGetshareafter()
    {
        global $_W, $_GPC;
        $table = "story_shareafter";

        $arr = array();
        $sql = "select * from " . tablename($table) . "where uniacid=:uniacid";
        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $result = pdo_fetchall($sql, $params);
        if ($result) {
            $arr['status'] = 1;
            $arr['msg'] = '获取数据成功';
            if (is_array($result)) {
                $arr["data"] = $result;
            } else {
                $arr["data"] = $result;
            }
            return json_encode($arr);
            exit();
        } else {
            $arr['status'] = 0;
            $arr['msg'] = '数据为空';
            $arr['data'] = array();
            return json_encode($arr);
            exit();
        }
    }

    function cut_str($string, $sublen, $start = 0, $code = 'UTF-8')

    {

        if ($code == 'UTF-8') {

            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";

            preg_match_all($pa, $string, $t_string);


            if (count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen)) . "...";

            return join('', array_slice($t_string[0], $start, $sublen));

        } else {

            $start = $start * 2;

            $sublen = $sublen * 2;

            $strlen = strlen($string);

            $tmpstr = '';


            for ($i = 0; $i < $strlen; $i++) {

                if ($i >= $start && $i < ($start + $sublen)) {

                    if (ord(substr($string, $i, 1)) > 129) {

                        $tmpstr .= substr($string, $i, 2);

                    } else {

                        $tmpstr .= substr($string, $i, 1);

                    }

                }

                if (ord(substr($string, $i, 1)) > 129) $i++;

            }

            if (strlen($tmpstr) < $strlen) $tmpstr .= "...";

            return $tmpstr;

        }

    }

    function json2array($json)

    {

        return json_decode($json, true);

    }

    private function get_php_file($filename)

    {

        return trim(substr(file_get_contents($filename), 15));

    }

    //设置responsemsg到文件
    private function set_php_file($filename, $content)

    {

        $fp = fopen($filename, "w");

        fwrite($fp, "" . $content);

        fclose($fp);

    }

    /**
     * 用于获取二维码函数
     * @param  [type] $url  [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    function api_notice_increment($url, $data)
    {
        $ch = curl_init();
        $header = ["Content-type: image/jpg"];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        curl_close($ch);

        return $tmpInfo;
    }

    /**
     * @param $appid 程序appid
     * @param $secret 密钥
     * @param $js_code 获取的code
     * @return mixed openid
     */

    public function getOpendId($appid, $secret, $js_code)

    {


        $urls = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $appid . "&secret=" . $secret . "&js_code=" . $js_code . "&grant_type=authorization_code";


        $html = file_get_contents($urls);


        $getcode = json_decode($html);

        return $getcode->openid;

    }

    //获取accessToken

    /**
     * @return mixed
     */
    //得到有效的accessToken
    public function getNewaccessToken()
    {
        $file = ATTACHMENT_ROOT . 'tonghuagushi/' . 'token.txt';
        $jsonStr = file_get_contents($file);
        if ($jsonStr) {
            $reArr = json_decode($jsonStr, true);
            if ($reArr['maxTime'] > time()) {
                //大于当前时间，还未过期，直接返回
                return $reArr['access_token'];
            }
        }
        //不存在或者过期了，重新获取
        $getsiteInfo = $this->getSiteinfo();//获取配置信息
        $appid = $getsiteInfo['appid'];
        $secret = $getsiteInfo['appsecret'];
        $reArr = $this->getAccessToken($appid, $secret);
        $reArr['maxTime'] = time() + $reArr['expires_in'];
        file_put_contents($file, json_encode($reArr));
        return $reArr['access_token'];
    }

    public function getAccessToken($appid, $secret)

    {

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;

        $con = file_get_contents($url);

        $getcode = json_decode($con);
        $data = array();
        $data['access_token'] = $getcode->access_token;
        $data['expires_in'] = $getcode->expires_in;

        return $data;


    }
    //post请求封装方法
    function request_post($url = '', $post_data = array()) {
        if (empty($url) || empty($post_data)) {
            return false;
        }

        $o = "";
        foreach ( $post_data as $k => $v )
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);

        $postUrl = $url;
        $curlPost = $post_data;
        $ch = curl_init();//初始化curl
        curl_setopt ( $ch, CURLOPT_SAFE_UPLOAD, false);
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

        return $data;
    }
    //curl方法上传文件
    public  function curlpost($url = '', $post_data = array()){
        if (empty($url) || empty($post_data)) {
            return false;
        }
        $ch = curl_init();
        $filePath = '/home/vagrant/test.png';
        $data     = array('name' => 'media', 'file' => '@' . $filePath);

        //兼容5.0-5.6版本的curl
        if (class_exists('\CURLFile')) {
            $data['file'] = new \CURLFile(realpath($filePath));
        } else {
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, FALSE);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_exec($ch);
    }
    /**将数组按字母A-Z排序SORT_ASC  Z-A  SORT_DESC
     * @param $arraystr 需要排序的数组
     * @param $totalstr 排序字段
     * @return array
     */
    function chartSort($arraystr,$totalstr){

        foreach ($arraystr as $k => $v) {
            //$v['chart']=$this->getFirstChart( $v[$totalstr] );
            $arraystr[$k]['chart']=$this->getFirstChart( $v[$totalstr] );

        }
       // var_dump($arraystr);
        $data=array();
        $flag=array();
        foreach ($arraystr as $k => $v) {
            if ( empty( $v['chart'] ) ) {
                $data[$v['chart']]=[];
            }else{
                $flag[]=$v['chart'];
            }

        }
      //var_dump($flag);
        array_multisort($flag,SORT_ASC,$arraystr);
        return $arraystr;
    }
    /**
     * 返回取汉字的第一个字的首字母
     * @param  [type] $str [string]
     * @return [type]      [strind]
     */
    function getFirstChart($str){
        if( empty($str) ){
            return '';
        }

        $char=ord($str);
        if( $char >= ord('A') && $char <= ord('z') ){
            return strtoupper($str);
        }
        $s1=iconv('UTF-8','gb2312',$str);
        $s2=iconv('gb2312','UTF-8',$s1);
        $s=$s2==$str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319&&$asc<=-20284) return 'A';
        if($asc>=-20283&&$asc<=-19776) return 'B';
        if($asc>=-19775&&$asc<=-19219) return 'C';
        if($asc>=-19218&&$asc<=-18711) return 'D';
        if($asc>=-18710&&$asc<=-18527) return 'E';
        if($asc>=-18526&&$asc<=-18240) return 'F';
        if($asc>=-18239&&$asc<=-17923) return 'G';
        if($asc>=-17922&&$asc<=-17418) return 'H';
        if($asc>=-17417&&$asc<=-16475) return 'J';
        if($asc>=-16474&&$asc<=-16213) return 'K';
        if($asc>=-16212&&$asc<=-15641) return 'L';
        if($asc>=-15640&&$asc<=-15166) return 'M';
        if($asc>=-15165&&$asc<=-14923) return 'N';
        if($asc>=-14922&&$asc<=-14915) return 'O';
        if($asc>=-14914&&$asc<=-14631) return 'P';
        if($asc>=-14630&&$asc<=-14150) return 'Q';
        if($asc>=-14149&&$asc<=-14091) return 'R';
        if($asc>=-14090&&$asc<=-13319) return 'S';
        if($asc>=-13318&&$asc<=-12839) return 'T';
        if($asc>=-12838&&$asc<=-12557) return 'W';
        if($asc>=-12556&&$asc<=-11848) return 'X';
        if($asc>=-11847&&$asc<=-11056) return 'Y';
        if($asc>=-11055&&$asc<=-10247) return 'Z';
        return null;
    }

}
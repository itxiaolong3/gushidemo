<?php
/**
 * 童话故事模块微站定义
 *
 * @author panshikj
 * @url http://www.zunyangkj.com
 */
defined('IN_IA') or exit('Access Denied');
class TonghuagushiModuleSite extends WeModuleSite
{
    //入口，配置信息
    public function doWebSite()
    {
        global $_W, $_GPC;
        $table = "story_config";
        $code = "site";
        $sql = "SELECT * FROM " . tablename($table) . " WHERE uniacid = :uniacid AND code = :code";
        $params = array(':uniacid' => $_W['uniacid'], ':code' => $code);
        $setting = pdo_fetch($sql, $params);
        $item = iunserializer($setting['value']);
        if ($_W['ispost']) {

            $data = array();
            $data['uniacid'] = $_W['uniacid'];
            $data['code'] = $code;
            $data['value'] = iserializer($_POST);
            if (empty($setting)) {
                pdo_insert($table, $data);
            } else {
                pdo_update($table, $data, array('id' => $setting['id']));
            }

            message('提交成功', referer(), success);
        }

        include $this->template('site');
    }
    //广告
    public function doWebAd()
    {
        global $_W, $_GPC;
        $table = "story_adinfo";
        $sql = "SELECT * FROM " . tablename($table) . " WHERE uniacid = :uniacid";
        $params = array(':uniacid' => $_W['uniacid']);
        $adinfo = pdo_fetchall($sql, $params);
        include $this->template('ad');
    }
    //添加广告
    public function doWebAddad()
    {
        global $_W, $_GPC;
        $table = "story_adinfo";
        if (!$_W['ispost']) {
            include $this->template("addad");
        } else {
            $arr = array();
            if (empty($_GPC['title'])) {
                message("广告标题不能为空", referer(), 'error');
            } else {
                $arr['title'] = $_GPC['title'];
            }
            if (empty($_GPC['contents'])) {
                message("广告内容不能为空", referer(), 'error');
            } else {
                $arr['contents'] = $_GPC['contents'];
            }
            $arr['uniacid'] = $_W['uniacid'];
            $arr['addtime'] = time();
            $add_result = pdo_insert($table, $arr);
            if (!empty($add_result)) {
                message("添加成功", $this->createWebUrl('ad'), 'success');
            } else {
                message("添加失败", referer(), 'error');
            }
        }
    }
    //编辑广告
    public function doWebEditad()
    {
        global $_W, $_GPC;
        $table = "story_adinfo";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `aid`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message('数据不存在', referer(), 'error');
            } else {
                if ($_W['ispost']) {
                    $arr = array();
                    if (empty($_GPC['title'])) {
                        message("广告标题不能为空", referer(), 'error');
                    } else {
                        $arr['title'] = $_GPC['title'];
                    }
                    if (empty($_GPC['contents'])) {
                        message("广告内容不能为空", referer(), 'error');
                    } else {
                        $arr['contents'] = $_GPC['contents'];
                    }
                    $arr['addtime']=time();
                    $edit_result = pdo_update($table, $arr, array('uniacid' => $_W['uniacid'], 'aid' => $id));

                    if (!empty($edit_result)) {
                        message("编辑成功", $this->createWebUrl('ad'), 'success');
                    } else {
                        message("编辑失败", referer(), 'error');
                    }
                }
                include $this->template('editad');
            }
        }

    }
    //删除广告
    public function doWebDelad()
    {
        global $_W, $_GPC;
        $table = "story_adinfo";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message('参数错误', referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `aid`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("数据不存在", referer(), 'error');
            } else {
                $del_result = pdo_delete($table, array('uniacid' => $_W['uniacid'], 'id' => $id));
                if ($del_result) {
                    message("删除成功", $this->createWebUrl('ad'), 'success');
                } else {
                    message("删除失败", referer(), 'error');
                }
            }
        }
    }
    //专辑列表
    public function doWebZhuanjilist()
    {
        global $_W, $_GPC;
        $table = "story_zhuanji";
        $sql = "SELECT * FROM " . tablename($table) . " WHERE uniacid = :uniacid";
        $params = array(':uniacid' => $_W['uniacid']);
        $zhuanjiinfo = pdo_fetchall($sql, $params);
        include $this->template('zhuanji');
    }
    //添加专辑
    public function doWebAddzhuanji()
    {
        global $_W, $_GPC;
        $table = "story_zhuanji";
        if (!$_W['ispost']) {
            include $this->template("addzhuanji");
        } else {
            $arr = array();
            if (empty($_GPC['zj_img'])) {
                message("封面图不能为空", referer(), 'error');
            } else {
                $arr['zj_img'] = $_GPC['zj_img'];
            }
            if (empty($_GPC['contents'])) {
                message("专辑内容不能为空", referer(), 'error');
            } else {
                $arr['zj_content'] = $_GPC['contents'];
            }
            $arr['uniacid'] = $_W['uniacid'];
            $arr['zj_addtime'] = time();
            $arr['zj_desc'] = $_GPC['zj_desc'];
            $add_result = pdo_insert($table, $arr);
            if (!empty($add_result)) {
                message("添加成功", $this->createWebUrl('zhuanjilist'), 'success');
            } else {
                message("添加失败", referer(), 'error');
            }
        }
    }
    //编辑专辑
    public function doWebEditzhuanji()
    {
        global $_W, $_GPC;
        $table = "story_zhuanji";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `zj_id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message('数据不存在', referer(), 'error');
            } else {
                if ($_W['ispost']) {
                    $arr = array();
                    if (empty($_GPC['zj_img'])) {
                        message("封面不能为空", referer(), 'error');
                    } else {
                        $arr['zj_img'] = $_GPC['zj_img'];
                    }
                    if (empty($_GPC['contents'])) {
                        message("专辑内容不能为空", referer(), 'error');
                    } else {
                        $arr['zj_content'] = $_GPC['contents'];
                    }
                    $arr['zj_addtime']=time();
                    $arr['zj_desc'] = $_GPC['zj_desc'];
                    $edit_result = pdo_update($table, $arr, array('uniacid' => $_W['uniacid'], 'zj_id' => $id));

                    if (!empty($edit_result)) {
                        message("编辑成功", $this->createWebUrl('zhuanjilist'), 'success');
                    } else {
                        message("编辑失败", referer(), 'error');
                    }
                }
                include $this->template('editzhuanji');
            }
        }

    }
    //删除专辑
    public function doWebDelzhuanji()
    {
        global $_W, $_GPC;
        $table = "story_zhuanji";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message('参数错误', referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `zj_id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("数据不存在", referer(), 'error');
            } else {
                $del_result = pdo_delete($table, array('uniacid' => $_W['uniacid'], 'zj_id' => $id));
                if ($del_result) {
                    message("删除成功", $this->createWebUrl('zhuanjilist'), 'success');
                } else {
                    message("删除失败", referer(), 'error');
                }
            }
        }
    }
    //骗审设置
    public function doWebPianshen()
    {
        global $_W, $_GPC;
        $table = "story_shen";
        $sql = "SELECT * FROM " . tablename($table) . " WHERE uniacid = :uniacid";
        $params = array(':uniacid' => $_W['uniacid']);
        $setting = pdo_fetch($sql, $params);
        $item = $setting;
        if ($_W['ispost']) {

            $data = array();
            $data['uniacid'] = $_W['uniacid'];
            $data['status'] = $_GPC['status'];
            $data['addtime']=time();
            if (empty($setting)) {
                pdo_insert($table, $data);
            } else {
                pdo_update($table, $data, array('id' => $setting['id']));
            }

            message('提交成功', referer(), success);
        }

        include $this->template('pianshen');
    }
    /*轮播图*/
    public function doWebBannerlist(){
        global $_W, $_GPC;
        load()->func('communication');
        load()->library('qrcode');
        $table = "story_banner";
        $table2 = "story_adinfo";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
       $getba=pdo_getall('story_banner',array('uniacid'=>$_W['uniacid']));
      
        foreach ($getba as $k=>$v){
            if ($v['adid']){
              $dd=pdo_get('story_adinfo',array('uniacid'=>$_W['uniacid'],'aid'=>$v['adid']));
                $getba[$k]['title']=$dd['title'];
            }
        }
      $banner_list = $getba;
       // $sql = "select * from " . tablename($table).'b,'.tablename($table2) . " ad where b.uniacid=:uniacid and b.adid=ad.aid  order by b.addtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        //$sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid  order by addtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $params = array(
            ':uniacid' => $_W['uniacid']
        );

        
        //var_dump($banner_list);exit;
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        $picture_attach = 'tonghuagushi/xiaolongtest_' . $_W['timestamp'] . '.png';
        $res=QRcode::png('http://www.baidu.com', ATTACHMENT_ROOT . $picture_attach);
        $path=tomedia($picture_attach);
        $ress=$res;
        include $this->template("banner");
    }

    //添加轮播图
    public function doWebAddbanner()
    {
        global $_W, $_GPC;
        $table = "story_banner";
        $table2 = "story_adinfo";
        $sql2 = "select * from " . tablename($table2) . " where `uniacid`=:uniacid";
        $params1 = array(
            ':uniacid' => $_W['uniacid']
        );
        $adlist = pdo_fetchall($sql2, $params1);
        $zjlist=pdo_getall('story_zhuanji',array('uniacid'=>$_W['uniacid']));
        if (!$_W['ispost']) {
            include $this->template("addbanner");
        } else {
            $arr = array();
            if (empty($_GPC['comment'])) {
                message("图片描述不能为空", referer(), 'error');
            } else {
                $arr['comment'] = $_GPC['comment'];
            }
           
            if (empty($_GPC['img'])) {
                message("轮播图不能为空", referer(), 'error');
            } else {
                $arr['img'] = $_GPC['img'];
            }
			if ($_GPC['item']==2){
                if (empty($_GPC['gourl'])) {
                    message("appid不能为空", referer(), 'error');
                } else {
                    $arr['gourl'] = $_GPC['gourl'];
                }
            }
            if ($_GPC['item']==0){
                $idname=$_GPC['zj'];
                $getidname=explode('|',$idname);
                $zjid=$getidname[0];
                $zjtitle=$getidname[1];
                $arr['zjid']=$zjid;
                $arr['zjtitle']=$zjtitle;
              
            }
            $arr['uniacid'] = $_W['uniacid'];
            $arr['addtime'] = time();
            $arr['position']=$_GPC['position'];
       
            $arr['adid']=$_GPC['adid'];
          
          $arr['type']=$_GPC['item'];
            $add_result = pdo_insert($table, $arr);
            if (!empty($add_result)) {
                message("添加成功", $this->createWebUrl('bannerlist'), 'success');
            } else {
                message("添加失败", referer(), 'error');
            }
        }
    }
    //编辑轮播图
    public function doWebEditbanner()
    {
        global $_W, $_GPC;
        $table = "story_banner";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:id";
            $table2 = "story_adinfo";
            $sql2 = "select * from " . tablename($table2) . " where `uniacid`=:uniacid";
            $params1 = array(
                ':uniacid' => $_W['uniacid']
            );
            $adlist = pdo_fetchall($sql2, $params1);
            $zjlist=pdo_getall('story_zhuanji',array('uniacid'=>$_W['uniacid']));
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message('数据不存在', referer(), 'error');
            } else {
                if ($_W['ispost']) {
                    $arr = array();
                    if (empty($_GPC['comment'])) {
                        message("图片描述不能为空", referer(), 'error');
                    } else {
                        $arr['comment'] = $_GPC['comment'];
                    }

                    if (empty($_GPC['img'])) {
                        message("轮播图不能为空", referer(), 'error');
                    } else {
                        $arr['img'] = $_GPC['img'];
                    }
                    if ($_GPC['item']==2){
                        if (empty($_GPC['gourl'])) {
                            message("appid不能为空", referer(), 'error');
                        } else {
                            $arr['gourl'] = $_GPC['gourl'];
                        }
                    }
                    if ($_GPC['item']==0){
                        $idname=$_GPC['zj'];
                        $getidname=explode('|',$idname);
                        $zjid=$getidname[0];
                        $zjtitle=$getidname[1];
                        $arr['zjid']=$zjid;
                        $arr['zjtitle']=$zjtitle;

                    }
                    $arr['position']=$_GPC['position'];
                    $arr['adid']=$_GPC['adid'];
                    $arr['addtime']=time();
                    
                    $edit_result = pdo_update($table, $arr, array('uniacid' => $_W['uniacid'], 'id' => $id));
                    if (!empty($edit_result)) {
                        message("编辑成功", $this->createWebUrl('bannerlist'), 'success');
                    } else {
                        message("编辑失败", referer(), 'error');
                    }
                }
                include $this->template('editbanner');
            }
        }

    }
    //删除轮播图
    public function doWebDelbanner()
    {
        global $_W, $_GPC;
        $table = "story_banner";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message('参数错误', referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("数据不存在", referer(), 'error');
            } else {
                $del_result = pdo_delete($table, array('uniacid' => $_W['uniacid'], 'id' => $id));
                if ($del_result) {
                    message("删除成功", $this->createWebUrl('bannerlist'), 'success');
                } else {
                    message("删除失败", referer(), 'error');
                }
            }
        }
    }

    //图库列表
    public function doWebTukulist()
    {
        global $_W, $_GPC;
        $table = "story_tuku";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
     	$getkeyword=$_GPC['keywords'];
        $where=" imgtitle LIKE '%".$getkeyword."%'";
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and ".$where."  order by t_addtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $result_list = pdo_fetchall($sql, $params);

        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("tukulist");

    }
    //添加在线图库图片
    public function doWebAddtuku()
    {
        global $_W, $_GPC;
        $tablename = "story_tuku";
        $table2 = "story_tukufenlei";
        $sql2 = "select * from " . tablename($table2) . " where `uniacid`=:uniacid  order by fl_addtime asc  ";
        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $tukufenleilist = pdo_fetchall($sql2, $params);
        if (!$_W['ispost']) {
            include $this->template('addtuku');
        } else {

            $arr = array();
            $arr['uniacid'] = $_W['uniacid'];
            $gett_id=$_GPC['t_id'];
            $gettkarrary=explode('|',$gett_id);
            $gettkid=$gettkarrary[0];
            $gettk_name=$gettkarrary[1];
            if (empty($_GPC['imgtitle'])) {
                message('图片标题不能为空', referer(), 'error');
            } else {
                $arr['imgtitle'] = $_GPC['imgtitle'];
            }
            if (empty($gettk_name)) {
                message('图片类型不可为空', referer(), 'error');
            } else {
                $arr['type'] = $gettk_name;
            }
            if (empty($_GPC['imgurl'])) {
                message('图片不能为空', referer(), 'error');
            } else {
                $arr['imgurl'] = $_GPC['imgurl'];
            }

            $arr['t_addtime'] = time();
            $add_result = pdo_insert('story_tuku', $arr);
            if (!empty($add_result)) {
                message('添加成功', $this->createWebUrl('Tukulist', array('type' => $arr['type'])), 'success');
            } else {
                message('添加失败', referer(), 'error');
            }
        }
    }
    //编辑图库图片
    public function doWebEdittuku()
    {
        global $_W, $_GPC;
        $table = "story_tuku";
        $id = intval($_GPC['id']);
        if ($id == "") {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("信息不存在", referer(), 'error');
            } else {
                $table2 = "story_tukufenlei";
                $tkfenleisql2 = "select * from " . tablename($table2) . " where `uniacid`=:uniacid  order by fl_addtime asc  ";
                $params = array(
                    ':uniacid' => $_W['uniacid']
                );
                $tkfenleilist = pdo_fetchall($tkfenleisql2, $params);
                if ($_W['ispost']) {

                    $arr = array();
                    $arr['uniacid'] = $_W['uniacid'];
                    $gett_id=$_GPC['t_id'];
                    $gettkarrary=explode('|',$gett_id);
                    $gettkid=$gettkarrary[0];
                    $gettk_name=$gettkarrary[1];
                    if (empty($_GPC['imgtitle'])) {
                        message('图片标题不能为空', referer(), 'error');
                    } else {
                        $arr['imgtitle'] = $_GPC['imgtitle'];
                    }
                    if (empty($gettk_name)) {
                        message('图片类型不可为空', referer(), 'error');
                    } else {
                        $arr['type'] = $gettk_name;
                    }

                    if (empty($_GPC['imgurl'])) {
                        message('请选择图片', referer(), 'error');
                    } else {
                        $arr['imgurl'] = $_GPC['imgurl'];
                    }

                    //$arr['addtime']=time();
                    $edit_result = pdo_update('story_tuku', $arr, array('uniacid' => $_W['uniacid'], 'id' => $id));
                    if (!empty($edit_result)) {
                        message('编辑成功', $this->createWebUrl('tukulist'), 'success');
                    } else {
                        message('编辑失败', referer(), 'error');
                    }
                } else {

                    include $this->template('edittuku');
                }

            }

        }
    }
    //删除图库图片
    public function doWebDeltuku()
    {
        global $_W, $_GPC;
        $table = "story_tuku";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message('参数错误', referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("数据不存在", referer(), 'error');
            } else {
                $del_result = pdo_delete($table, array('uniacid' => $_W['uniacid'], 'id' => $id));
                if ($del_result) {
                    message('删除成功', $this->createWebUrl('tukulist', array('type' => $result['type'])), 'success');
                } else {
                    message("删除失败", referer(), 'error');
                }
            }

        }
    }

    /*图库分类列表*/
    public function doWebTukufenleilist()
    {
        global $_W, $_GPC;
        $table = "story_tukufenlei";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid  order by fl_addtime asc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $fenlei_list = pdo_fetchall($sql, $params);
        //var_dump($fenlei_list);exit;
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("tukufenlei");
    }
    /*添加图库分类*/
    public function doWebAddtukufenlei()
    {
        global $_W, $_GPC;
        $table = "story_tukufenlei";
        if (!$_W['ispost']) {
            include $this->template("addtukufenlei");
        } else {
            $arr = array();
            if (empty($_GPC['typename'])) {
                message("分类名称不能为空", referer(), 'error');
            } else {
                $arr['typename'] = $_GPC['typename'];
            }

            $arr['uniacid'] = $_W['uniacid'];
            $arr['fl_addtime'] = time();
            $add_result = pdo_insert($table, $arr);
            if (!empty($add_result)) {
                message("添加成功", $this->createWebUrl('tukufenleilist'), 'success');
            } else {
                message("添加失败", referer(), 'error');
            }
        }
    }
    /*编辑图库分类*/
    public function doWebEdittukufenlei()
    {
        global $_W, $_GPC;
        $table = "story_tukufenlei";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:c_id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':c_id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message('数据不存在', referer(), 'error');
            } else {
                if ($_W['ispost']) {
                    $arr = array();
                    if (empty($_GPC['typename'])) {
                        message("分类名称不能为空", referer(), 'error');
                    } else {
                        $arr['typename'] = $_GPC['typename'];
                    }

                    $edit_result = pdo_update($table, $arr, array('uniacid' => $_W['uniacid'], 'id' => $id));
                    if (!empty($edit_result)) {
                        message("编辑成功", $this->createWebUrl('tukufenleilist'), 'success');
                    } else {
                        message("编辑失败", referer(), 'error');
                    }
                }
                include $this->template('edittukufenlei');
            }
        }

    }

    /*删除图库分类*/
    public function doWebDeltukufenlei()
    {
        global $_W, $_GPC;
        $table = "story_tukufenlei";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message('参数错误', referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:c_id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':c_id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("数据不存在", referer(), 'error');
            } else {
                $del_result = pdo_delete($table, array('uniacid' => $_W['uniacid'], 'id' => $id));
                if ($del_result) {
                    message("删除成功", $this->createWebUrl('tukufenleilist'), 'success');
                } else {
                    message("删除失败", referer(), 'error');
                }
            }
        }
    }

    /*背景音乐列表*/
    public function doWebBjmusiclist()
    {
        global $_W, $_GPC;
        $table = "story_bjmusic";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
     	$getkeyword=$_GPC['keywords'];
        $where=" name LIKE '%".$getkeyword."%'";
        $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid  and ".$where."  order by bj_addtime asc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $music_list = pdo_fetchall($sql, $params);
        //var_dump($fenlei_list);exit;
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("bjmusiclist");
    }
    /*添加背景音乐*/
    public function doWebAddbjmusic()
    {
        global $_W, $_GPC;
        $table = "story_bjmusic";
        if (!$_W['ispost']) {
            include $this->template("addbjmusic");
        } else {
            $arr = array();
            if (empty($_GPC['name'])) {
                message("音乐名称不能为空", referer(), 'error');
            } else {
                $arr['name'] = $_GPC['name'];
            }
            if (empty($_GPC['bjmusic'])) {
                message("请上传音乐", referer(), 'error');
            } else {
                $arr['url'] = $_GPC['bjmusic'];
            }
            $arr['uniacid'] = $_W['uniacid'];
            $arr['bj_addtime'] = time();
            $add_result = pdo_insert($table, $arr);
            if (!empty($add_result)) {
                message("添加成功", $this->createWebUrl('bjmusiclist'), 'success');
            } else {
                message("添加失败", referer(), 'error');
            }
        }
    }

    /*编辑音乐*/
    public function doWebEditbjmusic()
    {
        global $_W, $_GPC;
        $table = "story_bjmusic";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:c_id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':c_id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message('数据不存在', referer(), 'error');
            } else {
                if ($_W['ispost']) {
                    $arr = array();
                    if (empty($_GPC['name'])) {
                        message("分类名称不能为空", referer(), 'error');
                    } else {
                        $arr['name'] = $_GPC['name'];
                    }
                    if (empty($_GPC['bjmusic'])) {
                        message("请上传音乐", referer(), 'error');
                    } else {
                        $arr['url'] = $_GPC['bjmusic'];
                    }
                    $arr['bj_addtime'] = time();
                    $edit_result = pdo_update($table, $arr, array('uniacid' => $_W['uniacid'], 'id' => $id));
                    if (!empty($edit_result)) {
                        message("编辑成功", $this->createWebUrl('bjmusiclist'), 'success');
                    } else {
                        message("编辑失败", referer(), 'error');
                    }
                }
                include $this->template('editbjmusic');
            }
        }

    }
    /*删除背景音乐*/
    public function doWebDelbjmusic()
    {
        global $_W, $_GPC;
        $table = "story_bjmusic";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message('参数错误', referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:c_id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':c_id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("数据不存在", referer(), 'error');
            } else {
                $del_result = pdo_delete($table, array('uniacid' => $_W['uniacid'], 'id' => $id));
                if ($del_result) {
                    message("删除成功", $this->createWebUrl('bjmusiclist'), 'success');
                } else {
                    message("删除失败", referer(), 'error');
                }
            }
        }
    }

    //审核故事
    public function doWebShgushi()
    {
        global $_W, $_GPC;
        $table = "story_gushi";
        $table1 = "story_user";
        $s = intval($_GPC['s']);
        $id = intval($_GPC['id']);
        $b_id = intval($_GPC['b_id']);
        //是否已经有首发故事
        $selectfristsql = "select * from" . tablename($table)." where uniacid=:uniacid and isfris=1 and status=1 and u_id=:b_id";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':b_id'=>$b_id
        );
        $resfrist = pdo_fetchall($selectfristsql, $params);
        if (empty($resfrist)){
            //没有首发故事
            $result = pdo_update($table, array('status' => $s,'isfrist'=>1), array('uniacid' => $_W['uniacid'], 'id' => $id));
        }else{
            $result = pdo_update($table, array('status' => $s,'isfrist'=>0), array('uniacid' => $_W['uniacid'], 'id' => $id));
        }
        $sql = "select gscount from" . tablename($table1)." where uniacid=:uniacid and u_id=:b_id";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':b_id'=>$b_id
        );
        $recount = pdo_fetch($sql, $params);
        $gscount=$recount['gscount']+1;
        $updatacount = pdo_update($table1, array('gscount' => $gscount), array('uniacid' => $_W['uniacid'], 'u_id' => $b_id));
        if (!empty($result)&& !empty($updatacount)) {
            message('操作成功', referer(), 'success');
        } else {
            message('操作失败', referer(), 'error');
        }

    }
    //审核图文故事
    public function doWebShreadgushi()
    {
        global $_W, $_GPC;
        $table = "story_gushiread";
        $table1 = "story_user";
        $table2 = "story_pictext";
        $s = intval($_GPC['s']);
        $id = intval($_GPC['id']);
        $b_id = intval($_GPC['b_id']);
        $gsid = intval($_GPC['gsid']);
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':b_id'=>$b_id
        );
        $result = pdo_update($table, array('status' => $s), array('uniacid' => $_W['uniacid'], 'r_id' => $id));

        $sql = "select gscount from" . tablename($table1)." where uniacid=:uniacid and u_id=:b_id";
        $sql2 = "select p_count from" . tablename($table2)." where uniacid=:uniacid and p_id=:p_id";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':b_id'=>$b_id
        );
        $params2 = array(
            ':uniacid' => $_W['uniacid'],
            ':p_id'=>$gsid
        );
        $recount = pdo_fetch($sql, $params);
        $count2 = pdo_fetch($sql2, $params2);
        $gscount=$recount['gscount']+1;
        $gsreadcount=$count2['p_count']+1;
        $updatacount = pdo_update($table1, array('gscount' => $gscount), array('uniacid' => $_W['uniacid'], 'u_id' => $b_id));
        $updatareadcount = pdo_update($table2, array('p_count' => $gsreadcount), array('uniacid' => $_W['uniacid'], 'p_id' => $gsid));
        if (!empty($result)&& !empty($updatacount)&& !empty($updatareadcount)) {
            message('操作成功', referer(), 'success');
        } else {
            message('操作失败', referer(), 'error');
        }

    }
    //顶置故事
    public function doWebTopgushi()
    {
        global $_W, $_GPC;
        $table = "story_gushi";
        $id = intval($_GPC['id']);
        $gstype = intval($_GPC['gstype']);
        //$moretopsql="select orders from ims_story_gushi ORDER BY orders desc limit 1";
        $sql = "select orders from" . tablename($table)." where uniacid=:uniacid and gstype=:gstype order by orders desc limit 1 ";
        $params = array(
            ':uniacid' => $_W['uniacid'],
            ':gstype'=>$gstype
        );
        $orders = pdo_fetch($sql, $params);
        $toporders=$orders['orders']+1;
        $result = pdo_update($table, array('orders' => $toporders), array('uniacid' => $_W['uniacid'], 'id' => $id));
        if ($result) {
            message('操作成功', referer(), 'success');
        } else {
            message('操作失败', referer(), 'error');
        }

    }
    //小主播故事列表
    public function doWebGushilist()
    {
        global $_W, $_GPC;
        $table = "story_gushi";
        $table1 = "story_user";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $getkeyword=$_GPC['keywords'];
        $where=" gs.title LIKE '%".$getkeyword."%'";
        $sql = "select * from " . tablename($table) . "gs,".tablename($table1)."u where gs.uniacid=:uniacid and  gs.b_id=u.u_id and gs.is_delete=0 and gs.zjid=0 and gs.gstype=0  and ".$where."  order by gs.orders desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $orderssql = "select orders from" . tablename($table)." where uniacid=:uniacid and gstype=0 order by orders desc limit 1 ";
		
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $orders = pdo_fetch($orderssql, $params);
        $result_list = pdo_fetchall($sql, $params);
        $imgs = array();
        foreach ($result_list as $kk=>$vv){
            $imgslist = explode(",", $vv['storyimg']);
            $imgs[$kk]=$imgslist;
        }
        // var_dump($imgs);
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid and is_delete=0 and zjid=0 and gstype=0";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("gushilist");

    }

    //编辑小主播故事
    public function doWebEditgushi()
    {
        global $_W, $_GPC;
        $table = "story_gushi";
        $id = intval($_GPC['id']);
        $iszj = intval($_GPC['iszj']);
        if ($id == "") {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            $getgstype=$result['gstype'];
            $orderssql = "select orders from" . tablename($table)." where uniacid=:uniacid and gstype=:gstype order by orders desc limit 1 ";
            $orderparams = array(
                ':uniacid' => $_W['uniacid'],
                ':gstype'=>$getgstype
            );
            $orders = pdo_fetch($orderssql, $orderparams);
            if (empty($result)) {
                message("信息不存在", referer(), 'error');
            } else {
                if ($_W['ispost']) {

                    $arr = array();
                    $arr['uniacid'] = $_W['uniacid'];
                    if (empty($_GPC['title'])) {
                        message('故事标题不能为空', referer(), 'error');
                    } else {
                        $arr['title'] = $_GPC['title'];
                    }
                    if (empty($_GPC['languages'])) {
                        message('语言类型不可为空', referer(), 'error');
                    } else {
                        $arr['languages'] = $_GPC['languages'];
                    }
                    $arr['orders'] = $_GPC['orders'];
                    $arr['goodnum'] = $_GPC['goodnum'];
                    $arr['listennum'] = $_GPC['listennum'];
                    if (empty($_GPC['storyimg']) || !is_array($_GPC['storyimg'])) {
                        message("请选择图片", referer(), 'error');
                    } else {
                        $thumbs_str = "";
                        foreach ($_GPC['storyimg'] as $k => $v) {
                            $thumbs_str .= $v . ",";
                        }
                        $arr['storyimg'] = $thumbs_str;
                    }
                    //$arr['addtime']=time();
                    $edit_result = pdo_update('story_gushi', $arr, array('uniacid' => $_W['uniacid'], 'id' => $id));
                    if (!empty($edit_result)) {
                        if ($iszj){
                            message('编辑成功', $this->createWebUrl('zhuanjigushilist'), 'success');
                        }else{
                            message('编辑成功', $this->createWebUrl('Gushilist'), 'success');
                        }

                    } else {
                        message('编辑失败,数据没变动', referer(), 'error');
                    }
                } else {
                    $storyimg = array();
                    $thumbs_list = explode(",", $result['storyimg']);
                    foreach ($thumbs_list as $k => $v) {
                        if (!empty($v)) {
                            $storyimg[$k] = $v;
                        }
                    }
                    include $this->template('editgushi');
                }

            }

        }
    }
    //专辑故事列表
    public function doWebZhuanjigushilist()
    {
        global $_W, $_GPC;
        $table = "story_gushi";
        $table1 = "story_user";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $getkeyword=$_GPC['keywords'];
        $where=" gs.title LIKE '%".$getkeyword."%'";
        $sql = "select * from " . tablename($table) . "gs,".tablename($table1)."u where gs.uniacid=:uniacid and  gs.b_id=u.u_id and gs.is_delete=0 and gs.gstype=0 and gs.zjid>0 and ".$where." order by gs.orders desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $orderssql = "select orders from" . tablename($table)." where uniacid=:uniacid and gstype=0 order by orders desc limit 1 ";

        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $orders = pdo_fetch($orderssql, $params);
        $result_list = pdo_fetchall($sql, $params);
        $imgs = array();
        foreach ($result_list as $kk=>$vv){
            $imgslist = explode(",", $vv['storyimg']);
            $imgs[$kk]=$imgslist;
        }
        // var_dump($imgs);
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid and zjid>0";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("zhuanjigushilist");

    }
    //专辑阅读故事列表
    public function doWebZhuanjigushireadlist()
    {
        global $_W, $_GPC;
        $table = "story_gushiread";
        $table1 = "story_user";
        $table2 = "story_pictext";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
      	$getkeyword=$_GPC['keywords'];
        $where=" pt.p_title LIKE '%".$getkeyword."%'";
        $sql = "select * from " . tablename($table) . "gs,".tablename($table2). "pt,".tablename($table1)."u where gs.uniacid=:uniacid and  gs.b_id=u.u_id and gs.gsid=pt.p_id and gs.is_delete=0 and gs.g_zjid>0 and ".$where." order by gs.orders desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $orderssql = "select orders from" . tablename($table)." where uniacid=:uniacid order by orders desc limit 1 ";

        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $orders = pdo_fetch($orderssql, $params);
        $result_list = pdo_fetchall($sql, $params);
        foreach ($result_list as $k=>$v){
            $result_list[$k]['p_toppic']=tomedia($v['p_toppic']);
        }
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid and g_zjid>0";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("zhuanjigushireadlist");

    }
    //编辑图文故事
    public function doWebEditreadgushi()
    {
        global $_W, $_GPC;
        $table = "story_gushiread";
        $id = intval($_GPC['id']);
        $iszjread=intval($_GPC['iszjread']);
        if ($id == "") {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `r_id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            $orderssql = "select orders from" . tablename($table)." where uniacid=:uniacid order by orders desc limit 1 ";
            $orderparams = array(
                ':uniacid' => $_W['uniacid'],
            );
            $orders = pdo_fetch($orderssql, $orderparams);
            if (empty($result)) {
                message("信息不存在", referer(), 'error');
            } else {
                if ($_W['ispost']) {
                    $arr = array();
                    $arr['uniacid'] = $_W['uniacid'];
                    $arr['orders'] = $_GPC['orders'];
                    $arr['r_goodnum'] = $_GPC['goodnum'];
                    $arr['r_listennum'] = $_GPC['listennum'];
                    $arr['r_addtime']=time();
                    $edit_result = pdo_update('story_gushiread', $arr, array('uniacid' => $_W['uniacid'], 'r_id' => $id));
                    if (!empty($edit_result)) {
                        if ($iszjread){
                            message('编辑成功', $this->createWebUrl('Zhuanjigushireadlist'), 'success');
                        }else{
                            message('编辑成功', $this->createWebUrl('Readgushilist'), 'success');
                        }

                    } else {
                        message('编辑失败,数据没变动', referer(), 'error');
                    }
                } else {
                    include $this->template('editreadgushi');
                }

            }

        }
    }
    //删除故事
    public function doWebDelgushi()
    {
        global $_W, $_GPC;
        $table = "story_gushi";
        $table1 = "story_user";
        $id = intval($_GPC['id']);
        $b_id = intval($_GPC['b_id']);
        $status = intval($_GPC['status']);
        $arr = array();
        if ($id == "") {
            message("参数错误", referer(), 'error');
        } else {
            $arr['is_delete'] = 1;
            $res = pdo_update($table, $arr, array('uniacid' => $_W['uniacid'], 'id' => $id));
            $sql = "select gscount from" . tablename($table1)." where uniacid=:uniacid and u_id=:b_id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':b_id'=>$b_id
            );
            $recount = pdo_fetch($sql, $params);
            $gscount=$recount['gscount']-1;
            if($status){//已经审核过的故事删除时才需要减1
                $updatacount = pdo_update($table1, array('gscount' => $gscount), array('uniacid' => $_W['uniacid'], 'u_id' => $b_id));
            }else{
                $updatacount=1;
            }

            if (!empty($res)&&!empty($updatacount)) {
                message("操作成功", referer(), 'success');
            } else {
                message("操作失败", referer(), 'error');
            }
        }
    }
    //删除图文故事
    public function doWebDelreadgushi()
    {
        global $_W, $_GPC;
        $table = "story_gushiread";
        $table1 = "story_pictext";
        $id = intval($_GPC['id']);
        $gsid = intval($_GPC['gsid']);
        $status = intval($_GPC['status']);
        $arr = array();
        if ($id == "") {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select p_count from" . tablename($table1)." where uniacid=:uniacid and p_id=:g_id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':g_id'=>$gsid
            );
            $recount = pdo_fetch($sql, $params);
            $gscount=$recount['p_count']-1;
            $res=pdo_delete($table, array('uniacid' => $_W['uniacid'], 'r_id' => $id));
            if($status){//已经审核过的故事删除时才需要减1
                $updatacount = pdo_update($table1, array('p_count' => $gscount), array('uniacid' => $_W['uniacid'], 'p_id' => $gsid));
            }else{
                $updatacount=1;
            }

            if (!empty($res)&&!empty($updatacount)) {
                message("操作成功", referer(), 'success');
            } else {
                message("操作失败", referer(), 'error');
            }
        }
    }
    //阅读故事列表
    public function doWebReadgushilist()
    {
        global $_W, $_GPC;
        $table = "story_gushiread";
        $table1 = "story_user";
        $table2 = "story_pictext";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
      	$getkeyword=$_GPC['keywords'];
        $where=" pt.p_title LIKE '%".$getkeyword."%'";
        $sql = "select * from " . tablename($table) . "gs,".tablename($table2). "pt,".tablename($table1)."u where gs.uniacid=:uniacid and  gs.b_id=u.u_id and gs.gsid=pt.p_id and gs.is_delete=0 and ".$where." order by gs.orders desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $orderssql = "select orders from" . tablename($table)." where uniacid=:uniacid order by orders desc limit 1 ";

        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $orders = pdo_fetch($orderssql, $params);
        $result_list = pdo_fetchall($sql, $params);
        foreach ($result_list as $k=>$v){
            $result_list[$k]['p_toppic']=tomedia($v['p_toppic']);
        }
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("readgushilist");

    }
    //官方故事列表
    public function doWebZhibuxionggushilist()
    {
        global $_W, $_GPC;
        $table = "story_gushi";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $getkeyword=$_GPC['keywords'];
        $where=" title LIKE '%".$getkeyword."%'";
        $sql = "select * from " . tablename($table) . " where uniacid=:uniacid and is_delete=0 and gstype=1 and ".$where."  order by orders desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $orderssql = "select orders from" . tablename($table)." where uniacid=:uniacid and gstype=1 order by orders desc limit 1 ";
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $orders = pdo_fetch($orderssql, $params);
        $result_list = pdo_fetchall($sql, $params);
        $imgs = array();
        foreach ($result_list as $kk=>$vv){
            $imgslist = explode(",", $vv['storyimg']);
            $imgs[$kk]=$imgslist;
        }
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("zhibuxionggushilist");

    }
    //添加官方故事
    public function doWebAddzbxgushi(){
        global $_W, $_GPC;
        $table = "story_gushi";
        $table1 = "story_user";
        if (!$_W['ispost']) {
            include $this->template("addzbxgushi");
        } else {
            $arr = array();
            if (empty($_GPC['title'])) {
                message("故事名称不能为空", referer(), 'error');
            } else {
                $arr['title'] = $_GPC['title'];
            }

            if (empty($_GPC['yuyinurl'])) {
                message("请上传故事", referer(), 'error');
            } else {
                $arr['yuyinurl'] = tomedia($_GPC['yuyinurl']);
            }
            if (empty($_GPC['storyimg']) || !is_array($_GPC['storyimg'])) {
                message("故事图集不能为空", referer(), 'error');
            } else {
                $thumbs_str = "";
                foreach ($_GPC['storyimg'] as $k => $v) {
                    $thumbs_str .= $v . ",";
                }
                $arr['storyimg'] = $thumbs_str;
            }
            $langagetyep=$_GPC['t_id'];
            if ($langagetyep=='其他'){
                $arr['languages']=$_GPC['languages'];
            }else{
                $arr['languages']=$langagetyep;
            }
            $arr['bgmusic']=tomedia($_GPC['bgmusic']);
            $arr['uniacid'] = $_W['uniacid'];
            $arr['g_addtime'] = time();
            $arr['gstype'] = 1;
            $arr['b_id'] = 1;
            $add_result = pdo_insert($table, $arr);
            if (!empty($add_result)) {
                message("添加成功", $this->createWebUrl('Zhibuxionggushilist'), 'success');
            } else {
                message("添加失败", referer(), 'error');
            }
        }
    }
    //编辑官方故事
    public function doWebEditzbxgushi()
    {
        global $_W, $_GPC;
        $table = "story_gushi";
        $id = intval($_GPC['id']);
        if ($id == "") {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            $getgstype=$result['gstype'];
            $orderssql = "select orders from" . tablename($table)." where uniacid=:uniacid and gstype=:gstype order by orders desc limit 1 ";
            $orderparams = array(
                ':uniacid' => $_W['uniacid'],
                ':gstype'=>$getgstype
            );
            $orders = pdo_fetch($orderssql, $orderparams);
            if (empty($result)) {
                message("信息不存在", referer(), 'error');
            } else {
                if ($_W['ispost']) {

                    $arr = array();
                    $arr['uniacid'] = $_W['uniacid'];
                    if (empty($_GPC['title'])) {
                        message('故事标题不能为空', referer(), 'error');
                    } else {
                        $arr['title'] = $_GPC['title'];
                    }
                    $langagetyep=$_GPC['t_id'];
                    if ($langagetyep=='其他'){
                        if (empty($_GPC['languages'])) {
                            message('语言类型不可为空', referer(), 'error');
                        } else{
                            $arr['languages']=$_GPC['languages'];
                        }
                    }else{
                        $arr['languages']=$langagetyep;
                    }
//                    if (empty($_GPC['languages'])) {
//                        message('语言类型不可为空', referer(), 'error');
//                    } else {
//                        $arr['languages'] = $_GPC['languages'];
//                    }
                    $arr['orders'] = $_GPC['orders'];
                    $arr['goodnum'] = $_GPC['goodnum'];
                    $arr['listennum'] = $_GPC['listennum'];
                    if (empty($_GPC['storyimg']) || !is_array($_GPC['storyimg'])) {
                        message("请选择图片", referer(), 'error');
                    } else {
                        $thumbs_str = "";
                        foreach ($_GPC['storyimg'] as $k => $v) {
                            $thumbs_str .= $v . ",";
                        }
                        $arr['storyimg'] = $thumbs_str;
                    }
                    //$arr['addtime']=time();
                    $edit_result = pdo_update('story_gushi', $arr, array('uniacid' => $_W['uniacid'], 'id' => $id));
                    if (!empty($edit_result)) {
                        message('编辑成功', $this->createWebUrl('Zhibuxionggushilist'), 'success');
                    } else {
                        message('编辑失败,数据没变动', referer(), 'error');
                    }
                } else {
                    $storyimg = array();
                    $thumbs_list = explode(",", $result['storyimg']);
                    foreach ($thumbs_list as $k => $v) {
                        if (!empty($v)) {
                            $storyimg[$k] = $v;
                        }
                    }
                    include $this->template('editgushi');
                }

            }

        }
    }
    //顶置推文
    public function doWebToptwgushi()
    {
        global $_W, $_GPC;
        $table = "story_pictext";
        $id = intval($_GPC['id']);
        $gstype = intval($_GPC['gstype']);
        //$moretopsql="select orders from ims_story_gushi ORDER BY orders desc limit 1";
        $sql = "select p_count from" . tablename($table)." where uniacid=:uniacid order by p_count desc limit 1 ";
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $orders = pdo_fetch($sql, $params);
        $toporders=$orders['p_count']+1;
        $result = pdo_update($table, array('p_count' => $toporders), array('uniacid' => $_W['uniacid'], 'p_id' => $id));
        if ($result) {
            message('操作成功', referer(), 'success');
        } else {
            message('操作失败', referer(), 'error');
        }

    }
    //顶置图文故事
    public function doWebTopreadgushi()
    {
        global $_W, $_GPC;
        $table = "story_gushiread";
        $id = intval($_GPC['id']);
        $gstype = intval($_GPC['gstype']);
        //$moretopsql="select orders from ims_story_gushi ORDER BY orders desc limit 1";
        $sql = "select p_count from" . tablename($table)." where uniacid=:uniacid order by orders desc limit 1 ";
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $orders = pdo_fetch($sql, $params);
        $toporders=$orders['orders']+1;
        $result = pdo_update($table, array('orders' => $toporders), array('uniacid' => $_W['uniacid'], 'r_id' => $id));
        if ($result) {
            message('操作成功', referer(), 'success');
        } else {
            message('操作失败', referer(), 'error');
        }

    }
    //官方图文列表
    public function doWebTuwenlist()
    {
        global $_W, $_GPC;
        $table = "story_pictext";
        $table2 = "story_gushiread";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
     	$getkeyword=$_GPC['keywords'];
		$where=" p_title LIKE '%".$getkeyword."%'";
        $sql = "select * from " . tablename($table) . " where uniacid=:uniacid and ".$where." order by p_count desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $getmusicsql = "select r_yuyinurl from" . tablename($table2)." where uniacid=:uniacid and gsid=:p_id and gstype=1 order by r_id desc limit 1 ";

        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        $orderssql = "select p_count from" . tablename($table)." where `uniacid`=:uniacid order by p_count desc limit 1 ";
        $orders = pdo_fetch($orderssql, $params);
        $result = pdo_fetchall($sql, $params);
        foreach ($result as $k=>$vv){
            $param = array(
                ':uniacid' => $_W['uniacid'],
                ':p_id' => $vv['p_id'],
            );
            $getmusicurl= pdo_fetch($getmusicsql, $param);
            $result[$k]['musicurl'] =$getmusicurl['r_yuyinurl'];

        }

        $result_list=$result;
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";

        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("tuwenlist");

    }
    //添加添加图文
    public function doWebAddtuwen(){
        global $_W, $_GPC;
        $table = "story_pictext";
        $table1 = "story_gushiread";
        if (!$_W['ispost']) {
            include $this->template("addtuwen");
        } else {
            $arr = array();
            if (empty($_GPC['p_title'])) {
                message('故事标题不能为空', referer(), 'error');
            } else {
                $arr['p_title'] = $_GPC['p_title'];
            }
            $arr['p_count'] = $_GPC['p_count'];
            if (empty($_GPC['p_toppic'])) {
                message("请选择图片", referer(), 'error');
            } else {
                $arr['p_toppic'] = $_GPC['p_toppic'];
            }
            if (empty($_GPC['p_content'])) {
                message('故事内容不能为空', referer(), 'error');
            } else {
                $arr['p_content'] = $_GPC['p_content'];
            }
            if (empty($_GPC['p_auther'])) {
                message('作者不能为空', referer(), 'error');
            } else {
                $arr['p_auther'] = $_GPC['p_auther'];
            }
            if (empty($_GPC['r_yuyinurl'])) {
                message('配套音乐不能为空', referer(), 'error');
            }
            $arr['p_addtime'] = time();
            $arr['p_type']=$_GPC['p_type'];
            $arr['uniacid'] = $_W['uniacid'];
            $add_result = pdo_insert($table, $arr);
            if (!empty($add_result)) {
                $gsid = pdo_insertid();
                //把音乐放入到图文故事表中
                $musicurl=tomedia($_GPC['r_yuyinurl']);
                pdo_insert($table1, array('r_yuyinurl'=>$musicurl,'gsid'=>$gsid,'gstype'=>1,'uniacid'=>$_W['uniacid'],'status'=>1,'r_addtime'=>time()));
                message("添加成功", $this->createWebUrl('tuwenlist'), 'success');
            } else {
                message("添加失败", referer(), 'error');
            }
        }
    }
    //编辑官方图文
    public function doWebEdittuwen()
    {
        global $_W, $_GPC;
        $table = "story_pictext";
        $table2 = "story_gushiread";
        $id = intval($_GPC['id']);
        if ($id == "") {
            message("参数错误", referer(), 'error');
        } else {
            // $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `p_id`=:id";
            $sql = "select * from " . tablename($table) .'p,'.tablename($table2). " g where p.uniacid=:uniacid and p.p_id=:id and g.gsid=p.p_id";

            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            $getgstype=$result['gstype'];
            $orderssql = "select p_count from" . tablename($table)." where uniacid=:uniacid order by p_count desc limit 1 ";
            $orderparams = array(
                ':uniacid' => $_W['uniacid'],

            );
            $orders = pdo_fetch($orderssql, $orderparams);
            if (empty($result)) {
                message("信息不存在", referer(), 'error');
            } else {
                if ($_W['ispost']) {

                    $arr = array();
                    $arr['uniacid'] = $_W['uniacid'];
                    if (empty($_GPC['p_title'])) {
                        message('故事标题不能为空', referer(), 'error');
                    } else {
                        $arr['p_title'] = $_GPC['p_title'];
                    }
                    $arr['p_count'] = $_GPC['p_count'];
                    if (empty($_GPC['p_toppic'])) {
                        message("请选择图片", referer(), 'error');
                    } else {
                        $arr['p_toppic'] = $_GPC['p_toppic'];
                    }
                    if (empty($_GPC['p_content'])) {
                        message('故事内容不能为空', referer(), 'error');
                    } else {
                        $arr['p_content'] = $_GPC['p_content'];
                    }
                    if (empty($_GPC['p_auther'])) {
                        message('作者不能为空', referer(), 'error');
                    } else {
                        $arr['p_auther'] = $_GPC['p_auther'];
                    }
                    if (empty($_GPC['r_yuyinurl'])) {
                        message('配套音乐不可为空', referer(), 'error');
                    } else {
                        //把音乐放入到图文故事表中
                        $musicurl=tomedia($_GPC['r_yuyinurl']);
                        if (strstr($_GPC['r_yuyinurl'],'http')){
                            pdo_update($table2, array('r_yuyinurl'=>$_GPC['r_yuyinurl']),array('gsid'=>$id,'gstype'=>1));
                        }else{
                            pdo_update($table2, array('r_yuyinurl'=>$musicurl),array('gsid'=>$id,'gstype'=>1));
                        }
                    }
                    $arr['p_addtime']=time();
                    $arr['p_type']=$_GPC['p_type'];
                    $edit_result = pdo_update('story_pictext', $arr, array('uniacid' => $_W['uniacid'], 'p_id' => $id));
                    if (!empty($edit_result)) {
                        message('编辑成功', $this->createWebUrl('tuwenlist'), 'success');
                    } else {
                        message('编辑失败', referer(), 'error');
                    }
                } else {
                    include $this->template('edittuwen');
                }

            }

        }
    }
    /*删除图文*/
    public function doWebDeltuwen()
    {
        global $_W, $_GPC;
        $table = "story_pictext";
        $table1 = "story_gushiread";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message('参数错误', referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `p_id`=:p_id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':p_id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("数据不存在", referer(), 'error');
            } else {
                $del_result = pdo_delete($table, array('uniacid' => $_W['uniacid'], 'p_id' => $id));
                if ($del_result) {
                    //这里也要删除图文故事中所有使用该图文的故事
                    pdo_delete($table1, array('uniacid' => $_W['uniacid'], 'gsid' => $id));
                    message("删除成功", $this->createWebUrl('tuwenlist'), 'success');
                } else {
                    message("删除失败", referer(), 'error');
                }
            }
        }
    }
//投诉列表
    public function doWebTousulist()
    {
        global $_W, $_GPC;
        $table = "story_liuyan";
        $table1 = "story_user";
        $table2 = "story_gushi";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $sql = "select * from " . tablename($table) . "ly,".tablename($table2) . "gs,".tablename($table1)."u where ly.uniacid=:uniacid and  ly.uid=u.u_id and ly.gid= gs.id order by ly.l_addtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $result_list = pdo_fetchall($sql, $params);
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("tousulist");
    }
    //留言列表
    public function doWebLiuyanlist()
    {
        global $_W, $_GPC;
        $table = "story_liuyan";
        $table1 = "story_user";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $sql = "select * from " . tablename($table) . "ly,".tablename($table1)."u where ly.uniacid=:uniacid and  ly.uid=u.u_id and ly.gid=0 order by ly.l_addtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $result_list = pdo_fetchall($sql, $params);

        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("liuyanlist");
    }
    //回复留言或者编辑留言
    public function doWebEditliuyan()
    {
        global $_W, $_GPC;
        $table = "story_liuyan";
        $id = intval($_GPC['id']);
        if ($id == "") {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `l_id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("信息不存在", referer(), 'error');
            } else {
                if ($_W['ispost']) {

                    $arr = array();
                    $arr['uniacid'] = $_W['uniacid'];
                    if (empty($_GPC['rep_content'])) {
                        message('回复内容不能为空', referer(), 'error');
                    } else {
                        $arr['rep_content'] = trim($_GPC['rep_content']);
                    }
                    //$arr['addtime']=time();
                    $edit_result = pdo_update('story_liuyan', $arr, array('uniacid' => $_W['uniacid'], 'l_id' => $id));
                    if (!empty($edit_result)) {
                        if (empty($_GPC['tousu'])){
                            message('编辑成功', $this->createWebUrl('liuyanlist'), 'success');
                        }else{
                            message('编辑成功', $this->createWebUrl('tousulist'), 'success');
                        }

                    } else {
                        message('编辑失败,数据没变动', referer(), 'error');
                    }
                } else {

                    include $this->template('editliuyan');
                }

            }

        }
    }
    //删除留言
    public function doWebDelliuyan()
    {
        global $_W, $_GPC;
        $table = "story_liuyan";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message('参数错误', referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `l_id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("数据不存在", referer(), 'error');
            } else {
                $del_result = pdo_delete($table, array('uniacid' => $_W['uniacid'], 'l_id' => $id));
                if ($del_result) {
                    message('删除成功', $this->createWebUrl('liuyanlist', array('type' => $result['type'])), 'success');
                } else {
                    message("删除失败", referer(), 'error');
                }
            }

        }
    }

    //评论列表
    public function doWebPinlunlist()
    {
        global $_W, $_GPC;
        $table = "story_pinlun";
        $table1 = "story_gushi";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $getkeyword=$_GPC['keywords'];
		$where=" pl.content LIKE '%".$getkeyword."%'";
        $sql = "select * from " . tablename($table) . "pl,".tablename($table1)."gs where pl.uniacid=:uniacid and  pl.g_id=gs.id and ".$where." order by pl.gp_addtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $result_list = pdo_fetchall($sql, $params);

        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("pinlunlist");

    }
    //删除评论
    public function doWebDelpinlun()
    {
        global $_W, $_GPC;
        $table = "story_pinlun";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message('参数错误', referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `pl_id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("数据不存在", referer(), 'error');
            } else {
                $del_result = pdo_delete($table, array('uniacid' => $_W['uniacid'], 'pl_id' => $id));
                if ($del_result) {
                    message('删除成功', $this->createWebUrl('pinlunlist', array('type' => $result['type'])), 'success');
                } else {
                    message("删除失败", referer(), 'error');
                }
            }

        }
    }

    //分享后文字设置
    public function doWebShareafter()
    {
        global $_W, $_GPC;
        $table = "story_shareafter";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $sql = "select * from " . tablename($table) ." where `uniacid`=:uniacid limit " . ($pindex - 1) * $psize . ',' . $psize;
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $result_list = pdo_fetchall($sql, $params);

        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("shareafter");

    }
    //编辑分享后文字
   
    public function doWebEditshareafter()
    {
        global $_W, $_GPC;
        $table = "story_shareafter";
        load()->func('communication');
        load()->library('qrcode');
        $id = 4;
        if ($id < 0) {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message('数据不存在', referer(), 'error');
            } else {
                if ($_W['ispost']) {
                    $arr = array();
                    $getqrcode=$_GPC['qrcode'];
                    if (empty($getqrcode)) {
                        message('二维码不能为空', referer(), 'error');
                    }else{
                        $arr['qrcode']=tomedia($getqrcode);
                        $edit_result = pdo_update($table, $arr, array('uniacid' => $_W['uniacid'], 'id' => $id)); 
                    }
                    if (!empty($edit_result)) {
                        message("编辑成功", $this->createWebUrl('Editshareafter'), 'success');
                    } else {
                        message("编辑失败", referer(), 'error');
                    }
                }
                include $this->template('editshareafter');
            }
        }

    }
    //删除分享后文字
    public function doWebDelshareafter()
    {
        global $_W, $_GPC;
        $table = "story_shareafter";
        $id = intval($_GPC['id']);
        if ($id < 0) {
            message('参数错误', referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("数据不存在", referer(), 'error');
            } else {
                $del_result = pdo_delete($table, array('uniacid' => $_W['uniacid'], 'id' => $id));
                if ($del_result) {
                    message('删除成功', $this->createWebUrl('shareafter', array('type' => $result['type'])), 'success');
                } else {
                    message("删除失败", referer(), 'error');
                }
            }

        }
    }
    //设置分享后文字
    public function doWebAddshareafter()
    {
        global $_W, $_GPC;
        $tablename = "story_shareafter";
        load()->func('communication');
        load()->library('qrcode');
        if (!$_W['ispost']) {
            include $this->template('addshareafter');
        } else {

            $arr = array();
            $arr['uniacid'] = $_W['uniacid'];
            if (empty($_GPC['content'])) {
                message('文字内容不能为空', referer(), 'error');
            } else {
                $arr['content'] = $_GPC['content'];
                $picture_attach = 'tonghuagushi/share_' . $_W['timestamp'] . '.png';
                QRcode::png($_GPC['content'], ATTACHMENT_ROOT . $picture_attach);
                $path=tomedia($picture_attach);
                $arr['qrcode']=$path;
            }
            $add_result = pdo_insert('story_shareafter', $arr);
            if (!empty($add_result)) {
                message('添加成功', $this->createWebUrl('shareafter', array('type' => $arr['type'])), 'success');
            } else {
                message('添加失败', referer(), 'error');
            }
        }
    }

    //收藏列表
    public function doWebShoucang()
    {
        global $_W, $_GPC;
        $table = "story_shoucang";
        $table1 = "story_gushi";
        $table2 = "story_user";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $sql = "select * from " . tablename($table) . "sc,". tablename($table1) . "gs,".tablename($table2)."u where sc.uniacid=:uniacid and  sc.g_id=gs.id and sc.uid=u.u_id order by sc.addtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $result_list = pdo_fetchall($sql, $params);
        $imgs = array();
        foreach ($result_list as $kk=>$vv){
            $imgslist = explode(",", $vv['storyimg']);
            $imgs[$kk]=$imgslist;
        }
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("shoucang");

    }
    //收听列表
    public function doWebShouting()
    {
        global $_W, $_GPC;
        $table = "story_shoutingstory";
        $table1 = "story_gushi";
        $table2 = "story_user";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $sql = "select * from " . tablename($table) . "st,". tablename($table1) . "gs,".tablename($table2)."u where st.uniacid=:uniacid and  st.g_id=gs.id and st.uid=u.u_id order by st.addtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;

        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $result_list = pdo_fetchall($sql, $params);
        $imgs = array();
        foreach ($result_list as $kk=>$vv){
            $imgslist = explode(",", $vv['storyimg']);
            $imgs[$kk]=$imgslist;
        }
        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("shouting");

    }

    //用户列表
    public function doWebUserlist()
    {
        global $_W, $_GPC;
        $table = "story_user";
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $getkeyword=$_GPC['keywords'];
        $where=" nickname LIKE '%".$getkeyword."%'";
        $sql = "select * from " . tablename($table) ." where uniacid=:uniacid and is_delete=0 and ".$where." order by u_addtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $params = array(
            ':uniacid' => $_W['uniacid'],
        );
        $result_list = pdo_fetchall($sql, $params);

        $sql2 = "select count(*) from " . tablename($table) . " where `uniacid`=:uniacid";
        $total = pdo_fetchcolumn($sql2, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template("userlist");

    }
    //编辑用户
    public function doWebEdituser()
    {
        global $_W, $_GPC;
        $table = "story_user";
        $id = intval($_GPC['id']);
        if ($id == "") {
            message("参数错误", referer(), 'error');
        } else {
            $sql = "select * from " . tablename($table) . " where `uniacid`=:uniacid and `u_id`=:id";
            $params = array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $id
            );
            $result = pdo_fetch($sql, $params);
            if (empty($result)) {
                message("信息不存在", referer(), 'error');
            } else {
                if ($_W['ispost']) {

                    $arr = array();
                    $arr['uniacid'] = $_W['uniacid'];
                    if (empty($_GPC['wechat'])) {
                        message('微信号不能为空', referer(), 'error');
                    } else {
                        $arr['wechat'] = $_GPC['wechat'];
                    }
                    if (empty($_GPC['bsday'])) {
                        message('生日不可为空', referer(), 'error');
                    } else {
                        $arr['bsday'] = $_GPC['bsday'];
                    }
                    if (empty($_GPC['school'])) {
                        message('学校不可为空', referer(), 'error');
                    } else {
                        $arr['school'] = $_GPC['school'];
                    }

                    //$arr['addtime']=time();
                    $edit_result = pdo_update('story_user', $arr, array('uniacid' => $_W['uniacid'], 'u_id' => $id));
                    if (!empty($edit_result)) {
                        message('编辑成功', $this->createWebUrl('userlist'), 'success');
                    } else {
                        message('编辑失败,数据没变动', referer(), 'error');
                    }
                } else {

                    include $this->template('edituser');
                }

            }

        }
    }
    //删除用户
    public function doWebDeluser()
    {
        global $_W, $_GPC;
        $table = "story_user";
        $id = intval($_GPC['id']);
        $arr = array();
        if ($id == "") {
            message("参数错误", referer(), 'error');
        } else {
            $arr['is_delete'] = 1;
            $res = pdo_update($table, $arr, array('uniacid' => $_W['uniacid'], 'u_id' => $id));
            if (!empty($res)) {
                message("操作成功", referer(), 'success');
            } else {
                message("操作失败", referer(), 'error');
            }
        }
    }

    public function __construct()
    {
        global $_W, $_GPC;
        if ($_W['os'] == 'mobile') {

        } else {
            $do = $_GPC['do'];
            $doo = $_GPC['doo'];
            $act = $_GPC['act'];
            global $frames;
            if ($_W['user']['type'] < 3) {
                $frames = $this->getModuleFrames();
                $this->_calc_current_frames2($frames);
            } else {
                $frames = $this->getModuleFrames2();
                $this->_calc_current_frames2($frames);
            }

        }
    }

    function getModuleFrames()
    {

        $frames = array();
        $name = "tonghuagushi";
        $frames['set']['title'] = '管理中心';
        $frames['set']['active'] = '';
        $frames['set']['items'] = array();
        $frames['set']['items']['site']['url'] = url('site/entry/site', array('m' => $name));
        $frames['set']['items']['site']['title'] = '站点设置';
        $frames['set']['items']['site']['actions'] = array();
        $frames['set']['items']['site']['active'] = '';
        //骗审设置
        $frames['set']['items']['pian']['url'] = url('site/entry/pianshen', array('m' => $name));
        $frames['set']['items']['pian']['title'] = '骗审设置';
        $frames['set']['items']['pian']['actions'] = array();
        $frames['set']['items']['pian']['active'] = '';
        ////////////////////
        $frames['banner']['title'] = '轮播图管理';
        $frames['banner']['active'] = '';
        $frames['banner']['items'] = array();

        $frames['banner']['items']['bannerlist']['url'] = url('site/entry/bannerlist', array('m' => $name));
        $frames['banner']['items']['bannerlist']['title'] = '轮播图列表';
        $frames['banner']['items']['bannerlist']['actions'] = array();
        $frames['banner']['items']['bannerlist']['active'] = '';

        $frames['banner']['items']['ad']['url'] = url('site/entry/ad', array('m' => $name));
        $frames['banner']['items']['ad']['title'] = '广告列表';
        $frames['banner']['items']['ad']['actions'] = array();
        $frames['banner']['items']['ad']['active'] = '';
        ///////////////////////////////////
        $frames['bjmusic']['title'] = '背景音乐库管理';
        $frames['bjmusic']['active'] = '';
        $frames['bjmusic']['items'] = array();

        $frames['bjmusic']['items']['list']['url'] = url('site/entry/bjmusiclist', array('m' => $name));
        $frames['bjmusic']['items']['list']['title'] = '音乐列表';
        $frames['bjmusic']['items']['list']['actions'] = array();
        $frames['bjmusic']['items']['list']['active'] = '';
        ////////////////////
        $frames['tuku']['title'] = '在线图库管理';
        $frames['tuku']['active'] = '';
        $frames['tuku']['items'] = array();

        $frames['tuku']['items']['list1']['url'] = url('site/entry/tukulist', array('m' => $name));
        $frames['tuku']['items']['list1']['title'] = '图库列表';
        $frames['tuku']['items']['list1']['actions'] = array();
        $frames['tuku']['items']['list1']['active'] = '';

        $frames['tuku']['items']['list']['url'] = url('site/entry/tukufenleilist', array('m' => $name));
        $frames['tuku']['items']['list']['title'] = '图库分类列表';
        $frames['tuku']['items']['list']['actions'] = array();
        $frames['tuku']['items']['list']['active'] = '';


        $frames['tuku']['items']['addseller']['url'] = url('site/entry/addtuku', array('m' => $name));
        $frames['tuku']['items']['addseller']['title'] = '添加图片';
        $frames['tuku']['items']['addseller']['actions'] = array();
        $frames['tuku']['items']['addseller']['active'] = '';

        $frames['tuku']['items']['addtukufenlei']['url'] = url('site/entry/addtukufenlei', array('m' => $name));
        $frames['tuku']['items']['addtukufenlei']['title'] = '添加图库分类';
        $frames['tuku']['items']['addtukufenlei']['actions'] = array();
        $frames['tuku']['items']['addtukufenlei']['active'] = '';
        ////////////////////
        $frames['tonghuagushi']['title'] = '故事管理';
        $frames['tonghuagushi']['active'] = '';
        $frames['tonghuagushi']['items'] = array();

        $frames['tonghuagushi']['items']['list']['url'] = url('site/entry/gushilist', array('m' => $name));
        $frames['tonghuagushi']['items']['list']['title'] = '小主播故事列表';
        $frames['tonghuagushi']['items']['list']['actions'] = array();
        $frames['tonghuagushi']['items']['list']['active'] = '';
        $frames['tonghuagushi']['items']['guanfan']['url'] = url('site/entry/zhibuxionggushilist', array('m' => $name));
        $frames['tonghuagushi']['items']['guanfan']['title'] = '官方故事列表';
        $frames['tonghuagushi']['items']['guanfan']['actions'] = array();
        $frames['tonghuagushi']['items']['guanfan']['active'] = '';
        ////////////////////
        $frames['zj']['title'] = '专辑管理';
        $frames['zj']['active'] = '';
        $frames['zj']['items'] = array();

        $frames['zj']['items']['zj']['url'] = url('site/entry/zhuanjilist', array('m' => $name));
        $frames['zj']['items']['zj']['title'] = '专辑列表';
        $frames['zj']['items']['zj']['actions'] = array();
        $frames['zj']['items']['zj']['active'] = '';
        $frames['zj']['items']['zjgs']['url'] = url('site/entry/zhuanjigushilist', array('m' => $name));
        $frames['zj']['items']['zjgs']['title'] = '专辑故事列表';
        $frames['zj']['items']['zjgs']['actions'] = array();
        $frames['zj']['items']['zjgs']['active'] = '';
        $frames['zj']['items']['zjtwgs']['url'] = url('site/entry/zhuanjigushireadlist', array('m' => $name));
        $frames['zj']['items']['zjtwgs']['title'] = '专辑阅读故事列表';
        $frames['zj']['items']['zjtwgs']['actions'] = array();
        $frames['zj']['items']['zjtwgs']['active'] = '';
        ////////////////////
        ////////////////////
        $frames['twgushi']['title'] = '图文故事管理';
        $frames['twgushi']['active'] = '';
        $frames['twgushi']['items'] = array();

        $frames['twgushi']['items']['twlist']['url'] = url('site/entry/readgushilist', array('m' => $name));
        $frames['twgushi']['items']['twlist']['title'] = '阅读故事列表';
        $frames['twgushi']['items']['twlist']['actions'] = array();
        $frames['twgushi']['items']['twlist']['active'] = '';
        $frames['twgushi']['items']['guanfantw']['url'] = url('site/entry/tuwenlist', array('m' => $name));
        $frames['twgushi']['items']['guanfantw']['title'] = '官方图文列表';
        $frames['twgushi']['items']['guanfantw']['actions'] = array();
        $frames['twgushi']['items']['guanfantw']['active'] = '';
        ////////////////////
        $frames['liuyan']['title'] = '留言/投诉管理';
        $frames['liuyan']['active'] = '';
        $frames['liuyan']['items'] = array();

        $frames['liuyan']['items']['list']['url'] = url('site/entry/liuyanlist', array('m' => $name));
        $frames['liuyan']['items']['list']['title'] = '留言列表';
        $frames['liuyan']['items']['list']['actions'] = array();
        $frames['liuyan']['items']['list']['active'] = '';
        //投诉管理
        $frames['liuyan']['items']['tousu']['url'] = url('site/entry/tousulist', array('m' => $name));
        $frames['liuyan']['items']['tousu']['title'] = '投诉列表';
        $frames['liuyan']['items']['tousu']['actions'] = array();
        $frames['liuyan']['items']['tousu']['active'] = '';
        ////////////////////
        $frames['pinlun']['title'] = '评论管理';
        $frames['pinlun']['active'] = '';
        $frames['pinlun']['items'] = array();

        $frames['pinlun']['items']['list']['url'] = url('site/entry/pinlunlist', array('m' => $name));
        $frames['pinlun']['items']['list']['title'] = '评论列表';
        $frames['pinlun']['items']['list']['actions'] = array();
        $frames['pinlun']['items']['list']['active'] = '';
        ////////////////////
        $frames['other']['title'] = '其他管理';
        $frames['other']['active'] = '';
        $frames['other']['items'] = array();

        $frames['other']['items']['share']['url'] = url('site/entry/Editshareafter', array('m' => $name));
        $frames['other']['items']['share']['title'] = '分享后文字设置';
        $frames['other']['items']['share']['actions'] = array();
        $frames['other']['items']['share']['active'] = '';

        $frames['other']['items']['shoucang']['url'] = url('site/entry/shoucang', array('m' => $name));
        $frames['other']['items']['shoucang']['title'] = '收藏管理';
        $frames['other']['items']['shoucang']['actions'] = array();
        $frames['other']['items']['shoucang']['active'] = '';

        $frames['other']['items']['shouting']['url'] = url('site/entry/shouting', array('m' => $name));
        $frames['other']['items']['shouting']['title'] = '收听历史';
        $frames['other']['items']['shouting']['actions'] = array();
        $frames['other']['items']['shouting']['active'] = '';
        ////////////////////
        $frames['user']['title'] = '用户管理';
        $frames['user']['active'] = '';
        $frames['user']['items'] = array();

        $frames['user']['items']['list']['url'] = url('site/entry/userlist', array('m' => $name));
        $frames['user']['items']['list']['title'] = '用户列表';
        $frames['user']['items']['list']['actions'] = array();
        $frames['user']['items']['list']['active'] = '';
        return $frames;
    }
}
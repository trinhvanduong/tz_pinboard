<?php
/*------------------------------------------------------------------------

# TZ Pinboard Extension

# ------------------------------------------------------------------------

# author    TuNguyenTemPlaza

# copyright Copyright (C) 2013 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

defined("_JEXEC") or die;


class TZ_PinboardModelSearch extends JModelList{



    /**
     * Method to auto-populate the model state.

     */
    function populateState(){

        $app            = &JFactory::getApplication();
        $params         = $app -> getParams();
        $this -> setState('params',$params);
        $catid          = $params->get('catid');
        $limit_pin      = $params->get('tz_article_limit');
        $limitstart     = JRequest::getCmd('limitstart',0);
        $width_columns  = $params->get('width_columns');
        $tz_layout      = $params->get('tz_pinboard_layout');
        $type_detail    = $params->get('type_detail');
        $limit_commnet  = $params->get('Limits_comment');
        $state_comment  = $params->get('state_comment');
        $change_comment = $params->get('changecomment');
        $delete_text_cm = $params->get('remove_comment');
        $page_commnet   = $params->get('page_commnet');
        $type_show_pin  = $params->get('type_show_pin');
        $image_thum     = $params->get('portfolio_image_size');
        $arrangements_pins = $params->get('arrangements_pins');
        $search = JRequest::getString('tz_search');
        $tz_pin_approve = $params->get('tz_pin_approve');
        $this->setState('image_thum',$image_thum);
        $this->setState('type_detail',$type_detail);
        $this->setState('arrangements_pins',$arrangements_pins);
        $this->setState('check_status',$tz_pin_approve);
        $this->setState('tz_search',$search);
        $this->setState('type_show_pin',$type_show_pin);
        $this->setState('tz_layout',$tz_layout);
        $this->setState('page_cm',$page_commnet);
        $this->setState('star_page_cm',0);
        $this->setState('limit_commnet', $limit_commnet);
        $this->setState('remove_comment',$delete_text_cm);
        $this->setState('change_comment',$change_comment);
        $this->setState('state_comment',$state_comment);
        $this->setState('width_columns',$width_columns);
        $this->setState('catids',$catid);
        $this->setState('limit_pin',$limit_pin);
        $this->setState('limitstar',$limitstart);
    }



    /*
     * Method Check the PAGE
    */
    function getCheck_pt_pin(){
        $limit          = $this->getState('limit_pin');
        $type_show_pin  = $this->getState('type_show_pin');
        $tz_search      = $this->getState('tz_search');
        $tz_search      = trim($tz_search);
        $tz_search      = str_replace("'","\'",$tz_search);
        if(isset($tz_search) && !empty($tz_search)){
            $catids     = "where c.title like '%".$tz_search."%' or u.name='".$tz_search."' and c.state=1 order by c.$type_show_pin desc";
        }else{
            $catids     = "where c.state=1 order by c.$type_show_pin desc";
        }
        $db             = &JFactory::getDbo();
        $sql            ="SELECT u.id as id_user, c.title as conten_title,  c.id as content_id, pz.images as poro_img,
                                w.url as website , w.id_user_repin as id_user_repin, w.name_user_repin as name_user_repin,
                                c.catid as catidc, u.name as user_name,  us.images as user_img
                        FROM #__users AS u
                            LEFT JOIN #__tz_pinboard_boards AS ca ON u.id = ca.created_user_id
                            LEFT JOIN #__tz_pinboard_pins AS c ON ca.id = c.catid
                            LEFT JOIN #__tz_pinboard_xref_content AS pz ON c.id = pz.contentid
                            LEFT JOIN #__tz_pinboard_website AS w ON c.id = w.contentid
                            LEFT JOIN #__tz_pinboard_users as us ON u.id = us.usersid  $catids  ";
        $db   -> setQuery($sql);
        $tinh = $db->query();
        $total= $db->getNumRows($tinh);
        if($total < $limit){
            return 'f';
        }else{
            return 'tr';
        }
    }

    /*
     * method returns results when searching by name in module
    */
    function getListSearch(){
        $title      = strip_tags(htmlspecialchars( $_POST['title']));
        $title      = str_replace("'","\'",$title);
        if(isset($title) && !empty($title)){
            $db     = &JFactory::getDbo();
            $sql    = " select p.title as title, xr.images as imge  from #__tz_pinboard_pins as p left join #__tz_pinboard_xref_content as xr on p.id = xr.contentid where p.title like '%".$title."%'";
            $db->setQuery($sql);
            $row    = $db->loadObjectList();
            return $row;
        }
        return null;
    }


    /*
     * method returns the layout for module
    */
    function ajaxseacrch(){
        if (!isset($_SERVER['HTTP_REFERER'])) return null;
        $refer          =   $_SERVER['HTTP_REFERER'];
        $url_arr        =   parse_url($refer);
        if ($_SERVER['HTTP_HOST'] != $url_arr['host']) return null;
        require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'view.html.php'); // chen file view.html.php vao
        $view           = new TZ_PinboardViewSearch();
        $img_size       = $this->getState('image_thum');
        $view->assign('imgs',$img_size);
        $view -> assign('list',$this->getListSearch());
        return  $view -> loadTemplate('results');
    }


    /*
     * method show data
    */
    function getPins(){
        $limit              = $this->getState('limit_pin');
        $limitStart         = $this->getState('limitstar');
        $type_show_pin      = $this->getState('type_show_pin');
        $tz_layout          = $this->getState('tz_layout');
        $arrangements       = $this->getState('arrangements_pins');
        $tz_search          = $this->getState('tz_search');
        $search             = JRequest::getString('tz_search_url');
        $search             = json_decode(base64_decode($search));
        if(isset($search) && !empty($search)){
            $tz_search = $search;
        }else{
            $tz_search = $tz_search;
        }
        $tz_search = str_replace("'","\'",$tz_search);
        if(isset($tz_search) && !empty($tz_search)){
            $catids = "where c.title like '%".$tz_search."%' or u.name='".$tz_search."' and c.state=1 order by c.$type_show_pin $arrangements";
        }else{
            $catids="where c.state=1 order by c.$type_show_pin $arrangements";
        }
        $db = &JFactory::getDbo();
        $sql ="SELECT u.id as id_user, c.title as conten_title,  c.id as content_id, pz.images as poro_img,
                        w.url as website , w.id_user_repin as id_user_repin, w.name_user_repin as name_user_repin,
                        c.catid as catidc, u.name as user_name,  us.images as user_img
                FROM #__users AS u
                    LEFT JOIN #__tz_pinboard_boards AS ca ON u.id = ca.created_user_id
                    LEFT JOIN #__tz_pinboard_pins AS c ON ca.id = c.catid
                    LEFT JOIN #__tz_pinboard_xref_content AS pz ON c.id = pz.contentid
                    LEFT JOIN #__tz_pinboard_website AS w ON c.id = w.contentid
                    LEFT JOIN #__tz_pinboard_users as us ON u.id = us.usersid  $catids";
    
        $sql2 ="SELECT u.id as id_user, c.title as conten_title,  c.id as content_id, pz.images as poro_img,
                        w.url as website , w.id_user_repin as id_user_repin, w.name_user_repin as name_user_repin,
                        c.catid as catidc, u.name as user_name,  us.images as user_img
                FROM #__users AS u
                    LEFT JOIN #__tz_pinboard_boards AS ca ON u.id = ca.created_user_id
                    LEFT JOIN #__tz_pinboard_pins AS c ON ca.id = c.catid
                    LEFT JOIN #__tz_pinboard_xref_content AS pz ON c.id = pz.contentid
                    LEFT JOIN #__tz_pinboard_website AS w ON c.id = w.contentid
                    LEFT JOIN #__tz_pinboard_users as us ON u.id = us.usersid  $catids  ";
        $db->setQuery($sql);
        $tinh = $db->query();
        $total = $db->getNumRows($tinh);
        $this -> pagNavPins         = new JPagination($total,$limitStart,$limit);
        // Select the type of paging
        if($tz_layout =="default"){
            $db->setQuery($sql2,$this -> pagNavPins -> limitstart,$this -> pagNavPins -> limit);
        }else{
            $db->setQuery($sql2,$limitStart,$limit);
        }
        $row = $db->loadObjectList();
        foreach($row as $item){
            $check_l = $this->chekcLikeUser($item->content_id);
            $item->checl_l = $check_l;
            $demL = $this->countLike($item->content_id);
            $item->demL = $demL;
            $countComment = $this->countComment($item->content_id);
            $item->countComment = $countComment;
        }
    
        return $row;
    }



    /*
     * method paging in joomla
    */
    function getPaginationPins(){
        if(!$this->pagNavPins)
        return '';
        return $this->pagNavPins;
    }


    /*
    * Method check users like or not
    */
    function  chekcLikeUser($id_content){
        $user       = JFactory::getUser();
        $id_user    = $user->id;
        $db         = JFactory::getDbo();
        $sql        ="select like_p as p from #__tz_pinboard_like where id_content=$id_content AND id_user_p =$id_user";
        $db-> setQuery($sql);
        $row        = $db->loadAssoc();
        return $row;
    }



    /*
     * Method count Like
    */
    function countLike($id_content){
        $db     = JFactory::getDbo();
        $sql    ="select count(id) as count_l from #__tz_pinboard_like where id_content=$id_content AND like_p =1";
        $db ->  setQuery($sql);
        $row    = $db->loadObject();
        return $row;
    }

    /*
    * Method count comment
    */
    function countComment($id_content){
        $db     = JFactory::getDbo();
        $sql    = "select count(id) as count_l from #__tz_pinboard_comment where content_id=$id_content";
        $db -> setQuery($sql);
        $row    = $db->loadObject();
        return $row;
    }


    /*
    *  Method get logo user
    */
    function getUserImgLogin(){
        $user       = JFactory::getUser();
        $id_user    = $user->id;
        $db         = JFactory::getDbo();
        $sql        = "select images from #__tz_pinboard_users where usersid=$id_user";
        $db -> setQuery($sql);
        $row        = $db->loadObject();
        return $row;
    }


    /*
    *  Method get id user
    */
    function getSosanhuser(){
        $user       = JFactory::getUser();
        $id_user    = $user->id;
        return $id_user;
    }

    /*
    * method check user like or not like and get id user like
    */
    function checklike(){
        $user       = JFactory::getUser();
        $id_user    = $user->id;
        $id_content = $_POST['id_conten'];
        $db         = JFactory::getDbo();
        $SQL        = "SELECT id_user_p from #__tz_pinboard_like where id_user_p=$id_user AND id_content=$id_content";
        $db -> setQuery($SQL);
        $row        = $db->loadObject();
        return $row;
    }


    /*
    *  Method insert into table like
    */
    function inserLike(){
        if (!isset($_SERVER['HTTP_REFERER'])) return null;
        $refer      = $_SERVER['HTTP_REFERER'];
        $url_arr    = parse_url($refer);
        if ($_SERVER['HTTP_HOST'] != $url_arr['host']) return null;
        $user       = JFactory::getUser();
        $id_user    = $user->id;
        $id_content = $_POST['id_conten'];
        $db         = JFactory::getDbo();
        $checklik   = $this->checklike()->id_user_p;
        if(isset($id_user) && !empty($id_user)){
            if(isset($checklik) AND !empty($checklik)){
                $sql = "update #__tz_pinboard_like set like_p ='1' where id_content=$id_content AND id_user_p=$id_user";
            }else if(empty($checklik)){
            $sql     = "INSERT INTO #__tz_pinboard_like  VALUES(NULL,'1','".$id_content."','".$id_user."') ";
            }
            $db->setQuery($sql);
            $db->query();
            $lik     =  $this->countLike($id_content)->count_l;
            return $lik;
        }else{
            return "f";
        }
    }


    /*
    *  Method insert  unlike
    */
    function insertUnlike(){
        if (!isset($_SERVER['HTTP_REFERER'])) return null;
        $refer      =   $_SERVER['HTTP_REFERER'];
        $url_arr    =   parse_url($refer);
        if ($_SERVER['HTTP_HOST'] != $url_arr['host']) return null;
        $user       = JFactory::getUser();
        $id_user    = $user->id;
        $id_content = $_POST['id_conten'];
        $db         = JFactory::getDbo();
        $checklik   = $this->checklike()->id_user_p;
        if(isset($id_user) && !empty($id_user)){
            if(isset($checklik) AND !empty($checklik)){
                $sql    = "update #__tz_pinboard_like set like_p ='0' where id_content=$id_content AND id_user_p=$id_user   ";
            }else if(empty($checklik)){
                $sql    =   "INSERT INTO #__tz_pinboard_like  VALUES(NULL,'0','".$id_content."','".$id_user."') ";
            }
            $db->setQuery($sql);
            $db->query();
            $lik        =  $this->countLike($id_content)->count_l;
            return $lik;
        }else{
            return "f";
        }
    }





    /*
     * method  start  ajax paging khen tags = add_ajax
     */
    function PinAjax(){
        if (!isset($_SERVER['HTTP_REFERER'])) return null;
        $refer          =   $_SERVER['HTTP_REFERER'];
        $url_arr        =   parse_url($refer);
        if ($_SERVER['HTTP_HOST'] != $url_arr['host']) return null;
        require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pinboard'.DIRECTORY_SEPARATOR.'view.html.php'); // chen file view.html.php vao
        $view           = new TZ_PinboardViewSearch();
        $page           = JRequest::getInt('page');
        $limit          = $this ->getState('limit_pin');
        $limitstart1    =   $limit * ($page-1);
        $offset         = (int) $limitstart1;
        $this -> setState('limitstar',$offset);
        $text_commnet   = $this->getState('limit_commnet');
        $img_size       = $this->getState('image_thum');
        $width_columns  = $this->getState('width_columns');
        $tz_layout      = $this->getState('tz_layout');
        $type_detail    = $this->getState('type_detail');
        $view->assign('type_detail',$type_detail);
        $view->assign('Limit_comment',$text_commnet);
        $view->assign('UserImgLogin',$this->getUserImgLogin());
        $view->assign('sosanhuser',$this->getSosanhuser());
        $view->assign('tz_layout',$tz_layout);
        $view->assign('width_columns',$width_columns);
        $view->assign('img_size',$img_size);
        $view->assign('Pins',$this->getPins());
        return ($view -> loadTemplate('pinboard'));
    }



    /*
     * method check comment
     */
    function  checkInsertComment(){
        $user       = JFactory::getUser();
        $id_user    = $user->id;
        $IP         =  $_SERVER['REMOTE_ADDR'];
        $db         = &JFactory::getDbo();
        $sql        = "select checkIP FROM #__tz_pinboard_comment WHERE id_user=$id_user and IP ='".$IP."' limit 0,1";
        $db -> setQuery($sql);
        $row        = $db->loadObject();
        return $row;
    }



    /*
   * method insert commnet
   */
    function Tz_comment_Content(){
    if (!isset($_SERVER['HTTP_REFERER'])) return null;
    $refer              =   $_SERVER['HTTP_REFERER'];
    $url_arr            =   parse_url($refer);
    if ($_SERVER['HTTP_HOST'] != $url_arr['host']) return null;
    require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pinboard'.DIRECTORY_SEPARATOR.'view.html.php');
    $view   = new TZ_PinboardViewPinboard();
    $state = $this->getState('params');
    $id_content         =   strip_tags(htmlspecialchars($_POST['id_content']));
    $content_cm         =   strip_tags(htmlspecialchars( $_POST['content']));
    $content_cm         =   str_replace("'","\'",$content_cm);
    $delete_text        =   $this->setState('remove_comment');
    $change_comment     =   $this->setState('change_comment');
    $arr_commnet        =   explode(",",$delete_text);
    $arr_commnet        =   array_map("trim",$arr_commnet);
    $commnet_replace    =   str_replace($arr_commnet,$change_comment,$content_cm);
    $state              =   $this->setState('state_comment');
    $IP                 =  $_SERVER['REMOTE_ADDR'];
    $user               =   JFactory::getUser();
    $id_user            =   $user->id;
    $dt                 =   JFactory::getDate();
    $dtime              =   $dt->toSql();
    $db                 =   & JFactory::getDbo();
    $checkIP            =   $this->checkInsertComment();
    if($checkIP==""){
        $sql            =   "INSERT INTO #__tz_pinboard_comment VALUES('NULL','".$commnet_replace."', '$id_content', '$id_user','".$state."','".$dtime."','".$IP."','1')";
    }else{
        $checkIP        =   $this->checkInsertComment()->checkIP;
        $sql            =   "INSERT INTO #__tz_pinboard_comment VALUES('NULL','".$commnet_replace."', '$id_content', '$id_user','".$state."','".$dtime."','".$IP."','.$checkIP.')";

    }
    $db->setQuery($sql);
    $db->query();
    }

    /*
    * method display comment
    */
    function getShowCommnet(){
        $id_conten  = $_POST['id_conten'];
        $limit_star = $this->getState('star_page_cm');
        $limit = $this->getState('page_cm');
        $db = JFactory::getDbo();
        $sql="SELECT u.name as user_name,cm.content_id  as content_id_cm, u.id as id_user, tz.images as img_user, cm.content as content_cm, cm.dates as dates, cm.id as id_comment,
                     c.created_by as create_by
            FROM #__users AS u
                LEFT JOIN #__tz_pinboard_users AS tz ON u.id = tz.usersid
                LEFT JOIN #__tz_pinboard_comment AS cm ON cm.id_user = u.id
                LEFT JOIN #__tz_pinboard_pins AS c ON cm.content_id = c.id
            WHERE cm.content_id =$id_conten AND cm.state=1 AND cm.checkIP=1  order by cm.id desc limit $limit_star,$limit";
        $db->setQuery($sql);
        if($row = $db->loadObjectList()){
            return $row;
        }
        return false;
    }


    /*
    *  method count the number of comment
    */
    function getDemcommnet(){
        $id_conten  =   $_POST['id_content'];
        $db         =   JFactory::getDbo();
        $sql        =   "select count(id) as number_id from #__tz_pinboard_comment where content_id =$id_conten";
        $db->setQuery($sql);
        $row        = $db->loadObject();
        return $row;
    }



    /*
    * method Display comments as insert complete
    */
    function getShowcommnetInsert(){
        $user       = JFactory::getUser();
        $id_user    = $user->id;
        $db         = JFactory::getDbo();
        $content_id = JRequest::getInt('id_content');
        $sql        = "SELECT u.name as user_name,cm.content_id  as content_id_cm, u.id as id_user, tz.images as img_user, cm.content as content_cm, cm.dates as dates, cm.id as id_comment,
                              c.created_by as create_by
                      FROM #__users AS u
                        LEFT JOIN #__tz_pinboard_users AS tz ON u.id = tz.usersid
                        LEFT JOIN #__tz_pinboard_comment AS cm ON cm.id_user = u.id
                        LEFT JOIN #__tz_pinboard_pins AS c ON cm.content_id = c.id
                      WHERE cm.content_id =$content_id  AND cm.id_user =$id_user AND cm.state=1 AND cm.checkIP=1 order by cm.id desc limit 0,1";
        $db->setQuery($sql);
        $row        = $db->loadObjectList();
        return $row;
    }


    function ajaxCommnet(){
        if (!isset($_SERVER['HTTP_REFERER'])) return null;
        $refer      =   $_SERVER['HTTP_REFERER'];
        $url_arr    =   parse_url($refer);
        if ($_SERVER['HTTP_HOST'] != $url_arr['host']) return null;
        $this->Tz_comment_Content();
        require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'view.html.php'); // chen file view.html.php vao
        $view       = new TZ_PinboardViewSearch();
        $view-> assign('sosanhuser',$this->getSosanhuser());
        $view->assign('ShowCommnet',$this->getShowcommnetInsert());
        return $view->loadTemplate('pin_comment');
    }

    function ajaxcommnet_cm(){
        if (!isset($_SERVER['HTTP_REFERER'])) return null;
        $refer      =   $_SERVER['HTTP_REFERER'];
        $url_arr    =   parse_url($refer);
        if ($_SERVER['HTTP_HOST'] != $url_arr['host']) return null;
        $user       =   JFactory::getUser();
        $id_user    =   $user->id;
        if(!isset($id_user) && empty($id_user)) return null;
        $this->Tz_comment_Content();
        require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'search'.DIRECTORY_SEPARATOR.'view.html.php'); // chen file view.html.php vao
        $view   = new TZ_PinboardViewSearch();
        $view   -> assign('sosanhuser',$this->getSosanhuser());
        $view   ->  assign('ShowCommnet',$this->getShowcommnetInsert());
        $arr = array();
        $arr['contents'] = $view->loadTemplate('pin_cm');
        $arr['count_number'] = $this->getDemcommnet()->number_id;
        return $arr;
    }



}
?>
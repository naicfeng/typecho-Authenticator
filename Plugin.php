<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Google Authenticator for Typecho
 * 
 * @package GAuthenticator
 * @author WeiCN
 * @version 0.0.1
 * @link https://weicn.org
 */
class GAuthenticator_Plugin implements Typecho_Plugin_Interface
{
	private static $pluginName = 'GAuthenticator';
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
		Typecho_Plugin::factory('admin/menu.php')->navBar = array(__CLASS__, 'Authenticator_safe');
		Typecho_Plugin::factory('admin/header.php')->header = array(__CLASS__, 'Authenticator_verification');
		Typecho_Plugin::factory('Widget_Login')->loginFail = array(__CLASS__, 'Authenticator_login');
		Typecho_Plugin::factory('Widget_Login')->loginSucceed = array(__CLASS__, 'Authenticator_loginSucceed');
		return _t('当前两步验证还未启用，请进行<a href="options-plugin.php?config=' . self::$pluginName . '">初始化设置</a>');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void'
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
		$element = new Typecho_Widget_Helper_Form_Element_Text('SecretKey', NULL, '', _t('SecretKey'), '安装的时候自动计算密钥,手动修改无效,如需要修改请卸载重新安装或者手动修改数据库');
		$form->addInput($element);
		$element = new Typecho_Widget_Helper_Form_Element_Text('SecretQRurl', NULL, '', _t('二维码的网址'), '复制打开就可以显示当前SecretKey的二维码,扫描就可以绑定,调用的GoogleAPI,如果无法显示可能是被墙了');
		$form->addInput($element);
		$element = new Typecho_Widget_Helper_Form_Element_Text('SecretTime', NULL, 2, _t('容差时间'), '允许的容差时间,单位为30秒的倍数,如果这里是2 那么就是 2* 30 sec 一分钟.');
		$form->addInput($element);
		$element = new Typecho_Widget_Helper_Form_Element_Text('SecretCode', NULL, '', _t('客户端代码'), '输入你APP或者其他什么鬼上面显示的代码，<del>扫描二维码</del>(抱歉我不会在后台显示)或者输入上面的SecretKey即可生成');
		$form->addInput($element);
		$element = new Typecho_Widget_Helper_Form_Element_Radio('SecretOn', array('1' => '开启','0' => '关闭'), 0, _t('插件开关'), '这里关掉了，就不需要验证即可登录。');
		$form->addInput($element);
    }
    /**
     * 手动保存配置面板
     * @param $config array 插件配置
     * @param $is_init bool 是否初始化
     */
    public static function configHandle($config, $is_init)
    {
		if ($is_init) {//如果是第一次初始化插件
			require_once 'GoogleAuthenticator.php';
			$Authenticator = new PHPGangsta_GoogleAuthenticator();//初始化生成类
			$config['SecretKey'] = $Authenticator->createSecret();//生成一个随机安全密钥
			$config['SecretQRurl'] = $Authenticator->getQRCodeGoogleUrl(Helper::options()->title, $config['SecretKey']);//生成安全密钥的二维码网址
		}else{
			$config_old = Helper::options()->plugin(self::$pluginName);
			if(($config['SecretCode']!='' && $config['SecretOn']==1) || $config['SecretOn']==1){//如果启用,并且验证码不为空
				require_once 'GoogleAuthenticator.php';
				$Authenticator = new PHPGangsta_GoogleAuthenticator();
				if($Authenticator->verifyCode($config['SecretKey'], $config['SecretCode'], $config['SecretTime'])){
					$config['SecretOn'] = 1;//如果匹配,则启用
				}else{
					throw new Typecho_Plugin_Exception('两步验证代码校验失败,请重试或选择关闭');
				}
			}
			$config['SecretKey'] = $config_old->SecretKey;//保持初始化SecretKey不被修改
			$config['SecretQRurl'] = $config_old->SecretQRurl;//保持初始化SecretQRurl不被修改
		}
		$config['SecretCode'] = '';//每次保存不保存验证码
		Helper::configPlugin(self::$pluginName, $config);//保存插件配置
    }
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    public static function Authenticator_safe()
    {
		$config = Helper::options()->plugin(self::$pluginName);
		if($config->SecretOn==1){
			echo '<span class="message success">'.htmlspecialchars('已启用 Authenticator 验证').'</span>';
		}else{
			echo '<span class="message error">'.htmlspecialchars('未启用 Authenticator 验证').'</span>';
		}
    }

    public static function Authenticator_verification($header)
    {
		if (Typecho_Widget::widget('Widget_User')->hasLogin()) return $header;//如果已经登录则直接返回
		$config = Helper::options()->plugin(self::$pluginName);
		if (Typecho_Cookie::get('__typecho_Authenticator') == md5($config->SecretKey.Typecho_Cookie::getPrefix())) return $header;//如果COOKIES匹配则直接返回
		if($config->SecretOn==1){
			$options = Helper::options();
			$request = new Typecho_Request();
			require_once 'verification.php';
		}else{
			return $header;//如果未开启插件则直接返回
		}
    }

	public static function Authenticator_login($user, $name, $password, $remember)
    {
		$config = Helper::options()->plugin(self::$pluginName);
		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'javascript:history.back(-1);';
		if($config->SecretOn==1 && Typecho_Cookie::get('__typecho_Authenticator') != md5($config->SecretKey.Typecho_Cookie::getPrefix())){//如果插件启用且验证不匹配
			if($name=='_AuthenticatorCode'){
				require_once 'GoogleAuthenticator.php';
				$Authenticator = new PHPGangsta_GoogleAuthenticator();//初始化生成类
				//$oneCode = $Authenticator->getCode($secret);//服务器生成的一次性代码
				$oneCode = $password;//手机端生成的一次性代码
				if($Authenticator->verifyCode($config->SecretKey, $oneCode, $config->SecretTime)){//验证一次性代码
					Typecho_Cookie::set('__typecho_Authenticator',md5($config->SecretKey.Typecho_Cookie::getPrefix()));//由于typecho先判断在线状态才启用的session,所以我们用cookies来保存
					Typecho_Widget::widget('Widget_Notice')->set(_t('两步验证通过,请使用密码登录'), 'success');
					Typecho_Response::redirect($referer);
				}else{
					self::showErrorMsg('两步验证失败');
				}
			}else{
				self::showErrorMsg('未通过两步验证，禁止登录');//如果强行POST登录则拦截
				Typecho_Response::redirect($referer);
			}
		}elseif(Typecho_Cookie::get('__typecho_Authenticator') == md5($config->SecretKey.Typecho_Cookie::getPrefix())){
			Typecho_Widget::widget('Widget_Login')->login($name, $password, false, $remember);//调用系统登录接口
		}
    }
	public static function Authenticator_loginSucceed($user, $name, $password, $remember)
	{
		Typecho_Cookie::delete('__typecho_Authenticator');//登录成功后删掉验证用的COOKIES
	}
    /**
     *显示错误信息并跳回登录界面
     *@return void
     */
    public static function showErrorMsg($msg) {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'javascript:history.back(-1);';
        Typecho_Widget::widget('Widget_Notice')->set(_t($msg), 'error');
        Typecho_Response::redirect($referer);
    }
}

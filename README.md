看到好多网站都支持Google Authenticator的两步验证，所以写了这个小插件，参考了很多前辈写的插件。

Google Authenticator的PHP实现来自：http://www.phpgangsta.de

####实现接口  
`Widget_Login->loginFail`  
`Widget_Login->loginSucceed`  
并没有用更高级的  
`Widget_User->login`  
其实按道理来说后者使用更好，但是函数内判断了如果被插件~~插~~(注册)了，就直接返回插件返回的结果……  
  
####插件原理说明  
插入了`('admin/header.php')->header`来重新处理整个后台页面，实现自定义登录页面，隐藏了用户名输入，固定为`_Authenticator`，修改密码输入为两步验证的代码输入。  
  
用cookies来判断验证是否成功，显示系统的登录页面，因为未登录状态typecho并没有开启PHP SESSION支持，(为了系统性能)  
  
调用`loginFail`接口，也就是登录失败的接口，实现了用系统登录接口验证两步验证的代码。  
  
调用`loginSucceed`来清理保存验证的cookies  
  
####已知问题  
后台没法显示图片，或者是我不知道如何显示，反正就是没显示二维码，只给了一个二维码的网址，自己打开吧  
  
如果手机丢了，没法找回的哟，但是SecretKey保存在数据库`typecho_options/plugin:GAuthenticator`里，可以手动查询再次绑定  
  
####使用说明  
下载插件，修改文件名为`GAuthenticator`放到`/usr/plugins`目录，然后到后台启用  

插件默认关闭，首次开启需要扫描二维码绑定之后填写手机上显示的代码，验证成功之后才可以启用  

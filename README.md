# 停止维护公告

由于个人精力和生活原因，停止维护。

放弃所有权利，各位大神可自由开发，自由分发。

typecho 1.2+ 用户可使用 Vndroid 修复的Fork

https://github.com/Vndroid/GAuthenticator

本项目已归档






## 本版特点
相对于0.0.1版，0.0.2版的验证逻辑**全部更新**，推荐升级！
支持验证态保持，一次登录后，在session或cookie有效期内无需再次验证
废弃0.0.1使用的登录接口，采用插件内注册的Route来处理otp，无需等待tp返回的2s后验证
废弃0.0.1使用的插入点`header`，直接采用`common`插入

#### 兼容所有符合 [RFC6238](https://tools.ietf.org/html/rfc6238 "rfc6238") 规范的AuthOTP软件
- Microsoft Authenticator
- Google Authenticator
- 1Password
- Authy
- KeePass
- LastPass
- ...

## 更新说明

#### 0.0.6
- [change] 使用 `jquery-qrcode` 插件在浏览器端生成二维码(不再使用外站的API来生成二维码,保证Key的安全性).

#### 0.0.5
- [fix] 修复启用插件500错误，改为使用jQuery获取SecretKey显示二维码

#### 0.0.4
- [add] 支持后台直接显示二维码
- [fix] 修改为使用联图API显示二维码
- [fix] 修复博客名称为中文时扫描二维码提示错误
- [fix] 修复卸载的时候没有删除路由
- [fix] 登录成功后主动访问路由地址会显示一条msg 验证失败

#### 0.0.3
- [add] 更新支持记住本机

#### 0.0.2
+ 支持typecho最新版
+ 流程优化,符合大多数网站逻辑
 + 先验证登录信息
 + 然后再验证otp
+ 修复插入header导致的新版css错乱
+ 支持密码管理软件自动填充 (1password等)


## 食用方法
[下载插件](https://github.com/weicno/typecho-Authenticator/releases)，修改文件名为`GAuthenticator`放到`/usr/plugins`目录，然后到后台启用

插件默认关闭，首次开启需要**扫描二维码绑定**之后**填写手机上显示的代码**，验证成功之后才可以启用


## 请注意：从0.0.1升级到0.0.2+版本需要卸载重新安装！ ##

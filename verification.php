<?php
if (!defined('__TYPECHO_ADMIN__')) {
    exit;
}
list($prefixVersion, $suffixVersion) = explode('/', $options->version);
?><!DOCTYPE HTML>
<html class="no-js">
    <head>
        <meta charset="<?php $options->charset(); ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
        <meta name="renderer" content="webkit">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php _e('%s - %s - Powered by Typecho', 'Authenticator', $options->title); ?></title>
        <meta name="robots" content="noindex, nofollow">
        <?php echo $header; ?>
    </head>
    <body class="body-100">
    <!--[if lt IE 9]>
        <div class="message error browsehappy" role="dialog"><?php _e('当前网页 <strong>不支持</strong> 你正在使用的浏览器. 为了正常的访问, 请 <a href="http://browsehappy.com/">升级你的浏览器</a>'); ?>.</div>
    <![endif]-->
<div class="typecho-login-wrap">
    <div class="typecho-login">
        <h1><a href="http://typecho.org" class="i-logo">Typecho</a></h1>
        <form action="<?php $options->loginAction(); ?>" method="post" name="login" role="form">
            <p>
                <label for="name" class="sr-only"><?php _e('Authenticator验证码'); ?></label>
                <input type="text" id="name" name="name" value="_AuthenticatorCode" style="display:none;"/>
				<input type="password" id="password" name="password" class="text-l w-100" value="" placeholder="<?php _e('验证码'); ?>" />
            </p>
            <p class="submit">
                <button type="submit" class="btn btn-l w-100 primary"><?php _e('登录'); ?></button>
                <input type="hidden" name="referer" value="<?php echo htmlspecialchars($request->get('referer')); ?>" />
            </p>
        </form>
    </div>
</div>
<script src="<?php $options->adminStaticUrl('js', 'jquery.js?v=' . $suffixVersion); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'jquery-ui.js?v=' . $suffixVersion); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'typecho.js?v=' . $suffixVersion); ?>"></script>
<script>
    (function () {
        $(document).ready(function() {
            // 处理消息机制
            (function () {
                var prefix = '<?php echo Typecho_Cookie::getPrefix(); ?>',
                    cookies = {
                        notice      :   $.cookie(prefix + '__typecho_notice'),
                        noticeType  :   $.cookie(prefix + '__typecho_notice_type'),
                        highlight   :   $.cookie(prefix + '__typecho_notice_highlight')
                    },
                    path = '<?php echo Typecho_Cookie::getPath(); ?>';

                if (!!cookies.notice && 'success|notice|error'.indexOf(cookies.noticeType) >= 0) {
                    var head = $('.typecho-head-nav'),
                        p = $('<div class="message popup ' + cookies.noticeType + '">'
                        + '<ul><li>' + $.parseJSON(cookies.notice).join('</li><li>') 
                        + '</li></ul></div>'), offset = 0;

                    if (head.length > 0) {
                        p.insertAfter(head);
                        offset = head.outerHeight();
                    } else {
                        p.prependTo(document.body);
                    }

                    function checkScroll () {
                        if ($(window).scrollTop() >= offset) {
                            p.css({
                                'position'  :   'fixed',
                                'top'       :   0
                            });
                        } else {
                            p.css({
                                'position'  :   'absolute',
                                'top'       :   offset
                            });
                        }
                    }

                    $(window).scroll(function () {
                        checkScroll();
                    });

                    checkScroll();

                    p.slideDown(function () {
                        var t = $(this), color = '#C6D880';
                        
                        if (t.hasClass('error')) {
                            color = '#FBC2C4';
                        } else if (t.hasClass('notice')) {
                            color = '#FFD324';
                        }

                        t.effect('highlight', {color : color})
                            .delay(5000).slideUp(function () {
                            $(this).remove();
                        });
                    });

                    
                    $.cookie(prefix + '__typecho_notice', null, {path : path});
                    $.cookie(prefix + '__typecho_notice_type', null, {path : path});
                }

                if (cookies.highlight) {
                    $('#' + cookies.highlight).effect('highlight', 1000);
                    $.cookie(prefix + '__typecho_notice_highlight', null, {path : path});
                }
            })();


            // 导航菜单 tab 聚焦时展开下拉菜单
            (function () {
                $('#typecho-nav-list').find('.parent a').focus(function() {
                    $('#typecho-nav-list').find('.child').hide();
                    $(this).parents('.root').find('.child').show();
                });
                $('.operate').find('a').focus(function() {
                    $('#typecho-nav-list').find('.child').hide();
                });
            })();


            if ($('.typecho-login').length == 0) {
                $('a').each(function () {
                    var t = $(this), href = t.attr('href');

                    if ((href && href[0] == '#')
                        || /^<?php echo preg_quote($options->adminUrl, '/'); ?>.*$/.exec(href) 
                            || /^<?php echo substr(preg_quote(Typecho_Common::url('s', $options->index), '/'), 0, -1); ?>action\/[_a-zA-Z0-9\/]+.*$/.exec(href)) {
                        return;
                    }

                    t.attr('target', '_blank');
                });
            }
        });
    })();
</script>
    </body>
</html>
<?php exit; ?>

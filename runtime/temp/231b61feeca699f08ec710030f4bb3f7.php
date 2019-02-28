<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:97:"/Applications/xampp/xamppfiles/htdocs/huachuang/public/../application/admin/view/index/index.html";i:1547542403;}*/ ?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta name="renderer" content="webkit|ie-comp|ie-stand">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta http-equiv="Cache-Control" content="no-siteapp" />
	<link rel="Bookmark" href="/favicon.ico" >
	<link rel="Shortcut Icon" href="/favicon.ico" />
	<!--[if lt IE 9]>
	<script type="text/javascript" src="/static/lib/html5shiv.js"></script>
	<script type="text/javascript" src="/static/lib/respond.min.js"></script>
	<![endif]-->
	<link rel="stylesheet" type="text/css" href="/static/h-ui/css/H-ui.min.css" />
	<link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/H-ui.admin.css" />
	<link rel="stylesheet" type="text/css" href="/static/lib/Hui-iconfont/1.0.8/iconfont.css" />
	<link rel="stylesheet" type="text/css" href="/static/h-ui.admin/skin/default/skin.css" id="skin" />
	<link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/style.css" />
	<!--[if IE 6]>
	<script type="text/javascript" src="/static/lib/DD_belatedPNG_0.0.8a-min.js" ></script>
	<script>DD_belatedPNG.fix('*');</script>
	<![endif]-->
	<title>华创盛星-管理后台主页</title>
	<meta name="keywords" content="H-ui.admin v3.1">
	<meta name="description" content="H-ui.admin">
</head>
<body>
	<header class="navbar-wrapper">
		<div class="navbar navbar-fixed-top">
			<div class="container-fluid cl">
				<a class="logo navbar-logo f-l mr-10 hidden-xs" href="javascript:;">华创盛星</a>
				<span class="logo navbar-slogan f-l mr-10 hidden-xs">v1.0</span> 
			</nav>
			<nav id="Hui-userbar" class="nav navbar-nav navbar-userbar hidden-xs">
				<ul class="cl">
					<li><?php echo $data['roles_name']; ?></li>
					<li class="dropDown dropDown_hover">
						<a href="#" class="dropDown_A"><?php echo $data['admin_name']; ?> <i class="Hui-iconfont">&#xe6d5;</i></a>
						<ul class="dropDown-menu menu radius box-shadow">
							<li><a href="javascript:;" onClick="changepasswd()">修改密码</a></li>
							<li><a href="<?php echo url('login/loginout'); ?>">退出</a></li>
						</ul>
					</li>
					<li id="Hui-skin" class="dropDown right dropDown_hover"> 
						<a href="javascript:;" class="dropDown_A" title="换肤">
							<i class="Hui-iconfont" style="font-size:18px">&#xe62a;</i>
						</a>
						<ul class="dropDown-menu menu radius box-shadow">
							<li><a href="javascript:;" data-val="default" title="默认（黑色）">默认（黑色）</a></li>
							<li><a href="javascript:;" data-val="blue" title="蓝色">蓝色</a></li>
							<li><a href="javascript:;" data-val="green" title="绿色">绿色</a></li>
							<li><a href="javascript:;" data-val="red" title="红色">红色</a></li>
							<li><a href="javascript:;" data-val="yellow" title="黄色">黄色</a></li>
							<li><a href="javascript:;" data-val="orange" title="橙色">橙色</a></li>
						</ul>
					</li>
				</ul>
			</nav>
		</div>
	</div>
	</header>
	<aside class="Hui-aside">
		<?php if(is_array($menu) || $menu instanceof \think\Collection || $menu instanceof \think\Paginator): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu1): $mod = ($i % 2 );++$i;?>
		<div class="menu_dropdown bk_2">
			<dl id="menu-admin">
				<dt><i class="Hui-iconfont">&#xe62d;</i> <?php echo $menu1['name']; ?><i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
				<dd>
					<ul>
						<?php if(is_array($menu1['child']) || $menu1['child'] instanceof \think\Collection || $menu1['child'] instanceof \think\Paginator): $i = 0; $__LIST__ = $menu1['child'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu2): $mod = ($i % 2 );++$i;?>
						<li>
							<a data-href="<?php echo $menu2['url']; ?>" data-title="<?php echo $menu2['name']; ?>" href="javascript:void(0)"><?php echo $menu2['name']; ?></a>
						</li>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</ul>
				</dd>
			</dl>
		</div>
		<?php endforeach; endif; else: echo "" ;endif; ?>
	</aside>
	<div class="dislpayArrow hidden-xs">
		<a class="pngfix" href="javascript:void(0);" onClick="displaynavbar(this)"></a>
	</div>
	<section class="Hui-article-box">
		<div id="Hui-tabNav" class="Hui-tabNav hidden-xs">
			<div class="Hui-tabNav-wp">
				<ul id="min_title_list" class="acrossTab cl">
					<li class="active">
						<span title="我的桌面" data-href="welcome.html">我的桌面</span>
						<em></em>
					</li>
				</ul>
			</div>
			<div class="Hui-tabNav-more btn-group">
				<a id="js-tabNav-prev" class="btn radius btn-default size-S" href="javascript:;">
					<i class="Hui-iconfont">&#xe6d4;</i>
				</a>
				<a id="js-tabNav-next" class="btn radius btn-default size-S" href="javascript:;">
					<i class="Hui-iconfont">&#xe6d7;</i>
				</a>
			</div>
		</div>
		<div id="iframe_box" class="Hui-article">
			<div class="show_iframe">
				<div style="display:none" class="loading"></div>
				<iframe scrolling="yes" frameborder="0" src="/admin/index/welcome.html"></iframe>
		</div>
	</div>
	</section>

	<div class="contextMenu" id="Huiadminmenu">
		<ul>
			<li id="closethis">关闭当前 </li>
			<li id="closeall">关闭全部 </li>
		</ul>
	</div>
	<!--_footer 作为公共模版分离出去-->
	<script type="text/javascript" src="/static/lib/jquery/1.9.1/jquery.min.js"></script> 
	<script type="text/javascript" src="/static/lib/layer/2.4/layer.js"></script>
	<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script>
	<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.js"></script> <!--/_footer 作为公共模版分离出去-->

	<!--请在下方写此页面业务相关的脚本-->
	<script type="text/javascript" src="/static/lib/jquery.contextmenu/jquery.contextmenu.r2.js"></script>
	<script type="text/javascript">
		//修改密码、弹框
		function changepasswd(){
			layer.open({
				type: 1,
				area: ['375px','500px'],
				fix: false, //不固定
				maxmin: true,
				shade:0.4,
				title: '修改密码',
				content: '<article class="page-container"><form class="form form-horizontal" id="form-admin-add"><div class="row cl"><label class="form-label col-xs-4 col-sm-3"><span class="c-red"> </span>新密码：</label><div class="formControls col-xs-8 col-sm-9"><input type="hidden" name="id" value="<?php echo $data['admin']['id']; ?>"><input type="password" class="input-text" autocomplete="off" value="" placeholder="请输入新的密码" id="password" name="password"></div></div><div class="row cl"><div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3"><input class="btn btn-primary radius" type="button" value="&nbsp;&nbsp;提交&nbsp;&nbsp;" onclick="submitpasswd()"></div></div></form></article>'
			});
		}
		//修改密码、提交
		function submitpasswd(){
			var pw = $("#password").val();
			if (pw == '') {
				layer.msg('密码不能为空',{icon:2,time:2000});
				return;
			}
			if (pw.length < 6) {
				layer.msg('密码不能小于6位',{icon:2,time:2000});
				return;
			}
			$.ajax({
				type: 'POST',
				url: '/admin/index/changepw.html',
				data:$("#form-admin-add").serialize(),
				dataType: 'json',
				success: function(data){
					if (data.status == 200) {
						$('.layui-layer-close').click();
						layer.msg(data.msg,{icon:1,time:2000});
					}else{
						layer.msg(data.msg,{icon:2,time:2000});
					}
				},
				error:function(data) {
					alert(data.msg);
				},
			});
		}
		/*资讯-添加*/
		function article_add(title,url){
			var index = layer.open({
				type: 2,
				title: title,
				content: url
			});
			layer.full(index);
		}
		/*图片-添加*/
		function picture_add(title,url){
			var index = layer.open({
				type: 2,
				title: title,
				content: url
			});
			layer.full(index);
		}
		/*产品-添加*/
		function product_add(title,url){
			var index = layer.open({
				type: 2,
				title: title,
				content: url
			});
			layer.full(index);
		}
		/*用户-添加*/
		function member_add(title,url,w,h){
			layer_show(title,url,w,h);
		}
	</script> 
</body>
</html>
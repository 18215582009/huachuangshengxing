<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:97:"/Applications/xampp/xamppfiles/htdocs/huachuang/public/../application/admin/view/onoff/index.html";i:1548323204;}*/ ?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta name="renderer" content="webkit|ie-comp|ie-stand">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta http-equiv="Cache-Control" content="no-siteapp" />
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
	<title>开关列表</title>
	<style type="text/css">
		.dataTables_info {clear: both;float: left;padding-top: 10px;font-size: 14px;color: #666;}
		.pagination{float: right;padding-top: 10px;text-align: right;}
		.pagination>li{border: 1px solid #ccc;cursor: pointer;display: inline-block;margin-left: 2px;text-align: center;text-decoration: none;color: #666;height: 26px;line-height: 26px;text-decoration: none;margin: 0 0 6px 6px;padding: 0 10px;font-size: 14px;}
	</style>
</head>
<body>
	<nav class="breadcrumb">
		<i class="Hui-iconfont">&#xe67f;</i> 首页 
		<span class="c-gray en">&gt;</span> 开关管理 
		<span class="c-gray en">&gt;</span> 开关列表 
		<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" >
			<i class="Hui-iconfont">&#xe68f;</i>
		</a>
	</nav>
	<div class="page-container">
		<table class="table table-border table-bordered table-hover table-bg">
			<thead>
				<tr>
					<th scope="col" colspan="6">详细列表</th>
				</tr>
				<tr class="text-c">
					<th width="25"><input type="checkbox" value="" name=""></th>
					<th width="40">ID</th>
					<th width="200">开关位置</th>
					<th>状态</th>
					<th width="300">描述</th>
					<th width="70">操作</th>
				</tr>
			</thead>
			<tbody>
				<tr class="text-c">
					<td><input type="checkbox" value="" name=""></td>
					<td>1</td>
					<td>匹配学校开关</td>
					<td class="td-status">
						<?php if(($data['school_lock'] == 1)): ?>
						<a href="#" title="" style="color:green;">
							<i class="Hui-iconfont">&#xe619;</i>已启用
						</a>
						<?php elseif(($data['school_lock'] == 0)): ?>
						<a href="#" title="" style="color:#333;">
							<i class="Hui-iconfont">&#xe619;</i>未启用
						</a>
						<?php else: ?>
						<a href="#" title="" style="color:red;">
							<i class="Hui-iconfont">&#xe619;</i>开关文件不存在
						</a>
						<?php endif; ?>
					</td>
					<td>
						开启后，每个用户只能匹配到自己学校的人
					</td>
					<td class="td-manage">
						<?php if(($data['school_lock'] == 1)): if(( action('Base/is_power','/admin/onoff/closeschool.html') )): ?>
							<a style="text-decoration:none" onClick="admin_stop(this,id)" href="javascript:;" title="停用">
								<i class="Hui-iconfont">&#xe631;</i>
							</a>
							<?php endif; else: if(( action('Base/is_power','/admin/onoff/openschool.html') )): ?>
							<a style="text-decoration:none" onClick="admin_start(this,id)" href="javascript:;" title="启用">
								<i class="Hui-iconfont">&#xe615;</i>
							</a>
							<?php endif; endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<!--_footer 作为公共模版分离出去-->
	<script type="text/javascript" src="/static/lib/jquery/1.9.1/jquery.min.js"></script> 
	<script type="text/javascript" src="/static/lib/layer/2.4/layer.js"></script>
	<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script> 
	<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.js"></script>
	<!--/_footer 作为公共模版分离出去-->
	<!--请在下方写此页面业务相关的脚本-->
	<script type="text/javascript" src="/static/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>
	<script type="text/javascript">
		/*管理员-停用*/
		function admin_stop(obj,id){
			layer.confirm('确认要停用吗？',function(index){
				$.ajax({
					type: 'POST',
					url: '/admin/onoff/closeschool.html?v=hcsxschoollockclose',
					data:{id:id,status:0},
					dataType: 'json',
					success: function(data){
						console.log(data);
						if (data.status == 200) {
							//此处请求后台程序，下方是成功后的前台处理……
							$(obj).parents("tr").find(".td-manage").prepend('<a onClick="admin_start(this,id)" href="javascript:;" title="启用" style="text-decoration:none"><i class="Hui-iconfont">&#xe615;</i></a>');
							$(obj).parents("tr").find(".td-status").html('<a href="#" title="" style="color:#333;"><i class="Hui-iconfont">&#xe619;</i>未启用</a>');
							$(obj).remove();
							layer.msg(data.msg,{icon: 6,time:1000});
						}else{
							layer.msg(data.msg,{icon: 5,time:1000});
						}
					},
					error:function(data) {
						alert(data.msg);
					},
				});
			});
		}
		/*管理员-启用*/
		function admin_start(obj,id){
			layer.confirm('确认要启用吗？',function(index){
				$.ajax({
					type: 'POST',
					url: '/admin/onoff/openschool.html?v=hcsxschoollockopen',
					data:{id:id,status:1},
					dataType: 'json',
					success: function(data){
						console.log(data);
						if (data.status == 200) {
							//此处请求后台程序，下方是成功后的前台处理……
							$(obj).parents("tr").find(".td-manage").prepend('<a onClick="admin_stop(this,id)" href="javascript:;" title="停用" style="text-decoration:none"><i class="Hui-iconfont">&#xe631;</i></a>');
							$(obj).parents("tr").find(".td-status").html('<a href="#" title="" style="color:green;"><i class="Hui-iconfont">&#xe619;</i>已启用</a>');
							$(obj).remove();
							layer.msg(data.msg,{icon: 6,time:1000});
						}else{
							layer.msg(data.msg,{icon: 5,time:1000});
						}
					},
					error:function(data) {
						alert(data.msg);
					},
				});
			});
		}
	</script>
</body>
</html>
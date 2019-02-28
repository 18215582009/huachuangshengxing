<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:99:"/Applications/xampp/xamppfiles/htdocs/huachuang/public/../application/admin/view/aboutus/index.html";i:1548323204;}*/ ?>
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
	<title>协议管理</title>
	<style type="text/css">
		.dataTables_info {clear: both;float: left;padding-top: 10px;font-size: 14px;color: #666;}
		.pagination{float: right;padding-top: 10px;text-align: right;}
		.pagination>li{border: 1px solid #ccc;cursor: pointer;display: inline-block;margin-left: 2px;text-align: center;text-decoration: none;color: #666;height: 26px;line-height: 26px;text-decoration: none;margin: 0 0 6px 6px;padding: 0 10px;font-size: 14px;}
	</style>
</head>
<body>
	<nav class="breadcrumb">
		<i class="Hui-iconfont">&#xe67f;</i> 首页 
		<span class="c-gray en">&gt;</span> 文案管理 
		<span class="c-gray en">&gt;</span> 协议管理 
		<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" >
			<i class="Hui-iconfont">&#xe68f;</i>
		</a>
	</nav>
	<div class="page-container">
		<div class="cl pd-5 bg-1 bk-gray "> 
			<span class="l">
				<!-- <a href="javascript:;" onclick="datadel()" class="btn btn-danger radius">
					<i class="Hui-iconfont">&#xe6e2;</i> 批量删除
				</a>  -->
				<?php if(( action('Base/is_power','/admin/aboutus/add.html') )): ?>
				<a href="javascript:;" onclick="article_add('添加','/admin/aboutus/add.html','800','500')" class="btn btn-primary radius">
					<i class="Hui-iconfont">&#xe600;</i> 添加
				</a>
				<?php endif; ?>
			</span> 
			<span class="r">共有数据：<strong><?php echo $page['total']; ?></strong> 条</span> </div>
		<div class="mt-20">
			<table class="table table-border table-bordered table-bg table-hover table-sort table-responsive">
				<thead>
					<tr class="text-c">
						<th width="25"><input type="checkbox" name="" value=""></th>
						<th width="80">ID</th>
						<th width="100">分类</th>
						<th width="100">标题</th>
						<th width="180">内容</th>
						<th width="100">URL</th>
						<th width="130">创建时间</th>
						<th width="100">状态</th>
						<th width="120">操作</th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
					<tr class="text-c">
						<td><input type="checkbox" value="" name=""></td>
						<td><?php echo $vo['id']; ?></td>
						<td><?php echo $vo['classname']; ?></td>
						<td class="text-l">
							<u style="cursor:pointer" class="text-primary" onClick="article_show('查看','/<?php echo $vo['url']; ?>','375','500')" title="查看"><?php echo $vo['title']; ?></u>
						</td>
						<td>←点击左边标题查看</td>
						<td><?php echo $vo['url']; ?></td>
						<td><?php echo $vo['create_time']; ?></td>
						<td class="td-status">
							<?php if(($vo['is_used'] == 0)): ?>
							<span class="label label-default radius">已停用</span>
							<?php else: ?>
							<span class="label label-success radius">已启用</span>
							<?php endif; ?>
						</td>
						<td class="f-14 td-manage">
							<?php if(($vo['is_used'] == 1)): if(( action('Base/is_power','/admin/aboutus/stop.html') )): ?>
							<a style="text-decoration:none" onClick="admin_stop(this,'<?php echo $vo['id']; ?>')" href="javascript:;" title="停用">
								<i class="Hui-iconfont">&#xe631;</i>
							</a>
							<?php endif; else: if(( action('Base/is_power','/admin/aboutus/start.html') )): ?>
							<a style="text-decoration:none" onClick="admin_start(this,'<?php echo $vo['id']; ?>')" href="javascript:;" title="启用">
								<i class="Hui-iconfont">&#xe615;</i>
							</a>
							<?php endif; endif; if(( action('Base/is_power','/admin/aboutus/edit.html') )): ?>
							<a style="text-decoration:none" class="ml-5" onClick="article_edit('编辑','/admin/aboutus/edit.html?id=<?php echo $vo['id']; ?>','')" href="javascript:;" title="编辑">
								<i class="Hui-iconfont">&#xe6df;</i>
							</a> 
							<?php endif; if(( action('Base/is_power','/admin/aboutus/del.html') )): ?>
							<a style="text-decoration:none" class="ml-5" onClick="article_del(this,'<?php echo $vo['id']; ?>')" href="javascript:;" title="删除">
								<i class="Hui-iconfont">&#xe6e2;</i>
							</a>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; endif; else: echo "" ;endif; ?>
				</tbody>
			</table>
			<?php if(($page['last']>1)): ?>
			<div class="dataTables_info">当前第 <?php echo $page['current']; ?> 页 共 <?php echo $page['last']; ?> 页</div>
			<?php echo $page_data->render(); endif; ?>
		</div>
	</div>
	<!--_footer 作为公共模版分离出去-->
	<script type="text/javascript" src="/static/lib/jquery/1.9.1/jquery.min.js"></script> 
	<script type="text/javascript" src="/static/lib/layer/2.4/layer.js"></script>
	<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script> 
	<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.js"></script> 
	<!--/_footer 作为公共模版分离出去-->
	<!--请在下方写此页面业务相关的脚本-->
	<script type="text/javascript" src="/static/lib/My97DatePicker/4.8/WdatePicker.js"></script> 
	<script type="text/javascript" src="/static/lib/datatables/1.10.0/jquery.dataTables.min.js"></script> 
	<script type="text/javascript" src="/static/lib/laypage/1.2/laypage.js"></script>
	<script type="text/javascript">
		//查看
		function article_show(title,url,w,h){
			layer_show(title,url,w,h);
		}
		/*资讯-添加*/
		function article_add(title,url,w,h){
			layer_show(title,url,w,h);
		}
		/*资讯-编辑*/
		function article_edit(title,url,w,h){
			layer_show(title,url,w,h);
		}
		/*资讯-删除*/
		function article_del(obj,id){
			layer.confirm('确认要删除吗？',function(index){
				$.ajax({
					type: 'POST',
					url: '/admin/aboutus/del.html',
					data: {id:id},
					dataType: 'json',
					success: function(data){
						if (data.status == 200) {
							$(obj).parents("tr").remove();
							layer.msg(data.msg,{icon:1,time:2000});
						}else{
							layer.msg(data.msg,{icon:2,time:2000});
						}
					},
					error:function(data) {
						alert(data.msg);
					},
				});		
			});
		}
		/*管理员-停用*/
		function admin_stop(obj,id){
			layer.confirm('确认要停用吗？',function(index){
				$.ajax({
					type: 'POST',
					url: '/admin/aboutus/stop.html',
					data:{id:id,status:0},
					dataType: 'json',
					success: function(data){
						console.log(data);
						if (data.status == 200) {
							//此处请求后台程序，下方是成功后的前台处理……
							$(obj).parents("tr").find(".td-manage").prepend('<a onClick="admin_start(this,id)" href="javascript:;" title="启用" style="text-decoration:none"><i class="Hui-iconfont">&#xe615;</i></a>');
							$(obj).parents("tr").find(".td-status").html('<span class="label label-default radius">已禁用</span>');
							$(obj).remove();
							layer.msg(data.msg,{icon: 5,time:1000});
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
					url: '/admin/aboutus/start.html',
					data:{id:id,status:1},
					dataType: 'json',
					success: function(data){
						console.log(data);
						if (data.status == 200) {
							//此处请求后台程序，下方是成功后的前台处理……
							$(obj).parents("tr").find(".td-manage").prepend('<a onClick="admin_stop(this,id)" href="javascript:;" title="停用" style="text-decoration:none"><i class="Hui-iconfont">&#xe631;</i></a>');
							$(obj).parents("tr").find(".td-status").html('<span class="label label-success radius">已启用</span>');
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
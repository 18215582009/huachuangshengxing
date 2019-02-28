<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:97:"/Applications/xampp/xamppfiles/htdocs/huachuang/public/../application/admin/view/roles/index.html";i:1548323204;}*/ ?>
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
	<title>角色管理</title>
	<style type="text/css">
		.dataTables_info {clear: both;float: left;padding-top: 10px;font-size: 14px;color: #666;}
		.pagination{float: right;padding-top: 10px;text-align: right;}
		.pagination>li{border: 1px solid #ccc;cursor: pointer;display: inline-block;margin-left: 2px;text-align: center;text-decoration: none;color: #666;height: 26px;line-height: 26px;text-decoration: none;margin: 0 0 6px 6px;padding: 0 10px;font-size: 14px;}
	</style>
</head>
<body>
	<nav class="breadcrumb">
		<i class="Hui-iconfont">&#xe67f;</i> 首页 
		<span class="c-gray en">&gt;</span> 管理员管理 
		<span class="c-gray en">&gt;</span> 角色管理 
		<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" >
			<i class="Hui-iconfont">&#xe68f;</i>
		</a>
	</nav>
	<div class="page-container">
		<div class="cl pd-5 bg-1 bk-gray">
			<span class="l">
				<!-- <a href="javascript:;" onclick="datadel()" class="btn btn-danger radius">
					<i class="Hui-iconfont">&#xe6e2;</i> 批量删除
				</a> -->
				<?php if(( action('Base/is_power','/admin/roles/add.html') )): ?>
				<a class="btn btn-primary radius" href="javascript:;" onclick="admin_role_add('添加角色','/admin/roles/add.html','800')">
					<i class="Hui-iconfont">&#xe600;</i> 添加角色
				</a>
				<?php endif; ?>
			</span>
			<span class="r">共有数据：<strong><?php echo $page['total']; ?></strong> 条</span>
		</div>
		<table class="table table-border table-bordered table-hover table-bg">
			<thead>
				<tr>
					<th scope="col" colspan="6">详细列表</th>
				</tr>
				<tr class="text-c">
					<th width="25"><input type="checkbox" value="" name=""></th>
					<th width="40">ID</th>
					<th width="200">角色名</th>
					<th>管理用户列表</th>
					<th width="300">描述</th>
					<th width="70">操作</th>
				</tr>
			</thead>
			<tbody>
				<?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$roles): $mod = ($i % 2 );++$i;?>
				<tr class="text-c">
					<td><input type="checkbox" value="" name=""></td>
					<td><?php echo $roles['id']; ?></td>
					<td><?php echo $roles['name']; ?></td>
					<td>
						<?php if(is_array($roles['admins']) || $roles['admins'] instanceof \think\Collection || $roles['admins'] instanceof \think\Paginator): $i = 0; $__LIST__ = $roles['admins'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$adm): $mod = ($i % 2 );++$i;?>
						<a href="#" title="<?php echo $adm['name']; ?>"><?php echo $adm['account']; ?></a>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</td>
					<td><?php echo $roles['description']; ?></td>
					<td class="f-14">
						<?php if(( action('Base/is_power','/admin/roles/edit.html') )): ?>
						<a title="编辑" href="javascript:;" onclick="admin_role_edit('角色编辑','/admin/roles/edit.html?id=<?php echo $roles['id']; ?>','1')" style="text-decoration:none">
							<i class="Hui-iconfont">&#xe6df;</i>
						</a>
						<?php endif; if(( action('Base/is_power','/admin/roles/del.html') )): ?>
						<a title="删除" href="javascript:;" onclick="admin_role_del(this,'<?php echo $roles['id']; ?>')" class="ml-5" style="text-decoration:none">
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
	<!--_footer 作为公共模版分离出去-->
	<script type="text/javascript" src="/static/lib/jquery/1.9.1/jquery.min.js"></script> 
	<script type="text/javascript" src="/static/lib/layer/2.4/layer.js"></script>
	<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script> 
	<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.js"></script>
	<!--/_footer 作为公共模版分离出去-->
	<!--请在下方写此页面业务相关的脚本-->
	<script type="text/javascript" src="/static/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>
	<script type="text/javascript">
		/*管理员-角色-添加*/
		function admin_role_add(title,url,w,h){
			layer_show(title,url,w,h);
		}
		/*管理员-角色-编辑*/
		function admin_role_edit(title,url,id,w,h){
			layer_show(title,url,w,h);
		}
		/*管理员-角色-删除*/
		function admin_role_del(obj,id){
			layer.confirm('角色删除须谨慎，确认要删除吗？',function(index){
				$.ajax({
					type: 'POST',
					url: '/admin/roles/del.html',
					data:{id:id},
					dataType: 'json',
					success: function(data){
						if (data.status==200) {
							$(obj).parents("tr").remove();
							layer.msg(data.msg,{icon:1,time:2000});
						}else{
							layer.msg(data.msg,{icon:2,time:2000});
						}
					},
					error:function(data) {
						console.log(data.msg);
					},
				});		
			});
		}
	</script>
</body>
</html>
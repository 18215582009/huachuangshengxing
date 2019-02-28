<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:96:"/Applications/xampp/xamppfiles/htdocs/huachuang/public/../application/admin/view/user/index.html";i:1548829308;}*/ ?>
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
	<title>会员列表</title>
	<style type="text/css">
		.dataTables_info {clear: both;float: left;padding-top: 10px;font-size: 14px;color: #666;}
		.pagination{float: right;padding-top: 10px;text-align: right;}
		.pagination>li{border: 1px solid #ccc;cursor: pointer;display: inline-block;margin-left: 2px;text-align: center;text-decoration: none;color: #666;height: 26px;line-height: 26px;text-decoration: none;margin: 0 0 6px 6px;padding: 0 10px;font-size: 14px;}
		.fb-1{height:20px;}
		.fb-1>img{display:block;height:100%;float:left;cursor:pointer;}
	</style>
</head>
<body>
	<nav class="breadcrumb">
		<i class="Hui-iconfont">&#xe67f;</i> 首页 
		<span class="c-gray en">&gt;</span> 会员管理 
		<span class="c-gray en">&gt;</span> 会员列表 
		<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" >
			<i class="Hui-iconfont">&#xe68f;</i>
		</a>
	</nav>
	<div class="page-container">
		<form action="" method="get">
		<div class="text-c"> 日期范围：
			<input type="text" onfocus="WdatePicker({ maxDate:'#F{$dp.$D(\'datemax\')||\'%y-%M-%d\'}' })" id="datemin" class="input-text Wdate" style="width:120px;" name="datemin" readonly value="<?php echo $fanye['datemin']; ?>">
			-
			<input type="text" onfocus="WdatePicker({ minDate:'#F{$dp.$D(\'datemin\')}',maxDate:'%y-%M-%d' })" id="datemax" class="input-text Wdate" style="width:120px;" name="datemax" readonly value="<?php echo $fanye['datemax']; ?>">
			<span class="select-box inline">
				<select name="xz" class="select">
					<option value="">请选择</option>
					<option value="1" <?php if(($fanye['xz']==1)): ?> selected <?php endif; ?>>昵称</option>
					<option value="2" <?php if(($fanye['xz']==2)): ?> selected <?php endif; ?>>账号</option>
					<option value="3" <?php if(($fanye['xz']==3)): ?> selected <?php endif; ?>>电话</option>
				</select>
			</span>
			<input type="text" class="input-text" style="width:200px" placeholder="输入会员昵称、账号、电话" name="word" value="<?php echo $fanye['word']; ?>">
			<button type="submit" class="btn btn-success radius">
				<i class="Hui-iconfont">&#xe665;</i> 搜用户
			</button>
		</div>
		</form>
		<div class="cl pd-5 bg-1 bk-gray mt-20"> 
			<span class="l">
				<!-- <a href="javascript:;" onclick="member_add('添加用户','/admin/user/add.html','800','500')" class="btn btn-primary radius">
					<i class="Hui-iconfont">&#xe600;</i> 添加用户
				</a> -->
			</span> 
			<span class="r">共有数据：<strong><?php echo $page['total']; ?></strong> 条</span> 
		</div>
		<div class="mt-20">
		<table class="table table-border table-bordered table-hover table-bg table-sort">
			<thead>
				<tr class="text-c">
					<th width="25"><input type="checkbox" name="" value=""></th>
					<th width="60">ID</th>
					<th>用户信息</th>
					<th>vip等级</th>
					<th>小秘书</th>
					<th>实名认证</th>
					<th>状态</th>
					<th width="100">操作</th>
				</tr>
			</thead>
			<tbody>
				<?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
				<tr class="text-c">
					<td><input type="checkbox" value="<?php echo $vo['user_id']; ?>" name=""></td>
					<td><?php echo $vo['user_id']; ?></td>
					<td class="text-l">
						<div class="c-999 f-12">
							<u style="cursor:pointer" class="text-primary" onclick="member_show('<?php echo $vo['nickname']; ?>','','800','500')"><?php echo $vo['nickname']; ?>
							</u> 
							<span class="ml-20"><?php echo $vo['sex']; ?></span> 
							<span class="ml-20"><?php echo $vo['username']; ?></span> 
							<span class="ml-20"><?php echo $vo['phone']; ?></span> 
							<span class="ml-20"><?php echo $vo['address']; ?></span>
							<span class="ml-20"><?php echo $vo['sname']; ?></span>
							<!-- <span class="ml-20"><?php echo $vo['email']; ?></span> -->
							<span class="ml-20"><?php echo $vo['create_time']; ?></span>
						</div>
						<div>
							
						</div>
						<div class="fb-1">
							<img src="/<?php echo $vo['head_image']; ?>" onclick="member_show('<?php echo $vo['nickname']; ?>','/<?php echo $vo['head_image']; ?>','500','500')">
						</div>
					</td>
					<td><?php echo $vo['vip']; ?></td>
					<td><?php echo $vo['sec_name']; ?></td>
					<td><?php echo $vo['real']; ?></td>
					<td class="td-status">
						<?php if(($vo['status']==1)): ?>
						<span class="label label-success radius">已启用</span>
						<?php endif; if(($vo['status']==2)): ?>
						<span class="label label-defaunt radius">已停用</span>
						<?php endif; ?>
					</td>
					<td class="td-manage">
						<?php if(($vo['status']==1)): ?>
						<a style="text-decoration:none" onClick="member_stop(this,<?php echo $vo['user_id']; ?>)" href="javascript:;" title="停用">
							<i class="Hui-iconfont">&#xe631;</i>
						</a> 
						<?php endif; if(($vo['status']==2)): ?>
						<a style="text-decoration:none" onClick="member_start(this,<?php echo $vo['user_id']; ?>)" href="javascript:;" title="启用">
							<i class="Hui-iconfont">&#xe6e1;</i>
						</a>
						<?php endif; ?>
						<a title="编辑" href="javascript:;" onclick="member_edit('编辑','/admin/user/edit.html?id=<?php echo $vo['user_id']; ?>','800','500')" class="ml-5" style="text-decoration:none">
							<i class="Hui-iconfont">&#xe6df;</i>
						</a> 
						<a style="text-decoration:none" class="ml-5" onClick="change_password('修改密码','/admin/user/changepasswd.html?id=<?php echo $vo['user_id']; ?>','800','500')" href="javascript:;" title="修改密码">
							<i class="Hui-iconfont">&#xe63f;</i>
						</a> 
						<a title="删除" href="javascript:;" onclick="member_del(this,<?php echo $vo['user_id']; ?>)" class="ml-5" style="text-decoration:none">
							<i class="Hui-iconfont">&#xe6e2;</i>
						</a>
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
	<script type="text/javascript" src="/static/lib/jquery/1.9.1/jquery.min.js"></script> 
	<script type="text/javascript" src="/static/lib/layer/2.4/layer.js"></script>
	<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script> 
	<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.js"></script>
	<script type="text/javascript" src="/static/lib/My97DatePicker/4.8/WdatePicker.js"></script> 
	<script type="text/javascript" src="/static/lib/datatables/1.10.0/jquery.dataTables.min.js"></script> 
	<script type="text/javascript">
		/*用户-添加*/
		function member_add(title,url,w,h){
			layer_show(title,url,w,h);
		}
		/*用户-查看*/
		function member_show(title,url,w,h){
			layer_show(title,url,w,h);
		}
		/*用户-停用*/
		function member_stop(obj,id){
			layer.confirm('确认要停用吗？',function(index){
				$.ajax({
					type: 'POST',
					url: '/admin/user/stop.html',
					data:{id:id,status:1},
					dataType: 'json',
					success: function(data){
						if (data.status == 200) {
							$(obj).parents("tr").find(".td-manage").prepend('<a style="text-decoration:none" onClick="member_start(this,'+id+')" href="javascript:;" title="启用"><i class="Hui-iconfont">&#xe6e1;</i></a>');
							$(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius">已停用</span>');
							$(obj).remove();
							layer.msg(data.msg,{icon: 5,time:1000});
						}else{
							layer.msg(data.msg,{icon: 5,time:1000});
						}
					},
					error:function(data) {
						console.log(data.msg);
					},
				});		
			});
		}

		/*用户-启用*/
		function member_start(obj,id){
			layer.confirm('确认要启用吗？',function(index){
				$.ajax({
					type: 'POST',
					url: '/admin/user/start.html',
					data:{id:id,status:2},
					dataType: 'json',
					success: function(data){
						if (data.status == 200) {
							$(obj).parents("tr").find(".td-manage").prepend('<a style="text-decoration:none" onClick="member_stop(this,'+id+')" href="javascript:;" title="停用"><i class="Hui-iconfont">&#xe631;</i></a>');
							$(obj).parents("tr").find(".td-status").html('<span class="label label-success radius">已启用</span>');
							$(obj).remove();
							layer.msg(data.msg,{icon: 6,time:1000});
						}else{
							layer.msg(data.msg,{icon: 5,time:1000});
						}
					},
					error:function(data) {
						console.log(data.msg);
					},
				});
			});
		}
		/*用户-编辑*/
		function member_edit(title,url,w,h){
			layer_show(title,url,w,h);
		}
		/*密码-修改*/
		function change_password(title,url,w,h){
			layer_show(title,url,w,h);	
		}
		/*用户-删除*/
		function member_del(obj,id){
			layer.confirm('确认要删除吗？',function(index){
				$.ajax({
					type: 'POST',
					url: '/admin/user/del.html',
					data:{id:id},
					dataType: 'json',
					success: function(data){
						if (data.status == 200) {
							$(obj).parents("tr").remove();
							layer.msg(data.msg,{icon:1,time:1000});
						}else{
							layer.msg(data.msg,{icon:2,time:1000});
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